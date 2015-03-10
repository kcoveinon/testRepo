<?php 

/**
 * Hertz API base class
 *
 * @author Inon Baguio <inon.vroomvroomvroom.com.au>
 */
class HZ extends SupplierApi
{
	const SEARCH_VEHICLE_ACTION = "OTA_VehAvailRateRQ";
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
	 * @return MIXED
	 */
	public function searchVehicles($pickUpDate, 
								   $pickUpTime, 
								   $returnDate, 
								   $returnTime, 
								   $pickUpLocationId,
								   $returnLocationId,
								   $countryCode, 
								   $driverAge)
	{	
		ini_set('max_execution_time', 120);

		$depoObject = $this->returnDepotByLocationId($pickUpLocationId, $returnLocationId);
		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();	

		foreach ($depoObject as $key => $value) {
			$curlOptions = $this->defaultCurlOptions;
			$curlOptions[CURLOPT_POSTFIELDS] =  $this->getSearchVehicleXML(
													$this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime),
													$this->convertToDateTimeDefaultFormat($returnDate, $returnTime),
													$value->getDepotCode(),
													$value->getDepotCode(),
													$countryCode,
													self::SEARCH_VEHICLE_ACTION
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
	public function doBooking($pickUpDate, 
							   $pickUpTime, 
							   $returnDate, 
							   $returnTime, 
							   $pickUpLocationId,
							   $returnLocationId,
							   $countryCode, 
							   $vehCategory,
							   $vehClass)
	{	

		$xmlAction = "OTA_VehResRQ";

		$depoObject = $this->returnDepotByLocationId($pickUpLocationId, $returnLocationId);
		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();

		ini_set('max_execution_time', 120);
		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();	
		foreach ($depoObject as $key => $value) {
			$curlOptions = $this->defaultCurlOptions;
			$curlOptions[CURLOPT_POSTFIELDS] =  $this->getXmlForBooking(
													$this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime),
													$this->convertToDateTimeDefaultFormat($returnDate, $returnTime),
													$value->getDepotCode(),
													$value->getDepotCode(),
													$countryCode,
													$xmlAction,
													$vehCategory,
													$vehClass
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

	public function getXmlForBooking($pickUpDateTime,
									 $returnDateTime,
									 $pickUpLocationId,
									 $returnLocationId,
									 $countryCode,
									 $xmlAction,
									 $vehCategory,
									 $vehClass)
	{
		$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
				<OTA_VehResRQ xmlns=\"http://www.opentravel.org/OTA/2003/05\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.opentravel.org/OTA/2003/05 OTA_VehResRQ.xsd\" Version=\"1.008\" SequenceNmbr=\"123456789\">
				   <POS>
				      <Source PseudoCityCode=\"BNE\" ISOCountry=\"BS\" AgentDutyCode=\"B2S19P16R18\">
				         <RequestorID Type=\"4\" ID=\"T487\">
				            <CompanyName Code=\"CP\" CodeContext=\"A9CF\" />
				         </RequestorID>
				      </Source>
				   </POS>
				   <VehResRQCore Status=\"All\">
				      <VehRentalCore PickUpDateTime=\"2015-12-13T20:00:00Z\" ReturnDateTime=\"2015-12-19T20:00:00Z\">
				         <PickUpLocation CodeContext=\"IATA\" LocationCode=\"BNE\" />
				         <ReturnLocation CodeContext=\"IATA\" LocationCode=\"BNE\" />
				      </VehRentalCore>
				      <Customer>
				         <Primary>
				            <PersonName>
				               <GivenName>PrePaidThree</GivenName>
				               <Surname>Testing</Surname>
				            </PersonName>
				            <Telephone PhoneTechType=\"1\" AreaCityCode=\"999\" PhoneNumber=\"9999999\" />
				            <Telephone PhoneTechType=\"3\" AreaCityCode=\"US999\" PhoneNumber=\"9999999\" />
				            <Email>saford@hertz.com</Email>
				            <Address>
				               <AddressLine>5601 NW Exp</AddressLine>
				               <AddressLine>Bldg 2</AddressLine>
				               <CityName>Oklahoma City</CityName>
				               <PostalCode>73112</PostalCode>
				               <StateProv StateCode=\"OK\" />
				               <CountryName Code=\"BS\" />
				            </Address>
				         </Primary>
				      </Customer>
				      <VehPref AirConditionInd=\"true\" 
				      		   AirConditionPref=\"Preferred\" 
				      		   TransmissionType=\"Automatic\" 
				      		   TransmissionPref=\"Preferred\" 
				      		   FuelType=\"Diesel\" 
				      		   DriveType=\"Unspecified\"
				      		   Code=\"ICAR\" 
				      		   CodeContext=\"SIPP\">
				         <VehType VehicleCategory=\"".$vehCategory."\" />
				         <VehClass Size=\"".$vehClass."\"  />
				      </VehPref>
				   </VehResRQCore>
				   <VehResRQInfo>
				      <SpecialReqPref>Prefers Red Car with sunroof and 6-disk cd changer prefers beige leather interior no purple car</SpecialReqPref>
				      <ArrivalDetails TransportationCode=\"14\" Number=\"1234\">
				         <OperatingCompany Code=\"BA\" />
				      </ArrivalDetails>
				   </VehResRQInfo>
				</OTA_VehResRQ>
				";
		return $xml;
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
	private function getSearchVehicleXML($pickUpDateTime,
									     $returnDateTime,
									     $pickUpLocationId,
									     $returnLocationId,
									     $countryCode,
									     $xmlAction)
	{
		$xml = $this->getXMLCredentialNode($xmlAction, $countryCode);

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

	/**
	 * Returns POS credential node
	 * @param  string $xmlAction
	 * @param  string $countryCode
	 * @return XML Object
	 */
	public function getXMLCredentialNode($xmlAction, $countryCode)
	{
		$xml = new SimpleXMLElement("<$xmlAction></$xmlAction>");
		$xml->addAttribute("xmlns",self::DEFAULT_XMLNS);
		$xml->addAttribute("xmlns:xsi",self::DEFAULT_XMLNS_XSI);
		$xml->addAttribute("xsi:schemaLocation",self::DEFAULT_XMLNS. " ".$xmlAction.".xsd");
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

		return $xml;	
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
}


	