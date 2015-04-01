<?php

class AV extends SupplierApi
{
    /**
     * @var array
     */
    private $soapRequest;
    private $requestorType;
    private $requestorID;
    private $supplierCode;
    private $supplierName;
    private $apiURL;
    private $target;
    private $primaryLang;
    private $version;

    /**
     *
     */
    const TIME_LIMIT  = 150;
    const VENDOR_CODE = 'AV';
    const VENDOR_NAME = 'Avis';
    const VENDOR_CLASS_CODE = 'AV';

    /**
     * @var array
     */
    private $transmissionType = array(
        'Automatic' => 'AT',
        'Manual'    => 'MT'
    );

    /**
     *
     */
    public function __construct ()
    {
        $this->supplierCode        = get_class();
        $this->supplierName        = Config::get($this->supplierCode . '.api.supplierName');
        $this->target              = Config::get($this->supplierCode . '.api.target');
        $this->apiURI              = Config::get($this->supplierCode . '.api.uri');
        $this->apiLocation         = Config::get($this->supplierCode . '.api.location');
        $this->apiUsernameVariable = Config::get($this->supplierCode . '.api.usernameVariable');
        $this->apiPasswordVariable = Config::get($this->supplierCode . '.api.passwordVariable');
        $this->apiUsername         = Config::get($this->supplierCode . '.api.username');
        $this->apiPassword         = Config::get($this->supplierCode . '.api.password');
        $this->requestorType       = Config::get($this->supplierCode . '.api.requestorType');
        $this->requestorID         = Config::get($this->supplierCode . '.api.requestorID');
        $this->primaryLang         = Config::get($this->supplierCode . '.api.primaryLang');
        $this->version             = Config::get($this->supplierCode . '.api.version');

        $this->soapRequest = array(
            //'OTA_VehLocSearchRQ' => array(
                'xmlns'         => 'http://www.opentravel.org/OTA/2003/05',
                'xmlns:xsi'     => "http://www.w3.org/2001/XMLSchema-instance",
                //'Target'        => Config::get('AV.api.target'),
                'Version'       => "1.0",
                'MaxResponses'  => '99',
                //'PrimaryLangID' => Config::get('AV.api.primaryLangID'),
                'POS'           => array(
                    'Source' => array(
                        'ISOCountry' => 'AU',
                        'RequestorID' => array(
                            'Type' => '1',
                            'ID'   => 'vroom',
                            /*'CompanyName' => array(
                                'CompanyName' => 'Avis'
                            )*/
                        )
                    )
                )
            //)
        );

        //var_dump($this->soapRequest);die;

        $this->soapClient = new SoapClient(
            null,
            array(
                'location'   => $this->apiLocation,
                'uri'        => $this->apiURI,
                'trace'      => 1,
                'use'        => SOAP_LITERAL,
                'cache_wsdl' => WSDL_CACHE_NONE
            )
        );
    }

