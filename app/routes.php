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

Route::group(array('prefix' => 'prototype'), function () {
  Route::any('search-page', function() {
    return View::make('prototype.search_page');
  });

  // /prototype/async-search-requests/2015-12-13/10:00/2015-12-15/10:00/1926/1926/AU/25+
  Route::any(
    'async-search-requests/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{countryCode}/{driverAge}', 
    function (
      $pickUpDate,
      $pickUpTime,
      $returnDate,
      $returnTime,
      $pickUpLocationId,
      $returnLocationId,
      $countryCode,
      $driverAge
    ) {
      $data = array(
        'pickUpDate'        => $pickUpDate,
        'pickUpTime'        => $pickUpTime,
        'returnDate'        => $returnDate,
        'returnTime'        => $returnTime,
        'pickUpLocationId' => $pickUpLocationId,
        'returnLocationId' => $returnLocationId,
        'countryCode'       => $countryCode,
        'driverAge'         => $driverAge
      );

      return View::make('prototype.async_search_requests', $data);
    }
  );

  Route::any('europcar-booking', function () {
    return View::make('prototype.europcar_booking');
  });

  Route::any('acriss-decoder/{carCategoryCode}', function ($carCategoryCode) {
    $acrissHelper = new AcrissHelper();

    $expandedCode = $acrissHelper->expandCode($carCategoryCode);

    // print readable $expandedCode
    echo '<pre>' . print_r($expandedCode, true) . '</pre>';
  });
});

