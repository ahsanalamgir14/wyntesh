<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function (Request $request) {
    return 'Working';
});

//Route::get('migration', '\App\Http\Controllers\Admin\MigrationController@doMigration');

Route::get('edu', 'Auth\AuthController@edu');
Route::get('sms', '\App\Http\Controllers\User\MembersController@sendSMS');

Route::get('geneology', '\App\Http\Controllers\User\MembersController@adminGeneology');
Route::get('download-file', 'Admin\SettingsController@downloadFile');
Route::get('settings','\App\Http\Controllers\User\SettingsController@getCopanyDetailsSettings');

Route::get('country', '\App\Http\Controllers\User\ConfigController@getCountry');
Route::get('cities/{state}', '\App\Http\Controllers\User\ConfigController@getStateCities');
Route::get('states/{country}', '\App\Http\Controllers\User\ConfigController@getCountryStates');

// Authentication Routes
Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', 'Auth\AuthController@login');
    Route::post('a/login', 'Auth\AuthController@admin_login');
    Route::post('register', 'Auth\AuthController@register'); 
    Route::post('auth/google', 'Auth\AuthController@google');
    Route::post('change-password', 'Auth\AuthController@changePassword');
    Route::post('logout', 'Auth\AuthController@logout');
    Route::post('refresh', 'Auth\AuthController@refresh');
    Route::get('me', 'Auth\AuthController@me');

    //Route::get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
    Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
    Route::get('email-verify', 'Auth\AuthController@emailVerify');
});


Route::post('public/inquiry/submit', 'Admin\InquiriesController@store');
Route::post('public/subscribe', 'Admin\SubscribersController@store');
Route::post('auth/google', 'Auth\AuthController@google');
Route::get('member/check-sponsor-code/{code}', 'User\MembersController@checkSponsorCode');
Route::get('member/check-member-code/{code}', 'User\MembersController@checkMemberCode');
Route::post('member/registration', 'User\MembersController@addMember');
Route::post('member/add', 'User\MembersController@addMember');


