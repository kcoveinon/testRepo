<?php 

/**
 * Hertz API base class
 *
 * @author Inon Baguio <inon.vroomvroomvroom.com.au>
 */
class HZ extends SupplierApi
{
	const SEARCH_VEHICLE_ACTION    = 'OTA_VehAvailRateRQ';
	const BOOK_VEHICLE_ACTION      = 'OTA_VehResRQ';
	const GET_BOOKING_INFO_ACTION  = 'OTA_VehRetResRQ';
	const CANCEL_BOOKING_ACTION    = 'OTA_VehCancelRQ';
	const MODIFY_BOOKING_ACTION    = 'OTA_VehModifyRQ';
	const GET_DEPOT_DETAILS_ACTION = 'OTA_VehLocDetailRQ';
	const GET_LOCATION_DEPOTS      = 'OTA_VehLocSearchRQ';

	const DEFAULT_XMLNS            = 'http://www.opentravel.org/OTA/2003/05';
	const DEFAULT_XMLNS_XSI        = 'http://www.w3.org/2008/XMLSchema-instance';
	const DEFAULT_VERSION          = '1.0';
	const DEFAULT_MAXRESPONSE      = '99';
	const DEFAULT_PSEUDOCITYCODE   = 'LAX';
	const DEFAULT_REQUEST_TYPE     = '4';
	const DEFAULT_CODE_CONTEXT     = 'IATA';
	const DEFAULT_REQUEST_STATUS   = 'All';
	const DEFAULT_CONSUMER_PRODUCT = 'CP';

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

	/**
	 * The Supplier Code
	 */
	private $supplierCode;

