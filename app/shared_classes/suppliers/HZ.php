<?php 

/**
 * Hertz API base class
 *
 * @author Inon Baguio <inon.vroomvroomvroom.com.au>
 */
class HZ extends SupplierApi
{
	const DEFAULT_XML_ACTION = "OTA_VehAvailRateRQ";
	const DEFAULT_XMLNS = "http://www.opentravel.org/OTA/2003/05";
	const DEFAULT_XMLNS_XSI = "http://www.w3.org/2001/XMLSchema-instance";
	const DEFAULT_VERSION = "1.008";
	const DEFAULT_SEQUENCENUMBER = "123456789";
	const DEFAULT_MAXRESPONSE = "99";
	const DEFAULT_PSEUDOCITYCODE = "LAX";
	const DEFAULT_REQUEST_TYPE = "4";
	const DEFAULT_CODE_CONTEXT = "IATA";
	const DEFAULT_REQUEST_STATUS = "All";
	const DEFAULT_CONSUMER_PRODUCT = "CP";

	/*
	 * The API Validation Code
	 */
	private $apiValidationCode;

	/*
	 * The API Validation Number
	 */	
	private $apiValidationNumber;

	/*
	 * The API Consumer Product Code
	 */		
	private $apiConsumerProductCode;

	/*
	 * The Default Curl Options
	 */
	private $defaultCurlOptions;

	/*
	 * The cURL headers
	 */
	
	private $headers;

	public function __construct()
	{
		$this->apiUrl              		= Config::get(get_class() . '.api.url');
		$this->apiValidationCode 		= Config::get(get_class() . '.api.validationCode');
		$this->apiValidationNumber 		= Config::get(get_class() . '.api.validationNumber');
		$this->apiConsumerProductCode 	= Config::get(get_class() . '.api.consumerProductCode');

		$this->headers = array(
		    "Content-type: text/xml;charset=\"utf-8\"",
		    "Accept: text/xml",
		    "Cache-Control: no-cache",
		    "Pragma: no-cache"
		);

		$this->defaultCurlOptions = array(
			CURLOPT_URL				=> $this->apiUrl,
			CURLOPT_POST			=> true,
			CURLOPT_SSL_VERIFYHOST	=> false,
			CURLOPT_SSL_VERIFYPEER	=> false,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_TIMEOUT			=> false,
			CURLOPT_VERBOSE			=> false,
			CURLOPT_HTTPHEADER		=> $this->headers
		);
	}

	/**
	 * Function that handles the data pull from Hertz's API
	 * @param datetime $pickUpDate
	 * @param datetime $pickUpTime  
	 * @param datetime $returnDate   
	 * @param datetime $returnTime      
	 * @param int $pickUpLocationId
	 * @param int $returnLocationId 
	 * @param int $countryCode      
	 * @param int $driverAge
	 * @param string $xmlAction
	 * @return MIXED
	 */
	public function searchVehicles($pickUpDate, 
								   $pickUpTime, 
								   $returnDate, 
								   $returnTime, 
								   $pickUpLocationId,
								   $returnLocationId,
								   $countryCode, 
								   $driverAge,
								   $xmlAction = self::DEFAULT_XML_ACTION)
	{	
		ini_set('max_execution_time', 120);

		$depoObject = $this->returnDepotByLocationId($pickUpLocationId, $returnLocationId);
		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();	

		foreach ($depoObject as $key => $value) {
			$curlOptions = $this->defaultCurlOptions;
			$curlOptions[CURLOPT_POSTFIELDS] =  $this->getXML(
													$this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime),
													$this->convertToDateTimeDefaultFormat($returnDate, $returnTime),
													$value->getDepotCode(),
													$value->getDepotCode(),
													$countryCode,
													$xmlAction
												);	
		    $curlHandlers[$key] = curl_init();
		    curl_setopt_array($curlHandlers[$key], $curlOptions);
		    curl_multi_add_handle($curlMultiHandler, $curlHandlers[$key]);
		}

		do {
			curl_multi_select($curlMultiHandler);
		    curl_multi_exec($curlMultiHandler, $isRunning);
		} while ($isRunning > 0);

