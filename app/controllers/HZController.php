<?php 

class HZController extends BaseController
{

	public function __construct()
	{
		parent::__construct();
	}

	public function getLocationDepots($locationCode, $countryCode)
	{
		$hertApi = App::make("HZ");
		
		$result = $hertApi->getLocationDepots($locationCode, $countryCode);

		return Response::json($result);
	}

	public function getDepotDetails($locationCode, $countryCode)
	{
		$hertApi = App::make("HZ");
		
		$result = $hertApi->getDepotDetails($locationCode, $countryCode);

		return Response::json($result);
	}

	public function search($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $driverAge)
	{
		$result = array();

        $pickUpDepots = DB::select(DB::raw(
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
	                ld.locationID = :pickUpId"
        	),array( "pickUpId" => $pickUpLocationId));


        foreach ($pickUpDepots as $pickUpDepot) {
            if (!isset($supplierPickUpDepotCodes[$pickUpDepot->supplierCode])) {
                $supplierPickUpDepotCodes[$pickUpDepot->supplierCode] = $pickUpDepot->depotCode;
            }
        }

        if ($returnLocationId == $pickUpLocationId) {
            $supplierReturnDepotCodes = $supplierPickUpDepotCodes;
        } else {
            $returnDepots = DB::select(DB::raw(
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
                    ld.locationID = :returnIds"
            ),array( "pickUpId" => $pickUpLocationId));

            foreach ($returnDepots as $returnDepot) {
                if (!isset($supplierReturnDepotCodes[$returnDepot->supplierCode])) {
                    $supplierReturnDepotCodes[$returnDepot->supplierCode] = $returnDepot->depotCode;
                }
            }
        }

        foreach ($this->supplierCodes as $supplierCode) {
            if (isset($supplierPickUpDepotCodes[$supplierCode]) && isset($supplierReturnDepotCodes[$supplierCode])) {
                $supplierApi = App::make($supplierCode);

                $result = $supplierApi->searchVehicles($pickUpDate, $pickUpTime, $returnDate, $returnTime, $supplierPickUpDepotCodes[$supplierCode], $supplierReturnDepotCodes[$supplierCode], $countryCode, $driverAge);
            }
        }

        return Response::json($result);
	}


	
	public function modifyBooking($bookingId, $pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $vehCategory, $vehClass)
	{
		$result = [];
		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);
			$result[] = $supplierApi->modifyBooking($bookingId, $pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $vehCategory, $vehClass);

		}
		return Response::json($result);	
	}

	public function cancelBooking($bookingId, $countryCode)
	{
		$result = [];
		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);
			$result[] = $supplierApi->cancelBooking($bookingId, $countryCode);

		}
		return Response::json($result);	
	}

	public function getBookingInfo($bookingId, $countryCode)
	{
		$result = [];
		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);
			$result[] = $supplierApi->getBookingDetails($bookingId, $countryCode);

		}
		return Response::json($result);	
	}

	public function book($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $vehCategory, $vehClass)
	{
		$result = [];
		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result[] = $supplierApi->doBooking(
							$pickUpDate, 
							$pickUpTime, 
							$returnDate, 
							$returnTime, 
							$pickUpLocationId, 
							$returnLocationId,
							$countryCode, 
							$vehCategory, 
							$vehClass
						);
		}
		return Response::json($result);		
	}

}

