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

Route::group(array('prefix' => 'api/{supplierCode}'), function () {
    Route::get(
        'search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{countryCode}/{driverAge}', 
        function($supplierCode, $pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $driverAge) {
            $supplierApi = App::make($supplierCode);

            $result = $supplierApi->searchVehicles($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $driverAge);

            return Response::json($result);
        }
    );
});

Route::any('HZ/book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{countryCode}/{vehCategory}/{vehClass}', 'BookingController@book');
Route::any('HZ/bookingDetails/{bookingId}/{countryCode}', 'BookingController@getBookingInfo');
Route::any('HZ/cancelBooking/{bookingId}/{countryCode}', 'BookingController@cancelBooking');
Route::any('HZ/modifyBooking/{bookingId}/{countryCode}', 'BookingController@modifyBooking');
Route::any('HZ/getDepotDetails/{locationCode}/{countryCode}', 'VehicleController@getDepotDetails');
Route::any('HZ/getLocationDepots/{locationCode}/{countryCode}', 'VehicleController@getLocationDepots');

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