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
            $result = array();

            $pickUpDepot = DB::select(
                "SELECT 
                    d.depotCode,
                    s.supplierCode
                FROM 
                    phpvroom.locationdepot AS ld, 
                    phpvroom.depot AS d,
                    phpvroom.supplier AS s
                WHERE 
                    ld.depotID = d.depotID AND
                    d.supplierID = s.supplierID AND
                    ld.locationID = '" . $pickUpLocationId. "' AND
                    s.supplierCode = '" . $supplierCode . "'
                LIMIT 1"
            );

            if (empty($pickUpDepot)) {
                die('no pick up depot available');
            }

            $supplierPickUpDepotCode = $pickUpDepot[0]->depotCode;

            if ($returnLocationId == $pickUpLocationId) {
                $supplierReturnDepotCode = $supplierPickUpDepotCode;
            } else {
                $returnDepot = DB::select(
                "SELECT 
                        d.depotCode,
                        s.supplierCode
                    FROM 
                        phpvroom.locationdepot AS ld, 
                        phpvroom.depot AS d,
                        phpvroom.supplier AS s
                    WHERE 
                        ld.depotID = d.depotID AND
                        d.supplierID = s.supplierID AND
                        ld.locationID = '" . $returnLocationId. "'"
                );

                if (empty($returnDepot)) {
                    die('no return depot');
                }

                $supplierReturnDepotCode = $returnDepot[0]->depotCode;
            }

            // echo '<pre>' . print_r($supplierPickUpDepotCode, true) . '</pre>';
            // echo '<pre>' . print_r($supplierReturnDepotCode, true) . '</pre>'; die();

            $supplierApi = App::make($supplierCode);

            $result = $supplierApi->searchVehicles($pickUpDate, $pickUpTime, $returnDate, $returnTime, $supplierPickUpDepotCode, $supplierReturnDepotCode, $countryCode, $driverAge);

            return Response::json($result);
        }
    );
});

Route::group(array('prefix' => 'HZ/'), function()
{
    Route::any('showBooking', 'HZController@showBooking');
    Route::any('doBookingWithEquipments', 'HZController@doBookingWithEquipments');
	Route::any('search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{driverAge}', 'HZController@searchVehicles');
	Route::any('book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{countryCode}/{vehicleCategory}/{vehicleClass}', 'HZController@book');
	Route::any('get-booking-details/{bookingId}', 'HZController@getBookingInfo');
	Route::any('cancel-booking/{bookingId}', 'HZController@cancelBooking');
	Route::any('modify-booking/{bookingId}/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{vehicleCategory}/{vehicleClass}', 'HZController@modifyBooking');
	Route::any('get-depot-details/{locationCode}', 'HZController@getDepotDetails');
    Route::any('get-location-depots/{locationCode}', 'HZController@getLocationDepots');
	Route::any('export-depot-location', 'HZController@exportDepotCompilation');
});

Route::group(array('prefix' => 'RS/'), function()
{
    Route::any('get-fleet', 'RSController@getFleet');
    Route::any('get-locations', 'RSController@getLocations');
    Route::any('get-extras', 'RSController@getExtras');
    Route::any('show-booking-form', 'RSController@showBooking');
    Route::any('get-booking-details/{bookingId}', 'RSController@getBookingDetails');
    Route::any('do-booking-with-equipments', 'RSController@doBookingWithEquipments');
    Route::any('search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{vehicleClass}/{countryCode}', 'RSController@searchVehicles');
    Route::any('cancel-booking/{bookingId}', 'RSController@cancelBooking');
    Route::any('book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{vehicleClass}/{rateId}/{countryCode}', 'RSController@doBooking');
});


Route::group(array('prefix' => 'AV'), function() {
    // Get ping response
    Route::any('/ping', 'AVController@ping');

    // Get all locations
    Route::any('/locations', 'AVController@getLocations');

    // Get depots per location
    Route::any('/locationDepots/{locationCode}', 'AVController@getDepotsPerLocation');

    // Get location details
    Route::any('/locationDetails/{locationCode}', 'AVController@getLocationDetails');

    // Get rates
    Route::any(
        'getRates/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocation}/{returnLocation}/{carCategory}',
        'AVController@getRates'
    );

    // Search available cars
    Route::any(
        'search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{driverAge}',
        'AVController@search'
    );

    // Create booking
    Route::any(
        '/createBooking/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{carCategory}',
        'AVController@createBooking'
    );
});



Route::group(array('prefix' => 'TH'),function(){

   // Get all locations
   Route::any('/locations','THController@getlocations');

   // Get depots per location
   Route::any('/locationDepots/{locationCode}','THController@getDepotsPerLocation');

   // Get location details
   Route::any('/locationDetails/{locationCode}','THController@getLocationDetails');

   // Get rates
   Route::any(
       'getRates/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocation}/{returnLocation}/{carCategory}',
       'THController@getRates'
   );

   // Search available cars
   Route::any(
       'search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{driverAge}',
       'THController@search'
   );

   // Create booking
   Route::any(
       '/createBooking/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{carCategory}',
       'THController@createBooking'
   );
   
   // View booking details
   Route::any('/bookingDetails/{bookingId}','THController@getBookingDetails');
   
   // Cancel booking
   Route::any('/cancelBooking/{bookingId}','THController@cancelBooking');
   
   // Test route
   Route::get('/testAu','THController@testAu');
   
});

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