<?php

class AVController extends BaseController
{
	const DEFAULT_SUPPLIER_CODE = "AV";

	public function __construct()
	{
		parent::__construct();

		$this->supplierApi = App::make(self::DEFAULT_SUPPLIER_CODE);
	}

	/**
	 * @param $pickUpDate
	 * @param $pickUpTime
	 * @param $returnDate
	 * @param $returnTime
	 * @param $pickUpLocationCode
	 * @param $returnLocationCode
	 * @param $countryCode
	 * @param $vehicleCategory
	 * @param $vehicleClass
	 *
	 * @return mixed
	 */
	public function searchVehicles(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpLocationCode,
		$returnLocationCode,
		$countryCode,
		$vehicleCategory,
		$vehicleClass
	) {
		$result = $this->supplierApi->searchVehicles(
			$pickUpDate,
			$pickUpTime,
			$returnDate,
			$returnTime,
			$pickUpLocationCode,
			$returnLocationCode,
			$countryCode,
			$vehicleCategory,
			$vehicleClass
		);

		return Response::json($result);
	}

	/**
	 * @param $locationCode
	 *
	 * @return mixed
	 */
	public function getDepotsByCity($locationCode)
	{
		$result = $this->supplierApi->getDepots($locationCode);

		return Response::json($result);
	}

	/**
	 * @param $pickUpDate
	 * @param $pickUpTime
	 * @param $returnDate
	 * @param $returnTime
	 * @param $pickUpLocationCode
	 * @param $returnLocationCode
	 * @param $firstName
	 * @param $lastName
	 * @param $countryCode
	 * @param $vehicleCategory
	 * @param $vehicleClass
	 *
	 * @return mixed
	 */
	public function book(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpLocationCode,
		$returnLocationCode,
		$firstName,
		$lastName,
		$countryCode,
		$vehicleCategory,
		$vehicleClass
	) {
		$result = $data = $this->supplierApi->doBooking(
			$pickUpDate,
			$pickUpTime,
			$returnDate,
			$returnTime,
			$pickUpLocationCode,
			$returnLocationCode,
			$firstName,
			$lastName,
			$countryCode,
			$vehicleCategory,
			$vehicleClass
		);

		return Response::json($result);
	}

	/**
	 * @param $bookingId
	 * @param $pickUpDate
	 * @param $pickUpTime
	 * @param $returnDate
	 * @param $returnTime
	 * @param $pickUpLocationCode
	 * @param $returnLocationCode
	 * @param $firstName
	 * @param $lastName
	 * @param $countryCode
	 * @param $vehicleCategory
	 * @param $vehicleClass
	 *
	 * @return mixed
	 */
	public function modifyBooking(
		$bookingId,
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpLocationCode,
		$returnLocationCode,
		$firstName,
		$lastName,
		$countryCode,
		$vehicleCategory,
		$vehicleClass
	) {
		$result = $this->supplierApi->modifyBooking(
			$bookingId,
			$pickUpDate,
			$pickUpTime,
			$returnDate,
			$returnTime,
			$pickUpLocationCode,
			$returnLocationCode,
			$firstName,
			$lastName,
			$countryCode,
			$vehicleCategory,
			$vehicleClass
		);

		return Response::json($result);
	}

	/**
	 * @param $bookingId
	 * @param $surname
	 *
	 * @return mixed
	 */
	public function getBookingInfo($bookingId, $surname)
	{
		$result = $this->supplierApi->getBookingDetails($bookingId, $surname);
		return Response::json($result);
	}

	/**
	 * @param $bookingId
	 * @param $surname
	 *
	 * @return mixed
	 */
	public function cancelBooking($bookingId, $surname)
	{
		$result = $this->supplierApi->cancelBooking($bookingId, $surname);
		return Response::json($result);
	}

	/**
	 * @param $pickUpDate
	 * @param $pickUpTime
	 * @param $returnDate
	 * @param $returnTime
	 * @param $pickUpLocationCode
	 * @param $returnLocationCode
	 * @param $vehicleCategory
	 * @param $vehicleClass
	 *
	 * @return mixed
	 */
	public function getRates(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpLocationCode,
		$returnLocationCode,
		$vehicleCategory,
		$vehicleClass
	) {
		$result = $data = $this->supplierApi->getRates(
			$pickUpDate,
			$pickUpTime,
			$returnDate,
			$returnTime,
			$pickUpLocationCode,
			$returnLocationCode,
			$vehicleCategory,
			$vehicleClass
		);

		return Response::json($result);
	}

	public function ping()
	{
		$result = $this->supplierApi->ping();
		return Response::json($result);
	}
}