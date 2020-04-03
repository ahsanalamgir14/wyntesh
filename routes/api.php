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

Route::get('mail', 'Auth\AuthController@mailcheck');


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



// Superadmin Routes
Route::group(['middleware' => ['jwt.verify','role:user'],'prefix' => 'user','namespace'=>'User'], function($router)
{   
  
    Route::get('/profile', function(){
        $user = JWTAuth::user()->toArray();
        unset($user['comission_from_self']);
        unset($user['comission_from_child']);
        unset($user['verification_code']);
        unset($user['parent']);
        $user['isPasswordSet'] =  !empty(DB::table('users')->where('id',$user['id'])->get()[0]->password);
        return $user;
    });

    Route::post('auth/update-password','\App\Http\Controllers\Auth\AuthController@changePassword');
  
    Route::post('/profile','StaticController@update_profile');
    Route::get('static/home','StaticController@home');
    
});

// Admin Routes
Route::group(['middleware' => ['jwt.verify','role:admin'],'prefix' => 'admin','namespace'=>'Admin'], function($router)
{   
 
    Route::get('stats', 'SettingsController@stats');

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

    Route::get('packages', 'PackagesController@index');
    Route::get('packages/all', 'PackagesController@all');
    Route::post('packages', 'PackagesController@store');
    Route::post('package/{id}/update','PackagesController@update');
    Route::delete('package/{id}/delete', 'PackagesController@destroy');

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


