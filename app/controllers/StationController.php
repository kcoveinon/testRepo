<?php

class StationController extends BaseController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function anyGet($countryCode = '', $cityName = '')
	{
		if (Request::isMethod('post')) {
			$countryCode = Input::get('countryCode', '');
			$cityName    = Input::get('cityName', '');
		}

		$result = array();

		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->getStations($countryCode, $cityName);
		}

		return Response::json($result);
	}

	public function anyGetByCode($stationCode = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
		}

		$result = array();

		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->getStation($stationCode);
		}

		return Response::json($result);
	}

	public function anyGetSchedule($stationCode = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
		}

		$result = array();

		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->getStationSchedule($stationCode);
		}

		return Response::json($result);
	}

	public function anyGetOpenHours($stationCode = '', $date = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
			$date        = Input::get('date', '');
		}

		$result = array();

		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->getOpenHours($stationCode, $date);
		}

		return Response::json($result);	
	}
}