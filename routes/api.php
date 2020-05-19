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

Route::get('mail', 'Auth\AuthController@mailCheck');
Route::get('geneology', '\App\Http\Controllers\User\MembersController@adminGeneology');
Route::get('download-file', 'Admin\SettingsController@downloadFile');

// Authentication Routes
Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', 'Auth\AuthController@login');
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
Route::post('member/registration', 'User\MembersController@registerMember');
Route::post('member/add', 'User\MembersController@addMember');


// User Routes
Route::group(['middleware' => ['jwt.verify','role:user'],'prefix' => 'user','namespace'=>'User'], function($router)
{   
  
    Route::get('settings','SettingsController@getMemberSettings');

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
    Route::post('withdrawal-requests/reject', 'WalletController@rejectWithdrawalRequest');

    Route::get('wallet/balance', 'WalletController@getMyBalance');
    Route::get('withdrawals', 'WalletController@getWithdrawals');
    Route::get('wallet-transactions', 'WalletController@getWalletTransactions');
    Route::get('wallet-transfers', 'WalletController@getWalletTransfers');
    Route::post('wallet/balance/transfer', 'WalletController@createBalanceTransfer');

    Route::get('wallet/credit-requests', 'WalletController@creditRequests');
    Route::post('wallet/credit-requests', 'WalletController@createCreditRequest');

    Route::post('address','AddressesController@createAddress');
    Route::post('address/update', 'AddressesController@updateAddress');
    Route::get('addresses', 'AddressesController@getAddresses');
    Route::get('addresses/all', 'AddressesController@getAllAddresses');
    Route::get('address/{id}', 'AddressesController@getAddress');
    Route::delete('address/{id}/delete', 'AddressesController@deleteAddress');

    Route::get('categories/all', '\App\Http\Controllers\Admin\ProductsAndCategoryController@getAllCategories');
    Route::get('products', '\App\Http\Controllers\Admin\ProductsAndCategoryController@getProducts');
    Route::get('products/{id}', '\App\Http\Controllers\Admin\ProductsAndCategoryController@getProduct');

    Route::post('cart/add/product', 'ShoppingController@addToCart');
    Route::post('cart/update/qty', 'ShoppingController@updateCartQty');
    Route::get('my/cart/products', 'ShoppingController@myCartProducts');
    Route::get('my/cart', 'ShoppingController@myCart');
    Route::get('my/cart/count', 'ShoppingController@myCartCount');
    Route::delete('cart/product/{id}/remove', 'ShoppingController@removeFromCart');

    Route::post('order/place', 'ShoppingController@placeOrder');
    Route::get('orders', 'ShoppingController@getMyOrders');

});

Route::group(['middleware' => ['jwt.verify','role:user|admin'],'prefix' => 'user','namespace'=>'User'], function($router)
{   
  
    Route::get('packages/all', 'ConfigController@allPackages');
    Route::get('transaction-types/all', 'ConfigController@allTransactionTypes');
    Route::get('payment-modes/all', 'ConfigController@allPaymentModes');
    Route::get('bank-partners/all', 'ConfigController@allBankPartners');

    
});

// Admin Routes
Route::group(['middleware' => ['jwt.verify','role:admin'],'prefix' => 'admin','namespace'=>'Admin'], function($router)
{   
 
    Route::get('stats', 'DashboardController@stats');
    Route::post('settings','SettingsController@updateCompanyDetails');
    Route::get('settings','SettingsController@getSettings');
    Route::get('settings/company','SettingsController@getCompanySettings');

    Route::get('users', 'UserAndRoleController@getUsers');
    Route::post('user','UserAndRoleController@createUser');
    Route::post('user/update', 'UserAndRoleController@updateUser');
    Route::delete('user/{id}/delete', 'UserAndRoleController@deleteUser');
    Route::post('user/change-status', 'UserAndRoleController@changeUserStatus');
   
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

    Route::get('member/balance/{code}', '\App\Http\Controllers\User\MembersController@checkMemberBalance');

    Route::get('withdrawals', 'WalletController@getWithdrawals');
    Route::get('wallet-transactions', 'WalletController@getWalletTransactions');
    Route::get('wallet-transfers', 'WalletController@getWalletTransfers');
    Route::post('wallet/balance/transfer', 'WalletController@createBalanceTransfer');
    Route::post('wallet/balance/add', 'WalletController@addBalance');

    Route::get('wallet/credit-requests', 'WalletController@creditRequests');
    Route::post('wallet/approve-credit-requests', 'WalletController@approveCreditRequest');
    Route::post('wallet/reject-credit-requests', 'WalletController@rejectCreditRequest');

    Route::post('categories','ProductsAndCategoryController@createCategory');
    Route::post('categories/update', 'ProductsAndCategoryController@updateCategory');
    Route::get('categories', 'ProductsAndCategoryController@getCategories');
    Route::get('categories/all', 'ProductsAndCategoryController@getAllCategories');
    Route::delete('categories/{id}/delete', 'ProductsAndCategoryController@deleteCategory');

    Route::post('products','ProductsAndCategoryController@createProduct');
    Route::post('products/update', 'ProductsAndCategoryController@updateProduct');
    Route::get('products', 'ProductsAndCategoryController@getProducts');
    Route::get('products/{id}', 'ProductsAndCategoryController@getProduct');
    Route::delete('products/{id}/delete', 'ProductsAndCategoryController@deleteProduct');
    Route::post('products/image/upload', 'ProductsAndCategoryController@uploadProductImage');
    Route::delete('products/image/{id}/delete', 'ProductsAndCategoryController@deleteProductImage');

    Route::get('orders/new', 'ShoppingController@getNewOrders');
    Route::get('orders/all', 'ShoppingController@getAllOrders');
    Route::post('order/update', 'ShoppingController@updateOrder');


});

Route::group(['middleware' => ['jwt.verify','role:admin|superadmin'],'prefix' => 'admin','namespace'=>'Admin'], function($router)
{  
    Route::get('packages', 'PackagesController@index');
    Route::get('packages/all', 'PackagesController@all');
    Route::post('packages', 'PackagesController@store');
    Route::post('package/{id}/update','PackagesController@update');
    Route::post('package/change-status', 'PackagesController@changePackageStatus');
    Route::delete('package/{id}/delete', 'PackagesController@destroy');

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

    Route::get('settings','SettingsController@get');
    Route::post('settings','SettingsController@update');

    
});


