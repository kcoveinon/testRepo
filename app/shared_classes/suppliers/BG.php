<?php

class BG extends SupplierApi
{
	private $userID;
	private $requestorID;
	private $password;
	private $target;
	private $primaryLang;
	private $version;
	private $authUrl;

	const VENDOR_CODE                = 'BG';
	const VENDOR_NAME                = 'Budget';
	const VENDOR_CLASS_CODE          = 'BG';
	const VERSION                    = '1.0';

	const SEARCH_VEHICLE_ACTION      = 'OTA_VehAvailRateRQ';
	const BOOK_VEHICLE_ACTION        = 'OTA_VehResRQ';
	const GET_BOOKING_INFO_ACTION    = 'OTA_VehRetResRQ';
	const CANCEL_BOOKING_ACTION      = 'OTA_VehCancelRQ';
	const MODIFY_BOOKING_ACTION      = 'OTA_VehModifyRQ';
	const GET_LOCATION_SEARCH_ACTION = 'OTA_VehLocSearchRQ';
	const GET_RATE_RULE_ACTION       = 'OTA_VehRateRuleRQ';

	const DEFAULT_XMLNS              = 'http://www.opentravel.org/OTA/2003/05';
	const DEFAULT_XMLNS_XSI          = 'http://www.w3.org/2008/XMLSchema-instance';
	const DEFAULT_SEQUENCE_NUMBER    = '1';
	const DEFAULT_REQUEST_TYPE       = '1';
	const DEFAULT_CODE_CONTEXT       = 'IATA';
	const DEFAULT_REQUEST_STATUS     = 'Available';
	const DEFAULT_MAX_RESPONSE       = '1000';

	public function __construct ()
	{
		$this->target          = Config::get(self::VENDOR_CODE . '.api.target');
		$this->apiUri          = Config::get(self::VENDOR_CODE . '.api.uri');
		$this->apiLocation     = Config::get(self::VENDOR_CODE . '.api.location');
		$this->requestorID     = Config::get(self::VENDOR_CODE . '.api.requestorID');
		$this->userID          = Config::get(self::VENDOR_CODE . '.api.userID');
		$this->password        = Config::get(self::VENDOR_CODE . '.api.password');
		$this->primaryLang     = Config::get(self::VENDOR_CODE . '.api.primaryLang');
		$this->version         = Config::get(self::VENDOR_CODE . '.api.version');
		$this->authUrl         = Config::get(self::VENDOR_CODE . '.api.authUrl');

		$this->soapClient = new SoapClient(null, array(
			'location' => $this->apiLocation,
			'uri'      => $this->apiUri,
			'trace'    => 1
		));
	}

	public function ping()
	{
		/*$params = new stdClass();
		$params->OTA_PingRQ = new stdClass();
		$params->OTA_PingRQ->Version = '1.0';
		$params->EchoData = new stdClass();
		$params->EchoData = 'Hello World';
		$params->OTA_PingRQ->EchoData = $params->EchoData;*/

		$this->setSoapHeader();
		//header("Content-Type:text/xml");
		$echoData = "Hi, Hello World - Budget";
		try {
			return $this->soapClient->__soapCall('Request',
				array(
					new SoapVar('<OTA_PingRQ Version="1.0">', XSD_ANYXML),
					new SoapParam($echoData, 'EchoData'),
					new SoapVar('</OTA_PingRQ>', XSD_ANYXML)
				));
		} catch (SoapFault $e) {
			echo $this->soapClient->__getLastRequest();
		}
	}

	/**
	 * @param $locationCode
	 *
	 * @return array
	 */
	public function getDepots($locationCode)
	{
		$timeStart = time();

		if (!empty($locationCode)) {
			$this->setSoapHeader();
			$this->soapClient->__soapCall('Request',
				array(
					new SoapVar($this->otaRQNode(self::GET_LOCATION_SEARCH_ACTION, true), XSD_ANYXML),
					new SoapVar("<VehLocSearchCriterion>", XSD_ANYXML),
					new SoapVar("<CodeRef CodeContext='apo' LocationCode='{$locationCode}'/>", XSD_ANYXML),
					new SoapVar('</VehLocSearchCriterion>', XSD_ANYXML),
					new SoapVar("<Vendor Code='".self::VENDOR_NAME."'/>", XSD_ANYXML),
					new SoapVar($this->otaRQNode(self::GET_LOCATION_SEARCH_ACTION, false), XSD_ANYXML)
				)
			);

			//echo htmlentities($this->soapClient->__getLastRequest());die;
			$parseResponse = $this->parseResponse($this->soapClient->__getLastResponse());

			$response = [];
			if (isset($parseResponse->OTA_VehLocSearchRS->Errors)) {
				$response['status'] = "Failed";
				$response['data']   = (string) $parseResponse->OTA_VehLocSearchRS->Errors->Error;
			} else {
				$response = $parseResponse->OTA_VehLocSearchRS; // TODO need to send only required information
			}
		} else {
			$response =  ["result" => "Invalid Parameters"];
		}

		$response['executionTime'] = time() - $timeStart;
		$response['supplierCode']  = self::VENDOR_CODE;

		return $response;
	}