Route::group(array('prefix' => 'EC'), function () {
  Route::any('depots/update-records', function() {
    ini_set('max_execution_time', 300);

    $supplierCode = 'EC';

    $supplier = Supplier::whereCode($supplierCode)->first();

    if (empty($supplier)) {
      die('Unable to get supplier information');
    }

    $supplierId = $supplier->getId();

    $responseXML = simplexml_load_file('C:\Users\ibaguio\Documents\europcar_depots.xml');

    // get all countries for
    $countries = Country::all();

    foreach ($countries as $country) {
      $countryIds[$country->getCode()] = $country->getId();
    }

    $nonExistingCountries = array();
    $stationsNotAdded     = 0;
    $stationsAdded        = 0;

    foreach ($responseXML->station as $stationNode) {
      $station = array(
        'areaType'         => (string) $stationNode['areaType'],
        'address1'         => (string) $stationNode['address1'],
        'address2'         => (string) $stationNode['address2'],
        'countryCode'      => (string) $stationNode['countryCode'],
        'phoneNumber'      => (string) $stationNode['phoneNumber'],
        'phoneCountryCode' => (string) $stationNode['phoneCountryCode'],
        'phoneAreaCode'    => (string) $stationNode['phoneAreaCode'],
        'stationCode'      => (string) $stationNode['stationCode'],
        'stationName'      => (string) $stationNode['stationName'],
        'cityName'         => (string) $stationNode['cityName'],
        'postalCode'       => (string) $stationNode['postalCode'],
        'longitude'        => (string) $stationNode['longitude'],
        'latitude'         => (string) $stationNode['latitude'],
        'countryName'      => (string) $stationNode['countryName'], // temporary code
      );

      if (!isset($countryIds[$station['countryCode']])) {
        $nonExistingCountries[] = $station['countryCode'] . '+' . $station['countryName'];
        $stationsNotAdded++;
        continue;
      }

      $isAirPort   = ($station['areaType'] == 'T') ? 1 : 0;
      $address     = trim($station['address1'] . ' ' . $station['address2']);
      $countryId   = $countryIds[$station['countryCode']];
      $phoneNumber = '';

      if (!empty($station['phoneNumber'])) {
        if (!empty($station['phoneCountryCode'])) {
          $phoneNumber = '(' . $station['phoneCountryCode'] .') ';
        }

        if (!empty($station['phoneAreaCode'])) {
          $phoneNumber .= $station['phoneAreaCode'] . ' ';
        }

        $phoneNumber .= $station['phoneNumber'];
      }
        $data = array(
            'supplierID'   => $supplierId,
            'locationCode' => $station['stationCode'],
            'countryCode'  => $station['stationCode'],
            'postCode'     => $station['postalCode'],
            'city'         => $station['cityName'],
            'address'      => $address,
            'phoneNumber'  => $phoneNumber,
            'latitude'     => $station['latitude'],
            'longitude'    => $station['longitude'],
            'isAirport'    => $isAirPort,
            'locationName' => $station['stationName']
        );

        $response = Depot::updateOrCreateDepot($data);

        if (!$response["result"]) {
            break;
        }

        $stationsAdded++;

    }
    $result = array(
        "success"   => $response["result"],
        "message"   => $response["message"],
        "rowsAdded" => $stationsAdded
    );

    return Response::json($result);

    });

	Route::any(
		'search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpStationCode}/{returnStationCode}/{countryCode}/{driverAge}',
		'ECController@searchVehicles'
	);

	Route::any('vehicle/get/{stationCode}/{date}', 'ECController@getVehicle');
	Route::any('vehicle/get-equipment-list/{stationCode}/{date}', 'ECController@getVehicleEquipmentList');
	Route::any(
		'vehicle/get-quote/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpStationCode}/{returnStationCode}/{countryOfResidence}/{carCategoryCode}',
		'ECController@getVehicleQuote'
	);
	Route::any(
		'vehicle/get-multiple-rates/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpStationCode}/{returnStationCode}/{countryOfResidence}/{carCategoryPatterns?}',
		'ECController@getVehicleMultipleRates'
	);

	Route::any('country/get', 'ECController@getCountry');
	Route::any('country/get-residence', 'ECController@getCountryResidence');

	Route::any('city/get/{countryCode}', 'ECController@getCity');

	Route::any('station/get/{countryCode}/{cityName}', 'ECController@getStation');
	Route::any('station/get-by-code/{stationCode}', 'ECController@getStationByCode');
	Route::any('station/get-schedule/{stationCode}', 'ECController@getStationSchedule');
	Route::any('station/get-open-hours/{stationCode}/{date}', 'ECController@getStationOpenHours');

	// sample booking reference number 1007492913
	// /book/reservation/20151213/1000/20151213/1900/TXLT01/TXLT01/IDMR/MR/JOHN/SMITH/DE
	Route::any('book/reservation', 'ECController@bookReservation');
	Route::any( 
		'book/reservation/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpStationCode}/{returnStationCode}/{carCategoryCode}/{title}/{firstName}/{lastName}/{countryOfResidence}',
		'ECController@bookReservationOld'
	);
	Route::any('book/search-reservation-by-id/{reservationNumber}', 'ECController@searchReservationById');
	Route::any('book/cancel-reservation/{reservationNumber}', 'ECController@cancelReservation');
});

Route::group(array('prefix' => 'HZ/'), function()
{
    Route::any('show-booking-form', 'HZController@showBooking');
    Route::any('do-booking-with-equipments', 'HZController@doBookingWithEquipments');
    Route::any('search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{driverAge}', 'HZController@searchVehicles');
    Route::any('book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{countryCode}/{vehicleCategory}/{vehicleClass}/{equipemnts}/{age}/{firstName}/{lastName}', 'HZController@book');
    Route::any('get-booking-details/{bookingId}/{lastName}', 'HZController@getBookingInfo');
    Route::any('cancel-booking/{bookingId}/{lastName}', 'HZController@cancelBooking');
    Route::any('modify-booking/{bookingId}/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationId}/{returnLocationId}/{vehicleCategory}/{vehicleClass}', 'HZController@modifyBooking');
    Route::any('get-depot-details/{locationCode}', 'HZController@getDepotDetails');
    Route::any('get-location-depots/{locationCode}', 'HZController@getLocationDepots');
    Route::any('view-depots', 'HZController@viewDepots');
    Route::any('depots/update-records', 'HZController@exportDepotCompilation');
});