	public function __construct()
	{
		$this->supplierCode 		  = get_class();
		$this->apiUrl                 = Config::get($this->supplierCode  . '.api.url');
		$this->apiValidationCode      = Config::get($this->supplierCode  . '.api.validationCode');
		$this->apiValidationNumber    = Config::get($this->supplierCode  . '.api.validationNumber');
		$this->apiConsumerProductCode = Config::get($this->supplierCode  . '.api.consumerProductCode');

		$this->headers = array(
		    'Content-type: text/xml;charset="utf-8"',
		    'Accept: text/xml',
		    'Cache-Control: no-cache',
		    'Pragma: no-cache'
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
	 * Returns location depots
	 * 
	 * @param  string $locationCode
	 * 
	 * @return XML Object
	 */
	public function getLocationDepots($locationCode)
	{
		$xmlRequest  = $this->getXmlForGetLocationDepots($locationCode);

		return $this->executeCurl($xmlRequest->asXML());
	}

	/**
	 * Returns the details for a particular locationCode
	 * 
	 * @param  string $locationCode
	 * 
	 * @return object
	 */
	public function getDepotDetails($locationCode)
	{
		$xmlRequest  = $this->getXmlForDepotDetails($locationCode);

		return $this->executeCurl($xmlRequest->asXML());
	}

	/**
	 * Handles the cancel booking action
	 * 
	 * @param  int/array $bookingId
	 * @param  string $lastName
	 * 
	 * @return XML Object
	 */
	public function cancelBooking($bookingId, $lastName)
	{
		$xmlRequest = $this->getCancelBookingXml($bookingId, $lastName);

		return $this->executeCurl($xmlRequest->asXML());	
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
	 * @param string $vehicleCategory
	 * @param string $vehicleClass
	 * 
	 * @return XML Object
	 */
	public function modifyBooking(
		$bookingId, 
		$pickUpDate, 
		$pickUpTime, 
		$returnDate, 
		$returnTime, 
		$pickUplocationCode, 
		$returnLocationCode, 
		$vehicleCategory, 
		$vehicleClass
	) {
		$xmlRequest = $this->getModifyBookingXml(
					      $bookingId,
					      $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime),
					      $this->convertToDateTimeDefaultFormat($returnDate, $returnTime), 
					      $pickUplocationCode,
					      $returnLocationCode, 
					      $vehicleCategory, 
					      $vehicleClass
					  );

		return $this->executeCurl($xmlRequest->asXML());	
	}

	/**
	 * Retrieves booking details of a particular booking Id or an array of booking IDs
	 * 
	 * @param  int/array $bookingId
	 * @param  string $lastName
	 * 
	 * @return XML Object
	 */
	public function getBookingDetails($bookingId, $lastName)
	{
		$xmlRequest = $this->getBookingDetailsXML($bookingId, $lastName);
		return $this->executeCurl($xmlRequest->asXML());	
	}

	/**
	 * Executes Search functionality
	 * 
	 * @param  date $pickUpDate         
	 * @param  time $pickUpTime         
	 * @param  date $returnDate         
	 * @param  time $returnTime         
	 * @param  string $pickUpLocationCode 
	 * @param  string $returnLocationCode 
	 * @param  string $countryCode        
	 * @param  int $driverAge    
	 *       
	 * @return MIXED
	 */
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
		if ($this->validateDate($pickUpDate, $pickUpTime) && $this->validateDate($returnDate, $returnTime)) {
			$response =  $this->otaVehAvailRateRQ(
							$pickUpDate, 
							$pickUpTime, 
							$returnDate, 
							$returnTime, 
							$pickUpLocationCode,
							$returnLocationCode,
							$countryCode, 
							$driverAge
						 );
		} else {
			$response =  ['result' => 'Invalid Parameters'];
		}

		return $response;
	}

	/**
	 * Function that handles the booking action
	 * 
	 * @param datetime $pickUpDate
	 * @param datetime $pickUpTime  
	 * @param datetime $returnDate   
	 * @param datetime $returnTime      
	 * @param int $pickUplocationCode
	 * @param int $returnLocationCode
	 * @param int $countryCode      
	 * @param string $vehicleCategory
	 * @param string $vehicleClass
	 * @param string $equipments
	 * @param string $equipments
	 * @param string $firstName
	 * @param string $lastName
	 * 
	 * @return XML Object
	 */
	public function doBooking(
		$pickUpDate, 
		$pickUpTime, 
		$returnDate, 
		$returnTime, 
		$pickUplocationCode,
		$returnLocationCode,
		$countryCode, 
		$vehicleCategory,
		$vehicleClass,
		$equipments,
		$age,
		$firstName,
		$lastName
	) {	
		if (!$this->validateDate($pickUpDate, $pickUpTime) && !$this->validateDate($returnDate, $returnTime)) {
			$response = ['result' => 'Invalid Parameters'];
		} else {
			$curlOptions = $this->defaultCurlOptions;
			$xmlRequest = $this->getXmlForBooking(
							$this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime),
							$this->convertToDateTimeDefaultFormat($returnDate, $returnTime),
							$pickUplocationCode,
							$returnLocationCode,
							$countryCode,
							$vehicleCategory,
							$vehicleClass,
							$equipments,
							$age,
							$firstName,
							$lastName
						  );
			$response = $this->executeCurl($xmlRequest->asXML());
		}

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
		$timeStart = time();			
		$xmlRequest  = $this->getSearchVehicleXML(
							$this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime),
							$this->convertToDateTimeDefaultFormat($returnDate, $returnTime),
							$pickUpLocationCode,
							$returnLocationCode,
							$countryCode,
							$driverAge
					   );
		$xmlObject = $this->executeCurl($xmlRequest->asXML());
		$result = [];