	/**
	 * @param        $pickUpDate
	 * @param        $pickUpTime
	 * @param        $returnDate
	 * @param        $returnTime
	 * @param        $pickUpLocationCode
	 * @param        $returnLocationCode
	 * @param string $countryCode
	 * @param int    $vehicleCategory
	 * @param int    $vehicleClass
	 *
	 * @return response
	 */
	public function searchVehicles(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpLocationCode,
		$returnLocationCode,
		$countryCode = 'AU',
		$vehicleCategory = 1,
		$vehicleClass = 3
	) {
		$timeStart = time();

		$pickUpDateTime = $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime);
		$returnDateTime = $this->convertToDateTimeDefaultFormat($returnDate, $returnTime);

		if ($this->validateDate($pickUpDate, $pickUpTime)
		    && $this->validateDate($returnDate, $returnTime)
		) {
			$this->setSoapHeader();
			$this->soapClient->__soapCall('Request',
				array(
					new SoapVar($this->otaRQNode(self::SEARCH_VEHICLE_ACTION, true), XSD_ANYXML),
					new SoapVar("<VehAvailRQCore Status='Available'>", XSD_ANYXML),
					new SoapVar("<VehRentalCore PickUpDateTime='{$pickUpDateTime}' ReturnDateTime='{$returnDateTime}'>", XSD_ANYXML),
					new SoapVar("<PickUpLocation LocationCode='{$pickUpLocationCode}'/>", XSD_ANYXML),
					new SoapVar("<ReturnLocation LocationCode='{$returnLocationCode}'/>", XSD_ANYXML),
					new SoapVar("</VehRentalCore>", XSD_ANYXML),
					new SoapVar('<VendorPrefs>', XSD_ANYXML),
					new SoapVar("<VendorPref CompanyShortName='".self::VENDOR_NAME."'/>", XSD_ANYXML),
					new SoapVar('</VendorPrefs>', XSD_ANYXML),
					new SoapVar('<VehPrefs>', XSD_ANYXML),
					new SoapVar("<VehPref ClassPref='Preferred' TransmissionPref='Preferred' TransmissionType='Automatic' TypePref='Preferred'>", XSD_ANYXML),
					new SoapVar("<VehType VehicleCategory='{$vehicleCategory}'/>", XSD_ANYXML),
					new SoapVar("<VehClass Size='{$vehicleClass}'/>", XSD_ANYXML),
					new SoapVar('</VehPref>', XSD_ANYXML),
					new SoapVar('</VehPrefs>', XSD_ANYXML),
					new SoapVar("</VehAvailRQCore>", XSD_ANYXML),
					new SoapVar('<VehAvailRQInfo>', XSD_ANYXML),
					new SoapVar('<Customer>', XSD_ANYXML),
					new SoapVar('<Primary>', XSD_ANYXML),
					new SoapVar("<CitizenCountryName Code='{$countryCode}'/>", XSD_ANYXML),
					new SoapVar('</Primary>', XSD_ANYXML),
					new SoapVar('</Customer>', XSD_ANYXML),
					new SoapVar('</VehAvailRQInfo>', XSD_ANYXML),
					new SoapVar($this->otaRQNode(self::SEARCH_VEHICLE_ACTION, false), XSD_ANYXML)
				)
			);
			$parseResponse = $this->parseResponse($this->soapClient->__getLastResponse());

			$response = [];
			if (isset($parseResponse->OTA_VehAvailRateRS->Errors)) {
				$response['status'] =  "Failed";
				$response['data']   = (string) $parseResponse->OTA_VehAvailRateRS->Errors->Error;
			} else {
				$response = $parseResponse->OTA_VehAvailRateRS; // TODO need to send only required information
			}
		} else {
			$response =  ["result" => "Invalid Parameters"];
		}

