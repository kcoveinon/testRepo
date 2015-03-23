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
	Route::any('search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{driverAge}', 'HZController@searchVehicles');
	Route::any('book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{countryCode}/{vehicleCategory}/{vehicleClass}', 'HZController@book');
	Route::any('get-booking-details/{bookingId}', 'HZController@getBookingInfo');
	Route::any('cancel-booking/{bookingId}', 'HZController@cancelBooking');
	Route::any('modify-booking/{bookingId}/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{vehicleCategory}/{vehicleClass}', 'HZController@modifyBooking');
	Route::any('get-depot-details/{locationCode}', 'HZController@getDepotDetails');
	Route::any('get-location-depots/{locationCode}', 'HZController@getLocationDepots');
});

Route::group(array('prefix' => 'RS/'), function()
{
    Route::any('showAngular', 'RSController@showAngularTutorial');
    Route::any('showBooking', 'RSController@showBooking');
    Route::any('doBookingWithEquipments', 'RSController@doBookingWithEquipments');
    Route::any('search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{vehicleClass}/{countryCode}', 'RSController@searchVehicles');
    Route::any('cancel-booking/{bookingId}', 'RSController@cancelBooking');
    Route::any('book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{vehicleClass}/{rateId}/{countryCode}', 'RSController@doBooking');
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