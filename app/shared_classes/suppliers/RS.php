<?php 

/**
 * Hertz API base class
 *
 * @author Inon Baguio <inon.vroomvroomvroom.com.au>
 */
class RS extends SupplierApi
{
    const DEFAULT_XMLNS = 'http://www.thermeon.com/webXG/xml/webxml/';
    const DEFAULT_XML_VERSION = '2.2202';
    const DEFAULT_CONFIRMATION_NUMBER = '123ABC456';
    const DEFAULT_CORPORATE_ID = 'CDVROOM';

    /*
     * The API URL
     */ 
    protected $apiUrl;  

    /*
     * The API Username
     */ 
    protected $apiUsername;

    /*
     * The API Password
     */     
    protected $apiPassword;

    /**
     * The Supplier Code
     */
    protected $supplierCode;   

    /*
     * The cURL headers
     */
    protected $headers;

    /**
     * The Request Reference Number
     */
    private $referenceNumber;

    public function __construct()
    {
        $this->supplierCode = get_class();
        $this->apiUrl       = Config::get($this->supplierCode  . '.api.url');
        $this->apiUsername  = Config::get($this->supplierCode  . '.api.username');
        $this->apiPassword  = Config::get($this->supplierCode  . '.api.password');
        $this->feelUrl      = Config::get($this->supplierCode  . '.api.fleetUrl');
        $this->locationsUrl = Config::get($this->supplierCode  . '.api.locationsUrl');
        $this->optionsUrl   = Config::get($this->supplierCode  . '.api.optionsUrl');

        $this->headers = array(
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        );

        $this->defaultCurlOptions  = array(
            CURLOPT_URL            => $this->apiUrl,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => $this->apiUsername . ':' . $this->apiPassword,
            CURLOPT_HTTPHEADER     => $this->headers
        );

        $this->referenceNumber = 'r' . date_format(date_create(), 'U');

    }

    /**
     * Generic function for get vehicle rates
     * 
     * @param date $pickUpDate
     * @param time $pickUpTime
     * @param date $returnDate
     * @param time $returnTime
     * @param string $pickUpLocationCode
     * @param string $returnLocationCode
     * @param string $vehicleClass
     * @param string $countryCode
     * @param string $countryCode
     * 
     * @return XML
     */
    public function search(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate, 
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode, 
        $countryCode,
        $driverAge
    ) {
        $timeStart     = time();
        $searchResult  = $this->resRates(
                            $pickUpDate, 
                            $pickUpTime, 
                            $returnDate, 
                            $returnTime,
                            $pickUpLocationCode, 
                            $returnLocationCode, 
                            $countryCode 
                       );
        $searchResult['executionTime'] = time() - $timeStart;
        $searchResult['supplierCode']  = $this->supplierCode;

        return $searchResult;
    }

