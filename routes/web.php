<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Frontend Routing
Route::get('/', 'Frontend\HomeController@home');

// End Frontend Routing




// Backend Routing
Route::get('/admin', function () {
    return view('admin/dashboard');
});

Route::get('/admin/login', function (){
    return view('admin/login');
});

Route::get('/admin/user', 'Admin\UserManagementController@index');

// End Backend Routing