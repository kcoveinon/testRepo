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
	private $curlOptions;
	private $defaultCurlOptions;

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
		$this->apiURL              = Config::get($this->supplierCode . '.api.url');
		$this->apiUsernameVariable = Config::get($this->supplierCode . '.api.usernameVariable');
		$this->apiPasswordVariable = Config::get($this->supplierCode . '.api.passwordVariable');
		$this->apiUsername         = Config::get($this->supplierCode . '.api.username');
		$this->apiPassword         = Config::get($this->supplierCode . '.api.password');
		$this->requestorType       = Config::get($this->supplierCode . '.api.requestorType');
		$this->requestorID         = Config::get($this->supplierCode . '.api.requestorID');
		$this->primaryLang         = Config::get($this->supplierCode . '.api.primaryLang');
		$this->version             = Config::get($this->supplierCode . '.api.version');

		$this->headers = array(
		    'Content-type: text/xml;charset="utf-8"',
		    'Accept: text/xml',
		    'Cache-Control: no-cache',
		    'Pragma: no-cache'
		);

		$this->defaultCurlOptions = array(
			CURLOPT_URL				=> $this->apiURL,
			CURLOPT_POST			=> true,
			CURLOPT_SSL_VERIFYHOST	=> false,
			CURLOPT_SSL_VERIFYPEER	=> false,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_TIMEOUT			=> false,
			CURLOPT_VERBOSE			=> false,
			CURLOPT_HTTPHEADER		=> $this->headers
		);
		$this->soapRequest = array(
					'xmlns'         => 'http://www.opentravel.org/OTA/2003/05',
					'xmlns:xsi'     => 'http://www.w3.org/2008/XMLSchema-instance',
					'Target'        => $this->target,
					'Version'       => $this->version,
					'PrimaryLangID' => $this->primaryLang,
					'POS'           => array(
						'Source'    => array(
							'RequestorID' => array(
								'Type' => $this->requestorType,
								'ID'   => $this->requestorID)
						)
				)
		);
    }

    public function testCurl()
    {
		$curlOptions = $this->defaultCurlOptions;

		$curlOptions[CURLOPT_POSTFIELDS] = $this->testXml();
		$curlHandler = curl_init();
		curl_setopt_array($curlHandler, $curlOptions);
		$response = curl_exec($curlHandler);
		curl_close($curlHandler);

		return $response;
    }

    public function testXml()
    {
    	$xml = "<SOAP-ENV:Envelope xmlns:SOAP-ENV='http://www.w3.org/2001/12/soap-envelope' xmlns:xsi='http://www.w3.org/1999/XMLSchema-instance' xmlns:xsd='http://www.w3.org/1999/XMLSchema'>
                    <SOAP-ENV:Header>
                        <ns:credentials xmlns:ns='http://wsg.avis.com/wsbang/authInAny'>
                            <ns:userID ns:encodingType='xsd:string'>BSPAuto</ns:userID>
                            <ns:password ns:encodingType='xsd:string'>LQpgW0`2YLS^</ns:password>
                        </ns:credentials>
                       <ns:WSBang-Roadmap xmlns:ns='http://wsg.avis.com/wsbang'/>
                    </SOAP-ENV:Header>
                  <SOAP-ENV:Body> 
                  <ns:Request xmlns:ns='http://wsg.avis.com/wsbang'>
<OTA_VehLocSearchRQ xmlns:xsi='http://www.w3.org/2008/XMLSchema-instance' MaxResponses='1' Version='1.0'>
<POS>
<Source/>
</POS>
<VehLocSearchCriterion>
<Address>
<AddressLine>6 Sylvan Way</AddressLine>
<CityName>Parsippany</CityName>
<PostalCode>07054</PostalCode>
<County>Morris</County>
<StateProv StateCode='NJ'/>
<CountryName Code='US'/>
</Address>
<Radius DistanceMax='40' DistanceMeasure='Miles'/>
</VehLocSearchCriterion>
<Vendor Code='Avis'/>
<TPA_Extensions>
<SortOrderType>DESCENDING</SortOrderType>
<TestLocationType>NO</TestLocationType>
<LocationStatusType>OPEN</LocationStatusType>
<LocationType>RENTAL</LocationType>
</TPA_Extensions>
</OTA_VehLocSearchRQ>




				</ns:Request>
                  </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>
                ";

    	return trim($xml);

    }

	/**
	 * @param $url
	 */
	private function getSoapClient($url)
	{
		/*if (empty($url)) {
			return false;
		}*/

		try {
		$client = new SoapClient($this->apiURL,array());

$auth = array(
        'UserName'=>'USERNAME',
        'Password'=>'PASSWORD',
        'SystemId'=> array('_'=>'DATA','Param'=>'PARAM'),
        );
  $header = new SoapHeader('NAMESPACE','Auth',$auth,false);
  $client->__setSoapHeaders($header);
  echo '<pre>'; print_r($client); exit();

		} catch (Exception $e) {
			// Do nothing
			echo $e->getMessage();
		}
	}

	/**
	 * @return bool
	 */
	public function ping()
	{
		$client = $this->getSoapClient('');
		echo '<pre>'; print_r($client->__getLastRequest()); exit();
		if (empty($client)) {
			return false;
		}
	}

	/**
	 *
	 * @return boolean|unknown
	 */
	public function getLocations ()
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
		die('aa');

		// Set time limit for this one
		set_time_limit(self::TIME_LIMIT);

		$data = $client->GetAllLocations(array(
				'OTA_VehLocSearchRQ' => array_merge($this->soapRequest, $requestCore)
			));

		dd($data);

		return $data;
	}
}
