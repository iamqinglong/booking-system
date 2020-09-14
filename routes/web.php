<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['role:User']], function () {
    Route::get('bookings/get-all', 'BookingController@all')->name('bookings.all');
    Route::get('bookings/create', 'BookingController@create')->name('bookings.create');
    Route::post('/bookings/approved/{booking}', 'BookingController@approved')->name('user.bookings.approved');
    Route::post('/bookings/declined/{booking}', 'BookingController@declined')->name('user.bookings.declined');
    Route::get('bookings/{booking?}', 'BookingController@index')->name('bookings.index');
    Route::resource('bookings', 'BookingController', ['except' => ['index', 'create']]);
    Route::get('tables/get-all', 'TableController@all')->name('tables.all');
    Route::resource('tables', 'TableController');
});

Route::middleware('auth')->group(function () {
    Route::get('booking-remarks', 'EnumController@getBookingRemarks')->name('booking.remarks');
    Route::get('suggested-schedule-remarks', 'EnumController@getSuggestedScheduleRemarks')->name('suggested.schedule.remarks');
});

Route::group(['prefix' => 'admin', 'middleware' => ['role:Admin']], function () {
    Route::get('/', 'Admin\MainController@index')->name('admin.index');
    Route::post('/bookings/approved/{booking}', 'Admin\BookingController@approved')->name('admin.bookings.approved');
    Route::post('/bookings/declined/{booking}', 'Admin\BookingController@declined')->name('admin.bookings.declined');
    Route::post('/bookings/suggestion/{booking}', 'Admin\BookingController@suggest')->name('admin.bookings.declined');
    Route::get('bookings/get-all', 'Admin\BookingController@all')->name('admin.bookings.all');
    Route::get('bookings/{booking?}', 'Admin\BookingController@index')->name('admin.bookings.index');
    Route::resource('bookings', 'Admin\BookingController', ["as" => "admin", 'except' => ['index']]);
    Route::get('tables/get-all', 'Admin\TableController@all')->name('admin.tables.all');
    Route::put('/tables/toggle-availability/{table}', 'Admin\TableController@toggleAvailability');
    Route::resource('tables', 'Admin\TableController', ["as" => "admin"]);
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