    private function getSoapClient ()
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
            return new SoapClient(
                null,
                array(
                    //'location'   => $this->apiLocation,
                    'uri'        => $this->apiURI,
                    'stream_context' => $context,
                    'trace'      => 1,
                    'use'        => SOAP_LITERAL,
                    'cache_wsdl' => WSDL_CACHE_NONE
                )
            );
        } catch (Exception $e) {
            // Do nothing
            echo $e->getMessage();
        }
    }


    public function ping1()
    {
        $auth           = new stdClass();
        $auth->userID   = 'vroom';
        $auth->password = new stdClass();
        $auth->password = '09vroom15';

        $header = new SoapHeader('http://wsg.avis.com/wsbang/authInAny', 'credentials', $auth, false);
        $this->soapClient->__setSoapHeaders($header);

        $test = new stdClass();
        $test->EchoData = 'Test';

        $this->soapClient->Request(new SoapParam(new SoapVar($test, SOAP_ENC_OBJECT, "string", "http://wsg.avis.com/wsbang"), 'OTA_PingRQ'));
        header ("Content-Type:text/xml");
        print_r($this->soapClient->__getLastRequest());
        // print_r($this->soapClient->__getLastResponse());
        exit();
    }


    public function ping2()
    {
        $auth           = new AvisAuth('vroom', '09vroom15');
        $header = new SoapHeader('http://wsg.avis.com/wsbang/authInAny', 'credentials', $auth, false);
        $this->soapClient->__setSoapHeaders($header);

        $data =  array('EchoData' => 'EchoData');

        header ("Content-Type:text/xml");
        $this->soapClient->__soapCall('OTA_PingRQ',  array($data));
        print_r($this->soapClient->__getLastRequest());
        exit();
    }

    public function ping3()
    {
        $xml = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsi="http://www.w3.org/1999/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/1999/XMLSchema">
<SOAP-ENV:Header>
<ns:credentials xmlns:ns="http://wsg.avis.com/wsbang/authInAny">
<ns:userID ns:encodingType="xsd:string">vroom</ns:userID>
<ns:password ns:encodingType="xsd:string">09vroom15</ns:password>
</ns:credentials>
<ns:WSBang-Roadmap xmlns:ns="http://wsg.avis.com/wsbang"/>
</SOAP-ENV:Header>
<SOAP-ENV:Body>
<ns:Request xmlns:ns="http://wsg.avis.com/wsbang">
<OTA_PingRQ xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
Version="1.0">
<EchoData>Hello World</EchoData>
</OTA_PingRQ></ns:Request>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

        $params = new SoapVar(trim($xml), XSD_ANYXML);

        return $this->soapClient->__soapCall('OTA_PingRQ', array($params));
    }

    public function array_to_objecttree($array) {

        if (is_numeric(key($array))) { // Because Filters->Filter should be an array
            foreach ($array as $key => $value) {
                $array[$key] = $this->array_to_objecttree($value);
            }
            return $array;
        }
        $Object = new stdClass;
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $Object->$key = $this->array_to_objecttree($value);
            }  else {
                $Object->$key = $value;
            }
        }
        return $Object;
    }

    function fff()
    {
        $array = array(
            'SOAP-ENV:Envelope' =>
                array(
                    'xmlns:SOAP-ENV' => 'http://www.w3.org/2001/12/soap-envelope',
                    'xmlns:xsi'      => 'http://www.w3.org/1999/XMLSchema-instance',
                    'xmlns:xsd'      => 'http://www.w3.org/1999/XMLSchema',
                    'SOAP-ENV:Header' => array(
                        'ns:credentials' => array(
                            'xmlns:ns' => 'http://wsg.avis.com/wsbang/authInAny',
                            'ns:userID' => 'BSPAuto',
                            'ns:password' => 'LQpgW0`2YLS^'
                        ),
                        'ns:WSBang-Roadmap' => array(
                            'xmlns:ns' => 'http://wsg.avis.com/wsbang'
                        )
                    ),
                    'SOAP-ENV:Body' => array(
                        'ns:Request' => array(
                            'xmlns:ns' => 'http://wsg.avis.com/wsbang',
                            'OTA_VehLocSearchRQ' => array(
                                'xmlns'         => 'http://www.opentravel.org/OTA/2003/05',
                                'xmlns:xsi'     => "http://www.w3.org/2001/XMLSchema-instance",
                                //'Target'        => Config::get('AV.api.target'),
                                //'Version'       => "1.0",
                                'MaxResponses'  => '99',
                                //'PrimaryLangID' => Config::get('AV.api.primaryLangID'),
                                'POS'           => array(
                                    'Source' => array(
                                        'ISOCountry' => 'US',
                                        'RequestorID' => array(
                                            'Type' => '1',
                                            'ID'   => 'vroom',
                                            /*'CompanyName' => array(
                                        'CompanyName' => 'Avis'
                                    )*/
                                        )
                                    )
                                ),
                                'VehLocSearchCriterion' => array(
                                    'Address' => array(
                                        'AddressLine' => '52 Narra Drive',
                                        'CityName'    => 'Antiplo City',
                                        'PostalCode'  => '1870',
                                        'County'      => 'Philippines',
                                        'CountryName' => array('Code' => 'PH')
                                    ),
                                    'Radius' => array('DistanceMax' => '40', 'DistanceMeasure' => 'Miles')
                                ),
                                'Vendor' => array('Code' => 'Avis')
                            )
                        )
                    )
                )
        );
    }

    /**
     * @return bool
     */
    public function getLocationSearch2()
    {
        //$xml = '<OTA_VehLocSearchRQ xmlns="http://www.opentravel.org/OTA/2003/05" xsi="http://www.w3.org/2001/XMLSchema-instance" Version="1.0" SequenceNmbr="123456789" MaxResponses="99"><POS><Source ISOCountry="AU"><RequestorID Type="1" ID="vroom"/></Source></POS><VehLocSearchCriterion><Address><AddressLine>Near Chinatown/Central Sta)</AddressLine><CityName>SYDNEY</CityName><PostalCode>2000</PostalCode><StateProv>SYD</StateProv><CountryName Code="AU"/></Address></VehLocSearchCriterion><Vendor Code="Avis"/></OTA_VehLocSearchRQ>';

        //$xml = '<ns1:OTA_VehLocSearchRQ><param0><item><key>xmlns</key><value>http://www.opentravel.org/OTA/2003/05</value></item><item><key>xmlns:xsi</key><value>http://www.w3.org/2001/XMLSchema-instance</value></item><item><key>Target</key><value>Test</value></item><item><key>Version</key><value>1.0</value></item><item><key>MaxResponses</key><value>99</value></item><item><key>PrimaryLangID</key><value>EN</value></item><item><key>POS</key><value><item><key>Source</key><value><item><key>ISOCountry</key><value>US</value></item><item><key>RequestorID</key><value><item><key>Type</key><value>1</value></item><item><key>ID</key><value>vroom</value></item><item><key>CompanyName</key><value><item><key>CompanyName</key><value>Avis</value></item></value></item></value></item></value></item></value></item><item><key>VehLocSearchCriterion</key><value><item><key>Address</key><value><item><key>AddressLine</key><value>52 Narra Drive</value></item><item><key>CityName</key><value>Antiplo City</value></item><item><key>PostalCode</key><value>1870</value></item><item><key>County</key><value>Philippines</value></item><item><key>CountryName</key><value><item><key>Code</key><value>PH</value></item></value></item></value></item><item><key>Radius</key><value><item><key>DistanceMax</key><value>40</value></item><item><key>DistanceMeasure</key><value>Miles</value></item></value></item></value></item><item><key>Vendor</key><value><item><key>Code</key><value>Avis</value></item></value></item></param0></ns1:OTA_VehLocSearchRQ>';

        $xml = '
<ns2:credentials>
<userID>vroom</userID>
<password>09vroom15</password>
</ns2:credentials>

<ns1:OTA_PingRQ>
<EchoData xsi:type="xsd:string">ECHO DATA</EchoData>
</ns1:OTA_PingRQ>
';

        $params = new SoapVar(trim($xml), XSD_ANYXML);

        return $this->soapClient->VehAvailRate($params);
    }

}


class AvisAuth
{
    public $userID;
    public $password;

    public function __construct($userID, $password)
    {
        $this->userID = $userID;
        $this->password = $password;
    }
}