		$response['executionTime'] = time() - $timeStart;
		$response['supplierCode']  = self::VENDOR_CODE;

		return $response;
	}

	/**
	 * @param        $pickUpDate
	 * @param        $pickUpTime
	 * @param        $returnDate
	 * @param        $returnTime
	 * @param        $pickUpLocationCode
	 * @param        $returnLocationCode
	 * @param        $firstName
	 * @param        $lastName
	 * @param string $countryCode
	 * @param int    $vehicleCategory
	 * @param int    $vehicleClass
	 *
	 * @return array
	 */
	public function doBooking(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpLocationCode,
		$returnLocationCode,
		$firstName,
		$lastName,
		$countryCode = 'AU',
		$vehicleCategory = 1,
		$vehicleClass = 4
	) {
		$timeStart = time();

		$pickUpDateTime = $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime);
		$returnDateTime = $this->convertToDateTimeDefaultFormat($returnDate, $returnTime);

		if ($this->validateDate($pickUpDate, $pickUpTime)
		    && $this->validateDate($returnDate, $returnTime)
		) {
			$this->setSoapHeader();
			$this->soapClient->__soapCall('Request',
				array(
					new SoapVar($this->otaRQNode(self::BOOK_VEHICLE_ACTION, true), XSD_ANYXML),
					new SoapVar("<VehResRQCore Status='Available'>", XSD_ANYXML),
					new SoapVar("<VehRentalCore PickUpDateTime='{$pickUpDateTime}' ReturnDateTime='{$returnDateTime}'>", XSD_ANYXML),
					new SoapVar("<PickUpLocation LocationCode='{$pickUpLocationCode}'/>", XSD_ANYXML),
					new SoapVar("<ReturnLocation LocationCode='{$returnLocationCode}'/>", XSD_ANYXML),
					new SoapVar("</VehRentalCore>", XSD_ANYXML),
					new SoapVar('<Customer>', XSD_ANYXML),
					new SoapVar('<Primary>', XSD_ANYXML),
					new SoapVar('<PersonName>', XSD_ANYXML),
					new SoapParam($firstName, 'GivenName'),
					new SoapParam($lastName, 'Surname'),
					new SoapVar('</PersonName>', XSD_ANYXML),
					new SoapVar("<CitizenCountryName Code='{$countryCode}'/>", XSD_ANYXML),
					new SoapVar('</Primary>', XSD_ANYXML),
					new SoapVar('</Customer>', XSD_ANYXML),
					new SoapVar("<VendorPref CompanyShortName='".self::VENDOR_NAME."'/>", XSD_ANYXML),
					new SoapVar("<VehPref TypePref='Only' ClassPref='Only' TransmissionType='Automatic' TransmissionPref='Only' AirConditionPref='Only'>", XSD_ANYXML),
					new SoapVar("<VehType VehicleCategory='{$vehicleCategory}'/>", XSD_ANYXML),
					new SoapVar("<VehClass Size='{$vehicleClass}'/>", XSD_ANYXML),
					new SoapVar('</VehPref>', XSD_ANYXML),
					new SoapVar("<RateQualifier RateQualifier='2A'/>", XSD_ANYXML),
					new SoapVar('</VehResRQCore>', XSD_ANYXML),
					new SoapVar($this->otaRQNode(self::BOOK_VEHICLE_ACTION, false), XSD_ANYXML)
				)
			);
			$parseResponse = $this->parseResponse($this->soapClient->__getLastResponse());

			$response = [];
			if (isset($parseResponse->OTA_VehResRS->Errors)) {
				$response['status'] =  "Failed";
				$response['data']   = (string) $parseResponse->OTA_VehResRS->Errors->Error;
			} else {
				$response = $parseResponse->OTA_VehResRS; // TODO need to send only required information
			}
		} else {
			$response =  ["result" => "Invalid Parameters"];
		}

		$response['executionTime'] = time() - $timeStart;
		$response['supplierCode']  = self::VENDOR_CODE;

		return $response;
	}

	/**
	 * @param $bookingId
	 * @param $surname
	 *
	 * @return array
	 */
	public function getBookingDetails($bookingId, $surname)
	{
		$timeStart = time();

		if (!empty($bookingId)) {
			$this->setSoapHeader();
			$this->soapClient->__soapCall('Request',
				array(
					new SoapVar($this->otaRQNode(self::GET_BOOKING_INFO_ACTION, true), XSD_ANYXML),
					new SoapVar("<VehRetResRQCore>", XSD_ANYXML),
					new SoapVar("<UniqueID Type='14' ID='{$bookingId}'/>", XSD_ANYXML),
					new SoapVar('<PersonName>', XSD_ANYXML),
					new SoapParam($surname, 'Surname'),
					new SoapVar('</PersonName>', XSD_ANYXML),
					new SoapVar('</VehRetResRQCore>', XSD_ANYXML),
					new SoapVar('<VehRetResRQInfo>', XSD_ANYXML),
					new SoapVar("<Vendor CompanyShortName='".self::VENDOR_NAME."'/>", XSD_ANYXML),
					new SoapVar('</VehRetResRQInfo>', XSD_ANYXML),
					new SoapVar($this->otaRQNode(self::GET_BOOKING_INFO_ACTION, false), XSD_ANYXML)
				)
			);
			$parseResponse = $this->parseResponse($this->soapClient->__getLastResponse());

			$response = [];
			if (isset($parseResponse->OTA_VehRetResRS->Errors)) {
				$response['status'] = "Failed";
				$response['data']   = (string) $parseResponse->OTA_VehRetResRS->Errors->Error;
			} else {
				$response = $parseResponse->OTA_VehRetResRS; // TODO need to send only required information
			}
		} else {
			$response = ["result" => "Invalid Parameters"];
		}

		$response['executionTime'] = time() - $timeStart;
		$response['supplierCode']  = self::VENDOR_CODE;

		return $response;
	}

	/**
	 * @param        $bookingId
	 * @param        $pickUpDate
	 * @param        $pickUpTime
	 * @param        $returnDate
	 * @param        $returnTime
	 * @param        $pickUpLocationCode
	 * @param        $returnLocationCode
	 * @param        $firstName
	 * @param        $lastName
	 * @param string $countryCode
	 * @param int    $vehicleCategory
	 * @param int    $vehicleClass
	 *
	 * @return array
	 */
	public function modifyBooking(
		$bookingId,
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpLocationCode,
		$returnLocationCode,
		$firstName,
		$lastName,
		$countryCode = 'AU',
		$vehicleCategory = 1,
		$vehicleClass = 4
	) {
		$timeStart = time();

		$pickUpDateTime = $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime);
		$returnDateTime = $this->convertToDateTimeDefaultFormat($returnDate, $returnTime);

		if (!empty($bookingId)) {
			$this->setSoapHeader();
			$this->soapClient->__soapCall('Request',
				array(
					new SoapVar($this->otaRQNode(self::MODIFY_BOOKING_ACTION, true), XSD_ANYXML),
					new SoapVar("<VehModifyRQCore ModifyType='Modify'>", XSD_ANYXML),
					new SoapVar("<UniqueID Type='14' ID='{$bookingId}'/>", XSD_ANYXML),
					new SoapVar("<VehRentalCore PickUpDateTime='{$pickUpDateTime}' ReturnDateTime='{$returnDateTime}'>", XSD_ANYXML),
					new SoapVar("<PickUpLocation LocationCode='{$pickUpLocationCode}'/>", XSD_ANYXML),
					new SoapVar("<ReturnLocation LocationCode='{$returnLocationCode}'/>", XSD_ANYXML),
					new SoapVar("</VehRentalCore>", XSD_ANYXML),
					new SoapVar('<Customer>', XSD_ANYXML),
					new SoapVar('<Primary>', XSD_ANYXML),
					new SoapVar('<PersonName>', XSD_ANYXML),
					new SoapParam($firstName, 'GivenName'),
					new SoapParam($lastName, 'Surname'),
					new SoapVar('</PersonName>', XSD_ANYXML),
					new SoapVar("<CitizenCountryName Code='{$countryCode}'/>", XSD_ANYXML),
					new SoapVar('</Primary>', XSD_ANYXML),
					new SoapVar('</Customer>', XSD_ANYXML),
					new SoapVar("<VendorPref CompanyShortName='".self::VENDOR_NAME."'/>", XSD_ANYXML),
					new SoapVar("<VehPref TypePref='Only' ClassPref='Only' TransmissionType='Automatic' TransmissionPref='Only' AirConditionPref='Only'>", XSD_ANYXML),
					new SoapVar("<VehType VehicleCategory='{$vehicleCategory}'/>", XSD_ANYXML),
					new SoapVar("<VehClass Size='{$vehicleClass}'/>", XSD_ANYXML),
					new SoapVar('</VehPref>', XSD_ANYXML),
					new SoapVar("<RateQualifier RateQualifier='2A'/>", XSD_ANYXML),
					new SoapVar('</VehModifyRQCore>', XSD_ANYXML),
					new SoapVar($this->otaRQNode(self::MODIFY_BOOKING_ACTION, false), XSD_ANYXML)
				)
			);
			$parseResponse = $this->parseResponse($this->soapClient->__getLastResponse());

			$response = [];
			if (isset($parseResponse->OTA_VehModifyRS->Errors)) {
				$response['status'] =  "Failed";
				$response['data']   = (string) $parseResponse->OTA_VehModifyRS->Errors->Error;
			} else {
				$response = $parseResponse->OTA_VehModifyRS; // TODO need to send only required information
			}
		} else {
			$response =  ["result" => "Invalid Parameters"];
		}

		$response['executionTime'] = time() - $timeStart;
		$response['supplierCode']  = self::VENDOR_CODE;

		return $response;
	}

	/**
	 * @param $bookingId
	 * @param $surname
	 *
	 * @return array
	 */
	public function cancelBooking($bookingId, $surname)
	{
		$timeStart = time();

		if (!empty($bookingId)) {
			$this->setSoapHeader();
			$this->soapClient->__soapCall('Request',
				array(
					new SoapVar($this->otaRQNode(self::CANCEL_BOOKING_ACTION, true), XSD_ANYXML),
					new SoapVar("<VehCancelRQCore CancelType='Commit'>", XSD_ANYXML),
					new SoapVar("<UniqueID Type='15' ID='{$bookingId}'/>", XSD_ANYXML),
					new SoapVar('<PersonName>', XSD_ANYXML),
					new SoapParam($surname, 'Surname'),
					new SoapVar('</PersonName>', XSD_ANYXML),
					new SoapVar('</VehCancelRQCore>', XSD_ANYXML),
					new SoapVar('<VehCancelRQInfo>', XSD_ANYXML),
					new SoapVar("<Vendor CompanyShortName='".self::VENDOR_NAME."'/>", XSD_ANYXML),
					new SoapVar('</VehCancelRQInfo>', XSD_ANYXML),
					new SoapVar($this->otaRQNode(self::CANCEL_BOOKING_ACTION, false), XSD_ANYXML)
				)
			);
			$parseResponse = $this->parseResponse($this->soapClient->__getLastResponse());

			$response = [];
			if (isset($parseResponse->OTA_VehCancelRS->Errors)) {
				$response['status'] =  "Failed";
				$response['data']   = (string) $parseResponse->OTA_VehCancelRS->Errors->Error;
			} else {
				$response = $parseResponse->OTA_VehCancelRS; // TODO need to send only required information
			}
		} else {
			$response =  ["result" => "Invalid Parameters"];
		}

		$response['executionTime'] = time() - $timeStart;
		$response['supplierCode']  = self::VENDOR_CODE;

		return $response;
	}

	/**
	 * @param        $pickUpDate
	 * @param        $pickUpTime
	 * @param        $returnDate
	 * @param        $returnTime
	 * @param        $pickUpLocationCode
	 * @param        $returnLocationCode
	 * @param string $countryCode
	 * @param int    $vehicleCategory
	 * @param int    $vehicleClass
	 *
	 * @return array
	 */
	public function getRates(
		$pickUpDate,
		$pickUpTime,
		$returnDate,
		$returnTime,
		$pickUpLocationCode,
		$returnLocationCode,
		$countryCode = 'AU',
		$vehicleCategory = 1,
		$vehicleClass = 4
	) {
		$timeStart = time();

		$pickUpDateTime = $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime);
		$returnDateTime = $this->convertToDateTimeDefaultFormat($returnDate, $returnTime);

		if ($this->validateDate($pickUpDate, $pickUpTime)
		    && $this->validateDate($returnDate, $returnTime)
		) {
			$this->setSoapHeader();
			$this->soapClient->__soapCall('Request',
				array(
					new SoapVar($this->otaRQNode(self::GET_RATE_RULE_ACTION, true), XSD_ANYXML),
					new SoapVar("<RentalInfo>", XSD_ANYXML),
					new SoapVar("<VehRentalCore PickUpDateTime='{$pickUpDateTime}' ReturnDateTime='{$returnDateTime}'>", XSD_ANYXML),
					new SoapVar("<PickUpLocation LocationCode='{$pickUpLocationCode}'/>", XSD_ANYXML),
					new SoapVar("<ReturnLocation LocationCode='{$returnLocationCode}'/>", XSD_ANYXML),
					new SoapVar("</VehRentalCore>", XSD_ANYXML),
					new SoapVar("<VehicleInfo TypePref='Only' TransmissionPref='Only' TransmissionType='Automatic' AirConditionPref='Only' ClassPref='Only'>", XSD_ANYXML),
					new SoapVar("<VehType VehicleCategory='{$vehicleCategory}'/>", XSD_ANYXML),
					new SoapVar("<VehClass Size='{$vehicleClass}'/>", XSD_ANYXML),
					new SoapVar('</VehicleInfo>', XSD_ANYXML),
					new SoapVar("<RateQualifier RateQualifier='2A'/>", XSD_ANYXML),
					new SoapVar("<CustomerID Type='1' ID='{$countryCode}'/>", XSD_ANYXML),
					new SoapVar('</RentalInfo>', XSD_ANYXML),
					new SoapVar($this->otaRQNode(self::GET_RATE_RULE_ACTION, false), XSD_ANYXML)
				)
			);
			$parseResponse = $this->parseResponse($this->soapClient->__getLastResponse());

			$response = [];
			if (isset($parseResponse->OTA_VehRateRuleRS->Errors)) {
				$response['status'] =  "Failed";
				$response['data']   = (string) $parseResponse->OTA_VehRateRuleRS->Errors->Error;
			} else {
				$response = $parseResponse->OTA_VehRateRuleRS; // TODO need to send only required information
			}
		} else {
			$response =  ["result" => "Invalid Parameters"];
		}

		$response['executionTime'] = time() - $timeStart;
		$response['supplierCode']  = self::VENDOR_CODE;

		return $response;
	}

	/**
	 * @param      $action
	 * @param bool $openNode
	 *
	 * @return string
	 */
	private function otaRQNode($action, $openNode = true)
	{
		if($openNode) {
			$requestNode = array(
				new SoapVar("<$action xmlns:xsi='".self::DEFAULT_XMLNS_XSI."' Version='".self::VERSION."' MaxResponses='".self::DEFAULT_MAX_RESPONSE."' SequenceNmbr='".self::DEFAULT_SEQUENCE_NUMBER."'>", XSD_ANYXML),
				new SoapVar('<POS>', XSD_ANYXML),
				new SoapVar('<Source>', XSD_ANYXML),
				new SoapVar("<RequestorID Type='".self::DEFAULT_REQUEST_TYPE."' ID='{$this->requestorID}'/>", XSD_ANYXML),
				new SoapVar('</Source>', XSD_ANYXML),
				new SoapVar('</POS>', XSD_ANYXML)
			);
		} else {
			$requestNode = array(
				new SoapVar("</$action>", XSD_ANYXML)
			);
		}

		return $requestNode;
	}

	/**
	 * Set Soap Header
	 */
	private function setSoapHeader()
	{
		$auth           = new stdClass();
		$auth->userID   = $this->userID;
		$auth->password = new stdClass();
		$auth->password = $this->password;

		$header = new SoapHeader( $this->authUrl, 'credentials', $auth, false );
		$this->soapClient->__setSoapHeaders( $header );
	}

	/**
	 * @param $response
	 *
	 * @return parsedResponse
	 */
	private function parseResponse($response)
	{
		$response = preg_replace("/<\\/?SOAP-ENV:Envelope(\\s+.*?>|>)/", "", $response);
		$response = preg_replace("/<\\/?SOAP-ENV:Body(\\s+.*?>|>)/", "", $response);

		$response = preg_replace("/<\\/?env:Fault(\\s+.*?>|>)/", "", $response);
		$response = preg_replace("/<\\/?env:Server(\\s+.*?>|>)/", "", $response);

		$response = preg_replace("/<\\/?env:Envelope(\\s+.*?>|>)/", "", $response);
		$response = preg_replace("/<\\/?env:Body(\\s+.*?>|>)/", "", $response);

		$response = str_replace(array("\n", "\r", "\t"), '', $response);

		$parsedResponse = simplexml_load_string($response);

		return $parsedResponse;
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
	 * Functions that validates time and date
	 * @param  date $date
	 * @param  time $time
	 * @return bool
	 */
	private function validateDate($date, $time)
	{
		$dateTime = $date. " " . $time . ":00";
		$d = DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
		return $d && $d->format('Y-m-d H:i:s') == $dateTime;
	}
}