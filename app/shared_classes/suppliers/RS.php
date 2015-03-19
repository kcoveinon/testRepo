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

    public function doBooking()
    {
        return $this->executeCurl($this->getXMLForBooking()); 
    }

    public function getXMLForBooking()
    {
        return '
            <Request version="1.3" referenceNumber="r1263272805888" xmlns="http://www.thermeon.com/webXG/xml/webxml/">
              <NewReservationRequest confirmAvailability="true">
                <Pickup locationCode="ADL" dateTime="2015-05-01T12:00:00"/>
                <Return locationCode="BNE" dateTime="2015-05-05T12:00:00"/>
                <Vehicle classCode="CDAR"/>
                <Renter>
                  <RenterName firstName="test" lastName="test"/>
                  <Address>
                    <Email>info@redspotcars.com.au</Email>
                    <HomeTelephoneNumber>0283032222</HomeTelephoneNumber>
                  </Address>
                </Renter>
                <QuotedRate rateID="11030115055333CDAR" classCode="CDAR"/>
                <Flight airlineCode="QF" flightNumber="142"/>
              </NewReservationRequest>
            </Request>
            ';
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
        $resRatesnode->addChild("Class", $vehicleClass);
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


    public function createRootRequestNode()
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
    public function executeCurl($postField)
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

