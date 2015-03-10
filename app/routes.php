<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/


Route::any('search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{countryCode}/{driverAge}', 'VehicleController@search');
Route::any('book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{countryCode}/{vehCategory}/{vehClass}', 'BookingController@book');
Route::any('bookingDetails/{bookingId}/{countryCode}', 'BookingController@getBookingInfo');
Route::any('cancelBooking/{bookingId}/{countryCode}', 'BookingController@cancelBooking');
Route::any('modifyBooking/{bookingId}/{countryCode}', 'BookingController@modifyBooking');

Route::controller('booking', 'BookingController');
Route::controller('vehicle-classification', 'VehicleClassificationController');
Route::controller('vehicle', 'VehicleController');
Route::controller('country', 'CountryController');
Route::controller('city', 'CityController');
Route::controller('station', 'StationController');
Route::get('/', function()
{
	/*App::abort(404);*/
});