// User Routes
Route::group(['middleware' => ['jwt.verify','role:user'],'prefix' => 'user','namespace'=>'User'], function($router)
{   

    Route::get('settings','SettingsController@getMemberSettings');
    Route::get('statuses/all', '\App\Http\Controllers\Superadmin\StatusesController@getAllStatuses');
    Route::get('notice', '\App\Http\Controllers\Admin\NoticesController@get');
    Route::get('welcome-letter', '\App\Http\Controllers\Admin\WelcomeLetterController@get');

 
    
    Route::get('currencies/all', '\App\Http\Controllers\Admin\CurrenciesController@all');

    Route::get('stats', 'DashboardController@stats');
    Route::get('payout/stats', 'DashboardController@payoutStats');
    Route::get('order/stats', 'DashboardController@orderStats');
    Route::get('downline/stats', 'DashboardController@downlineStats');
    Route::get('referral/stats', 'DashboardController@referralStats');
    Route::get('downline/latest', 'DashboardController@latestDownlines');
    Route::get('transactions/latest', 'DashboardController@latestTransactions');

    Route::get('profile', 'MembersController@getProfile');
    Route::post('profile', 'MembersController@updateProfile');
    Route::get('account/status', 'MembersController@getAccuntStatus');

    Route::post('tickets', '\App\Http\Controllers\Admin\SupportController@createTicket');    
    Route::get('tickets/opened', '\App\Http\Controllers\Admin\SupportController@getMyOpened');
    Route::get('tickets/closed', '\App\Http\Controllers\Admin\SupportController@getMyClosed');
    Route::get('ticket/{id}/conversations', '\App\Http\Controllers\Admin\SupportController@getConversations');
    Route::post('tickets/close', '\App\Http\Controllers\Admin\SupportController@closeUserTicket');
    Route::post('tickets/add/message', '\App\Http\Controllers\Admin\SupportController@addUserMessage');

    Route::post('auth/update-password','\App\Http\Controllers\Auth\AuthController@changePassword');
    
    Route::get('geneology', 'MembersController@myGeneology');
    Route::get('geneology/member/{id}', 'MembersController@myMemberGeneology');
    Route::get('downlines', 'MembersController@getDownlines');
    Route::get('referrals', 'MembersController@getReferrals');

    Route::get('pending-pin-requests', 'PinsController@myPendingPinRequests');
    Route::get('approved-pin-requests', 'PinsController@myApprovedPinRequests');
    Route::get('rejected-pin-requests', 'PinsController@myRejectedPinRequests');
    Route::post('pin-requests', 'PinsController@store');
    Route::delete('pin-requests/{id}/delete', 'PinsController@destroy');
    Route::get('request/{id}/pins', 'PinsController@getRequestPins');
    Route::get('my/pins', 'PinsController@getMyPins');
    Route::get('unused/my/pins', 'PinsController@getNotUsedPins');
    Route::post('pins/transfer', 'PinsController@transferPinsToMember');
    Route::get('pins/transfer-log', 'PinsController@getPinTransferLog');
    Route::get('account/pins/used', 'PinsController@getMyUsedPins');
    Route::get('pin/check/{pin}', 'PinsController@checkPin');
    Route::post('account/pin/activation', 'ShoppingController@placePackageOrder');

    Route::get('withdrawal-requests', 'WalletController@withdrawalRequests');
    Route::post('withdrawal-requests', 'WalletController@createWithdrawal');
    Route::delete('withdrawal-requests/{id}/delete', 'WalletController@destroy');
    Route::delete('transfer-requests/{id}/delete', 'WalletController@transferDestroy');
    Route::post('withdrawal-requests/reject', 'WalletController@rejectWithdrawalRequest');

    Route::get('income-transfers-requests', 'WalletController@incomeTransfersRequests');
    Route::get('income-withdrawal-requests', 'WalletController@incomeWithdrawalRequests');

    Route::post('income-withdrawal-requests', 'WalletController@createIncomeWithdrawal');
    
    Route::post('income-transfer-requests', 'WalletController@createIncomeTransferRequests');
    // Route::delete('income-withdrawal-requests/{id}/delete', 'WalletController@incomeDestroy');
    Route::post('income-withdrawal-requests/reject', 'WalletController@rejectWithdrawalRequest');

    Route::get('wallet/balance', 'WalletController@getMyBalance');
    Route::get('withdrawals', 'WalletController@getWithdrawals');
    Route::get('wallet-transactions', 'WalletController@getWalletTransactions');
    Route::get('wallet-transfers', 'WalletController@getWalletTransfers');
    Route::post('wallet/balance/transfer', 'WalletController@createBalanceTransfer');

    Route::get('income-wallet-transactions', 'WalletController@getIncomeWalletTransactions');
    Route::get('luxury-wallet-transactions', 'WalletController@getLuxuryWalletTransactions');

    Route::get('wallet/credit-requests', 'WalletController@creditRequests');
    Route::post('wallet/credit-requests', 'WalletController@createCreditRequest');

    Route::post('address','AddressesController@createAddress');
    Route::post('address/update', 'AddressesController@updateAddress');
    Route::get('addresses', 'AddressesController@getAddresses');
    Route::get('addresses/all', 'AddressesController@getAllAddresses');
    Route::get('address/{id}', 'AddressesController@getAddress');
    Route::delete('address/{id}/delete', 'AddressesController@deleteAddress');

    Route::get('categories/all', '\App\Http\Controllers\Admin\ProductConfigurationsController@getAllCategories');
    Route::get('products', '\App\Http\Controllers\Admin\ProductsController@getUserProducts');
    Route::get('products/{id}', '\App\Http\Controllers\Admin\ProductsController@getProduct');

    Route::get('product/{id}', 'ShoppingController@getSingleProduct');
    Route::get('getsizebycolor', 'ShoppingController@getSizebyColor');
    Route::get('getcolorbysize/{id}', 'ShoppingController@getColorBySize');
    Route::get('getstock', 'ShoppingController@getStock');

    Route::post('cart/add/product', 'ShoppingController@addToCart');
    Route::post('cart/update/qty', 'ShoppingController@updateCartQty');
    Route::get('my/cart/products', 'ShoppingController@myCartProducts');
    Route::get('my/cart', 'ShoppingController@myCart');
    Route::get('my/cart/count', 'ShoppingController@myCartCount');
    Route::delete('cart/product/{id}/remove', 'ShoppingController@removeFromCart');

    Route::post('order/place', 'ShoppingController@placeOrder');
    Route::get('orders', 'ShoppingController@getMyOrders');
    Route::get('affiliate-bonus', 'PayoutsController@myAffiliateBonus');
    
    Route::get('daily/bv', 'PayoutsController@getDailyBVReport');
    Route::get('rewards', 'PayoutsController@rewards');
    Route::get('payouts', 'PayoutsController@getPayouts');
    Route::get('payout-incomes', 'PayoutsController@getPayoutIncomes');
    Route::get('income-holdings', 'PayoutsController@getIncomeHoldings');    
    Route::get('income-holding-payouts', 'PayoutsController@getIncomeHoldingPayouts');
    Route::get('group-and-matching-pvs', 'PayoutsController@getGroupAndMatchingPvs');
    Route::get('personal-pv-monthly', 'ShoppingController@getPersonalPVMonthly');
    Route::get('rank-logs', 'RanksController@getRankLogs');

    Route::get('member-payout/{id}/report', 'PayoutsController@getMemberPayoutReport');

});

