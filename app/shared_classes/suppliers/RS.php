<?php 

/**
 * Hertz API base class
 *
 * @author Inon Baguio <inon.vroomvroomvroom.com.au>
 */
class RS extends SupplierApi
{
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
    }

    public function search()
    {
        return $this->executeCurl($this->getXml());
    }

    public function cancelBooking()
    {
        return $this->executeCurl($this->getXMLForCancelBooking());
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
                <Source confirmationNumber="123ABC456" countryCode="AU"/> corporateRateID="CDBGWHEL">
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

    public function getXMLForCancelBooking()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
            <Request xmlns="http://www.thermeon.com/webXG/xml/webxml/" referenceNumber="r1263372689587" version="2.2202">
              <CancelReservationRequest reservationNumber="asdasdasdasdasd"/>
            </Request>
            ';
    }

    public function getXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
            <Request xmlns="http://www.thermeon.com/webXG/xml/webxml/" referenceNumber="r1263372689587" version="2.2202">
              <ResRates>
                <Pickup locationCode="ADL" dateTime="2015-05-01T12:00:00"/>
                <Return locationCode="BNE" dateTime="2015-05-05T12:00:00"/>
                <Class>CDAR</Class>
                <Source countryCode="AU" />
              </ResRates>
            </Request>

            ';

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

