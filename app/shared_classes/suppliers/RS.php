<?php 

/**
 * Hertz API base class
 *
 * @author Inon Baguio <inon.vroomvroomvroom.com.au>
 */
class RS extends SupplierApi
{
    const DEFAULT_XMLNS = "http://www.thermeon.com/webXG/xml/webxml/";
    const DEFAULT_XML_VERSION = "2.2202";
    const DEFAULT_CONFIRMATION_NUMBER = "123ABC456";
    const DEFAULT_CORPORATE_ID = "CDBGWHEL";

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

        $this->headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache"
        );

        $this->defaultCurlOptions = array(
            CURLOPT_URL            => $this->apiUrl,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => $this->apiUsername . ":" . $this->apiPassword,
            CURLOPT_HTTPHEADER     => $this->headers
        );

        $this->referenceNumber = "r" . date_format(date_create(), "U");
    }

    /**
     * Handles the cURL request for the get vehicle rates
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
    public function search(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate, 
        $returnTime,
        $pickUpLocationCode, 
        $returnLocationCode, 
        $vehicleClass, 
        $countryCode 
    ) {
        $xml = new SimpleXMLElement(file_get_contents($this->feelUrl));

        $xmlRequest = $this->getSearchVehicleXML(
                            $pickUpDate, 
                            $pickUpTime, 
                            $returnDate, 
                            $returnTime,
                            $pickUpLocationCode, 
                            $returnLocationCode, 
                            $vehicleClass, 
                            $countryCode 
                       );
        return $this->executeCurl($xmlRequest->asXML());
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
        $countryCode 
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
                        $countryCode 
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
        $countryCode        
    ) {
        $xml = $this->createRootRequestNode();
        $newReservationRequestNode = $xml->addChild("NewReservationRequest");
        $newReservationRequestNode->addAttribute("confirmAvailability", "true");

        $pickUpLocationNode = $newReservationRequestNode->addChild("Pickup");
        $pickUpLocationNode->addAttribute("locationCode", $pickUpLocationCode);
        $pickUpLocationNode->addAttribute("dateTime", $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime));
        $returnLocationNode = $newReservationRequestNode->addChild("Return");
        $returnLocationNode->addAttribute("locationCode", $returnLocationCode);
        $returnLocationNode->addAttribute("dateTime", $this->convertToDateTimeDefaultFormat($returnDate, $returnTime));        

        $sourceNode = $newReservationRequestNode->addChild("Source");
        $sourceNode->addAttribute("confirmationNumber", self::DEFAULT_CONFIRMATION_NUMBER);
        $sourceNode->addAttribute("countryCode", $countryCode);
        $sourceNode->addAttribute("corporateRateID", self::DEFAULT_CORPORATE_ID);
        $newReservationRequestNode->addChild("Vehicle")->addAttribute("classCode", $vehicleClass);

        $renterNode = $newReservationRequestNode->addChild("Renter");
        $renterNameNode = $renterNode->addChild("RenterName");
        $renterNameNode->addAttribute("firstName", "test");
        $renterNameNode->addAttribute("lastName", "test");
        $addressNode = $renterNode->addChild("Address");
        $addressNode->addChild("Email", "info@redspotcars.com.au");
        $addressNode->addChild("HomeTelephoneNumber", "0283032222");

        $quotedRateNode = $newReservationRequestNode->addChild("QuotedRate");
        $quotedRateNode->addAttribute("rateID", $rateId);
        $quotedRateNode->addAttribute("classCode", $vehicleClass);

        $flightNode = $newReservationRequestNode->addChild("Flight");
        $flightNode->addAttribute("airlineCode", "QF");
        $flightNode->addAttribute("flightNumber", "142");

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
        $cancelReservationRequestNode = $xml->addChild("CancelReservationRequest");
        $cancelReservationRequestNode->addAttribute("reservationNumber", $bookingId);

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
        $vehicleClass,
        $countryCode
    ) {
        $xml = $this->createRootRequestNode();
        $resRatesnode = $xml->addChild("ResRates");
        $pickUpLocationNode = $resRatesnode->addChild("Pickup");
        $pickUpLocationNode->addAttribute("locationCode", $pickUpLocationCode);
        $pickUpLocationNode->addAttribute("dateTime", $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime));
        $returnLocationNode = $resRatesnode->addChild("Return");
        $returnLocationNode->addAttribute("locationCode", $returnLocationCode);
        $returnLocationNode->addAttribute("dateTime", $this->convertToDateTimeDefaultFormat($returnDate, $returnTime));        
        // $resRatesnode->addChild("Class", $vehicleClass);
        $sourceNode = $resRatesnode->addChild("Source");
        $sourceNode->addAttribute("countryCode", $countryCode);

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
        $date =  new \DateTime($date." ".$time);
        $result = $date->format('Y-m-d H:i:s');

        return str_replace(" ", "T", $result);
    }

    /**
     * Creates the Request Node
     * 
     * @return XML Object
     */
    private function createRootRequestNode()
    {
        $xml = new SimpleXMLElement('<Request></Request>');
        $xml->addAttribute("xmlns", self::DEFAULT_XMLNS);
        $xml->addAttribute("referenceNumber", $this->referenceNumber);
        $xml->addAttribute("version", self::DEFAULT_XML_VERSION);

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