    /**
     * Dedicated function for search()
     * 
     * @param  date $pickUpDate        
     * @param  date $pickUpTime        
     * @param  date $returnDate        
     * @param  time $returnTime     
     * @param  string $pickUpLocationCode
     * @param  string $returnLocationCode
     * @param  string $countryCode
     * 
     * @return Array
     */
    public function resRates(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate, 
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode, 
        $countryCode         
    ) {
        $xmlRequest  = $this->getSearchVehicleXML(
                            $pickUpDate, 
                            $pickUpTime, 
                            $returnDate, 
                            $returnTime,
                            $pickUpLocationCode, 
                            $returnLocationCode, 
                            $countryCode 
                       );
        $xmlCurlResponse  = $this->executeCurl($xmlRequest->asXML());
        $mappedCarDetails = $this->mapVehicleDetails($xmlCurlResponse);
        $result           = array('status' => 'Failed');
        return $xmlCurlResponse;
        if ((string) $xmlCurlResponse->ResRates->attributes()->success !== 'true') {
            $result['data'][] = $xmlCurlResponse;
            return $result;
        }

        $acrissHelper     = new AcrissHelper();
        $result['status'] = 'OK';
        $counter          = 0;

        foreach ($xmlCurlResponse->ResRates->Rate as $value) {
            if (!empty($mappedCarDetails[$counter]) && (string) trim($value->Availability) === "Available") {
                $result['data'][] = array(
                    'supplierCode'    => (string) $this->supplierCode,
                    'hasAirCondition' => (string) 'N/A',
                    'transmission'    => (string) $mappedCarDetails[$counter]->gearbox,
                    'baggageQty'      => $this->getSumOfNumbersFromString($mappedCarDetails[$counter]->storage),
                    'co2Qty'          => 'N/A',
                    'categoryCode'    => (string) $value->Class,
                    'expandedCode'    => $acrissHelper->expandCode((string) $value->Class),                    
                    'doorCount'       => $this->getSumOfNumbersFromString($mappedCarDetails[$counter]->doors),
                    'name'            => (string) $mappedCarDetails[$counter]->make . " " . $mappedCarDetails[$counter]->model,
                    'seats'           => $this->getSumOfNumbersFromString($mappedCarDetails[$counter]->capacity),
                    'vehicleStatus'   => array(
                        'code'        => 'N/A',
                        'description' => 'N/A',
                    ),
                    'rateId'    => (string) $value->RateID,
                    'basePrice' => (string) $value->RateOnlyEstimate,
                    'currency'  => (string) $value->CurrencyCode,
                    'bookingCurrencyOfTotalRateEstimate' => 'N/A',
                    'xrsBasePrice'                       => 'N/A',
                    'xrsBasePriceInBookingCurrency'      => 'N/A',
                    'totalRateEstimate'                  => (string) $value->Estimate,
                    'totalRateEstimateInBookingCurrency' => 'N/A',
                );
            }

            $counter++;
        } 

        return $result;
    }

    /**
     * Handles the request for getting bookg details
     * 
     * @param  int $bookingId
     * 
     * @return XML
     */
    public function getBookingDetails($bookingId)
    {
        $xmlRequest = $this->getXMLForBookingDetails($bookingId);
        return $this->executeCurl($xmlRequest->asXML());
    }

    /**
     * Retrieve XML for getBookingDetails
     * 
     * @param  int $bookingId
     * 
     * @return XML
     */
    public function getXMLForBookingDetails($bookingId)
    {
        $xml = $this->createRootRequestNode();
        $retrieveReservationRequestNode = $xml->addChild('RetrieveReservationRequest');
        $retrieveReservationRequestNode->addAttribute("reservationNumber", $bookingId);

        return $xml;
    }

    /**
     * Strips numbers from a string and get their sum
     * @param  string $stringWithNumber
     * @return int
     */
    public function getSumOfNumbersFromString($stringWithNumber)
    {
        preg_match_all('!\d+!', $stringWithNumber, $matches);
        return array_sum($matches[0]);
    }

    /**
     * Map Car Details on the RedSpot's Fleet Object
     * 
     * @param  xml $xml
     * 
     * @return ARRAY
     */
    public function mapVehicleDetails($xml)
    {
        $mapCarDetails = array();
        $fleetObject = $this->getFleet();
        foreach ($xml->ResRates->Rate as $value) {
            $detail          = $fleetObject->xpath($value->Class);
            $mapCarDetails[] = reset($detail);
        }

        return $mapCarDetails;
    }

    /**
     * Handles the fetching of options xml
     * @return XML Object
     */
    public function getExtras()
    {
        return new SimpleXMLElement(file_get_contents($this->optionsUrl));
    }

    /**
     * Updates Depot table for RedSpot
     * @return bool
     */
    public function updateDepots()
    {
        $response = new SimpleXMLElement(file_get_contents($this->locationsUrl));
        $supplierObject = Supplier::getSupplierIDByCode($this->supplierCode);
        $stationsAdded = 0;
        if(!is_null($supplierObject)) {
            foreach ($response as $key => $value) {
                $stateObject = State::whereCode($value->statecode)->first();
                $data = array(
                    'supplierID'   => $supplierObject->getId(),
                    'locationCode' => $key,
                    'countryCode'  => is_null($stateObject) ? '0' : $stateObject->getCountryId(),
                    'postCode'     => $value->postcode,
                    'city'         => $value->suburb,
                    'address'      => trim($value->address1 . ' ' . $value->address2),
                    'phoneNumber'  => $value->phone,
                    'latitude'     => $value->longitude,
                    'longitude'    => $value->longitude,
                    'isAirport'    => strpos(strtolower($value->name), 'airport') !== false ? 1 : 0,
                    'locationName' => $value->name
                );
                $response = Depot::updateOrCreateDepot($data);
                if (!$response["result"]) {
                    break;
                }
                else {
                    $stationsAdded++;
                }
            }
        }

        $result = array(
            "success"   => $response["result"],
            "message"   => $response["message"],
            "rowsAdded" => $stationsAdded,
            "supplierCode" => $this->supplierCode
        );

        return $result;
    }


