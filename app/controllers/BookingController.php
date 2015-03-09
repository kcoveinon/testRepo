<?php

class BookingController extends BaseController 
{
	public function book()
	{
		exit();
	}
	public function anyCreate(
		$vehicleCategoryCode = '',
		$checkoutStationCode = '',
		$checkinStationCode  = '',
		$checkoutDate        = '',
		$checkinDate         = '',
		$checkoutTime        = '',
		$checkinTime         = '',
		$countryOfResidence  = '',
		$firstName           = '',
		$lastName            = ''
	) {
		if (Request::isMethod('post')) {
			$vehicleCategoryCode = Input::get('vehicleCategoryCode', '');				
			$stationCodes = Input::get('stationCodes', array(
				'checkout' => '',
				'checkin'  => '',
			));

			$driver = Input::get('driver', array(
				'firstName'          => '',
				'lastName'           => '',
				'countryOfResidence' => '',
			));

		} else {
			$stationCodes = array(
				'checkout' => $checkoutStationCode,
				'checkin'  => $checkinStationCode,
			);

			$driver = array(
				'firstName'          => $firstName,
				'lastName'           => $lastName,
				'countryOfResidence' => $countryOfResidence
			);
		}

		$result = array();
		
		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->createBooking(array(
				'vehicleCategoryCode' => $vehicleCategoryCode,
				'stationCodes'        => $stationCodes,
				'driver'              => $driver
			));
		}

		return Response::json($result);
	}

	public function anyUpdate() {
		if (Request::isMethod('post')) {

		}
	}

	public function anyCancel() {
		if (Request::isMethod('post')) {

		}
	}
}