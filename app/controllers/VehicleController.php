<?php

class VehicleController extends BaseController
{
	public function __construct()
	{
		parent::__construct();
	}



	public function search($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $driverAge)
	{
		$result = array();

		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->searchVehicles($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $driverAge);
		}

		return Response::json($result);
	}

	public function anyGet($stationCode = '', $date = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
			$date        = Input::get('date', '');
		}

		$result = array();

		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);
			$result = $supplierApi->getVehicles($stationCode, $date);
		}

		return Response::json($result);
	}

	public function anyGetEquipmentList($stationCode = '', $date = '')
	{
		if (Request::isMethod('post')) {
			$stationCode = Input::get('stationCode', '');
			$date        = Input::get('date', '');
		}
		
		$result = array();

		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->getEquipmentList($stationCode, $date);
		}

		return Response::json($result);
	}
}