    /**
     * Handles the fetching of locations xml
     * @return XML Object
     */
    public function getLocations()
    {
        return new SimpleXMLElement(file_get_contents($this->locationsUrl));
    }

    /**
     * Handles the cURL request for 
     * @return XML
     */
    public function getFleet()
    {
        return new SimpleXMLElement(file_get_contents($this->feelUrl));
    }

    /**
     * Handles the API request for Cancel Boooking
     * @param  int $bookingId
     * @return XML Object
     */
    public function cancelBooking($bookingId)
    {
        $xmlRequest = $this->getXMLForCancelBooking($bookingId);
        return $this->executeCurl($xmlRequest->asXML());
    }

    /**
     * Handles the API request for Booking Request
     * 
     * @param  date $pickUpDate
     * @param  time $pickUpTime
     * @param  date $returnDate
     * @param  time $returnTime
     * @param  string $pickUpLocationCode
     * @param  string $returnLocationCode
     * @param  string $vehicleClass
     * @param  int $rateId
     * @param  string $countryCode
     * @param  array $vehicleEquipments
     * 
     * @return XML
     */
    public function doBooking(
        $pickUpDate,
        $pickUpTime,
        $returnDate,
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode,
        $vehicleClass,
        $rateId,
        $countryCode,
        $vehicleEquipments,
        $firstName,
        $lastName
    ) {
        $xmlRequest = $this->getXMLForBooking(
                          $pickUpDate,
                          $pickUpTime,
                          $returnDate,
                          $returnTime,
                          $pickUpLocationCode, 
                          $returnLocationCode,
                          $vehicleClass,
                          $rateId,
                          $countryCode,
                          $vehicleEquipments,
                          $firstName,
                          $lastName
                       );

        return $this->executeCurl($xmlRequest->asXML()); 
    }

    /**
     * Creates the XML Request for Book Vehicles
     * 
     * @param  date $pickUpDate
     * @param  time $pickUpTime
     * @param  date $returnDate
     * @param  time $returnTime
     * @param  string $pickUpLocationCode
     * @param  string $returnLocationCode
     * @param  string $vehicleClass
     * @param  int $rateId
     * @param  string $countryCode
     * @param  array $vehicleEquipments
     * @param  string $firstName     
     * @param  string $lastName     
     * 
     * @return XML
     */
    public function getXMLForBooking(
        $pickUpDate,
        $pickUpTime,
        $returnDate,
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode,
        $vehicleClass,
        $rateId,
        $countryCode,
        $vehicleEquipments,
        $firstName,
        $lastName       
    ) {
        $xml = $this->createRootRequestNode();
        $newReservationRequestNode = $xml->addChild('NewReservationRequest');
        $newReservationRequestNode->addAttribute('confirmAvailability', 'true');

        $pickUpLocationNode = $newReservationRequestNode->addChild('Pickup');
        $pickUpLocationNode->addAttribute('locationCode', $pickUpLocationCode);
        $pickUpLocationNode->addAttribute('dateTime', $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime));
        $returnLocationNode = $newReservationRequestNode->addChild('Return');
        $returnLocationNode->addAttribute('locationCode', $returnLocationCode);
        $returnLocationNode->addAttribute('dateTime', $this->convertToDateTimeDefaultFormat($returnDate, $returnTime));        

        $sourceNode = $newReservationRequestNode->addChild('Source');
        $sourceNode->addAttribute('confirmationNumber', self::DEFAULT_CONFIRMATION_NUMBER);
        $sourceNode->addAttribute('countryCode', $countryCode);
        $sourceNode->addAttribute('corporateRateID', self::DEFAULT_CORPORATE_ID);
        $newReservationRequestNode->addChild('Vehicle')->addAttribute('classCode', $vehicleClass);

