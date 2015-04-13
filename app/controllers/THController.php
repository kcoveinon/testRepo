<?php
class THController extends BaseController
{

    private $supplierClass;

    const SUPPLIER_ID = 160,
            AU_COUNTRY_ID = 2053;

    public function __construct()
    {
        parent::__construct();
        $this->supplierClass = App::make('TH');

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
    
    public function vehAvailRate(
        $pickUpDate,
        $pickUpTime,
        $returnDate,
        $returnTime,
        $pickUpLocationCode,
        $returnLocationCode,       
            
        $driverAge
    ){
        $supplierApi = App::make('TH');

        $result = $supplierApi->vehAvailRate(
            $pickUpDate,
            $pickUpTime,
            $returnDate,
            $returnTime,
            $pickUpLocationCode,
            $returnLocationCode,            
            $driverAge
        );
        
        return Response::json($result);
    }

    public function getAllDepots()
    {
        $response = $data = $this->supplierClass->vehLocDetailsNotif();
        return Response::json($response);
    }

    public function getDepotsByCity($locationCode)
    {
        $data = $this->supplierClass->getDepotsPerLocation($locationCode);
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
        $data = $this->supplierClass->vehLocDetail($locationCode);
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
         $data = $this->supplierClass->getRates(
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
        $carCategory,
        $inetId
    ){        
        $result = $data = $this->supplierClass->VehRes(
            $pickUpDate,
            $pickUpTime,
            $returnDate,
            $returnTime,
            $pickUpLocationCode,
            $returnLocationCode,
            $carCategory,
            $inetId
        );

       return Response::json($result);
    }
    
    /**
     * Get the booking details
     * @param string $bookingId
     * @return json
     */
    public function getBookingDetails($bookingId)
    {
        $result = $data   = $this->supplierClass->getBookingDetails($bookingId);
        return Response::json($result);
    }

    /**
     * Cancel booking
     * @param type $bookingID
     * @return json
     */
    public function cancelBooking($bookingID)
    {
        $result = $data = $this->supplierClass->cancelBooking($bookingID);
        return Response::json($result);
    }
    
    /**
     * Update all depots for thrifty
     * @return type json if success or not 
     */
    public function updateDepots()
    {
        // Time counter
        $timeStart    = time();
        // Set time limit to 0, we have a large chuink of data here
        set_time_limit(0);                
        $updatedDepots = array();
        $data = $this->supplierClass->vehLocDetailsNotif();
        
        if (isset($data->VehLocDetailsNotifResult->LocationDetails->LocationDetail)) {
            
            $depots = $data->VehLocDetailsNotifResult->LocationDetails->LocationDetail;
            
            foreach ($depots as $depot) {
                
                $depotDetail = $this->supplierClass->vehLocDetail($depot->Code);
                $updatedDepots[] = $depot->Code;
                
                if(
                    isset($depotDetail->VehLocDetailResult->LocationDetail) 
                    && !empty($depotDetail->VehLocDetailResult->LocationDetail)
                ){ 
                    $depotRecord = $depotDetail->VehLocDetailResult;                    
                    $phoneNumber = is_array($depotRecord->LocationDetail->Telephone) ? reset($depotRecord->LocationDetail->Telephone) : NULL;                                        
                    $coordinates = isset( $depotRecord->LocationDetail->AdditionalInfo->CounterLocation->_ ) 
                            ? explode(',',$depotRecord->LocationDetail->AdditionalInfo->CounterLocation->_) 
                            : array();
                    $updateData  = array(
                        'locationCode' => $depotRecord->LocationDetail->Code,
                        'supplierID'   => self::SUPPLIER_ID,
                        'countryCode'  => self::AU_COUNTRY_ID,
                        'locationName' => $depotRecord->LocationDetail->Name,
                        'address'      => $depotRecord->LocationDetail->Address->AddressLine,
                        'city'         => $depotRecord->LocationDetail->Address->CityName,
                        'isAirport'    => $depotRecord->LocationDetail->AtAirport,
                        'postCode'     => $depotRecord->LocationDetail->Address->PostalCode,
                        'phoneNumber'  => is_object($phoneNumber) && isset($phoneNumber->PhoneNumber) ? $phoneNumber->PhoneNumber : NULL,
                        'latitude'     => isset($coordinates[0]) ? $coordinates[0] : 'N/A',
                        'longitude'    => isset($coordinates[1]) ? $coordinates[1] : 'N/A',
                        'deletedAt'    => 0
                    );

                    Depot::updateOrCreateDepot($updateData);                                        
                }    
                
            }
            
            // Mark as success
            $result['success'] = true;
            $result['message'] = 'Records updated';
            $result['rowsAdded'] = count($updatedDepots);
                                    
            // Mark as deleted all unupdated records
            Depot::markAsDeletedOtherDepots(self::SUPPLIER_ID, $updatedDepots);
            
        }                        
        
        // before the return keyword
        $result['executionTime'] = time() - $timeStart;
        
        return Response::json($result);
        
    }

    public function testAu()
    {                
        $this->supplierClass->testRquestAu();        
    }
    
}
