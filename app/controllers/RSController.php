<?php 

class RSController extends BaseController
{
    const DEFAULT_SUPPLIER_CODE = "RS";

    private $supplierApi;

    public function __construct()
    {
        $this->supplierApi = App::make(self::DEFAULT_SUPPLIER_CODE);
    }
    public function searchVehicles(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate, 
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode, 
        $vehicleClass, 
        $countryCode        
    ) {
        return Response::json($this->supplierApi->search(
                                $pickUpDate, 
                                $pickUpTime, 
                                $returnDate, 
                                $returnTime,
                                $pickUpLocationCode, 
                                $returnLocationCode, 
                                $vehicleClass, 
                                $countryCode  
                        )); 
    }

    public function cancelBooking()
    {
        return Response::json($this->supplierApi->cancelBooking());
    }

    public function doBooking()
    {
        return Response::json($this->supplierApi->doBooking());        
    }

}
