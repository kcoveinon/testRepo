<?php

class ECController extends BaseController
{
	private $supplierCode;

	public function __construct()
	{
		parent::__construct();

		$this->supplierCode = 'EC';
	}

	/* SupplierApi interface functions */
	public function searchVehicles(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpStationCode,
		$returnStationCode,
		$countryCode,
		$driverAge
	) {
		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->searchVehicles(
			$pickUpDate,
			$pickUpTime,
			$returnDate,
			$returnTime,
			$pickUpStationCode,
			$returnStationCode,
			$countryCode,
			$driverAge
		);

		return Response::json($result);
	}


	/* Europcar API functions */
	public function getVehicle($stationCode = '', $date = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
			$date        = Input::get('date', '');
		}

		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getVehicles($stationCode, $date);

		return Response::json($result);
	}

	public function getVehicleEquipmentList($stationCode = '', $date = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
			$date        = Input::get('date', '');
		}
		
		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getEquipmentList($stationCode, $date);

		return Response::json($result);
	}

	public function getVehicleQuote(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpStationCode,
		$returnStationCode,
		$countryOfResidence,
		$carCategoryCode
	) {
		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getQuote(
			$pickUpDate,
			$pickUpTime,
			$returnDate,
			$returnTime,
			$pickUpStationCode,
			$returnStationCode,
			$countryOfResidence,
			$carCategoryCode
		);

		return Response::json($result);
	}

	public function getVehicleMultipleRates(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpStationCode,
		$returnStationCode,
		$countryOfResidence,
		$carCategoryPatterns = ''
	) {
		$carCategoryPatterns = (array) $carCategoryPatterns;		

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getMultipleRates(
			$pickUpDate,
			$pickUpTime,
			$returnDate,
			$returnTime,
			$pickUpStationCode,
			$returnStationCode,
			$countryOfResidence,
			$carCategoryPatterns
		);

		return Response::json($result);	
	}

	public function getCountry()
	{
		$result = array();
		
		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getCountries();

		return Response::json($result);
	}

	public function getCountryResidence()
	{
		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getCountriesResidence();

		/*if(Request::isMethod('post')) {
			$supplierCode = Input::get('supplierCode', '');
		}

		if(!empty($supplierCode)) {
			$supplierCode = strtoupper($supplierCode);
		}
		$result = array();
		$status = '';
		$error = '';

		if(Supplier::isValid($supplierCode)) {											
			$functionName = debug_backtrace()[0]['function'];
			$functionName = lcfirst(str_replace('any', '', $functionName));
			$result = $supplierCode::getInstance()->$functionName();
		} else {				
			$result['error'] = 'Invalid supplier code';
			$result['status'] = 'FAILED';
		}*/

		return Response::json($result);
	}

	public function getCity($countryCode = '') 
	{
		if (Request::isMethod('post')) {
			$countryCode = Input::get('countryCode', '');
		}

		$result = array();
		
		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getCities($countryCode);

		return Response::json($result);
	}

	public function getStation($countryCode = '', $cityName = '')
	{
		if (Request::isMethod('post')) {
			$countryCode = Input::get('countryCode', '');
			$cityName    = Input::get('cityName', '');
		}

		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getStations($countryCode, $cityName);

		return Response::json($result);
	}

	public function getStationByCode($stationCode = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
		}

		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getStation($stationCode);

		return Response::json($result);
	}

	public function getStationSchedule($stationCode = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
		}

		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getStationSchedule($stationCode);

		return Response::json($result);
	}

	public function getStationOpenHours($stationCode = '', $date = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
			$date        = Input::get('date', '');
		}

		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getOpenHours($stationCode, $date);

		return Response::json($result);	
	}

	public function bookReservationOld(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpStationCode,
		$returnStationCode,
		$carCategoryCode,
		$title,
		$firstName,
		$lastName,
		$countryOfResidence
	) {
		// get $equipmentList from Input::post('equipmentList')
		$equipmentList = array();

		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->bookReservation(
				      $pickUpDate,
				      $pickUpTime,
				      $returnDate,
				      $returnTime,
				      $pickUpStationCode,
				      $returnStationCode,
				      $carCategoryCode,
				      $title,
				      $firstName,
				      $lastName,
				      $countryOfResidence,
				      $equipmentList
				  );

		return Response::json($result);
	}

	public function bookReservation()
	{
		$result = array();
		$inputValues = Input::all();

		$pickUpDate         = Input::get('pickUpDate');
		$pickUpTime         = Input::get('pickUpTime');
		$returnDate         = Input::get('returnDate');
		$returnTime         = Input::get('returnTime');
		$pickUpStationCode  = Input::get('pickUpStationCode');
		$returnStationCode  = Input::get('returnStationCode');
		$carCategoryCode    = Input::get('carCategoryCode');
		$title              = Input::get('title');
		$firstName          = Input::get('firstName');
		$lastName           = Input::get('lastName');
		$countryOfResidence = Input::get('countryOfResidence');
		$equipmentList      = array();

		foreach (Input::get('equipmentList') as $equipment) {
			$equipmentList[$equipment['code']] = $equipment['quantity'];
		}

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->bookReservation(
				      $pickUpDate,
				      $pickUpTime,
				      $returnDate,
				      $returnTime,
				      $pickUpStationCode,
				      $returnStationCode,
				      $carCategoryCode,
				      $title,
				      $firstName,
				      $lastName,
				      $countryOfResidence,
				      $equipmentList
				  );

		return Response::json($result);
	}

	public function searchReservationById($reservationNumber)
	{
		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->searchReservationById($reservationNumber);

		return Response::json($result);
	}

	public function cancelReservation($reservationNumber)
	{
		$result = array();

		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->cancelReservation($reservationNumber);

		return Response::json($result);
	}
}