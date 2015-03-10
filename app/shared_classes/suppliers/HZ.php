<?php 

/**
 * Hertz API base class
 *
 * @author Inon Baguio <inon.vroomvroomvroom.com.au>
 */
class HZ extends SupplierApi
{
	const SEARCH_VEHICLE_ACTION = "OTA_VehAvailRateRQ";
	const BOOK_VEHICLE_ACTION = "OTA_VehResRQ";
	const GET_BOOKING_INFO_ACTION = "OTA_VehRetResRQ";

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

	public function getBookingDetails($bookingId, $countryCode)
	{
		ini_set('max_execution_time', 120);
		$bookingIdArray[] = $bookingId;

		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();	
		$curlOptions = $this->defaultCurlOptions;

		$iterableArray = is_array($bookingId) ? reset($bookingIdArray) : $bookingIdArray;
		foreach ($iterableArray as $key => $value) {
			$curlOptions[CURLOPT_POSTFIELDS] =  $this->getBookingDetailsXML(
													$value,
													$countryCode
												);	
		    $curlHandlers[$key] = curl_init();
		    curl_setopt_array($curlHandlers[$key], $curlOptions);
		    curl_multi_add_handle($curlMultiHandler, $curlHandlers[$key]);
		}
		do {
			curl_multi_select($curlMultiHandler);
		    curl_multi_exec($curlMultiHandler, $isRunning);
		} while ($isRunning > 0);

		foreach ($curlHandlers as $key => $curlHandler) {
		    $response[$key] = new SimpleXMLElement(curl_multi_getcontent($curlHandler));
		    curl_multi_remove_handle($curlMultiHandler, $curlHandler);
		}

		curl_multi_close($curlMultiHandler);
		return $response;	
	}

	public function getBookingDetailsXML($bookingId, $countryCode)
	{
		$xmlAction = self::GET_BOOKING_INFO_ACTION;

		$xml = $this->getXMLCredentialNode($xmlAction, $countryCode);

		$vehRetResRQCoreNode = $xml->addChild("VehRetResRQCore");
		$uniqueIDNode = $vehRetResRQCoreNode->addChild("UniqueID");
		$uniqueIDNode->addAttribute("Type","14");
		$uniqueIDNode->addAttribute("ID", (string)$bookingId);

		$personNameNode = $vehRetResRQCoreNode->addChild("PersonName");
		$personNameNode->addChild("Surname","Testing");	
		
		return $xml->asXML();
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

		$pickUpDateTime = $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime);
		$returnDateTime = $this->convertToDateTimeDefaultFormat($returnDate, $returnTime);
		$curlOptions = $this->defaultCurlOptions;
		foreach ($depoObject as $key => $value) {
			$curlOptions[CURLOPT_POSTFIELDS] =  $this->getSearchVehicleXML(
													$pickUpDateTime,
													$returnDateTime,
													$value->getDepotCode(),
													$value->getDepotCode(),
													$countryCode
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

		$depoObject = $this->returnDepotByLocationId($pickUpLocationId, $returnLocationId);
		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();

		ini_set('max_execution_time', 120);
		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();	

		$pickUpDateTime = $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime);
		$returnDateTime = $this->convertToDateTimeDefaultFormat($returnDate, $returnTime);

		$curlOptions = $this->defaultCurlOptions;
		foreach ($depoObject as $key => $value) {
			$curlOptions[CURLOPT_POSTFIELDS] =  $this->getXmlForBooking(
													$pickUpDateTime,
													$returnDateTime,
													$value->getDepotCode(),
													$value->getDepotCode(),
													$countryCode,
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
									 $pickUplocationCode,
									 $returnLocationCode,
									 $countryCode,
									 $vehCategory,
									 $vehClass)
	{
		$xmlAction = self::BOOK_VEHICLE_ACTION;
		$xml = $this->getXMLCredentialNode($xmlAction, $countryCode);

		$vehRsCore = $xml->addChild("VehResRQCore");
		$vehRsCore->addAttribute("Status",self::DEFAULT_REQUEST_STATUS);

		$vehRentalCoreNode = $vehRsCore->addChild("VehRentalCore");
		$vehRentalCoreNode->addAttribute("PickUpDateTime",$pickUpDateTime);
		$vehRentalCoreNode->addAttribute("ReturnDateTime",$returnDateTime);

		$pickUplocationNode = $vehRentalCoreNode->addChild("PickUpLocation");
		$pickUplocationNode->addAttribute("CodeContext",self::DEFAULT_CODE_CONTEXT);
		$pickUplocationNode->addAttribute("LocationCode",$pickUplocationCode);

		$returnLocationNode = $vehRentalCoreNode->addChild("ReturnLocation");
		$returnLocationNode->addAttribute("CodeContext",self::DEFAULT_CODE_CONTEXT);
		$returnLocationNode->addAttribute("LocationCode",$returnLocationCode);

		$customerNode = $vehRsCore->addChild("Customer");
		$primaryNode = $customerNode->addChild("Primary");
		$personNameNode = $primaryNode->addChild("PersonName");
		$personNameNode->addChild("GivenName","PrePaidThree");
		$personNameNode->addChild("Surname","Testing");
		$telephoneNode = $primaryNode->addChild("Telephone");
		$telephoneNode->addAttribute("PhoneTechType","1");
		$telephoneNode->addAttribute("AreaCityCode","9999");
		$telephoneNode->addAttribute("PhoneNumber","9999999");
		$primaryNode->addChild("Email","saford@hertz.com");		

		$addressNode = $primaryNode->addChild("Address");
		$addressNode->addChild("AddressLine","5601 NW Exp");
		$addressNode->addChild("AddressLine","Bldg 2");
		$addressNode->addChild("CityName","Oklahoma City");
		$addressNode->addChild("PostalCode","73112");
		$stateProveNode = $addressNode->addChild("StateProv");
		$stateProveNode->addAttribute("StateCode","OK");
		$addressNode->addChild("CountryName")->addAttribute("Code",$countryCode);

		$vehPrefNode = $vehRsCore->addChild("VehPref");
		$vehPrefNode->addAttribute("AirConditionInd","true");
		$vehPrefNode->addAttribute("AirConditionPref","Preferred");
		$vehPrefNode->addAttribute("TransmissionType","Automatic");
		$vehPrefNode->addAttribute("TransmissionPref","Preferred");
		$vehPrefNode->addAttribute("FuelType","Diesel");
		$vehPrefNode->addAttribute("DriveType","Unspecified");
		$vehPrefNode->addAttribute("Code","ICAR");
		$vehPrefNode->addAttribute("CodeContext","SIPP");

		$vehTypeNode = $vehPrefNode->addChild("VehType");
		$vehTypeNode->addAttribute("VehicleCategory", $vehCategory);
		$vehClassNode = $vehPrefNode->addChild("VehClass");
		$vehClassNode = $vehClassNode->addAttribute("Size", $vehCategory);

		return $xml->asXML();
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
									     $countryCode)
	{
		$xml = $this->getXMLCredentialNode(self::SEARCH_VEHICLE_ACTION, $countryCode);

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
		$sourceNode->addAttribute("PseudoCityCode","BNE");
		$sourceNode->addAttribute("ISOCountry","BS");
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


	