Route::group(array('prefix' => 'RS/'), function()
{
    Route::any('depots/update-records', 'HZController@exportDepotCompilation');  
    Route::any('get-fleet', 'RSController@getFleet');
    Route::any('depots/update-records', 'RSController@exportLocations');
    Route::any('get-locations', 'RSController@getLocations');
    Route::any('get-extras', 'RSController@getExtras');
    Route::any('show-booking-form', 'RSController@showBooking');
    Route::any('get-booking-details/{bookingId}', 'RSController@getBookingDetails');
    Route::any('do-booking-with-equipments', 'RSController@doBookingWithEquipments');
    Route::any('search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{driverAge}', 'RSController@searchVehicles');
    Route::any('cancel-booking/{bookingId}', 'RSController@cancelBooking');
    Route::any('book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{vehicleClass}/{rateId}/{countryCode}', 'RSController@doBooking');
});

Route::any('update-supplier-depots', 'CronController@updateDepotTable');

Route::group(array('prefix' => 'TH'),function(){

     Route::get('/depots/update-records' ,'THController@updateDepots');

   // Get all locations
   Route::any('/getAllDepots','THController@getAllDepots');

   // Get depots per location
   Route::any('/getDepotsByCity/{locationCode}','THController@getDepotsByCity');

   // Get location details
   Route::any('/depotDetails/{locationCode}','THController@depotDetails');

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


Route::group(array('prefix' => 'AV'), function() {
  // Get ping response
  Route::any('/ping', 'AVController@ping');

  // Search locations
  Route::any('/get-depots-by-city/{locationCode}', 'AVController@getDepotsByCity');

  // Search available vehicles
  Route::any(
    'search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{vehicleCategory}/{vehicleClass}',
    'AVController@searchVehicles'
  );

  // Booking for a vehicle
  Route::any(
    'book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{firstName}/{lastName}/{countryCode}/{vehicleCategory}/{vehicleClass}',
    'AVController@book'
  );

  // Booking details
  Route::any(
    'get-booking-details/{bookingId}/{surname}',
    'AVController@getBookingInfo'
  );

  // Cancel a booking
  Route::any('cancel-booking/{bookingId}/{surname}',
    'AVController@cancelBooking'
  );

  // Update a booking
  Route::any(
    'modify-booking/{bookingId}/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{firstName}/{lastName}/{countryCode}/{vehicleCategory}/{vehicleClass}',
    'AVController@modifyBooking'
  );

  // Get rates
  Route::any(
    'get-rates/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{carCategory}/{vehicleClass}',
    'AVController@getRates'
  );
});


Route::group(array('prefix' => 'BG'), function() {
  // Get ping response
  Route::any('/ping', 'AVController@ping');

  // Search locations
  Route::any('/get-depots-by-city/{locationCode}', 'BGController@getDepotsByCity');

  // Search available vehicles
  Route::any(
    'search/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{vehicleCategory}/{vehicleClass}',
    'BGController@searchVehicles'
  );

  // Booking for a vehicle
  Route::any(
    'book/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{firstName}/{lastName}/{countryCode}/{vehicleCategory}/{vehicleClass}',
    'BGController@book'
  );

  // Booking details
  Route::any(
    'get-booking-details/{bookingId}/{surname}',
    'BGController@getBookingInfo'
  );

  // Cancel a booking
  Route::any('cancel-booking/{bookingId}/{surname}',
    'BGController@cancelBooking'
  );

  // Update a booking
  Route::any(
    'modify-booking/{bookingId}/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{firstName}/{lastName}/{countryCode}/{vehicleCategory}/{vehicleClass}',
    'BGController@modifyBooking'
  );

  // Get rates
  Route::any(
    'get-rates/{pickUpDate}/{pickUpTime}/{returnDate}/{returnTime}/{pickUpLocationCode}/{returnLocationCode}/{countryCode}/{carCategory}/{vehicleClass}',
    'BGController@getRates'
  );
});


Route::controller('vehicle-classification', 'VehicleClassificationController');
Route::controller('location', 'LocationController');
Route::controller('cron', 'CronController');

Route::get('/', function()
{
	/*App::abort(404);*/
});