		if (isset($xmlObject->Errors)) {
			$result['status'] =  'Failed';
			$result['data']   = (string) $xmlObject->Errors->Error->attributes()->ShortText;
		} else {
		
			$vehRsCore = $xmlObject->VehAvailRSCore->VehVendorAvails->VehVendorAvail;
			$result['status'] = 'OK';	
			$acrissHelper = new AcrissHelper();

			foreach ($vehRsCore->VehAvails->VehAvail as $key => $value) {
				$carDetails = $value->VehAvailCore->Vehicle;
				$rentalDetails = $value->VehAvailCore->RentalRate;				
				$result['data'][] = array(
					'supplierCode'	  => (string) $this->supplierCode,
		            'hasAirCondition' => (string) $carDetails->attributes()->AirConditionInd,
		            'transmission'    => (string) $carDetails->attributes()->TransmissionType,
		            'baggageQty'      => (string) $carDetails->attributes()->BaggageQuantity,
		            'co2Qty'          => 'N/A',
		            'categoryCode'    => (string) $carDetails->attributes()->Code,
		            'expandedCode'	  => $acrissHelper->expandCode((string) $carDetails->attributes()->Code),
		            'doorCount'       => (string) $carDetails->VehType->attributes()->DoorCount,
		            'name'            => (string) $carDetails->VehMakeModel->attributes()->Name,
		            'seats'           => (string) $carDetails->attributes()->PassengerQuantity,
		            'vehicleStatus'   => array(
		                'code'        => 'N/A',
		                'description' => 'N/A',
		            ),
		            'rateId'    => (string) $value->VehAvailCore->Reference->attributes()->ID,
		            'basePrice' => (string) $rentalDetails->VehicleCharges->VehicleCharge->attributes()->Amount,
		            'currency'  => (string) $rentalDetails->VehicleCharges->VehicleCharge->attributes()->CurrencyCode,
		            'bookingCurrencyOfTotalRateEstimate' => 'N/A',
		            'xrsBasePrice'                       => 'N/A',
		            'xrsBasePriceInBookingCurrency'      => 'N/A',
		            'totalRateEstimate'                  => (string) $value->VehAvailCore->TotalCharge->attributes()->EstimatedTotalAmount,
		            'totalRateEstimateInBookingCurrency' => 'N/A',
		        );
			}
		}
		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;		

