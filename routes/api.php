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


// Authentication Routes
Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', 'Auth\AuthController@login');
    Route::post('register', 'Auth\AuthController@register'); 
    Route::post('auth/google', 'Auth\AuthController@google');
    Route::post('change-password', 'Auth\AuthController@changePassword');
    Route::post('logout', 'Auth\AuthController@logout');
    Route::post('refresh', 'Auth\AuthController@refresh');
    Route::get('me', 'Auth\AuthController@me');

    Route::get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
    Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
    Route::get('email-verify', 'Auth\AuthController@emailVerify');
});


Route::post('public/inquiry/submit', 'Admin\InquiriesController@store');
Route::post('public/subscribe', 'Admin\SubscribersController@store');
Route::post('auth/google', 'Auth\AuthController@google');
Route::get('member/check-sponsor-code/{code}', 'User\MembersController@checkSponsorCode');
Route::post('member/registration', 'User\MembersController@registerMember');


// Superadmin Routes
Route::group(['middleware' => ['jwt.verify','role:user'],'prefix' => 'user','namespace'=>'User'], function($router)
{   
  
    Route::get('profile', 'MembersController@getProfile');
    Route::post('profile', 'MembersController@updateProfile');

    Route::post('auth/update-password','\App\Http\Controllers\Auth\AuthController@changePassword');
    Route::get('static/home','StaticController@home');
    
});

// Admin Routes
Route::group(['middleware' => ['jwt.verify','role:admin'],'prefix' => 'admin','namespace'=>'Admin'], function($router)
{   
 
    Route::get('stats', 'SettingsController@stats');
    Route::post('settings','SettingsController@updateCompanyDetails');
    Route::get('settings','SettingsController@getAdminSettings');

    Route::get('users', 'UserAndRoleController@getUsers');
    Route::post('user','UserAndRoleController@createUser');
    Route::post('user/update', 'UserAndRoleController@updateUser');
    Route::delete('user/{id}/delete', 'UserAndRoleController@deleteUser');
    Route::post('user/change-status', 'UserAndRoleController@changeUserStatus');
    Route::post('user/package/update-expire-date','UserAndRoleController@updatePackageExpireDate');
    
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

    Route::get('packages', 'PackagesController@index');
    Route::get('packages/all', 'PackagesController@all');
    Route::post('packages', 'PackagesController@store');
    Route::post('package/{id}/update','PackagesController@update');
    Route::delete('package/{id}/delete', 'PackagesController@destroy');

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


});

// Superadmin Routes
Route::group(['middleware' => ['jwt.verify','role:superadmin'],'prefix' => 'sadmin','namespace'=>'Admin'], function($router)
{   
    
    Route::get('users', 'UserAndRoleController@getAdminUsers');
    Route::post('user','UserAndRoleController@createAdminUser');
    Route::post('user/update', 'UserAndRoleController@updateAdminUser');
    Route::delete('user/{id}/delete', 'UserAndRoleController@deleteUser');

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

    Route::get('settings','SettingsController@get');
    Route::post('settings','SettingsController@update');

    
});


