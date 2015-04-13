<?php

class TH extends SupplierApi
{
    private $soapRequest;

    const TIME_LIMIT  = 250;
    const VENDOR_CODE = 'ZT';
    const VENDOR_NAME = 'Thrifty';
    const VENDOR_CLASS_CODE = 'TH';
    
    private $transmitionCode = array(
        'Automatic' => 'AT',
        'Manual'    => 'MT'
    );

    public function __construct()
    {
        $this->soapRequest = array(
            'xmlns'         => 'http://www.opentravel.org/OTA/2003/05',
            'xmlns:xsi'     => 'http://www.w3.org/2001/XMLSchema-instance',
            'Target'        => Config::get('TH.api.target'),
            'Version'       => '2.000',
            'PrimaryLangID' => Config::get('TH.AU.primaryLangID'),
            'POS'           => array(
                'Source' => array(
                    'ISOCountry' => 'AU',
                    'ISOCurrency' => 'AUD',
                    'RequestorID' => array(
                        'Type' => Config::get('TH.AU.requestorIdType'),
                        'ID'   => Config::get('TH.AU.accountNumber')
                    )
                )
            )
        );
    }
               
    private function getSoapClient($url)
    {
        if (empty($url)) {
            return false;
        }

        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                ),
                'ssl'  => array(
                    'ciphers'          => 'RC4-SHA',
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                )
            );

            // SOAP 1.2 client
            $context = stream_context_create($opts);
            $clientUrl = Config::get('TH.US.url') . $url;
            
            $return =  new SoapClient($clientUrl, array(
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE,
                'trace'          => 1,
                'exceptions'     => 1,
            ));
            
            return $return;
        } catch (Exception $e) {
            // Do nothing
            echo $e->getMessage();
        }
    }
    
    private function getSoapClientAu()
    {
        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                ),
                'ssl'  => array(
                    'ciphers'          => 'RC4-SHA',
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                )
            );

            // SOAP 1.2 client
            $context = stream_context_create($opts);
            set_time_limit(self::TIME_LIMIT);
            $client = new SoapClient(Config::get('TH.AU.url') , array(
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE,
                'trace'          => 1,
                 'exceptions'     => 1,
            ));
            
            return $client;
            
        } catch (Exception $e) {
            // Do nothing
            echo $e->getMessage();
        }
    }

    /**
     * Source : http://stackoverflow.com/questions/21861077/soap-error-parsing-wsdl-couldnt-load-from-but-works-on-wamp
     */
    public function searchVehicles(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate, 
        $returnTime, 
        $pickUpLocationCode, 
        $returnLocationCode, 
        $countryCode, 
        $driverAge
    ) {        
        $result       = array();
        $timeStart    = time();
        $acrissHelper = new \AcrissHelper();        
        $client       = $this->getSoapClientAu();
                
        if (empty($client)) {
            return false;
        }
        
        $requestCore = array(
            'request' => array(
                'VehAvailRQCore' => array(
                    'VehRentalCore' => array(
                        'PickUpDateTime' => date('Y-m-d\TH:i:s', strtotime($pickUpDate . ' ' . $pickUpTime)),
                        'ReturnDateTime' => date('Y-m-d\TH:i:s', strtotime($returnDate . ' ' . $returnTime)),
                        'PickUpLocation' => $pickUpLocationCode,
                        'ReturnLocation' => $returnLocationCode,                        
                        'DriverType'     => array(
                            'Age' => $driverAge
                        ),
                    ),
                ),
            )
        );

        set_time_limit(self::TIME_LIMIT);
        $data = $client->VehAvailRate($requestCore);
                    
        if(isset($data->OTA_VehAvailRateRS->Errors)){
            return $data;
        }
               
        // Get the results
        $vehAvails = $data->VehAvailRateResult
                        ->VehAvailRSCore
                        ->VehVendorAvails
                        ->VehVendorAvail
                        ->VehAvails
                        ->VehAvail;
                
        if (isset($data->VehAvailRateResult->Success)) {
            $result['status'] = 'OK';

            foreach ($vehAvails as $vehAvail) {
                $result['data'][] = array(
                    'supplierCode' => self::VENDOR_CLASS_CODE,
                    'hasAirCondition' => $vehAvail->VehAvailCore->Vehicle->AirConditionInd,
                    'transmission' => array(
                        'code'        => $this->transmitionCode[$vehAvail->VehAvailCore->Vehicle->TransmissionType],
                        'description' => "{$vehAvail->VehAvailCore->Vehicle->TransmissionType} Transmission"
                    ),
                    'baggageQty' => $vehAvail->VehAvailCore->Vehicle->BaggageQuantity,
                    'co2Qty' => 'N/A',
                    'categoryCode' => $vehAvail->VehAvailCore->Vehicle->VehMakeModel->Code,
                    'expandedCode' => $acrissHelper->expandCode($vehAvail->VehAvailCore->Vehicle->VehMakeModel->Code),
                    'doorCount' => 'N/A',
                    'name' => $vehAvail->VehAvailCore->Vehicle->VehMakeModel->Name,
                    'seats' => $vehAvail->VehAvailCore->Vehicle->PassengerQuantity,
                    'vehicleStatus' => array(
                        'code'        => 'N/A',
                        'description' => 'N/A'
                    ),
                    'maxAge' => 'N/A',
                    'minAge' => 'N/A',
                    'rateId' => 'N/A',
                    'basePrice' => 'N/A',
                    'currency' => 'N/A',
                    'bookingCurrencyOfTotalRateEstimate' => 'N/A',
                    'xrsBasePrice' => 'N/A',
                    'xrsBasePriceInBookingCurrency' => 'N/A',
                    'totalRateEstimate' => 'N/A',
                    'totalRateEstimateInBookingCurrency' => 'N/A',
                );
            }
        }   
        
        // before the return keyword
        $result['executionTime'] = time() - $timeStart;
        $result['supplierCode']  = self::VENDOR_CLASS_CODE;

        return $result;
    }

    /**
     *
     * @return boolean|unknown
     */
    public function vehLocDetailsNotif()
    {
        $client = $this->getSoapClientAu();
        
        if (empty($client)) {            
            return false;
        }

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->VehLocDetailsNotif();
        return $data;
    }

    public function getDepotsPerLocation($locationCode)
    {
        $client = $this->getSoapClient('LocationService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }
        
        $requestCore = array(
            'VehLocSearchCriterion' => array(
                'ExactMatch'     => 'true',
                'ImportanceType' => 'Mandatory',
                'CodeRef'        => array(
                    'LocationCode' => $locationCode
                ),
                'RefPoint' => array($locationCode)
            ),
            'Vendor' => array(
                'Code' => self::VENDOR_CODE
            )
        );
      

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->GetReturnLocations(array(
            'OTA_VehLocSearchRQ' => array_merge($this->soapRequest, $requestCore)
        ));

        return $data;
    }

    public function getLocationDetails($locationCode)
    {
        $client = $this->getSoapClient('LocationService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }
        
        $requestCore = array(
            'Location' => array(
                'LocationCode' => $locationCode
            ),
            'Vendor'   => array(
                'Code' => self::VENDOR_CODE
            )
        );
                        
        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->GetLocationDetails(array(
            'OTA_VehLocDetailRQ' => array_merge($this->soapRequest, $requestCore)
        ));

        return $data;
    }

    /**
     *
     * Get rate per pick up location,date and car type
     * As of now always returning errors
     *
     * @param unknown $pickUpDate
     * @param unknown $pickUpTime
     * @param unknown $returnDate
     * @param unknown $returnTime
     * @param unknown $pickUpLocation
     * @param unknown $returnLocation
     * @param unknown $carCategory
     * @return boolean|unknown
     *
     */
    public function getRates(
        $pickUpDate, 
        $pickUpTime,
        $returnDate,
        $returnTime, 
        $pickUpLocation, 
        $returnLocation, 
        $carCategory
    ) {
        $client = $this->getSoapClient('RateService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }
        
        $requestCore = array(
            'VehAvailRQCore' => array(
                'Status'        => 'Available',
                'VehRentalCore' => array(
                    'PickUpDateTime' => date('Y-m-d\TH:i:s', strtotime($pickUpDate . ' ' . $pickUpTime)),
                    'ReturnDateTime' => date('Y-m-d\TH:i:s', strtotime($returnDate . ' ' . $returnTime)),
                    'PickUpLocation' => array(
                        'LocationCode' => $pickUpLocation
                    ),
                    'ReturnLocation' => array(
                        'LocationCode' => $returnLocation
                    )
                ),
                'VendorPrefs'   => array(
                    'VendorPref' => 'ZT'
                ),
                'VehPrefs'      => array(
                    'Code'        => $carCategory,
                    'CodeContext' => 'ACRISS'
                ),
                'RateQualifier' => array(
                    'RateCategory' => '16',
                    'RatePeriod'   => 'Daily'
                )
            )
        );

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);

        $data = $client->GetRates(array(
            'OTA_VehAvailRateRQ' => array_merge($this->soapRequest, $requestCore)
        ));

        return $data;
    }

    /**
     * Create a booking
     *
     * @param unknown $pickUpDateTime
     * @param unknown $returnDateTime
     * @param unknown $pickUpLocationCode
     * @param unknown $returnLocationCode
     * @param unknown $carCategory
     * @return unknown
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
    ) {
        $client = $this->getSoapClient('ReservationService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }
        
        $requestCore = array(
            'VehResRQCore' => array(
                'OptionChangeIndicator' => 'true',
                'VehRentalCore' => array(
                    'PickUpDateTime' => date('Y-m-d\TH:i:s', strtotime($pickUpDate . ' ' . $pickUpTime)),
                    'ReturnDateTime' => date('Y-m-d\TH:i:s', strtotime($returnDate . ' ' . $returnTime)),
                    'PickUpLocation' => array(
                        'LocationCode' => $pickUpLocationCode,
                    ),
                    'ReturnLocation' => array(
                        'LocationCode' => $returnLocationCode,
                    )
                ),
                'Customer' => array(
                    'Primary' => array( 
                        'PersonName' => array(
                            'GivenName' => 'TEST', // Todo : Add real values
                            'Surname'   => 'TESTER',
                        ), // Todo : Add real values      
                        'Email' => 'test@test.com',
                    ),
                ),
                'VendorPref' => array(
                    'Code' => 'ZT',
                ),
                
                'VehPref' => array(
                    'Code'        => $carCategory,
                    'CodeContext' => 'ACRISS',
                ),                
            ),
            'VehResRQInfo' => array(
                'Reference' => array(
                    'Type'       => '8',
                    'ID'         => $inetId,
                    'ID_Context' => 'InetID',                    
                )
            )
        );
       
        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->MakeReservation(array(
            'OTA_VehResRQ' => array_merge($this->soapRequest, $requestCore)
        ));
            
        return $data;
    }

    public function getBookingDetails($bookingId)
    {
        if (empty($bookingId)) {
            return false;
        }
        
        // Request core details
        $requestCore = array(
            'VehRetResRQCore' => array(
                'UniqueID'   => array(
                    'Type' => '14', // @TODO : Check what is this typeo
                    'ID'   => $bookingId,
                ),
                'PersonName' => array(
                    'GivenName' => 'test',
                    'Surname'   => 'tester',
                ),
            ),
            'VehRetResRQInfo' => array(
                'Vendor' => array(
                    'Code' => self::VENDOR_CODE,
                    'CompanyShortName' => self::VENDOR_NAME,
                ),
                'Telephone' => array(
                    'PhoneNumber' => '123-123-1234'
                ), // Christopher: Client phone number?
            ),
        );

        $client = $this->getSoapClient('ReservationService.svc?wsdl');
        if (empty($client)) {
            return false;
        }

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->GetReservation(array(
            'OTA_VehRetResRQ' => array_merge($this->soapRequest, $requestCore)
        ));

        return $data;
    }

    public function cancelBooking($bookingId)
    {   
        $client = $this->getSoapClient('ReservationService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }
        
        // Request core details
        $requestCore = array(
            'VehCancelRQCore' => array(
                'CancelType' => 'Cancel',
                'UniqueID'   => array(
                    'Type' => '14', // @TODO : Check what is this type
                    'ID'   => $bookingId,
                ),
                'PersonName' => array(
                    'GivenName' => 'test',
                    'Surname'   => 'tester',
                ),
            ),
            'VehCancelRQInfo' => array(
                'Vendor'    => array(
                    'Code' => self::VENDOR_CODE,
                ),
                'Telephone' => array(
                    'PhoneNumber' => '123-123-1234'
                ) // Christopher: Company
            ),
        );

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->CancelReservation(array(
            'OTA_VehCancelRQ' => array_merge($this->soapRequest, $requestCore)
        ));

        return $data;
    }
    
    
    /** Thrifty AU functions. It is using OTA 2007A **/
    
    /**
     * Search vehicles based on OTA 2007A specification
     * @param type $pickUpDate
     * @param type $pickUpTime
     * @param type $returnDate
     * @param type $returnTime
     * @param type $pickUpLocationCode
     * @param type $returnLocationCode
     * @param type $countryCode
     * @param type $driverAge
     * @return boolean
     */
    public function vehAvailRate(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate, 
        $returnTime, 
        $pickUpLocationCode, 
        $returnLocationCode,
        $driverAge
    ) {        
        $result       = array();
        $timeStart    = time();
        $acrissHelper = new \AcrissHelper();
        $client       = $this->getSoapClientAu();
        
        if (empty($client)) {
            return false;
        }
        
        $requestCore = array(
            'request' => array(
                'VehAvailRQCore' => array(
                    'VehRentalCore' => array(
                        'PickUpDateTime' => date('Y-m-d\TH:i:s', strtotime($pickUpDate . ' ' . $pickUpTime)),
                        'ReturnDateTime' => date('Y-m-d\TH:i:s', strtotime($returnDate . ' ' . $returnTime)),
                        'PickUpLocation' => $pickUpLocationCode,
                        'ReturnLocation' => $returnLocationCode,                        
                        'DriverType'     => array(
                            'Age' => $driverAge,
                        ),
                        'RateQualifier' => array(
                            'RateQualifier' => 'True',
                        ), 
                        'TPA_Extensions' => array(
                            'SingleQuote' => 'True',
                        ),
                    ),
                ),
            )
        );

        set_time_limit(self::TIME_LIMIT);
        $data = $client->VehAvailRate($requestCore);
        
        return $data;        
    }
    
    public function vehRes(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate,
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode, 
        $carCategory
    ) {
        $client = $this->getSoapClient('ReservationService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }
        
        $requestCore = array(
            'VehResRQCore' => array(                
                'VehRentalCore' => array(
                    'PickUpDateTime' => date('Y-m-d\TH:i:s', strtotime($pickUpDate . ' ' . $pickUpTime)),
                    'ReturnDateTime' => date('Y-m-d\TH:i:s', strtotime($returnDate . ' ' . $returnTime)),
                    'PickUpLocation' => array(
                        'LocationCode' => $pickUpLocationCode,
                    ),
                    'ReturnLocation' => array(
                        'LocationCode' => $returnLocationCode,
                    ),
                ),
                'Customer' => array(
                    'Primary' => array(
                        'BirthDate'  => '1989-01-01',
                        'PersonName' => array(
                            'GivenName' => 'TEST', // Todo : Add real values
                            'Surname' => 'TESTER',
                        ),
                        'Email' => array('test@test.com'),
                    )
                ),
                'VendorPref' => array(
                    'Code' => 'ZT',
                ),
                'VehPref' => array(
                    'VehType' => array(
                        'VehicleCategory' => '1', // OTA code for car check if we can give accriss
                        'DoorCount'       => '4', // Check if we need this
                    ),
                ),
                'SpecialEquipPrefs' => array(
                    'SpecialEquipPref' => array(
                        'EquipType' => '8', //OTA code
                        'Quantity'  => '1'
                    ),
                ),
            ),
        );

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->OTA_VehResRQ(array_merge($this->soapRequest, $requestCore));
        
        return $data;
    }
    
    public function getBookingDetails2007A($bookingId)
    {
        if (empty($bookingId)) {
            return false;
        }
        
        // Request core details
        $requestCore = array(
            'VehRetResRQCore' => array(
                'UniqueID'   => array(
                    'Type' => '14', // OTA code optional only
                    'ID'   => $bookingId,
                ),
                'PersonName' => array(
                    'GivenName' => 'test',
                    'Surname' => 'tester'
                ),
            ),            
        );

        $client = $this->getSoapClient('ReservationService.svc?wsdl');
        if (empty($client)) {
            return false;
        }

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->OTA_VehResRQ(array_merge($this->soapRequest, $requestCore));

        return $data;
    }
    
    public function cancelBooking2007A($bookingId)
    {   
        $client = $this->getSoapClient('ReservationService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }
        
        // Request core details
        $requestCore = array(
            'VehCancelRQCore' => array(
                'CancelType' => 'Cancel',
                'UniqueID'   => array(                    
                    'ID'   => $bookingId,
                ),
                'PersonName' => array(
                    'GivenName' => 'test',
                    'Surname'   => 'tester',
                ),
            ),            
        );

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->OTA_VehCancelRQ(array_merge($this->soapRequest, $requestCore));

        return $data;
    }
    
    public function locationSearch2007A($location)
    {   
        $client = $this->getSoapClient('ReservationService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }
        
        // Request core details
        $requestCore = array(
            'VehLocSearchCriterion' => array(
                'Address' => array(
                    'CityName' => $location,
                ),
            ),            
        );

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->OTA_VehLocSearchRQ(array_merge($this->soapRequest, $requestCore));

        return $data;
    }
    
    public function vehLocDetail($location)
    {           
        $client = $this->getSoapClientAu();
                
        $return = $client->VehLocDetail(array(
            'Request' => array(
                'Location' => array(
                    'LocationCode' => $location
                )
            )
        ));
       
        return $return;
    }
    
}   