		foreach ($curlHandlers as $stationCode => $curlHandler) {
		    $response[$stationCode] = new SimpleXMLElement(curl_multi_getcontent($curlHandler));
		    curl_multi_remove_handle($curlMultiHandler, $curlHandler);
		}

		curl_multi_close($curlMultiHandler);
		return $response;
	}

	/**
	 * Returns depots per location IDs
	 * @param int $pickUpLocationId
	 * @param int $returnLocationId
	 * @return Object
	 */
	public function returnDepotByLocationId($pickUpLocationId, $returnLocationId)
	{
		$pickUpObj = Location::find($pickUpLocationId);
		return $pickUpObj ? Depot::getGroupedDepotCode($pickUpObj->getCity())->get() : false;
	}

	/**
	 * Returns default datetime format
	 * @param  date $date
	 * @param  time $time
	 * @return DATE object
	 */
	private function convertToDateTimeDefaultFormat($date, $time)
	{
		$date =  new DateTime($date." ".$time);

		return $date->format('Y-m-d H:i:s');
	}

	/**
	 * Returns XML for post data
	 * @param  date $pickUpDateTime   
	 * @param  date  $returnDateTime   
	 * @param  string $pickUpLocationId 
	 * @param  string $returnLocationId 
	 * @param  string $countryCode      
	 * @param  string $xmlAction        
	 * @return XML Object                   
	 */
	private function getXML($pickUpDateTime,
						    $returnDateTime,
						    $pickUpLocationId,
						    $returnLocationId,
						    $countryCode,
						    $xmlAction)
	{
		$xml = new SimpleXMLElement("<$xmlAction></$xmlAction>");
		$xml->addAttribute("xmlns",self::DEFAULT_XMLNS);
		$xml->addAttribute("xmlns:xsi",self::DEFAULT_XMLNS_XSI);
		$xml->addAttribute("xsi:schemaLocation",self::DEFAULT_XMLNS_XSI. " ".$xmlAction.".xsd");
		$xml->addAttribute("Version",self::DEFAULT_VERSION);
		$xml->addAttribute("SequenceNmbr",self::DEFAULT_SEQUENCENUMBER);

		$posNode = $xml->addChild("POS");
		$sourceNode = $posNode->addChild("Source");
		$sourceNode->addAttribute("PseudoCityCode",self::DEFAULT_PSEUDOCITYCODE);
		$sourceNode->addAttribute("ISOCountry",$countryCode);
		$sourceNode->addAttribute("AgentDutyCode",$this->apiValidationCode);

		$requestNode = $sourceNode->addChild("RequestorID");
		$requestNode->addAttribute("Type",self::DEFAULT_REQUEST_TYPE);
		$requestNode->addAttribute("ID",$this->apiValidationNumber);

		$companyNameNode = $requestNode->addChild("CompanyName");
		$companyNameNode->addAttribute("Code",self::DEFAULT_CONSUMER_PRODUCT);
		$companyNameNode->addAttribute("CodeContext",$this->apiConsumerProductCode);

		$vehAvailRQCoreNode = $xml->addChild("VehAvailRQCore");
		$vehAvailRQCoreNode->addAttribute("Status",self::DEFAULT_REQUEST_STATUS);

		$vehRentalCoreNode = $vehAvailRQCoreNode->addChild("VehRentalCore");
		$vehRentalCoreNode->addAttribute("PickUpDateTime",$pickUpDateTime);
		$vehRentalCoreNode->addAttribute("ReturnDateTime",$returnDateTime);

		$pickUplocationNode = $vehRentalCoreNode->addChild("PickUpLocation");
		$pickUplocationNode->addAttribute("CodeContext",self::DEFAULT_CODE_CONTEXT);
		$pickUplocationNode->addAttribute("LocationCode",$pickUpLocationId);

		$returnLocationNode = $vehRentalCoreNode->addChild("ReturnLocation");
		$returnLocationNode->addAttribute("CodeContext",self::DEFAULT_CODE_CONTEXT);
		$returnLocationNode->addAttribute("LocationCode",$returnLocationId);

		return $xml->asXML();
	}
}


	