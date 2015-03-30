<?php 

class HZController extends BaseController
{

	const DEFAULT_SUPPLIER_CODE = "HZ";

    private $supplierApi;

	public function __construct()
	{
		parent::__construct();
        $this->supplierApi = App::make(self::DEFAULT_SUPPLIER_CODE);		
	}

	public function showBooking()
	{
		return View::make("hz-booking");
	}

	public function getLocationDepots($locationCode)
	{
		$result = $this->supplierApi->getLocationDepots($locationCode);
		return Response::json($result);
	}

	public function getDepotDetails($locationCode)
	{
		$result = $this->supplierApi->getDepotDetails($locationCode);
		return Response::json($result);
	}

	public function searchVehicles($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationCode, $returnLocationCode, $countryCode, $driverAge)
	{
      	$result = $this->supplierApi->searchVehicles($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationCode, $returnLocationCode, $countryCode, $driverAge);
        return Response::json($result);
	}

	public function modifyBooking($bookingId, $pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationCode, $returnLocationCode, $vehicleCategory, $vehicleClass)
	{
		$result = $this->supplierApi->modifyBooking($bookingId, $pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationCode, $returnLocationCode, $vehicleCategory, $vehicleClass);
		return Response::json($result);	
	}

	public function doBookingWithEquipments()
	{
		$result = $this->supplierApi->doBooking(
						Input::get("pickUpDate"), 
						Input::get("pickUpTime"), 
						Input::get("returnDate"), 
						Input::get("returnTime"), 
						Input::get("pickUpLocationCode"),
						Input::get("returnLocationCode"),
						Input::get("countryCode"), 
						Input::get("vehicleCategory"), 
						Input::get("vehicleClass"),
						Input::get("vehicleEquipments")
					);

		return Response::json($result);			
	}

	public function cancelBooking($bookingId)
	{
		$result = $this->supplierApi->cancelBooking($bookingId);
		return Response::json($result);	
	}

	public function getBookingInfo($bookingId)
	{
		$result = $this->supplierApi->getBookingDetails($bookingId);
		return Response::json($result);	
	}

	public function book($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationCode, $returnLocationCode, $countryCode, $vehicleCategory, $vehicleClass)
	{
		$result = $this->supplierApi->doBooking(
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

	public function exportDepotCompilation()
	{
	    $file_content = file_get_contents(public_path() . "/misc/GDEX1ADC.txt");
	    $explodedArray = explode("|", $file_content);
	    $chunkedArray = array_chunk($explodedArray, 110, false);
	    echo '<pre>'; print_r($chunkedArray); exit();
	}
}