		return $result;		
	}

	/**
	 * Returns XMl for get location depots
	 * 
	 * @param  string $locationCode
	 * 
	 * @return XML
	 */
	public function getXmlForGetLocationDepots($locationCode)
	{
		$xmlAction = self::GET_LOCATION_DEPOTS;
		$xml = $this->getXMLCredentialNode($xmlAction);
		$vehLocSearchCriterionNode = $xml->addChild('VehLocSearchCriterion');
		$codeRefNode = $vehLocSearchCriterionNode->addChild('CodeRef');
		$codeRefNode->addAttribute('LocationCode', $locationCode);

		$vendorNode = $xml->addChild('Vendor');
		$vendorNode->addAttribute('Code', 'ZE');

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
	 * @param int $pickUplocationCode
	 * @param int $returnLocationCode 
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
		$vehicleCategory,
		$vehicleClass	
	) {
		$xmlAction = self::MODIFY_BOOKING_ACTION;
		
		$xml = $this->getXMLCredentialNode($xmlAction);
		
		$vehModifyRQCore    = $xml->addChild('VehModifyRQCore');
		$vehModifyRQCore->addAttribute('Status', 'Confirmed');
		$vehModifyRQCore->addAttribute('ModifyType', 'Book');
		
		$uniqueIDNode       = $vehModifyRQCore->addChild('UniqueID');
		$uniqueIDNode->addAttribute('Type', '14');
		$uniqueIDNode->addAttribute('ID', (string) $bookingId);
		
		$vehRentalCoreNode  = $vehModifyRQCore->addChild('VehRentalCore');
		$vehRentalCoreNode->addAttribute('PickUpDateTime', $pickUpDateTime);
		$vehRentalCoreNode->addAttribute('ReturnDateTime', $returnDateTime);
		
		$pickUplocationNode = $vehModifyRQCore->addChild('PickUpLocation');
		$pickUplocationNode->addAttribute('CodeContext', self::DEFAULT_CODE_CONTEXT);
		$pickUplocationNode->addAttribute('LocationCode', $pickUplocationCode);
		
		$returnLocationNode = $vehModifyRQCore->addChild('ReturnLocation');
		$returnLocationNode->addAttribute('CodeContext', self::DEFAULT_CODE_CONTEXT);
		$returnLocationNode->addAttribute('LocationCode', $returnLocationCode);
		
		$customerNode       = $vehModifyRQCore->addChild('Customer');
		$primaryNode        = $customerNode->addChild('Primary');
		$personNameNode     = $primaryNode->addChild('PersonName');
		$personNameNode->addChild('GivenName', 'PrePaidThree');
		$personNameNode->addChild('Surname', 'Testing');

		$telephoneNode      = $primaryNode->addChild('Telephone');
		$telephoneNode->addAttribute('PhoneTechType', '1');
		$telephoneNode->addAttribute('AreaCityCode', '9999');
		$telephoneNode->addAttribute('PhoneNumber', '9999999');
		$primaryNode->addChild('Email', 'saford@hertz.com');		
		
		$addressNode        = $primaryNode->addChild('Address');
		$addressNode->addChild('AddressLine', '5601 NW Exp');
		$addressNode->addChild('AddressLine', 'Bldg 2');
		$addressNode->addChild('CityName', 'Oklahoma City');
		$addressNode->addChild('PostalCode', '73112');
		$stateProveNode     = $addressNode->addChild('StateProv');
		$stateProveNode->addAttribute('StateCode', 'OK');
		
		$vehPrefNode        = $vehModifyRQCore->addChild('VehPref');
		$vehPrefNode->addAttribute('AirConditionInd', 'true');
		$vehPrefNode->addAttribute('AirConditionPref', 'Preferred');
		$vehPrefNode->addAttribute('TransmissionType', 'Automatic');
		$vehPrefNode->addAttribute('TransmissionPref', 'Preferred');
		$vehPrefNode->addAttribute('FuelType', 'Diesel');
		$vehPrefNode->addAttribute('DriveType', 'Unspecified');
		$vehPrefNode->addAttribute('Code', 'ICAR');
		$vehPrefNode->addAttribute('CodeContext', 'SIPP');
		
		$vehTypeNode      = $vehPrefNode->addChild('VehType');
		$vehTypeNode->addAttribute('VehicleCategory', $vehicleCategory);
		$vehicleClassNode = $vehPrefNode->addChild('vehicleClass');
		$vehicleClassNode = $vehicleClassNode->addAttribute('Size', $vehicleCategory);

		return $xml;
	}

	/**
	 * Returns XML request for getDepotDetails
	 * 
	 * @param  string $locationCode
	 * 
	 * @return XML
	 */
	public function getXmlForDepotDetails($locationCode)
	{
		$xmlAction = self::GET_DEPOT_DETAILS_ACTION;
		$xml = $this->getXMLCredentialNode($xmlAction);
		$locationNode = $xml->addChild('Location');
		$locationNode->addAttribute('LocationCode', $locationCode);
		
		return $xml;
	}

	/**
	 * Returns the needed XML request for modify booking action
	 * 
	 * @param  int $bookingId
	 * @param  string $lastName
	 * 
	 * @return XML
	 */
	public function getCancelBookingXml($bookingId, $lastName)
	{
		$xmlAction = self::CANCEL_BOOKING_ACTION;

		$xml = $this->getXMLCredentialNode($xmlAction);

		$vehCancelRQCore = $xml->addChild('VehCancelRQCore');
		$vehCancelRQCore->addAttribute('CancelType', 'Book');
		
		$uniqueIDNode    = $vehCancelRQCore->addChild('UniqueID');
		$uniqueIDNode->addAttribute('Type', '14');
		$uniqueIDNode->addAttribute('ID', (string) $bookingId);
		
		$personNameNode  = $vehCancelRQCore->addChild('PersonName');
		$personNameNode->addChild('Surname', $lastName);	
		
		return $xml;
	}	

	/**
	 * Returns the needed XML request for booking details
	 * 
	 * @param  int $bookingId
	 * @param  string $lastName
	 * 
	 * @return XML
	 */
	public function getBookingDetailsXML($bookingId, $lastName)
	{
		$xmlAction = self::GET_BOOKING_INFO_ACTION;

		$xml = $this->getXMLCredentialNode($xmlAction);

		$vehRetResRQCoreNode = $xml->addChild('VehRetResRQCore');
		$uniqueIDNode = $vehRetResRQCoreNode->addChild('UniqueID');
		$uniqueIDNode->addAttribute('Type', '14');
		$uniqueIDNode->addAttribute('ID', (string) $bookingId);

		$personNameNode = $vehRetResRQCoreNode->addChild('PersonName');
		$personNameNode->addChild('Surname', trim($lastName));	
		
		return $xml;
	}

	/**
	 * Returns the needed XML request for booking action
	 * 
	 * @param datetime $pickUpDateTime
	 * @param datetime $returnDateTime      
	 * @param string $pickUplocationCode
	 * @param string $returnLocationCode
	 * @param string $countryCode
	 * @param int $vehicleCategory      
	 * @param int $vehicleClass
	 * @param int $equipments
	 * @param int $age
	 * @param int $firstName
	 * @param int $lastName
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
		$vehicleClass,
		$equipments,
		$age,
		$firstName,
		$lastName
	) {

		$xmlAction = self::BOOK_VEHICLE_ACTION;

		$xml = new SimpleXMLElement('<' . $xmlAction . '></' . $xmlAction . '>');
		$xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2008/XMLSchema-instance');
		$xml->addAttribute('Version', self::DEFAULT_VERSION);

		$posNode = $xml->addChild('POS');
		$sourceNode = $posNode->addChild('Source');
		$sourceNode->addAttribute('ISOCountry', 'AU');
		$sourceNode->addAttribute('AgentDutyCode', $this->apiValidationCode);

		$requestNode = $sourceNode->addChild('RequestorID');
		$requestNode->addAttribute('Type', self::DEFAULT_REQUEST_TYPE);
		$requestNode->addAttribute('ID', $this->apiValidationNumber);

		$companyNameNode = $requestNode->addChild('CompanyName');
		$companyNameNode->addAttribute('Code', self::DEFAULT_CONSUMER_PRODUCT);
		$companyNameNode->addAttribute('CodeContext', $this->apiConsumerProductCode);

		$vehRsCore = $xml->addChild('VehResRQCore');
		$vehRsCore->addAttribute('Status','Available');

		$vehRentalCoreNode = $vehRsCore->addChild('VehRentalCore');
		$vehRentalCoreNode->addAttribute('PickUpDateTime', $pickUpDateTime);
		$vehRentalCoreNode->addAttribute('ReturnDateTime', $returnDateTime);

		$pickUplocationNode = $vehRentalCoreNode->addChild('PickUpLocation');
		$pickUplocationNode->addAttribute('CodeContext', self::DEFAULT_CODE_CONTEXT);
		$pickUplocationNode->addAttribute('LocationCode', $pickUplocationCode);

		$returnLocationNode = $vehRentalCoreNode->addChild('ReturnLocation');
		$returnLocationNode->addAttribute('CodeContext', self::DEFAULT_CODE_CONTEXT);
		$returnLocationNode->addAttribute('LocationCode', $returnLocationCode);

		$customerNode = $vehRsCore->addChild('Customer');
		$primaryNode = $customerNode->addChild('Primary');

		if ($age > 0 || $age !== '') {
			$date =  new DateTime(date('Y') - ((int) str_replace('+', '', $age)) . '-' . date('m-d'));
			$result = $date->format('Y-m-d');
			$primaryNode->addAttribute('BirthDate', $result);
		}

		$personNameNode = $primaryNode->addChild('PersonName');
		$personNameNode->addChild('GivenName', $firstName);
		$personNameNode->addChild('Surname', $lastName);
		$citizenCountryNameNode = $primaryNode->addChild('CitizenCountryName');
		$citizenCountryNameNode->addAttribute('Code', $countryCode)	;

		$vehPrefNode = $vehRsCore->addChild('VehPref');
		$vehPrefNode->addAttribute('AirConditionInd', 'true');
		$vehPrefNode->addAttribute('AirConditionPref', 'Preferred');
		$vehPrefNode->addAttribute('TransmissionType', 'Automatic');
		$vehPrefNode->addAttribute('TransmissionPref', 'Preferred');
		$vehPrefNode->addAttribute('FuelType', 'Diesel');
		$vehPrefNode->addAttribute('DriveType', 'Unspecified');
		$vehPrefNode->addAttribute('Code', 'ICAR');
		$vehPrefNode->addAttribute('CodeContext', 'SIPP');

		$vehTypeNode = $vehPrefNode->addChild('VehType');
		$vehTypeNode->addAttribute('VehicleCategory', $vehicleCategory);
		$vehicleClassNode = $vehPrefNode->addChild('VehClass');
		$vehicleClassNode->addAttribute('Size', $vehicleClass);

		$rateQualifierNode = $vehRsCore->addChild('RateQualifier');
		$rateQualifierNode->addAttribute('RateQualifier', 'BEST');

		if(count($equipments) > 0) {
			$specialEquipPrefNode = $vehRsCore->addChild('SpecialEquipPrefs');

			foreach ($equipments as $equipmentDetails) {
				$specialEquipPre = $specialEquipPrefNode->addChild('SpecialEquipPref');
				$specialEquipPre->addAttribute('EquipType', trim($equipmentDetails['eqOTACode']));
				$specialEquipPre->addAttribute('Quantity', trim($equipmentDetails['qty']));
			}
		}

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
	 * @param  int $countryCode      
	 *    
	 * @return XML                   
	 */
	private function getSearchVehicleXML(
		$pickUpDateTime,
		$returnDateTime,
		$pickUpLocationId,
		$returnLocationId,
		$countryCode,
		$driverAge
	) {
		$xml = $this->getXMLCredentialNode(self::SEARCH_VEHICLE_ACTION, $countryCode);

		$vehAvailRQCoreNode = $xml->addChild('VehAvailRQCore');
		$vehAvailRQCoreNode->addAttribute('Status', self::DEFAULT_REQUEST_STATUS);	

		$vehRentalCoreNode = $vehAvailRQCoreNode->addChild('VehRentalCore');
		$vehRentalCoreNode->addAttribute('PickUpDateTime', $pickUpDateTime);
		$vehRentalCoreNode->addAttribute('ReturnDateTime', $returnDateTime);

		$pickUplocationNode = $vehRentalCoreNode->addChild('PickUpLocation');
		$pickUplocationNode->addAttribute('CodeContext', self::DEFAULT_CODE_CONTEXT);
		$pickUplocationNode->addAttribute('LocationCode', $pickUpLocationId);

		$returnLocationNode = $vehRentalCoreNode->addChild('ReturnLocation');
		$returnLocationNode->addAttribute('CodeContext', self::DEFAULT_CODE_CONTEXT);
		$returnLocationNode->addAttribute('LocationCode', $returnLocationId);

        $vehAvailRQInfoNode = $xml->addChild('VehAvailRQInfo');
        $customerNode       = $vehAvailRQInfoNode->addChild('Customer');
        $primaryNode        = $customerNode->addChild('Primary');

		$date =  new DateTime(date('Y') - ((int) str_replace('+', '', $driverAge)) . '-' . date('m-d'));
		$result = $date->format('Y-m-d');

        $primaryNode->addAttribute('BirthDate', $result);
        $primaryNode->addChild('Email', 'saford@hertz');

        $addressNode = $primaryNode->addChild('Address');
        $addressNode->addChild('AddressLine', '5601 NW 20th');
        $addressNode->addChild('AddressLine', 'Apt 207');
        $addressNode->addChild('CityName', 'OKLAHOMA CITY');
        $addressNode->addChild('PostalCode', '73112');
        $stateProveNode     = $addressNode->addChild('StateProv');
        $stateProveNode->addAttribute('StateCode', 'OK');
        $countryNode = $addressNode->addChild('CountryName');
        $countryNode->addAttribute('Code', 'AU');  

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
	public function getXMLCredentialNode($xmlAction, $countryCode = 'AU')
	{
		$xml = new SimpleXMLElement('<' . $xmlAction . '></' . $xmlAction . '>');
		$xml->addAttribute('xmlns', self::DEFAULT_XMLNS);
		$xml->addAttribute('xmlns:xsi', self::DEFAULT_XMLNS_XSI);
		$xml->addAttribute('xsi:schemaLocation', self::DEFAULT_XMLNS. ' ' . $xmlAction . '.xsd');
		$xml->addAttribute('Version', self::DEFAULT_VERSION);
		$xml->addAttribute('SequenceNmbr', date_format(date_create(), 'U'));
		$xml->addAttribute('MaxResponses', self::DEFAULT_MAXRESPONSE);		

		$posNode = $xml->addChild('POS');
		$sourceNode = $posNode->addChild('Source');
		$sourceNode->addAttribute('ISOCountry', $countryCode);
		$sourceNode->addAttribute('AgentDutyCode', $this->apiValidationCode);

		$requestNode = $sourceNode->addChild('RequestorID');
		$requestNode->addAttribute('Type', self::DEFAULT_REQUEST_TYPE);
		$requestNode->addAttribute('ID', $this->apiValidationNumber);

		$companyNameNode = $requestNode->addChild('CompanyName');
		$companyNameNode->addAttribute('Code', self::DEFAULT_CONSUMER_PRODUCT);
		$companyNameNode->addAttribute('CodeContext', $this->apiConsumerProductCode);

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
		$date =  new \DateTime($date.' '.$time);
		$result = $date->format('Y-m-d H:i:s');

		return str_replace(' ', 'T', $result);
	}

	/**
	 * Functions that validates time and date
	 * 
	 * @param  date $date
	 * @param  time $time
	 * 
	 * @return bool
	 */
	private function validateDate($date, $time)
	{
		$dateTime = $date. ' ' . $time . ':00';
	    $d = DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);

	    return $d && $d->format('Y-m-d H:i:s') == $dateTime;
	}

	/**
	 * Updates Depot table for Hertz
	 * 
	 * @param  date $date
	 * @param  time $time
	 * 
	 * @return bool
	 */
	public function updateDepots()
	{
		$file = public_path() . '/misc/GDEX1ADC.txt';

		if (file_exists($file)) {
			ini_set('max_execution_time', 0);
			ini_set('memory_limit', '10000M');

			$file_content  = file_get_contents($file);
			$explodedArray = explode('|', $file_content);
			$chunkedArray  = array_chunk($explodedArray, 110, false);
			$supplierObject = Supplier::getSupplierIDByCode($this->supplierCode);
			$countryArray = [];
            foreach ($chunkedArray as $key => $value) {
                if (count($value) > 1) {
					$country   = strlen($value[5])  < 1 ? '' : trim($value[5]);
					$zipCode   = strlen($value[7])  < 1 ? '' : trim($value[7]);
					$city      = strlen($value[8])  < 1 ? '' : trim($value[8]);
					$state     = strlen($value[6])  < 1 ? '' : trim($value[6]);
					$address1  = strlen($value[9])  < 1 ? '' : trim($value[9]);
					$address2  = strlen($value[10]) < 1 ? '' : trim($value[6]);
					$address3  = strlen($value[10]) < 1 ? '' : trim($value[10]);
					$phone     = strlen($value[12]) < 1 ? '' : trim($value[12]);
					$locDesc   = strlen($value[62]) < 1 ? '' : trim($value[62]);
					$latitude  = strlen($value[60]) < 1 ? '' : trim($value[60]);
					$longitude = strlen($value[61]) < 1 ? '' : trim($value[61]);
					$oagCode   = strlen($value[4])  < 1 ? '' : trim($value[4]);

					$countryObj = Country::where('countryCode', $country)->first();

					$data = array(
						'supplierID'   => is_null($supplierObject) ? '0' : $supplierObject->getSupplierID(),
						'locationCode' => $oagCode,
						'countryCode'  => is_null($countryObj) ? '0' : $countryObj->getId(),
						'postCode'     => $zipCode,
						'city'         => $city,
						'address'      => trim($address1 . ' ' . $address2 . ' '. $address3),
						'operationSchedule' => array(
							'monday'    => htmlentities($value[18]) . '-' . htmlentities($value[19]),
							'tuesday'   => htmlentities($value[24]) . '-' . htmlentities($value[25]),
							'wednesday' => htmlentities($value[30]) . '-' . htmlentities($value[31]),
							'thursday'  => htmlentities($value[36]) . '-' . htmlentities($value[37]),
							'friday'    => htmlentities($value[42]) . '-' . htmlentities($value[43]),
							'saturday'  => htmlentities($value[48]) . '-' . htmlentities($value[49]),
							'sunday'    => htmlentities($value[54]) . '-' . htmlentities($value[55])
						),
						'phoneNumber'  => $phone,
						'latitude'     => $longitude,
						'longitude'    => $longitude,
						'locationName' => htmlentities($locDesc)
					);
					$response = Depot::updateDepotRecord($data);
            	} else {
            		break;
            	}
            }
        } else {
        	 echo 'File does not exist';
        }	
	}

	/**
	 * Returns the Hertz's depots per location
	 * 
	 * @return XML
	 */
	public function getDepots()
	{
		$file = public_path() . '/misc/GDEX1ADC.txt';

		if (file_exists($file)) {
			ini_set('max_execution_time', 0);
			ini_set('memory_limit', '10000M');

			$file_content  = file_get_contents($file);
			$explodedArray = explode('|', $file_content);
			$chunkedArray  = array_chunk($explodedArray, 110, false);

            foreach ($chunkedArray as $key => $value) {
                if (count($value) > 1) {
					$country   = strlen($value[5])  < 1 ? 'N/A' : $value[5];
					$zipCode   = strlen($value[7])  < 1 ? 'N/A' : $value[7];
					$city      = strlen($value[8])  < 1 ? 'N/A' : $value[8];
					$state     = strlen($value[6])  < 1 ? 'N/A' : $value[6];
					$address1  = strlen($value[9])  < 1 ? 'N/A' : $value[9];
					$address2  = strlen($value[10]) < 1 ? '' : $value[6];
					$address3  = strlen($value[10]) < 1 ? '' : $value[10];
					$phone     = strlen($value[12]) < 1 ? 'N/A' : $value[12];
					$fax       = strlen($value[14]) < 1 ? 'N/A' : $value[14];
					$email     = strlen($value[17]) < 1 ? 'N/A' : $value[17];
					$locDesc   = strlen($value[62]) < 1 ? 'N/A' : $value[62];
					$latitude  = strlen($value[60]) < 1 ? 'N/A' : $value[60];
					$longitude = strlen($value[61]) < 1 ? 'N/A' : $value[61];
					$city      = strlen($value[8])  < 1 ? 'N/A' : $value[8];
					$oagCode   = strlen($value[4])  < 1 ? 'N/A' : $value[4];

					$data['result'][] = [
						'key' => $key,
						'locationCode' => $oagCode,
						'countryCode' => $country,
						'stateCode' => $state,
						'zipCode' => $zipCode,
						'city' => $city,
						'address' => trim($address1 . ' ' . $address2 . ' '. $address3),
						'operationSchedule' => [
							'monday'    => htmlentities($value[18]) .'-' . htmlentities($value[19]),
							'tuesday'   => htmlentities($value[24]) .'-' . htmlentities($value[25]),
							'wednesday' => htmlentities($value[30]) .'-' . htmlentities($value[31]),
							'thursday'  => htmlentities($value[36]) .'-' . htmlentities($value[37]),
							'friday'    => htmlentities($value[42]) .'-' . htmlentities($value[43]),
							'saturday'  => htmlentities($value[48]) .'-' . htmlentities($value[49]),
							'sunday'    => htmlentities($value[54]) .'-' . htmlentities($value[55])
						],
						'fax' => $fax,
						'phone' => $phone,
						'email' => $email,
						'latitude' => $longitude,
						'longitude' => $longitude,
						'locationName' => htmlentities($locDesc)
					];

            	} else {
            		break;
            	}
            }

            return $data;

        } else {
        	 echo 'File does not exist';
        }
	}

}


	