Route::group(['middleware' => ['jwt.verify','role:user|admin'],'prefix' => 'user','namespace'=>'User'], function($router)
{   

    Route::get('order/{id}', 'ShoppingController@getOrder');

    Route::get('packages/all', 'ConfigController@allPackages');
    Route::get('transaction-types/all', 'ConfigController@allTransactionTypes');
    Route::get('payment-modes/all', 'ConfigController@allPaymentModes');
    Route::get('bank-partners/all', 'ConfigController@allBankPartners');
    Route::get('incomes/all', '\App\Http\Controllers\Admin\IncomesController@getAllIncomes');
    Route::get('member-payout/{id}', '\App\Http\Controllers\Admin\PayoutsController@getMemberPayout');
    Route::get('wall-of-wyntash', '\App\Http\Controllers\Admin\PayoutsController@wallOfWyntash');

    Route::get('contests', 'ContestsController@getContestStats');
    Route::get('contest/current', 'ContestsController@getCurrentContest');
    Route::get('contest/current/banner', 'ContestsController@getCurrentContestRankBanner');
    Route::get('contest/awards', 'ContestsController@getSpecialAwards');


});

// Admin Routes
Route::group(['middleware' => ['jwt.verify','role:admin'],'prefix' => 'admin','namespace'=>'Admin'], function($router)
{   

    Route::get('stats', 'DashboardController@stats');
    Route::get('order/stats', 'DashboardController@orderStats');
    Route::get('activation/stats', 'DashboardController@pinActivations');
    Route::get('monthly-joinings', 'DashboardController@monthlyJoiningsCount');
    Route::get('monthly-business', 'DashboardController@monthlyBusiness');

    Route::post('settings','SettingsController@update');
    Route::get('settings','SettingsController@getCopanyDetailsSettings');
    Route::get('company-settings','CompanySettingsController@get');
    Route::post('company-settings','CompanySettingsController@update');
    Route::get('company-admin-settings', 'CompanySettingsController@getAdminSettings');
    Route::get('statuses/all', '\App\Http\Controllers\Superadmin\StatusesController@getAllStatuses');

    Route::post('notice','NoticesController@save');
    Route::get('notice','NoticesController@get');

    Route::post('welcome-letter','WelcomeLetterController@save');
    Route::get('welcome-letter','WelcomeLetterController@get');

    Route::get('users', 'UserAndRoleController@getUsers');
    Route::post('user','UserAndRoleController@createUser');
    Route::post('user/update', 'UserAndRoleController@updateUser');
    Route::delete('user/{id}/delete', 'UserAndRoleController@deleteUser');
    Route::post('user/change-status', 'UserAndRoleController@changeUserStatus');
    Route::post('user/change-status/activation', 'UserAndRoleController@changeUserActivationStatus');
    
    Route::get('inquiries', 'InquiriesController@index');
    Route::post('inquiry/change-status', 'InquiriesController@changeInquiryStatus');
    Route::delete('inquiry/{id}/delete', 'InquiriesController@destroy');

    Route::get('subscribers', 'SubscribersController@index');
    Route::delete('subscriber/{id}/delete', 'SubscribersController@destroy');

    Route::post('news','NewsesController@createNews');
    Route::post('news/update', 'NewsesController@updateNews');
    Route::get('newses', 'NewsesController@getNewses');
    Route::get('news/{id}', 'NewsesController@getNews');
    Route::delete('news/{id}/delete', 'NewsesController@deleteNews');

    Route::post('achiever','AchieversController@createAchiever');
    Route::post('achiever/update', 'AchieversController@updateAchiever');
    Route::get('achievers', 'AchieversController@getAchievers');
    Route::get('achiever/{id}', 'AchieversController@getAchiever');
    Route::delete('achiever/{id}/delete', 'AchieversController@deleteAchiever');

    Route::post('contest','ContestsController@createContest');
    Route::post('contest/update', 'ContestsController@updateContest');
    Route::get('contest/special/rewards', 'ContestsController@getContestRewards');
    Route::post('contest/special/reward', 'ContestsController@createSpecialReward');
    Route::post('contest/special/reward/update', 'ContestsController@updateSpecialReward');
    Route::delete('contest/special/reward/{id}/delete', 'ContestsController@deleteContestSpecialReward');
    Route::get('contest/banners', 'ContestsController@getContestBanners');
    Route::post('contest/banner', 'ContestsController@createContestBanner');
    Route::delete('contest/banner/{id}/delete', 'ContestsController@deleteContestBanner');
    Route::get('contests/all', 'ContestsController@getAllContests');
    Route::get('contests', 'ContestsController@getContests');
    Route::get('contest/{id}', 'ContestsController@getContest');
    Route::get('contest/start/{id}', 'ContestsController@startContest');
    Route::delete('contest/{id}/delete', 'ContestsController@deleteContest');

    Route::post('popup','PopupsController@createPopup');
    Route::post('popup/update', 'PopupsController@updatePopup');
    Route::get('popups', 'PopupsController@getPopups');
    Route::delete('popup/{id}/delete', 'PopupsController@deletePopup');    

    Route::post('download','DownloadsController@createDownload');
    Route::post('download/update', 'DownloadsController@updateDownload');
    Route::get('downloads', 'DownloadsController@getDownloads');
    Route::delete('download/{id}/delete', 'DownloadsController@deleteDownload');

    Route::post('gallery','GalleryController@createGallery');
    Route::post('gallery/update', 'GalleryController@updateGallery');
    Route::get('gallery', 'GalleryController@getGallery');
    Route::delete('gallery/{id}/delete', 'GalleryController@deleteGallery');

    Route::post('slider','SlidersController@createSlider');
    Route::post('slider/update', 'SlidersController@updateSlider');
    Route::get('sliders', 'SlidersController@getSliders');
    Route::delete('slider/{id}/delete', 'SlidersController@deleteSlider');

    Route::post('testimonial','TestimonialsController@createTestimonial');
    Route::post('testimonial/update', 'TestimonialsController@updateTestimonial');
    Route::get('testimonials', 'TestimonialsController@getTestimonials');
    Route::delete('testimonial/{id}/delete', 'TestimonialsController@deleteTestimonial');

    Route::post('bank-partner','BankPartnersController@createBankPartner');
    Route::post('bank-partner/update', 'BankPartnersController@updateBankPartner');
    Route::get('bank-partners', 'BankPartnersController@getBankPartners');
    Route::delete('bank-partner/{id}/delete', 'BankPartnersController@deleteBankPartner');

    Route::get('pending/kyc', 'KycController@getPendingKyc');
    Route::get('rejected/kyc', 'KycController@getRejectedKyc');
    Route::get('verified/kyc', 'KycController@getVerifiedKyc');
    Route::post('kyc/update', 'KycController@updateMemberKyc');

    Route::get('tickets/opened', 'SupportController@getOpened');
    Route::get('tickets/closed', 'SupportController@getClosed');
    Route::get('ticket/{id}/conversations', 'SupportController@getConversations');
    Route::post('tickets/close', 'SupportController@closeTicket');
    Route::post('tickets/add/message', 'SupportController@addAdminMessage');

    Route::get('geneology', '\App\Http\Controllers\User\MembersController@adminGeneology');
    Route::get('geneology/member/{id}', '\App\Http\Controllers\User\MembersController@adminMemberGeneology');

    Route::get('pending-pin-requests', 'PinsController@pendingPinRequests');
    Route::get('approved-pin-requests', 'PinsController@approvedPinRequests');
    Route::get('rejected-pin-requests', 'PinsController@rejectedPinRequests');
    Route::delete('pin-requests/{id}/delete', 'PinsController@destroy');
    Route::post('pin-requests/reject', 'PinsController@reject');
    Route::post('generate-pins', 'PinsController@generatePins');

    Route::get('request/{id}/pins', 'PinsController@getRequestPins');
    Route::get('all/pins', 'PinsController@getAllPins');
    Route::get('unused/pins', 'PinsController@getNotUsedPins');
    Route::post('pins/transfer', 'PinsController@transferPinsToMember');
    Route::get('pins/transfer-log', 'PinsController@getPinTransferLog');
    Route::get('member/pins/used', 'PinsController@getMemberUsedPins');
    Route::get('pin/check/{pin}', 'PinsController@checkPin');
    Route::post('member/pin/activation', 'ShoppingController@placePackageOrder');

    Route::get('withdrawal-requests', 'WalletController@withdrawalRequests');
    Route::delete('withdrawal-requests/{id}/delete', 'WalletController@destroy');
    Route::post('withdrawal-requests/reject', 'WalletController@rejectWithdrawalRequest');
    Route::post('withdrawal-requests/approve', 'WalletController@approveWithdrawal');


    Route::get('transfer-requests', 'WalletController@transferRequests');
    Route::delete('transfer-requests/{id}/delete', 'WalletController@destroyTransfer');
    Route::post('transfer-requests/reject', 'WalletController@rejectTransferRequest');
    Route::post('transfer-requests/approve', 'WalletController@approveTransfer');

    Route::get('member/balance/{code}', '\App\Http\Controllers\User\MembersController@checkMemberBalance');
    Route::get('member/income-balance/{code}', '\App\Http\Controllers\User\MembersController@checkMemberIncomeBalance');
    Route::get('member/luxury-balance/{code}', '\App\Http\Controllers\User\MembersController@checkMemberLuxuryBalance');

    Route::get('withdrawals', 'WalletController@getWithdrawals');
    Route::get('wallet-transactions', 'WalletController@getWalletTransactions');
    Route::get('income-wallet-transactions', 'WalletController@getIncomeWalletTransactions');
    Route::get('wallet-transfers', 'WalletController@getWalletTransfers');
    Route::post('wallet/balance/transfer', 'WalletController@createBalanceTransfer');
    Route::post('wallet/balance/add', 'WalletController@addBalance');
    
    Route::get('wallet/income-debits', 'WalletController@getIncomeWalletDebitTransactions');
    Route::get('wallet/debits', 'WalletController@getDebitTransactions');
    Route::post('wallet/balance/debit', 'WalletController@debitBalance');
    Route::post('wallet/income-balance/debit', 'WalletController@debitIncomeBalance');

    Route::get('luxury-wallet-transactions', 'WalletController@getLuxuryWalletTransactions');
    Route::get('wallet/luxury-debits', 'WalletController@getLuxuryWalletDebitTransactions');
    Route::post('wallet/luxury-balance/debit', 'WalletController@debitLuxuryBalance');

    Route::get('wallet/credit-requests', 'WalletController@creditRequests');
    Route::post('wallet/approve-credit-requests', 'WalletController@approveCreditRequest');
    Route::post('wallet/reject-credit-requests', 'WalletController@rejectCreditRequest');

    Route::post('categories','ProductConfigurationsController@createCategory');
    Route::post('categories/update', 'ProductConfigurationsController@updateCategory');
    Route::get('categories', 'ProductConfigurationsController@getCategories');
    Route::get('categories/all', 'ProductConfigurationsController@getAllCategories');
    Route::delete('categories/{id}/delete', 'ProductConfigurationsController@deleteCategory');

    Route::post('variant/{id}/change-status', 'ProductsController@changeVariantStatus');
    Route::get('product-variant/all', 'ProductsController@getAllProductVariant');
    Route::get('products/all', 'ProductsController@getAllProducts');
    Route::get('product-variant/{id}', 'ProductsController@getAllProductVariantList');
    Route::get('color-variant/all', 'ProductConfigurationsController@getColorVariantAll');
    Route::get('size-variant/all', 'ProductConfigurationsController@getSizeVariantAll');

    Route::post('products','ProductsController@createProduct');
    Route::post('products/update', 'ProductsController@updateProduct');
    Route::get('products', 'ProductsController@getProducts');
    Route::get('products/{id}', 'ProductsController@getProduct');
    Route::post('products/{id}/change-status', 'ProductsController@changeProductStatus');
    Route::post('products/image/upload', 'ProductsController@uploadProductImage');
    Route::delete('products/image/{id}/delete', 'ProductsController@deleteProductImage');

    Route::get('size-variants', 'ProductConfigurationsController@getSizeVariant');
    Route::get('size-variants/all', 'ProductConfigurationsController@getSizeVariantAll');
    Route::post('size-variants', 'ProductConfigurationsController@addSizeVariant');
    Route::post('size-variant/{id}/update','ProductConfigurationsController@updateSizeVariant');
    Route::post('size-variant/change-status', 'ProductConfigurationsController@changeSizeVariantStatus');
    Route::delete('size-variant/{id}/delete', 'ProductConfigurationsController@deleteSizeVariant');

    Route::get('color-variants', 'ProductConfigurationsController@getColorVariants');
    Route::get('color-variants/all', 'ProductConfigurationsController@getColorVariantAll');
    Route::post('color-variants', 'ProductConfigurationsController@addColorVariant');
    Route::post('color-variant/{id}/update','ProductConfigurationsController@updateColorVariant');
    Route::post('color-variant/change-status', 'ProductConfigurationsController@changeColorVariantStatus');
    Route::delete('color-variant/{id}/delete', 'ProductConfigurationsController@deleteColorVariant');
    Route::get('product-variants', 'ProductConfigurationsController@allProductVariants');

    Route::post('product-variant/add', 'ProductsController@addProductVariant');

    Route::get('product/stocks', 'ProductStockController@getProductStocks');
    Route::post('stock/add', 'ProductStockController@addStock');
    Route::get('stock-logs', 'ProductStockController@getStockLogs');

    Route::get('orders/new', 'ShoppingController@getNewOrders');
    Route::get('orders/all', 'ShoppingController@getAllOrders');
    Route::get('gst/report', 'ShoppingController@getGSTReport');
    Route::post('order/update', 'ShoppingController@updateOrder');

    Route::post('email','EmailsController@create');
    Route::post('email/update', 'EmailsController@update');
    Route::get('emails', 'EmailsController@getEmails');
    Route::get('emails/all', 'EmailsController@getAllEmails');
    Route::get('email/{id}', 'EmailsController@get');
    Route::delete('email/{id}/delete', 'EmailsController@delete');

    Route::post('sms','SmsesController@create');
    Route::post('sms/update', 'SmsesController@update');
    Route::get('smses', 'SmsesController@getSmses');
    Route::get('smses/all', 'SmsesController@getAllSmses');
    Route::get('sms/{id}', 'SmsesController@get');
    Route::delete('sms/{id}/delete', 'SmsesController@delete');

    Route::post('marketing/email/mass', 'MarketingController@sendMassEmail');
    Route::post('marketing/sms/mass', 'MarketingController@sendMassSMS');

    Route::post('payout/holding', 'PayoutsController@generateHolding');
    Route::post('payout/generate', 'PayoutsController@generateManualPayout');
    Route::post('payout/generate-matching-points', 'PayoutsController@generateMatchingPoints');
    Route::get('matching-points', 'PayoutsController@getMatchingPoints');
    Route::get('rewards', 'PayoutsController@rewards');
    Route::get('check-member/{code}', 'PayoutsController@memberCheck');
    
    Route::post('reward-add', 'PayoutsController@AddReward');
        
    Route::get('monthly-business', 'PayoutsController@getMonthlyBusiness');
    Route::get('payouts', 'PayoutsController@getPayouts');
    Route::get('payout-incomes', 'PayoutsController@getPayoutIncomes');
    Route::get('payouts/member', 'PayoutsController@getMemberPayouts');
    Route::get('payout-incomes/member', 'PayoutsController@getMemberPayoutIncomes');
    Route::get('income-holdings/member', 'PayoutsController@getMemberIncomeHoldings');
    Route::post('payout/release/member-holding', 'PayoutsController@releaseMemberHoldPayout');
    Route::get('get-monthly-overview', 'ShoppingController@getMonthlyOverview');
    Route::get('group-and-matching-pvs', 'PayoutsController@getGroupAndMatchingPvs');

    Route::get('tds/member', 'PayoutsController@getMemberTDS');
    Route::get('top-wallet', 'PayoutsController@getMemberTopWallet');
});

