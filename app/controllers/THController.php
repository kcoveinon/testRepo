<?php
class THController extends BaseController
{

    private $ThriftyClass;

    public function __construct()
    {
        parent::__construct();
        $this->ThriftyClass = App::make('TH');

    }

    public function search(
        $pickUpDate,
        $pickUpTime,
        $returnDate,
        $returnTime,
        $pickUpLocationCode,
        $returnLocationCode,
        $countryCode,
        $driverAge
    ){
        $supplierApi = App::make('TH');

        $result = $supplierApi->searchVehicles(

            $pickUpDate,
            $pickUpTime,
            $returnDate,
            $returnTime,
            $pickUpLocationCode,
            $returnLocationCode,
            $countryCode,
            $driverAge
        );

        return Response::json($result);
    }

    public function getAllDepots()
    {
        $response = $data = $this->ThriftyClass->getLocations();
        return Response::json($response);
    }

    public function getDepotsByCity($locationCode)
    {
        $data = $this->ThriftyClass->getDepotsPerLocation($locationCode);
        return Response::json($data);
    }
    
    /**
     * 
     * Get location details
     * 
     * @param type $locationCode
     * @return type
     */
    public function depotDetails($locationCode)
    {
        $data = $this->ThriftyClass->getLocationDetails($locationCode);
        return Response::json($data);
    }
    
    /**
     * Get rates for supplier 
     * 
     * @param type $pickUpDate
     * @param type $pickUpTime
     * @param type $returnDate
     * @param type $returnTime
     * @param type $pickUpLocation
     * @param type $returnLocation
     * @param type $carCategory
     * @return json
     */
    public function getRates(
        $pickUpDate,
        $pickUpTime,
        $returnDate,
        $returnTime,
        $pickUpLocation,
        $returnLocation,
        $carCategory
    ){
         $data = $this->ThriftyClass->getRates(
            $pickUpDate,
            $pickUpTime,
            $returnDate,
            $returnTime,
            $pickUpLocation,
            $returnLocation,
            $carCategory
        );
        return Response::json($data);
    }

    /**
     * Create a booking function
     *
     * @param datetime $pickUpDateTime
     * @param datetime $returnDateTime
     * @param locationcode $pickUpLocationCode
     * @param locatiocode $returnLocationCode
     * @param int $carCategory
     */
    public function createBooking(
        $pickUpDate,
        $pickUpTime,
        $returnDate,
        $returnTime,
        $pickUpLocationCode,
        $returnLocationCode,
        $carCategory
    )
    {        
        $result = $data = $this->ThriftyClass->createBooking(
            $pickUpDate,
            $pickUpTime,
            $returnDate,
            $returnTime,
            $pickUpLocationCode,
            $returnLocationCode,
            $carCategory
        );

       return Response::json($result);
    }
    
    /**
     * Get the booking details
     * @param string $bookingId
     * @return json
     */
    public function getBookingDetails($bookingId){
        $result = $data = $this->ThriftyClass->getBookingDetails($bookingId);        
        return Response::json($result);
    }
    
    /**
     * Cancel booking
     * @param type $bookingID
     * @return json
     */
    public function cancelBooking($bookingID){
        $result = $data = $this->ThriftyClass->cancelBooking($bookingID);
        return Response::json($result);
    }
    
    public function testAu()
    {                
        $data = $this->ThriftyClass->testRquestAu();
        echo '<pre> ' . __FILE__ . ':' . __LINE__ . '<br/>';
        print_r($data);
        echo '</pre>';
        die;
    }
    
}
