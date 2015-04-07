<?php

class TH extends SupplierApi
{
    private $soapRequest;

    const TIME_LIMIT  = 150;
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
            'PrimaryLangID' => Config::get('TH.api.primaryLangID'),
            'POS'           => array(
                'Source' => array(
                    'RequestorID' => array(
                        'Type' => Config::get('TH.api.requestorIdType'),
                        'ID'   => Config::get('TH.api.accountNumber')
                    )
                )
            )
        );
    }
    
    
    
    public function testRquestAu(){
       
       
        $username = 'C||3103424||';
        $password = '490INT0014';
        $URL      = 'https://xmlweb.thrifty.com.au/thriftyens/Thrifty.OBS.Service.WebService.cls?wsdl';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_TIMEOUT, 180); //timeout after 30 seconds
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
        $result=curl_exec ($ch);
               
        echo '<pre> ' . __FILE__ . ':' . __LINE__ . '<br/>';
        print_r($ch);
        print_r( var_dump(curl_errno ( $ch )) );
        print_r(var_dump( $status_code ));
        print_r(var_dump( $result ) );
        echo '</pre>';
        
        curl_close ($ch);
        die;
       
       
              
       
       
        try {
            $opts = array(
                'http' => array(
                    'user_agent' => 'PHPSoapClient'
                ),                
            );

            // SOAP 1.2 client
            $context = stream_context_create($opts);
            $clientUrl = 'http://xmlwebdev.thrifty.com.au/csp/obsdevens/Thrifty.OBS.Service.WebService.cls';
                      
            $client =  new SoapClient(NULL, array(
                'location'       => $clientUrl,
                'uri'            => 'http://xmlwebdev.thrifty.com.au/',
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE,
                'trace'          => 1,
                'exceptions'     => 1,                
            ));
                        
            
            
            
            

            // Request core details
            $requestCore = array(
                'VehLocSearchCriterion' => array(
                    'Address' => array(
                        'CityName' => 'SYD'
                    )
                ),
            );

            // Set time limit for this one
            set_time_limit(self::TIME_LIMIT);
            
            
            
            $data = $client->__soapCall('OTA_VehLocSearchRQ',array_merge($this->soapRequest, $requestCore));

            die(__FILE__ . ':' . __LINE__ . "");
            return $data;
            
//            $auth   = array(
//                'UserName' => 'C||3103424||',
//                'Password' => '490INT0014',                
//            );
//            $header = new SoapHeader('NAMESPACE', 'Auth', $auth, false);
//            $client->__setSoapHeaders($header);
            
            return $client;
            
        } catch (Exception $e) {
            // Do nothing
            echo $e->getMessage();
        }
                
       
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
            $clientUrl = Config::get('TH.api.url') . $url;
            
            return new SoapClient($clientUrl, array(
                'stream_context' => $context,
                'cache_wsdl'     => WSDL_CACHE_NONE,
                'trace'          => 1,
                'exceptions'     => 1,
            ));
            
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
    ){        
        $result         = array();
        $timeStart      = time();
        $acrissHelper   = new \AcrissHelper();        
        $client         = $this->getSoapClient('RateService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }

        $requestCore = array(
            'VehAvailRQCore' => array(
                'VehRentalCore' => array(
                    'PickUpDateTime' => date('Y-m-d\TH:i:s', strtotime($pickUpDate . ' ' . $pickUpTime)),
                    'ReturnDateTime' => date('Y-m-d\TH:i:s', strtotime($returnDate . ' ' . $returnTime)),
                    'PickUpLocation' => array(
//                        'LocationCode' => 'SYD', //
//                        'ExtendedLocationCode' => $pickUpLocationCode,
                        'LocationCode' => $pickUpLocationCode,
                    ),
                    'ReturnLocation' => array(
//                        'LocationCode' => 'SYD',
//                        'ExtendedLocationCode' => $returnLocationCode,
                        'LocationCode' => $returnLocationCode,
                    )
                ),
                'VendorPrefs'   => array(
                    'VendorPref' => array(
                        'Code' => 'ZT'
                    )
                )
            )
        );

        set_time_limit(self::TIME_LIMIT);
        $data = $client->GetRates(array(
            'OTA_VehAvailRateRQ' => array_merge($this->soapRequest, $requestCore)
        ));
        
        if(isset($data->OTA_VehAvailRateRS->Errors)){
            return $data;
        }
       
        // Get the results
        $vehAvails = $data->OTA_VehAvailRateRS
                        ->VehAvailRSCore
                        ->VehVendorAvails
                        ->VehVendorAvail
                        ->VehAvails
                        ->VehAvail;
                
        if ( isset($data->OTA_VehAvailRateRS->Success) ){
            $result['status'] = 'OK';
            foreach ( $vehAvails as $vehAvail 
            ){                               
                $result['data'][] = array(
                    'supplierCode' => self::VENDOR_CLASS_CODE,
                    'hasAirCondition' => $vehAvail->VehAvailCore->Vehicle->AirConditionInd,
                    'transmission' => array(
                        'code'        => $this->transmitionCode[$vehAvail->VehAvailCore->Vehicle->TransmissionType],
                        'description' => "{$vehAvail->VehAvailCore->Vehicle->TransmissionType} Transmission"
                    ),
                    'baggageQty' => $vehAvail->VehAvailCore->Vehicle->BaggageQuantity,
                    'co2Qty' => 'N/A',
                    'categoryCode' => $vehAvail->VehAvailCore->Vehicle->Code,
                    'expandedCode' => $acrissHelper->expandCode($vehAvail->VehAvailCore->Vehicle->Code),
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
                    'basePrice' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
                    'currency' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->CurrencyCode,
                    'bookingCurrencyOfTotalRateEstimate' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->CurrencyCode,
                    'xrsBasePrice' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
                    'xrsBasePriceInBookingCurrency' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
                    'totalRateEstimate' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
                    'totalRateEstimateInBookingCurrency' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
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
    public function getLocations()
    {
        $requestCore = array(
            'Vendor' => array(
                'Code' => self::VENDOR_CODE
            )
        );
        $client = $this->getSoapClient('LocationService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);

        $data = $client->GetAllLocations(array(
            'OTA_VehLocSearchRQ' => array_merge($this->soapRequest, $requestCore)
        ));

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
                'RefPoint'       => array($locationCode)
            ),
            'Vendor'                => array(
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
    ){
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
        $carCategory
    ){
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
//                        'LocationCode'         => 'LAX', //
//                        'ExtendedLocationCode' => 'ATLC61',
                    ),
                    'ReturnLocation' => array(
                        'LocationCode' => $returnLocationCode,
//                        'LocationCode'         => 'LAX',
//                        'ExtendedLocationCode' => 'ATLC61',
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
                    'ID'         => '11451964',
                    'ID_Context' => 'InetID',                    
                )
            )
        );
       
        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->MakeReservation(array(
            'OTA_VehResRQ' => array_merge($this->soapRequest, $requestCore)
        ));
        
        
        echo '<pre> ' . __FILE__ . ':' . __LINE__ . '<br/>';
        print_r( str_replace('>','><br/>', htmlentities( $client->__getLastRequest() )));
        print_r($data);
        echo '</pre>';
        die;

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
                'Vendor'    => array(
                    'Code'             => self::VENDOR_CODE,
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
    public function searchVehicles2007A(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate, 
        $returnTime, 
        $pickUpLocationCode, 
        $returnLocationCode, 
        $countryCode, 
        $driverAge
    ){        
        $result         = array();
        $timeStart      = time();
        $acrissHelper   = new \AcrissHelper();        
        $client         = $this->getSoapClient('RateService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }

        $requestCore = array(
            'VehAvailRQCore' => array(
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
            )
        );

        set_time_limit(self::TIME_LIMIT);
        $data = $client->OTA_VehAvailRateRQ(array_merge($this->soapRequest, $requestCore));
        
        return $data;
        /*
        // Get the results
        $vehAvails = $data->OTA_VehAvailRateRS
                        ->VehAvailRSCore
                        ->VehVendorAvails
                        ->VehVendorAvail
                        ->VehAvails
                        ->VehAvail;
                
        if ( isset($data->OTA_VehAvailRateRS->Success) ){
            $result['status'] = 'OK';
            foreach ( $vehAvails as $vehAvail 
            ){                               
                $result['data'][] = array(
                    'supplierCode' => self::VENDOR_CLASS_CODE,
                    'hasAirCondition' => $vehAvail->VehAvailCore->Vehicle->AirConditionInd,
                    'transmission' => array(
                        'code'        => $this->transmitionCode[$vehAvail->VehAvailCore->Vehicle->TransmissionType],
                        'description' => "{$vehAvail->VehAvailCore->Vehicle->TransmissionType} Transmission"
                    ),
                    'baggageQty' => $vehAvail->VehAvailCore->Vehicle->BaggageQuantity,
                    'co2Qty' => 'N/A',
                    'categoryCode' => $vehAvail->VehAvailCore->Vehicle->Code,
                    'expandedCode' => $acrissHelper->expandCode($vehAvail->VehAvailCore->Vehicle->Code),
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
                    'basePrice' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
                    'currency' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->CurrencyCode,
                    'bookingCurrencyOfTotalRateEstimate' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->CurrencyCode,
                    'xrsBasePrice' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
                    'xrsBasePriceInBookingCurrency' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
                    'totalRateEstimate' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
                    'totalRateEstimateInBookingCurrency' => $vehAvail->VehAvailCore->RentalRate->VehicleCharges->VehicleCharge->Amount,
                );
            }
        }   
        
        // before the return keyword
        $result['executionTime'] = time() - $timeStart;
        $result['supplierCode']  = self::VENDOR_CLASS_CODE;
        return $result;
         * 
         */
    }
    
    public function createBooking2007A(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate,
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode, 
        $carCategory
    ){
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
                'Customer'          => array(
                    'Primary' => array(
                        'BirthDate'  => '1989-01-01',
                        'PersonName' => array(
                            'GivenName' => 'TEST', // Todo : Add real values
                            'Surname'   => 'TESTER',
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
                    'Surname'   => 'tester'
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
    
    public function locationDetail2007A($location)
    {   
        $client = $this->getSoapClient('ReservationService.svc?wsdl');
        
        if (empty($client)) {
            return false;
        }
        
        // Request core details
        $requestCore = array(
            'Location' => array(
                'Code' => array($location),
            ),            
        );

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);
        $data = $client->OTA_VehLocDetailRQ(array_merge($this->soapRequest, $requestCore));

        return $data;
    }
    
}   