Route::group(['middleware' => ['jwt.verify','role:admin|superadmin'],'prefix' => 'admin','namespace'=>'Admin'], function($router)
{  
    Route::get('packages', 'PackagesController@index');
    Route::get('packages/all', 'PackagesController@all');
    Route::post('packages', 'PackagesController@store');
    Route::post('package/{id}/update','PackagesController@update');
    Route::post('package/change-status', 'PackagesController@changePackageStatus');
    Route::delete('package/{id}/delete', 'PackagesController@destroy');

    Route::post('notification-setting/create', 'NotificationSettingsController@create');    
    Route::get('notification-settings', 'NotificationSettingsController@get');
    Route::post('notification-settings', 'NotificationSettingsController@save');

    Route::post('income', 'IncomesController@createIncome');
    Route::post('income/update', 'IncomesController@updateIncome');
    Route::get('incomes', 'IncomesController@getIncomes');
    Route::get('incomes/all', 'IncomesController@getAllIncomes');
    Route::get('income/{id}', 'IncomesController@getIncome');
    Route::delete('income/{id}/delete', 'IncomesController@deleteIncome');

    Route::post('income-parameter', 'IncomesController@createIncomeParameter');
    Route::post('income-parameter/update', 'IncomesController@updateIncomeParameter');
    Route::get('income-parameters', 'IncomesController@getIncomeParameters');
    Route::get('income-parameter/{id}', 'IncomesController@getIncomeParameter');
    Route::delete('income-parameter/{id}/delete', 'IncomesController@deleteIncomeParameter');

    Route::post('payout-type', 'PayoutTypesController@createPayoutType');
    Route::post('payout-type/update', 'PayoutTypesController@updatePayoutType');
    Route::get('payout-types', 'PayoutTypesController@getPayoutTypes');
    Route::get('payout-types/all', 'PayoutTypesController@getAllPayoutTypes');
    Route::get('payout-type/{id}', 'PayoutTypesController@getPayoutType');
    Route::delete('payout-type/{id}/delete', 'PayoutTypesController@deletePayoutType');
    Route::get('scheduled-types', 'PayoutTypesController@getScheduledTypes');

    Route::post('rank', 'RanksController@createRank');
    Route::post('rank/update', 'RanksController@updateRank');
    Route::get('ranks', 'RanksController@getRanks');
    Route::get('ranks/all', 'RanksController@getAllRanks');
    Route::get('rank/{id}', 'RanksController@getRank');
    Route::delete('rank/{id}/delete', 'RanksController@deleteRank');

});