        $renterNode = $newReservationRequestNode->addChild('Renter');
        $renterNameNode = $renterNode->addChild('RenterName');
        $renterNameNode->addAttribute('firstName', $firstName);
        $renterNameNode->addAttribute('lastName', $lastName);

        $addressNode = $renterNode->addChild('Address');
        $addressNode->addChild('Email', 'info@redspotcars.com.au');
        $addressNode->addChild('HomeTelephoneNumber', '0283032222');

        $quotedRateNode = $newReservationRequestNode->addChild('QuotedRate');
        $quotedRateNode->addAttribute('rateID', $rateId);
        $quotedRateNode->addAttribute('classCode', $vehicleClass);

        $flightNode = $newReservationRequestNode->addChild('Flight');
        $flightNode->addAttribute('airlineCode', 'QF');
        $flightNode->addAttribute('flightNumber', '142');

        if(!empty($vehicleEquipments)) {
            foreach ($vehicleEquipments as $key => $value) {
                $optionNode = $newReservationRequestNode->addChild('Option');
                $optionNode->addChild('Code', trim($value["name"]));
                $optionNode->addChild('Qty', trim($value["qty"]));
            }
        }

        return $xml;
    }

    /**
     * Creates the XML Request for Cancel Booking
     * 
     * @param  int $bookingId
     * 
     * @return XML Object
     */
    public function getXMLForCancelBooking($bookingId)
    {
        $xml = $this->createRootRequestNode();
        $cancelReservationRequestNode = $xml->addChild('CancelReservationRequest');
        $cancelReservationRequestNode->addAttribute('reservationNumber', $bookingId);

        return $xml;
    }

    /**
     * Creates the XML Request for Search Vehicles
     * 
     * @param  date $pickUpDate
     * @param  time $pickUpTime
     * @param  date $returnDate
     * @param  time $returnTime
     * @param  string $pickUpLocationCode
     * @param  string $returnLocationCode
     * @param  string $vehicleClass
     * @param  string $countryCode
     * 
     * @return XML
     */
    public function getSearchVehicleXML(
        $pickUpDate,
        $pickUpTime,
        $returnDate,
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode,
        $countryCode
    ) {
        $xml = $this->createRootRequestNode();
        $resRatesnode = $xml->addChild('ResRates');

        $pickUpLocationNode = $resRatesnode->addChild('Pickup');
        $pickUpLocationNode->addAttribute('locationCode', $pickUpLocationCode);
        $pickUpLocationNode->addAttribute('dateTime', $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime));

        $returnLocationNode = $resRatesnode->addChild('Return');
        $returnLocationNode->addAttribute('locationCode', $returnLocationCode);
        $returnLocationNode->addAttribute('dateTime', $this->convertToDateTimeDefaultFormat($returnDate, $returnTime));        
        $sourceNode = $resRatesnode->addChild('Source');
        $sourceNode->addAttribute('countryCode', $countryCode);

        $resRatesnode->addChild('CorpRateID', self::DEFAULT_CORPORATE_ID);

        return $xml;
    }

    /**
     * Returns default datetime format
     * 
     * @param  date $date
     * @param  time $time
     * 
     * @return DATE object
     */
    private function convertToDateTimeDefaultFormat($date, $time)
    {
        $date   = new DateTime($date.' '.$time);
        $result = $date->format('Y-m-d H:i:s');

        return str_replace(' ', 'T', $result);
    }

    /**
     * Creates the Request Node
     * 
     * @return XML Object
     */
    private function createRootRequestNode()
    {
        $xml = new SimpleXMLElement('<Request></Request>');
        $xml->addAttribute('xmlns', self::DEFAULT_XMLNS);
        $xml->addAttribute('referenceNumber', $this->referenceNumber);
        $xml->addAttribute('version', self::DEFAULT_XML_VERSION);

        return $xml;
    }

    /**
     * Executes cURL
     * 
     * @param  xml $postField
     * 
     * @return XML Object
     */
    private function executeCurl($postField)
    {
        $curlOptions = $this->defaultCurlOptions;
        $curlOptions[CURLOPT_POSTFIELDS] = $postField;
        $curlHandler = curl_init();
        curl_setopt_array($curlHandler, $curlOptions);
        $response = new SimpleXMLElement(curl_exec($curlHandler));
        curl_close($curlHandler);

        return $response;
    }    
}

