<?php 

class RSController extends BaseController
{
    const DEFAULT_SUPPLIER_CODE = "RS";

    private $supplierApi;

    public function __construct()
    {
        $this->supplierApi = App::make(self::DEFAULT_SUPPLIER_CODE);
    }

    public function showAngularTutorial()
    {
        return View::make("angular");
    }
    public function doBookingWithEquipments()
    {
        return Response::json($this->supplierApi->doBooking(
                                Input::get("pickUpDate"),
                                Input::get("pickUpTime"),
                                Input::get("returnDate"),
                                Input::get("returnTime"),
                                Input::get("pickUpLocationCode"), 
                                Input::get("returnLocationCode"),
                                Input::get("vehicleClass"),
                                Input::get("rateId"),
                                Input::get("countryCode"),
                                Input::get("vehicleEquipments")
                            ));
    }

    public function showBooking()
    {
        return View::make('rs-booking');
    }

    public function getBookingDetails($bookingId)
    {
        return Response::json($this->supplierApi->getBookingDetails($bookingId));
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

    public function cancelBooking($bookingId)
    {
        return Response::json($this->supplierApi->cancelBooking($bookingId));
    }

    public function doBooking(
        $pickUpDate,
        $pickUpTime,
        $returnDate,
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode,
        $vehicleClass,
        $rateId,
        $countryCode 
    ) {
        return Response::json($this->supplierApi->doBooking(
                                $pickUpDate,
                                $pickUpTime,
                                $returnDate,
                                $returnTime,
                                $pickUpLocationCode, 
                                $returnLocationCode,
                                $vehicleClass,
                                $rateId,
                                $countryCode 
                            ));
    }

}