// Superadmin Routes
Route::group(['middleware' => ['jwt.verify','role:superadmin'],'prefix' => 'superadmin','namespace'=>'Superadmin'], function($router)
{   

    Route::get('users', 'UserAndRoleController@getAdminUsers');
    Route::post('user','UserAndRoleController@createAdminUser');
    Route::post('user/update', 'UserAndRoleController@updateAdminUser');
    Route::delete('user/{id}/delete', 'UserAndRoleController@deleteUser');
    Route::post('user/change-status', 'UserAndRoleController@changeUserStatus');
    
    // Role Routes
    Route::post('role', 'UserAndRoleController@createRole');
    Route::post('role/update', 'UserAndRoleController@updateRole');
    Route::get('roles', 'UserAndRoleController@getRoles');
    Route::get('role/{id}', 'UserAndRoleController@getRole');
    Route::delete('role/{id}/delete', 'UserAndRoleController@deleteRole');

    Route::post('permission', 'UserAndRoleController@createPermission');
    Route::post('permission/update', 'UserAndRoleController@updatePermission');
    Route::get('permissions', 'UserAndRoleController@getPermissions');
    Route::get('permission/{id}', 'UserAndRoleController@getPermission');
    Route::delete('permission/{id}/delete', 'UserAndRoleController@deletePermission');

    Route::post('transaction-type','TransactionTypesController@createTransactionType');
    Route::post('transaction-type/update', 'TransactionTypesController@updateTransactionType');
    Route::get('transaction-types', 'TransactionTypesController@getTransactionTypes');
    Route::delete('transaction-type/{id}/delete', 'TransactionTypesController@deleteTransactionType');

    Route::post('payment-mode','PaymentModesController@createPaymentMode');
    Route::post('payment-mode/update', 'PaymentModesController@updatePaymentMode');
    Route::get('payment-modes', 'PaymentModesController@getPaymentModes');
    Route::delete('payment-mode/{id}/delete', 'PaymentModesController@deletePaymentMode');

    Route::post('status','StatusesController@createStatus');
    Route::post('status/update', 'StatusesController@updateStatus');
    Route::get('statuses', 'StatusesController@getStatuses');
    Route::delete('status/{id}/delete', 'StatusesController@deleteStatus');

    Route::get('settings','SettingsController@get');
    Route::post('settings','SettingsController@update');


    
});


