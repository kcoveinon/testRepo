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
	const CANCEL_BOOKING_ACTION = "OTA_VehCancelRQ";
	const MODIFY_BOOKING_ACTION = "OTA_VehModifyRQ";
	const GET_DEPOT_DETAILS_ACTION = "OTA_VehLocDetailRQ";
	const GET_LOCATION_DEPOTS = "OTA_VehLocSearchRQ";

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
		$this->apiUrl                 = Config::get(get_class() . '.api.url');
		$this->apiValidationCode      = Config::get(get_class() . '.api.validationCode');
		$this->apiValidationNumber    = Config::get(get_class() . '.api.validationNumber');
		$this->apiConsumerProductCode = Config::get(get_class() . '.api.consumerProductCode');

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

	public function getLocationDepots($locationCode, $countryCode)
	{
		return $this->otaVehLocSearchRQ($locationCode, $countryCode);
	}

	public function getDepotDetails($locationCode, $countryCode)
	{
		return $this->otaVehLocDetailRQ($locationCode, $countryCode);
	}

	public function cancelBooking($bookingId, $countryCode)
	{
		return $this->otaVehCancelRQ($bookingId, $countryCode);
	}

	public function modifyBooking(
		$bookingId, 
		$pickUpDate, 
		$pickUpTime, 
		$returnDate, 
		$returnTime, 
		$pickUpLocationId, 
		$returnLocationId, 
		$countryCode, 
		$vehicleCategory, 
		$vehicleClass
	) {
		return $this->otaVehModifyRQ(
					$bookingId, 
					$pickUpDate, 
					$pickUpTime, 
					$returnDate, 
					$returnTime, 
					$pickUpLocationId, 
					$returnLocationId, 
					$countryCode, 
					$vehicleCategory, 
					$vehicleClass
				);
	}

	public function getBookingDetails($bookingId, $countryCode)
	{
		return $this->otaVehRetResRQ($bookingId, $countryCode);
	}

	public function searchVehicles(
		$pickUpDate, 
		$pickUpTime, 
		$returnDate, 
		$returnTime, 
		$pickUpLocationCode,
		$returnLocationCode,
		$countryCode, 
		$driverAge
	) {	
		return $this->otaVehAvailRateRQ(
					$pickUpDate, 
					$pickUpTime, 
					$returnDate, 
					$returnTime, 
					$pickUpLocationCode,
					$returnLocationCode,
					$countryCode, 
					$driverAge
				);
	}

	public function doBooking(
		$pickUpDate, 
		$pickUpTime, 
		$returnDate, 
		$returnTime, 
		$pickUpLocationId,
		$returnLocationId,
		$countryCode, 
		$vehicleCategory,
		$vehicleClass
	) {	
		return $this->otaVehResRQ(
				$pickUpDate, 
				$pickUpTime, 
				$returnDate, 
				$returnTime, 
				$pickUpLocationId,
				$returnLocationId,
				$countryCode, 
				$vehicleCategory,
				$vehicleClass
			);
	}

	/**
	 * Returns location depots
	 * 
	 * @param  string $locationCode
	 * @param  string $countryCode
	 * @return XML Object
	 * 
	 */
	private function otaVehLocSearchRQ($locationCode, $countryCode)
	{
		ini_set('max_execution_time', 120);
		$curlOptions = $this->defaultCurlOptions;
		$xmlRequest  = $this->getXmlForGetLocationDepots(
								$locationCode,
								$countryCode
							);
		$curlOptions[CURLOPT_POSTFIELDS] = 	$xmlRequest->asXML();
		$curlHandler = curl_init();
		curl_setopt_array($curlHandler, $curlOptions);
		$response = curl_exec($curlHandler);
		curl_close($curlHandler);

		return new SimpleXMLElement($response);
	}

	/**
	 * Returns the details for a particular locationCode
	 * 
	 * @param  string $locationCode
	 * @param  string $countryCode
	 * 
	 * @return object
	 */
	private function otaVehLocDetailRQ($locationCode, $countryCode)
	{
		ini_set('max_execution_time', 120);
		$curlOptions = $this->defaultCurlOptions;
		$xmlRequest  = $this->getXmlForDepotDetails(
								$locationCode,
								$countryCode
							);
		$curlOptions[CURLOPT_POSTFIELDS] = 	$xmlRequest->asXML();
		$curlHandler = curl_init();
		curl_setopt_array($curlHandler, $curlOptions);
		$response = curl_exec($curlHandler);
		curl_close($curlHandler);

		return new SimpleXMLElement($response);
	}

	/**
	 * Handles the cancel booking action
	 * 
	 * @param  int/array $bookingId (You can pass here an array of Ids or just a single booking ID)
	 * @param  string $countryCode
	 * 
	 * @return XML Object
	 */
	private function otaVehCancelRQ($bookingId, $countryCode)
	{
		ini_set('max_execution_time', 120);
		$bookingIdArray[] = $bookingId;

		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();
		$curlOptions      = $this->defaultCurlOptions;

		$iterableArray = is_array($bookingId) ? reset($bookingIdArray) : $bookingIdArray;

		foreach ($iterableArray as $key => $value) {
			$xmlRequest = $this->getCancelBookingXml(
									$value,
									$countryCode
								);
			$curlOptions[CURLOPT_POSTFIELDS] =  $xmlRequest->asXML();
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

	/**
	 * Function that handles the modify booking action
	 * 
	 * @param int $bookingId
	 * @param datetime $pickUpDate
	 * @param datetime $pickUpTime  
	 * @param datetime $returnDate   
	 * @param datetime $returnTime      
	 * @param int $pickUpLocationId
	 * @param int $returnLocationId 
	 * @param int $countryCode      
	 * @param string $vehicleCategory
	 * @param string $vehicleClass
	 * 
	 * @return XML Object
	 */
	private function otaVehModifyRQ(
		$bookingId, 
		$pickUpDate, 
		$pickUpTime, 
		$returnDate, 
		$returnTime, 
		$pickUpLocationId, 
		$returnLocationId, 
		$countryCode, 
		$vehicleCategory, 
		$vehicleClass
	) {
		ini_set('max_execution_time', 120);
		$bookingIdArray[] = $bookingId;

		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();	
		$curlOptions      = $this->defaultCurlOptions;

		$pickUpDateTime = $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime);
		$returnDateTime = $this->convertToDateTimeDefaultFormat($returnDate, $returnTime);
		$iterableArray = is_array($bookingId) ? reset($bookingIdArray) : $bookingIdArray;

		foreach ($iterableArray as $key => $value) {
			$xmlRequest = $this->getModifyBookingXml(
						      $value,
						      $pickUpDateTime,
						      $returnDateTime, 
						      $pickUpLocationId,
						      $returnLocationId, 
						      $countryCode, 
						      $vehicleCategory, 
						      $vehicleClass
						   );
			$curlOptions[CURLOPT_POSTFIELDS] =  $xmlRequest->asXML();
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

	/**
	 * Retrieves booking details of a particular booking Id or an array of booking IDs
	 * 
	 * @param  int/array $bookingId (You can pass here an array of Ids or just a single booking ID)
	 * @param  string $countryCode
	 * 
	 * @return XML Object
	 */
	private function otaVehRetResRQ($bookingId, $countryCode)
	{
		ini_set('max_execution_time', 120);
		$bookingIdArray[] = $bookingId;

		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();	
		$curlOptions      = $this->defaultCurlOptions;

		$iterableArray = is_array($bookingId) ? reset($bookingIdArray) : $bookingIdArray;
		foreach ($iterableArray as $key => $value) {
			$xmlRequest = $this->getBookingDetailsXML(
									$value,
									$countryCode
								);			
			$curlOptions[CURLOPT_POSTFIELDS] =  $xmlRequest->asXML();
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

	/**
	 * Function that handles the data pull for search
	 * 
	 * @param datetime $pickUpDate
	 * @param datetime $pickUpTime  
	 * @param datetime $returnDate   
	 * @param datetime $returnTime      
	 * @param int $pickUpLocationCode
	 * @param int $returnLocationCode 
	 * @param int $countryCode      
	 * @param int $driverAge
	 * 
	 * @return XML Object
	 */
	private function otaVehAvailRateRQ(
		$pickUpDate, 
		$pickUpTime, 
		$returnDate, 
		$returnTime, 
		$pickUpLocationCode,
		$returnLocationCode,
		$countryCode, 
		$driverAge
	) {	
		
		ini_set('max_execution_time', 120);

		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();	

		$curlOptions = $this->defaultCurlOptions;
		$xmlRequest  = $this->getSearchVehicleXML(
							$this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime),
							$this->convertToDateTimeDefaultFormat($returnDate, $returnTime),
							$pickUpLocationCode,
							$returnLocationCode,
							$countryCode
						);
		$curlOptions[CURLOPT_POSTFIELDS] = $xmlRequest->asXML();
		$curlHandler = curl_init();
		curl_setopt_array($curlHandler, $curlOptions);
		$response = curl_exec($curlHandler);
		curl_close($curlHandler);
		
		$xmlObject = new SimpleXMLElement($response);
		$result = [];

		if (isset($xmlObject->Errors)) {
			$result['status'] =  "Failed";
			$result['data'][] = $xmlObject->Errors->Error->attributes()->ShortText;
		} else {
			$vehRsCore = $xmlObject->VehAvailRSCore->VehVendorAvails->VehVendorAvail;
			$carDetails = $vehRsCore->VehAvails->VehAvail->VehAvailCore->Vehicle;
			$rentalDetails = $vehRsCore->VehAvails->VehAvail->VehAvailCore->RentalRate;

			$result['status'][] = "Success";
			$result['data'][] = array(
	            'hasAirCondition' => $carDetails->attributes()->AirConditionInd,
	            'transmission'    => $carDetails->attributes()->TransmissionType,
	            'baggageQty'      => 'N/A',
	            'co2Qty'          => 'N/A',
	            'categoryCode'    => $carDetails->attributes()->Code,
	            'doorCount'       => $carDetails->VehType->attributes()->DoorCount,
	            'name'            => $carDetails->VehMakeModel->attributes()->Name,
	            'seats'           => $carDetails->VehClass->attributes()->Size,
	            'vehicleStatus'   => array(
	                'code'        => 'N/A',
	                'description' => 'N/A',
	            ),
	            'rateId'    => $vehRsCore->VehAvails->VehAvail->VehAvailCore->Reference->attributes()->ID,
	            'basePrice' => $rentalDetails->VehicleCharges->VehicleCharge->attributes()->Amount,
	            'currency'  => $rentalDetails->VehicleCharges->VehicleCharge->attributes()->CurrencyCode,
	            'bookingCurrencyOfTotalRateEstimate' => 'N/A',
	            'xrsBasePrice'                       =>  'N/A',
	            'xrsBasePriceInBookingCurrency'      =>  'N/A',
	            'totalRateEstimate'                  =>  $vehRsCore->VehAvails->VehAvail->VehAvailCore->TotalCharge->attributes()->EstimatedTotalAmount,
	            'totalRateEstimateInBookingCurrency' =>  'N/A',
	        );
		}

		return $result;		
	}

	/**
	 * Function that handles the booking action
	 * 
	 * @param datetime $pickUpDate
	 * @param datetime $pickUpTime  
	 * @param datetime $returnDate   
	 * @param datetime $returnTime      
	 * @param int $pickUpLocationId
	 * @param int $returnLocationId 
	 * @param int $countryCode      
	 * @param string $vehicleCategory
	 * @param string $vehicleClass
	 * 
	 * @return XML Object
	 */
	public function otaVehResRQ(
		$pickUpDate, 
		$pickUpTime, 
		$returnDate, 
		$returnTime, 
		$pickUpLocationId,
		$returnLocationId,
		$countryCode, 
		$vehicleCategory,
		$vehicleClass
	) {	

		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();

		ini_set('max_execution_time', 120);
		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();	

		$pickUpDateTime = $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime);
		$returnDateTime = $this->convertToDateTimeDefaultFormat($returnDate, $returnTime);

		$depotObject = $this->getLocationDepots("BNE","AU");
		$depotArray  = [];	
		foreach ($depotObject->VehMatchedLocs->VehMatchedLoc as $value) {
			$attribut         = $value->LocationDetail->attributes();
			$depotArray[] =  $test->Code;
		}

		$curlOptions = $this->defaultCurlOptions;
		foreach ($depotArray as $key => $value) {
 			$xmlRequest = $this->getXmlForBooking(
							$pickUpDateTime,
							$returnDateTime,
							$value,
							$value,
							$countryCode,
							$vehicleCategory,
							$vehicleClass
						);
 			$curlOptions[CURLOPT_POSTFIELDS] = $xmlRequest->asXML();
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
	 * Returns XMl for get location depots
	 * 
	 * @param  string $locationCode
	 * @param  string $countryCode
	 * 
	 * @return XML
	 */
	public function getXmlForGetLocationDepots($locationCode, $countryCode)
	{
		$xmlAction = self::GET_LOCATION_DEPOTS;

		$xml = $this->getXMLCredentialNode($xmlAction, $countryCode);

		$vehLocSearchCriterionNode = $xml->addChild("VehLocSearchCriterion");
		$codeRefNode = $vehLocSearchCriterionNode->addChild("CodeRef");
		$codeRefNode->addAttribute("LocationCode", $locationCode);

		$vendorNode = $xml->addChild("Vendor");
		$vendorNode->addAttribute("Code","ZE");
		
		return $xml;
	}	

	/**
	 * Constructs the xml request for modify booking
	 * 
	 * @param int $bookingId
	 * @param datetime $pickUpDate
	 * @param datetime $pickUpTime  
	 * @param datetime $returnDate   
	 * @param datetime $returnTime      
	 * @param int $pickUpLocationId
	 * @param int $returnLocationId 
	 * @param int $countryCode      
	 * @param string $vehicleCategory
	 * @param string $vehicleClass
	 * 
	 * @return XML Object
	 */
	public function getModifyBookingXml(
		$bookingId,
		$pickUpDateTime,
		$returnDateTime,
		$pickUplocationCode,
		$returnLocationCode,
		$countryCode,
		$vehicleCategory,
		$vehicleClass	
	) {
		$xmlAction = self::MODIFY_BOOKING_ACTION;
		
		$xml = $this->getXMLCredentialNode($xmlAction, $countryCode);
		
		$vehModifyRQCore    = $xml->addChild("VehModifyRQCore");
		$vehModifyRQCore->addAttribute("Status", "Confirmed");
		$vehModifyRQCore->addAttribute("ModifyType", "Quote");
		
		$uniqueIDNode       = $vehModifyRQCore->addChild("UniqueID");
		$uniqueIDNode->addAttribute("Type", "14");
		$uniqueIDNode->addAttribute("ID", (string) $bookingId);
		
		$vehRentalCoreNode  = $vehModifyRQCore->addChild("VehRentalCore");
		$vehRentalCoreNode->addAttribute("PickUpDateTime", $pickUpDateTime);
		$vehRentalCoreNode->addAttribute("ReturnDateTime", $returnDateTime);
		
		$pickUplocationNode = $vehModifyRQCore->addChild("PickUpLocation");
		$pickUplocationNode->addAttribute("CodeContext", self::DEFAULT_CODE_CONTEXT);
		$pickUplocationNode->addAttribute("LocationCode", $pickUplocationCode);
		
		$returnLocationNode = $vehModifyRQCore->addChild("ReturnLocation");
		$returnLocationNode->addAttribute("CodeContext", self::DEFAULT_CODE_CONTEXT);
		$returnLocationNode->addAttribute("LocationCode", $returnLocationCode);
		
		$customerNode       = $vehModifyRQCore->addChild("Customer");
		$primaryNode        = $customerNode->addChild("Primary");
		$personNameNode     = $primaryNode->addChild("PersonName");
		$personNameNode->addChild("GivenName", "PrePaidThree");
		$personNameNode->addChild("Surname", "Testing");

		$telephoneNode      = $primaryNode->addChild("Telephone");
		$telephoneNode->addAttribute("PhoneTechType", "1");
		$telephoneNode->addAttribute("AreaCityCode", "9999");
		$telephoneNode->addAttribute("PhoneNumber", "9999999");
		$primaryNode->addChild("Email", "saford@hertz.com");		
		
		$addressNode        = $primaryNode->addChild("Address");
		$addressNode->addChild("AddressLine", "5601 NW Exp");
		$addressNode->addChild("AddressLine", "Bldg 2");
		$addressNode->addChild("CityName", "Oklahoma City");
		$addressNode->addChild("PostalCode", "73112");
		$stateProveNode     = $addressNode->addChild("StateProv");
		$stateProveNode->addAttribute("StateCode", "OK");
		$addressNode->addChild("CountryName")->addAttribute("Code", $countryCode);	
		
		$vehPrefNode        = $vehModifyRQCore->addChild("VehPref");
		$vehPrefNode->addAttribute("AirConditionInd", "true");
		$vehPrefNode->addAttribute("AirConditionPref", "Preferred");
		$vehPrefNode->addAttribute("TransmissionType", "Automatic");
		$vehPrefNode->addAttribute("TransmissionPref", "Preferred");
		$vehPrefNode->addAttribute("FuelType", "Diesel");
		$vehPrefNode->addAttribute("DriveType", "Unspecified");
		$vehPrefNode->addAttribute("Code", "ICAR");
		$vehPrefNode->addAttribute("CodeContext", "SIPP");
		
		$vehTypeNode        = $vehPrefNode->addChild("VehType");
		$vehTypeNode->addAttribute("VehicleCategory", $vehicleCategory);
		$vehicleClassNode       = $vehPrefNode->addChild("vehicleClass");
		$vehicleClassNode       = $vehicleClassNode->addAttribute("Size", $vehicleCategory);

		return $xml;
	}

	/**
	 * Returns XML request for getDepotDetails
	 * 
	 * @param  string $locationCode
	 * @param  string $countryCode
	 * 
	 * @return XML
	 */
	public function getXmlForDepotDetails($locationCode, $countryCode)
	{
		$xmlAction = self::GET_DEPOT_DETAILS_ACTION;

		$xml = $this->getXMLCredentialNode($xmlAction, $countryCode);

		$locationNode = $xml->addChild("Location");
		$locationNode->addAttribute("LocationCode", $locationCode);
		
		return $xml;
	}

	/**
	 * Returns the needed XML request for modify booking action
	 * 
	 * @param  int $bookingId
	 * @param  string $countryCode
	 * 
	 * @return XML
	 */
	public function getCancelBookingXml($bookingId, $countryCode)
	{
		$xmlAction = self::CANCEL_BOOKING_ACTION;

		$xml = $this->getXMLCredentialNode($xmlAction, $countryCode);

		$vehCancelRQCore = $xml->addChild("VehCancelRQCore");
		$vehCancelRQCore->addAttribute("CancelType", "Book");

		$uniqueIDNode = $vehCancelRQCore->addChild("UniqueID");
		$uniqueIDNode->addAttribute("Type", "14");
		$uniqueIDNode->addAttribute("ID", (string) $bookingId);

		$personNameNode = $vehCancelRQCore->addChild("PersonName");
		$personNameNode->addChild("Surname","Testing");	
		
		return $xml;
	}	

	/**
	 * Returns the needed XML request for booking details
	 * 
	 * @param  int $bookingId
	 * @param $countryCode
	 * 
	 * @return XML
	 */
	public function getBookingDetailsXML($bookingId, $countryCode)
	{
		$xmlAction = self::GET_BOOKING_INFO_ACTION;

		$xml = $this->getXMLCredentialNode($xmlAction, $countryCode);

		$vehRetResRQCoreNode = $xml->addChild("VehRetResRQCore");
		$uniqueIDNode = $vehRetResRQCoreNode->addChild("UniqueID");
		$uniqueIDNode->addAttribute("Type", "14");
		$uniqueIDNode->addAttribute("ID", (string) $bookingId);

		$personNameNode = $vehRetResRQCoreNode->addChild("PersonName");
		$personNameNode->addChild("Surname", "Testing");	
		
		return $xml;
	}

	/**
	 * Returns the needed XML request for booking action
	 * 
	 * @param datetime $pickUpDate
	 * @param datetime $pickUpTime  
	 * @param datetime $returnDate   
	 * @param datetime $returnTime      
	 * @param int $pickUpLocationId
	 * @param int $returnLocationId 
	 * @param int $vehicleCategory      
	 * @param int $vehicleClass
	 * 
	 * @return XML
	 */
	public function getXmlForBooking(
		$pickUpDateTime,
		$returnDateTime,
		$pickUplocationCode,
		$returnLocationCode,
		$countryCode,
		$vehicleCategory,
		$vehicleClass
	) {
		$xmlAction = self::BOOK_VEHICLE_ACTION;
		$xml = $this->getXMLCredentialNode($xmlAction, $countryCode);

		$vehRsCore = $xml->addChild("VehResRQCore");
		$vehRsCore->addAttribute("Status",self::DEFAULT_REQUEST_STATUS);

		$vehRentalCoreNode = $vehRsCore->addChild("VehRentalCore");
		$vehRentalCoreNode->addAttribute("PickUpDateTime", $pickUpDateTime);
		$vehRentalCoreNode->addAttribute("ReturnDateTime", $returnDateTime);

		$pickUplocationNode = $vehRentalCoreNode->addChild("PickUpLocation");
		$pickUplocationNode->addAttribute("CodeContext", self::DEFAULT_CODE_CONTEXT);
		$pickUplocationNode->addAttribute("LocationCode", $pickUplocationCode);

		$returnLocationNode = $vehRentalCoreNode->addChild("ReturnLocation");
		$returnLocationNode->addAttribute("CodeContext", self::DEFAULT_CODE_CONTEXT);
		$returnLocationNode->addAttribute("LocationCode", $returnLocationCode);

		$customerNode = $vehRsCore->addChild("Customer");
		$primaryNode = $customerNode->addChild("Primary");
		$personNameNode = $primaryNode->addChild("PersonName");
		$personNameNode->addChild("GivenName", "PrePaidThree");
		$personNameNode->addChild("Surname", "Testing");
		$telephoneNode = $primaryNode->addChild("Telephone");
		$telephoneNode->addAttribute("PhoneTechType", "1");
		$telephoneNode->addAttribute("AreaCityCode", "9999");
		$telephoneNode->addAttribute("PhoneNumber", "9999999");
		$primaryNode->addChild("Email", "saford@hertz.com");		

		$addressNode = $primaryNode->addChild("Address");
		$addressNode->addChild("AddressLine", "5601 NW Exp");
		$addressNode->addChild("AddressLine", "Bldg 2");
		$addressNode->addChild("CityName", "Oklahoma City");
		$addressNode->addChild("PostalCode", "73112");
		$stateProveNode = $addressNode->addChild("StateProv");
		$stateProveNode->addAttribute("StateCode", "OK");
		$addressNode->addChild("CountryName")->addAttribute("Code", $countryCode);

		$vehPrefNode = $vehRsCore->addChild("VehPref");
		$vehPrefNode->addAttribute("AirConditionInd", "true");
		$vehPrefNode->addAttribute("AirConditionPref", "Preferred");
		$vehPrefNode->addAttribute("TransmissionType", "Automatic");
		$vehPrefNode->addAttribute("TransmissionPref", "Preferred");
		$vehPrefNode->addAttribute("FuelType", "Diesel");
		$vehPrefNode->addAttribute("DriveType", "Unspecified");
		$vehPrefNode->addAttribute("Code", "ICAR");
		$vehPrefNode->addAttribute("CodeContext", "SIPP");

		$vehTypeNode = $vehPrefNode->addChild("VehType");
		$vehTypeNode->addAttribute("VehicleCategory", $vehicleCategory);
		$vehicleClassNode = $vehPrefNode->addChild("vehicleClass");
		$vehicleClassNode = $vehicleClassNode->addAttribute("Size", $vehicleCategory);

		return $xml;
	}	

	/**
	 * Returns XML Request for search vehicle action
	 * 
	 * @param  date $pickUpDateTime   
	 * @param  date  $returnDateTime   
	 * @param  string $pickUpLocationId 
	 * @param  string $returnLocationId 
	 * @param  string $countryCode      
	 *    
	 * @return XML                   
	 */
	private function getSearchVehicleXML(
		$pickUpDateTime,
		$returnDateTime,
		$pickUpLocationId,
		$returnLocationId,
		$countryCode
	) {
		$xml = $this->getXMLCredentialNode(self::SEARCH_VEHICLE_ACTION, $countryCode);

		$vehAvailRQCoreNode = $xml->addChild("VehAvailRQCore");
		$vehAvailRQCoreNode->addAttribute("Status", self::DEFAULT_REQUEST_STATUS);

		$vehRentalCoreNode = $vehAvailRQCoreNode->addChild("VehRentalCore");
		$vehRentalCoreNode->addAttribute("PickUpDateTime", $pickUpDateTime);
		$vehRentalCoreNode->addAttribute("ReturnDateTime", $returnDateTime);

		$pickUplocationNode = $vehRentalCoreNode->addChild("PickUpLocation");
		$pickUplocationNode->addAttribute("CodeContext", self::DEFAULT_CODE_CONTEXT);
		$pickUplocationNode->addAttribute("LocationCode", $pickUpLocationId);

		$returnLocationNode = $vehRentalCoreNode->addChild("ReturnLocation");
		$returnLocationNode->addAttribute("CodeContext", self::DEFAULT_CODE_CONTEXT);
		$returnLocationNode->addAttribute("LocationCode", $returnLocationId);

		return $xml;
	}	

	/**
	 * Returns POS credential node
	 * 
	 * @param  string $xmlAction
	 * @param  string $countryCode
	 * 
	 * @return XML
	 */
	public function getXMLCredentialNode($xmlAction, $countryCode)
	{
		$xml = new SimpleXMLElement('<' . $xmlAction . '></' . $xmlAction . '>');
		$xml->addAttribute("xmlns", self::DEFAULT_XMLNS);
		$xml->addAttribute("xmlns:xsi", self::DEFAULT_XMLNS_XSI);
		$xml->addAttribute("xsi:schemaLocation", self::DEFAULT_XMLNS. " " . $xmlAction . ".xsd");
		$xml->addAttribute("Version", self::DEFAULT_VERSION);
		$xml->addAttribute("SequenceNmbr", self::DEFAULT_SEQUENCENUMBER);

		$posNode = $xml->addChild("POS");
		$sourceNode = $posNode->addChild("Source");
		$sourceNode->addAttribute("PseudoCityCode", "BNE");
		$sourceNode->addAttribute("ISOCountry", $countryCode);
		$sourceNode->addAttribute("AgentDutyCode", $this->apiValidationCode);

		$requestNode = $sourceNode->addChild("RequestorID");
		$requestNode->addAttribute("Type", self::DEFAULT_REQUEST_TYPE);
		$requestNode->addAttribute("ID", $this->apiValidationNumber);

		$companyNameNode = $requestNode->addChild("CompanyName");
		$companyNameNode->addAttribute("Code", self::DEFAULT_CONSUMER_PRODUCT);
		$companyNameNode->addAttribute("CodeContext", $this->apiConsumerProductCode);

		return $xml;	
	}

	/**
	 * Returns depots per location IDs
	 * 
	 * @param int $pickUpLocationId
	 * @param int $returnLocationId
	 * 
	 * @return DEPOT Object
	 */
	public function returnDepotByLocationId($pickUpLocationId, $returnLocationId)
	{
		$pickUpObj = Location::find($pickUpLocationId);
		return $pickUpObj ? Depot::getGroupedDepotCode($pickUpObj->getCity())->get() : false;
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

		return $date->format('Y-m-d H:i:s');
	}
}


	