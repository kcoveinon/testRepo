<?php 

class HZController extends BaseController
{

	const DEFAULT_SUPPLIER_CODE = "HZ";

	public function __construct()
	{
		parent::__construct();
	}

	public function getLocationDepots($locationCode, $countryCode)
	{
		$hertzApi = App::make(self::DEFAULT_SUPPLIER_CODE);
		$result = $hertzApi->getLocationDepots($locationCode, $countryCode);
		return Response::json($result);
	}

	public function getDepotDetails($locationCode, $countryCode)
	{
		$hertzApi = App::make(self::DEFAULT_SUPPLIER_CODE);
		$result = $hertzApi->getDepotDetails($locationCode, $countryCode);
		return Response::json($result);
	}

	public function searchVehicles($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationCode, $returnLocationCode, $countryCode, $driverAge)
	{
		$hertzApi = App::make(self::DEFAULT_SUPPLIER_CODE);
      	$result = $hertzApi->searchVehicles($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationCode, $returnLocationCode, $countryCode, $driverAge);

        return Response::json($result);
	}

	public function modifyBooking($bookingId, $pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $vehicleCategory, $vehicleClass)
	{
		$hertzApi = App::make(self::DEFAULT_SUPPLIER_CODE);
		$result = $hertzApi->modifyBooking($bookingId, $pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $vehicleCategory, $vehicleClass);
		return Response::json($result);	
	}

	public function cancelBooking($bookingId, $countryCode)
	{
		$hertzApi = App::make(self::DEFAULT_SUPPLIER_CODE);
		$result = $hertzApi->cancelBooking($bookingId, $countryCode);
		return Response::json($result);	
	}

	public function getBookingInfo($bookingId, $countryCode)
	{
		$hertzApi = App::make(self::DEFAULT_SUPPLIER_CODE);
		$result = $hertzApi->getBookingDetails($bookingId, $countryCode);
		return Response::json($result);	
	}

	public function book($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationCode, $returnLocationCode, $countryCode, $vehicleCategory, $vehicleClass)
	{
		$supplierApi = App::make(self::DEFAULT_SUPPLIER_CODE);

		$result = $supplierApi->doBooking(
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

}

