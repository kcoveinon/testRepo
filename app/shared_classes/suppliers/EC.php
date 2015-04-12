<?php

/*namespace Supplier;*/

class EC extends SupplierApi
{
	private $supplierCode;
	private $apiUsernameVariable;
	private $apiPasswordVariable;
	private $days;
	private $scheduleTypes;
	private $deliveryDescriptions;
	private $equipmentStatus;
	private $openHoursTypes;
	private $stationTypes;
	private $afterHours;
	private $hasAirCondition;
	private $carCategoryTransmissions;
	private $carCategoryStatus;
	private $carCategoryCarTypes;
	private $defaultStationStatus;
	private $defaultCurlOptions;

	public function __construct()
	{
		$this->supplierCode        = get_class();
		$this->apiUrl              = Config::get($this->supplierCode . '.api.url');
		$this->apiUsernameVariable = Config::get($this->supplierCode . '.api.usernameVariable');
		$this->apiPasswordVariable = Config::get($this->supplierCode . '.api.passwordVariable');
		$this->apiUsername         = Config::get($this->supplierCode . '.api.username');
		$this->apiPassword         = Config::get($this->supplierCode . '.api.password');

		$this->defaultCurlOptions = array(
			CURLOPT_URL            => $this->apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_POST           => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POSTFIELDS     => array(
				'XML-Request' => '',
				$this->apiUsernameVariable => $this->apiUsername,
				$this->apiPasswordVariable => $this->apiPassword,
			),
		);

		$this->days = array(
			'Sunday',
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday'
		);

		$this->scheduleTypes = array(
			'G' => 'General',
			'E' => 'Exceptional',
		);

		$this->openHoursTypes = array(
			'N' => 'Normal Hours',
			'A' => 'After Hours',
		);

		$this->stationTypes = array(
			'C' => 'City',
			'D' => 'Chauffeur drive station',
			'E' => 'East suburb',
			'L' => 'Railway station',
			'X' => 'Railway station',
			'N' => 'North suburb',
			'O' => 'Off terminal',
			'P' => 'Ferry station',
			'R' => 'Ressort',
			'S' => 'South suburb',
			'T' => 'Airport terminal',
			'W' => 'West suburb',
		);

		$this->afterHours = array(
			'Y' => true,
			'N' => false,
		);
		
		$this->hasAirCondition = array(
			'Y' => true,
			'N' => false,
		);

		$this->carCategoryTransmissions = array(
			'Y' => true,
			'N' => false,
		);

		$this->carCategoryStatus = array(
			'F' => 'Free Sell',
			'R' => 'On Request'
		);

		$this->carCategoryCarTypes = array(
			'CR' => 'Cars Only',
			'TR' => 'Trucks Only',
			'CM' => 'Campers and motorcycles'
		);

		$this->deliveryDescriptions = array(
			'F' => 'Free Sell',
			'N' => 'No Sell',
			'R' => 'On Request'
		);

		$this->equipmentStatus = array(
			'F' => 'Free Sell',
			'N' => 'No Sell',
			'R' => 'On Request'
		);

		$this->defaultStationStatus = 'OPEN';
	}

	/**
	 * returns an array containing transmission code and description base on $isAutomatic
	 * @param  boolean $isAutomatic "true" if automatic and "false" if manual
	 * @return array                the transmission code and description
	 */
	private function createTransmissionArray($isAutomatic)
	{
		if ($isAutomatic) {
			$transmission = array(
				'code'        => 'AT',
				'description' => 'Automatic Transmission',
			);
		} else {
			$transmission = array(
				'code'        => 'MT',
				'description' => 'Manual Transmission',
			);
		}

		return $transmission;
	}

	/**
	 * executes a curl request with the specified curl options.
	 * @param  array  $curlOptions the curl options.
	 * @return mixed               returns "true" on success or "false" on failure. however, if the 
	 *                             "CURLOPT_RETURNTRANSFER" option is set, it will return the 
	 *                             result on success, "false" on failure.
	 */
	private function executeCurl($curlOptions = array())
	{
		if (!empty($curlOptions[CURLOPT_POSTFIELDS])) {
			$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($curlOptions[CURLOPT_POSTFIELDS]);
		}

		$curlHandler = curl_init();

		curl_setopt_array($curlHandler, $curlOptions);
		$response = curl_exec($curlHandler);
		curl_close($curlHandler);

		return $response;
	}

	/**
	 * returns an instance of the SimpleXMLElement object with a "message" node.
	 * @return object a SimpleXMLElement object.
	 */
	private function createRequestXML($serviceRequest)
	{
		$xmlString = '<message><serviceRequest serviceCode="' . $serviceRequest . '"></serviceRequest></message>';

		return new SimpleXMLElement($xmlString);
	}

	/**
	 * returns the service request xml for the "getOpenHours" service request.
	 * @param  string $stationCode the station code.
	 * @param  string $date        the date you want to check
	 * @return object              a SimpleXMLElement object.
	 */
	private function createGetOpenHoursRequestXML($stationCode, $date)
	{
		$serviceRequestName = 'getOpenHours';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$reservationNode = $serviceParamsNode->addChild('reservation');

		$checkoutNode = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $stationCode);
		$checkoutNode->addAttribute('date', str_replace('-', '', $date));

		return $requestXML;
	}

	/**
	 * returns an array with data from the response XML of the "getOpenHours" service request.
	 * @param  string $xmlString the response XML from the "getOpenHours" service request.
	 * @return array             the result array containing the status and the data.
	 */
	private function createGetOpenHoursArrayFromXML($xmlString)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($xmlString);
		// /station/get-open-hours/BNEW02/2015-12-13
		// this station does not have any attributes
		$returnCode  = $responseXML->serviceResponse['returnCode'];
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		if (!isset($responseXML->serviceResponse->openHoursList)) {
			$result['data'] = array();

			return $result;
		}

		$openHours = $responseXML->serviceResponse->openHoursList->openHours;
		
		$tmpBeginTime = str_split((string) $openHours['beginTime'], 2);
		$beginTime    = $tmpBeginTime[0] . ':' . $tmpBeginTime[1];

		$tmpEndTime = str_split((string) $openHours['endTime'], 2);
		$endTime    = $tmpEndTime[0] . ':' . $tmpEndTime[1];

		$type     = (string) $openHours['type'];
		$typeName = $this->openHoursTypes[$type];

		$result['data'] = array(
			'time' => array(
				'begin' => $beginTime,
				'end'   => $endTime
			),
			'type' => array(
				'code' => $type,
				'name' =>  $typeName
			)
		);

		return $result;
	}

	/**
	 * returns the service request xml for the "getCarCategories" service request.
	 * @param  string $stationCode the station code.
	 * @param  string $date        the date you want to check
	 * @return object              a SimpleXMLElement object.
	 */
	private function createGetVehiclesRequestXML($stationCode, $date)
	{
		$serviceRequestName = 'getCarCategories';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$reservationNode = $serviceParamsNode->addChild('reservation');

		$checkoutNode = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $stationCode);
		$checkoutNode->addAttribute('date', str_replace('-', '', $date));

		return $requestXML;
	}

	/**
	 * returns an array with data from the response XML of the "getCarCategories" service request.
	 * @param  string $xmlString the response XML from the "getCarCategories" service request.
	 * @return array             the result array containing the status and the data.
	 */
	private function createGetVehiclesArrayFromXML($xmlString)
	{
		$result       = array();
		$responseXML  = new SimpleXMLElement($xmlString);
		$acrissHelper = new AcrissHelper();
		$returnCode   = $responseXML->serviceResponse['returnCode'];
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$carCategory = $responseXML->serviceResponse->carCategoryList->carCategory;

		foreach ($carCategory as $index => $category) {
			$carType         = (string) $category['carType'];
			$hasAirCondition = $this->hasAirCondition[(string) $category['carCategoryAirCond']];
			$isAutomatic     = $this->carCategoryTransmissions[(string) $category['carCategoryAutomatic']];
			$transmission    = $this->createTransmissionArray($isAutomatic);
			$carCategoryStatusCode = (string) $category['carCategoryStatusCode'];

			$result['data'][] = array(
				'supplierCode'    => $this->supplierCode,
				'hasAirCondition' => $hasAirCondition,
				'transmission'    => $transmission,
				'baggageQty'      => (string) $category['carCategoryBaggageQuantity'],
				'co2Qty'          => (string) $category['carCategoryCO2Quantity'],
				'categoryCode'    => (string) $category['carCategoryCode'],
				'expandedCode'    => $acrissHelper->expandCode((string) $category['carCategoryCode']),
				'doorCount'       => (string) $category['carCategoryDoors'],
				'name'            => (string) $category['carCategorySample'] . ' or similar',
				'seats'           => (string) $category['carCategorySeats'],
				'vehicleStatus'   => array(
					'code'        => $carCategoryStatusCode,
					'description' => $this->carCategoryStatus[$carCategoryStatusCode],
				),
				'vehicleType'     => array(
					'code'        => $carType,
					'description' => $this->carCategoryCarTypes[$carType],
				),
			);
		}

		return $result;
	}

	/**
	 * returns the service request xml for the "getEquipmentList" service request.
	 * @param  string $stationCode the station code.
	 * @param  string $date        the date you want to check
	 * @return object              a SimpleXMLElement object.
	 */
	private function createGetEquipmentListRequestXML($stationCode, $date)
	{
		$serviceRequestName = 'getEquipmentList';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$reservationNode = $serviceParamsNode->addChild('reservation');

		$checkoutNode = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $stationCode);
		$checkoutNode->addAttribute('date', str_replace('-', '', $date));

		return $requestXML;
	}

	/**
	 * returns an array with data from the response XML of the "getEquipmentList" service request.
	 * @param  string $xmlString the response XML from the "getEquipmentList" service request.
	 * @return array             the result array containing the status and the data.
	 */
	private function createGetEquipmentListArrayFromXML($xmlString)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($xmlString);
		$returnCode  = $responseXML->serviceResponse['returnCode'];
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$equipments = $responseXML->serviceResponse->equipmentList->equipment;

		foreach ($equipments as $equipment) {
			$equipmentStatusCode = (string) $equipment['statusCode'];

			$result['data'][] = array(
				'code'            => (string) $equipment['code'],
				'description'     => (string) $equipment['descr'],
				'equipmentStatus' => array(
					'code'        => $equipmentStatusCode,
					'description' => $this->equipmentStatus[$equipmentStatusCode]
				)
			);
		}

		return $result;
	}

	/**
	 * returns the service request xml for the "getStationSchedule" service request.
	 * @param  string $stationCode the station code.
	 * @return object              a SimpleXMLElement object.
	 */
	private function createGetStationScheduleRequestXML($stationCode)
	{
		$serviceRequestName = 'getStationSchedule';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$stationNode = $serviceParamsNode->addChild('station');
		$stationNode->addAttribute('stationCode', $stationCode);

		return $requestXML;
	}

	/**
	 * returns an array with data from the response XML of the "getStationSchedule" service request.
	 * @param  string $xmlString the response XML from the "getStationSchedule" service request.
	 * @return array             the result array containing the status and the data.
	 */
	private function createGetStationScheduleArrayFromXML($xmlString)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($xmlString);
		$returnCode  = $responseXML->serviceResponse['returnCode'];

		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		if (!isset($responseXML->serviceResponse->openHoursList)) {
			$result['data'] = array();

			return $result;
		}

		$stationDaySched = $responseXML->serviceResponse->openHoursList->stationDaySched;

		foreach ($stationDaySched as $sched) {
			$afterHours       = (string) $sched['afterHours'];
			$dayNumber        = (string) $sched['dayNumber'];
			$schedType        = (string) $sched['schedType'];
			$status           = $this->defaultStationStatus;
			$timeBegin        = '';
			$timeEnd          = '';
			$validPeriodBegin = '';
			$validPeriodEnd   = '';
			$schedTypeName    = $this->scheduleTypes[$schedType];

			if (isset($sched['desc']) && $sched['desc'] == 'CLOSED') {
					$status = $sched['desc'];
			} else {
				$tmpTimeBegin = str_split((string) $sched['timeBegin'], 2);
				$timeBegin    = $tmpTimeBegin[0] . ':' . $tmpTimeBegin[1];

				$tmpTimeEnd = str_split((string) $sched['timeEnd'], 2);
				$timeEnd    = $tmpTimeEnd[0] . ':' . $tmpTimeEnd[1];

				$tmpValidPeriodBegin = str_split((string) $sched['validPeriodBegin'], 2);
				$validPeriodBegin    = $tmpValidPeriodBegin[0] . $tmpValidPeriodBegin[1] . '-' . $tmpValidPeriodBegin[2] . '-' . $tmpValidPeriodBegin[3];
				
				$tmpValidPeriodEnd = str_split((string) $sched['validPeriodEnd'], 2);
				$validPeriodEnd    = $tmpValidPeriodEnd[0] . $tmpValidPeriodEnd[1] . '-' . $tmpValidPeriodEnd[2] . '-' . $tmpValidPeriodEnd[3];
			}

			$result['data'][] = array(
				'isAfterHours' => $this->afterHours[$afterHours],
				'day'          => $this->days[($dayNumber - 1)],
				'status'       => $status,
				'schedule'     => array(
					'type' => $schedType,
					'name' => $schedTypeName
				),
				'time'         => array(
					'begin' => $timeBegin,
					'end'   => $timeEnd
				),
				'validPeriod'  => array(
					'begin' => $validPeriodBegin,
					'end'   => $validPeriodEnd
				)
			);
		}

		return $result;
	}

	/**
	 * returns the service request xml for the "getStation" service request.
	 * @param  string $stationCode the station code.
	 * @return object              a SimpleXMLElement object.
	 */
	private function createGetStationRequestXML($stationCode)
	{
		$serviceRequestName = 'getStation';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$stationNode = $serviceParamsNode->addChild('station');
		$stationNode->addAttribute('stationCode', $stationCode);

		return $requestXML;
	}

	/**
	 * returns an array with data from the response XML of the "getStation" service request.
	 * @param  string $xmlString the response XML from the "getStation" service request.
	 * @return array             the result array containing the status and the data.
	 */
	private function createGetStationArrayFromXML($xmlString)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($xmlString);
		$returnCode  = $responseXML->serviceResponse['returnCode'];
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$stationAttributes = $responseXML->serviceResponse->station->attributes();

		$delivery = (string) $stationAttributes->delivery;
		$deliveryDescription = $this->deliveryDescriptions[$delivery];

		$result['data'] = array(
			'address'     => (string) $stationAttributes->address1,
			'areaType'    => (string) $stationAttributes->areaType,
			'cityName'    => (string) $stationAttributes->cityName,
			'country'     => array(
				'code' => (string) $stationAttributes->countryCode,
				'name' => (string) $stationAttributes->countryName,
			),
			'county'      => (string) $stationAttributes->county,
			'delivery'    => array(
				'code'        => $delivery,
				'description' => $deliveryDescription,
			),
			'coordinates' => array(
				'lon' => (string) $stationAttributes->longitude,
				'lat' => (string) $stationAttributes->latitude,
			),
			'leadTime'    => (string) $stationAttributes->leadTime,
			'phone'       => array(
				'areacode'            => (string) $stationAttributes->phoneAreaCode,
				'countryCode'         => (string) $stationAttributes->phoneCountryCode,
				'number'              => (string) $stationAttributes->phoneNumber,
				'internationalNumber' => (string) $stationAttributes->phoneWithInternationalDialling,
			),
			'postalCode'  => (string) $stationAttributes->postalCode,
			'station'     => array(
				'code' => (string) $stationAttributes->stationCode,
				'name' => (string) $stationAttributes->stationName,
			),
		);

		return $result;
	}

	/**
	 * returns the service request xml for the "getStations" service request.
	 * @param  string $countryCode the two letter country coce.
	 * @param  string $cityName    the city name.
	 * @return object              a SimpleXMLElement object.
	 */
	private function createGetStationsRequestXML($countryCode, $cityName)
	{
		$serviceRequestName = 'getStations';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$stationNode = $serviceParamsNode->addChild('station');
		$stationNode->addAttribute('countryCode', $countryCode);
		$stationNode->addAttribute('cityName', $cityName);

		return $requestXML;
	}

	/**
	 * returns an array with data from the response XML of the "getStations" service request.
	 * @param  string $xmlString the response XML from the "getStations" service request.
	 * @return array             the result array containing the status and the data.
	 */
	private function createGetStationsArrayFromXML($xmlString)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($xmlString);
		$returnCode  = $responseXML->serviceResponse['returnCode'];
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$stations = $responseXML->serviceResponse->stationList->station;

		foreach ($stations as $station) {
			$result['data'][] = array(
				'code' => (string) $station['stationCode'],
				'name' => (string) $station['stationName'],
			);
		}

		return $result;
	}

	/**
	 * returns the service request xml for the "getCities" service request.
	 * @param  string $countryCode the two letter country code.
	 * @return object              a SimpleXMLElement object.
	 */
	private function createGetCitiesRequestXML($countryCode)
	{
		$serviceRequestName = 'getCities';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$countryNode = $serviceParamsNode->addChild('country');
		$countryNode->addAttribute('countryCode', $countryCode);

		return $requestXML;
	}

	/**
	 * returns an array with data from the response XML of the "getCities" service request.
	 * @param  string $xmlString the response XML from the "getCities" service request.
	 * @return array             the result array containing the status and the data.
	 */
	private function createGetCitiesArrayFromXML($xmlString)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($xmlString);
		$returnCode  = $responseXML->serviceResponse['returnCode'];

		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$cities   = $responseXML->serviceResponse->cityList->city;

		foreach ($cities as $city) {
			$result['data'][] = array(
				'code'        => (string) $city['cityCode'],
				'description' => (string) $city['cityDescription'],
			);
		}

		return $result;
	}

	/**
	 * returns the service request xml for the "getCountriesOfResidence" service request.
	 * @return object a SimpleXMLElement object.
	 */
	private function createGetCountriesResidenceRequestXML()
	{
		$serviceRequestName = 'getCountriesOfResidence';

		$requestXML = $this->createRequestXML($serviceRequestName);

		return $requestXML;
	}

	/**
	 * returns an array with data from the response XML of the "getCountriesOfResidence" service request.
	 * @param  string $xmlString the response XML from the "getCountriesOfResidence" service request.
	 * @return array             the result array containing the status and the data.
	 */
	private function createGetCountriesResidenceArrayFromXML($xmlString)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($xmlString);
		$returnCode  = $responseXML->serviceResponse['returnCode'];
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$countries = $responseXML->serviceResponse->countryList->country;

		foreach ($countries as $country) {
			$result['data'][] = array(
				'code'        => (string) $country['countryCode'],
				'description' => (string) $country['countryDescription'],
			);
		}

		return $result;
	}

	/**
	 * returns the service request xml for the "getCountries" service request.
	 * @return object a SimpleXMLElement object.
	 */
	private function createGetCountriesRequestXML()
	{
		$serviceRequestName = 'getCountries';

		$requestXML = $this->createRequestXML($serviceRequestName);

		return $requestXML;
	}

	/**
	 * returns an array with data from the response XML of the "getCountries" service request.
	 * @param  string $xmlString the response XML from the "getCountries" service request.
	 * @return array             the result array containing the status and the data.
	 */
	private function createGetCountriesArrayFromXML($xmlString)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($xmlString);
		$returnCode  = $responseXML->serviceResponse['returnCode'];
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$countries = $responseXML->serviceResponse->countryList->country;

		foreach ($countries as $country) {
			$result['data'][] = array(
				'code'        => (string) $country['countryCode'],
				'description' => (string) $country['countryDescription'],
			);
		}

		return $result;
	}

	private function createGetQuoteRequestXML(
		$checkoutDate,
		$checkoutTime,
		$checkinDate,
		$checkinTime,
		$checkoutStationId,
		$checkinStationId,
		$countryOfResidence,
		$carCategoryCode
	) {
		$serviceRequestName = 'getQuote';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$reservationNode = $serviceParamsNode->addChild('reservation');
		$reservationNode->addAttribute('carCategory', $carCategoryCode);

		$checkoutNode = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $checkoutStationId);
		$checkoutNode->addAttribute('date', str_replace('-', '', $checkoutDate));
		$checkoutNode->addAttribute('time', str_replace(':', '', $checkoutTime));

		$checkinNode  = $reservationNode->addChild('checkin');
		$checkinNode->addAttribute('stationID', $checkinStationId);
		$checkinNode->addAttribute('date', str_replace('-', '', $checkinDate));
		$checkinNode->addAttribute('time', str_replace(':', '', $checkinTime));

		$driverNode = $serviceParamsNode->addChild('driver');
		$driverNode->addAttribute('countryOfResidence', $countryOfResidence);

		return $requestXML;
	}

	private function createGetQuoteArrayFromXML($xmlString)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($xmlString);
		$returnCode  = $responseXML->serviceResponse['returnCode'];

		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$quote = $responseXML->serviceResponse->reservation->quote;

		$result['data'] = array(
			'basePrice' => (string) $quote['basePrice'], // base price node is obsolete
			'currency'  => (string) $quote['currency'],
			'rentingCurrencyOfTotalRateEstimate' => (string) $quote['rentingCurrencyOfTotalRateEstimate'],
			'bookingCurrencyOfTotalRateEstimate' => (string) $quote['bookingCurrencyOfTotalRateEstimate'],
			'totalRateEstimate'                  => (string) $quote['totalRateEstimate'],
			'xrsBasePrice'                       => (string) $quote['xrsBasePrice'],
			'xrsBasePriceInBookingCurrency'      => (string) $quote['xrsBasePriceInBookingCurrency'],
			'totalRateEstimateInBookingCurrency' => (string) $quote['totalRateEstimateInBookingCurrency'],
			'totalRateEstimateInRentingCurrency' => (string) $quote['totalRateEstimateInRentingCurrency'],
		);

		return $result;
	}

	private function createGetMultipleRatesRequestXML(
		$checkoutDate,
		$checkoutTime,
		$checkinDate,
		$checkinTime,
		$checkoutStationId,
		$checkinStationId,
		$countryOfResidence,
		$carCategoryPatterns = array()
	) {
		$serviceRequestName = 'getMultipleRates';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$reservationNode = $serviceParamsNode->addChild('reservation'); 

		if (!empty($carCategoryPatterns)) {
			$reservationNode->addAttribute('carCategoryPattern', implode('', $carCategoryPatterns));
		}

		$checkoutNode = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $checkoutStationId);
		$checkoutNode->addAttribute('date', str_replace('-', '', $checkoutDate));
		$checkoutNode->addAttribute('time', str_replace(':', '', $checkoutTime));

		$checkinNode = $reservationNode->addChild('checkin');
		$checkinNode->addAttribute('stationID', $checkinStationId);
		$checkinNode->addAttribute('date', str_replace('-', '', $checkinDate));
		$checkinNode->addAttribute('time', str_replace(':', '', $checkinTime));

		$driverNode = $serviceParamsNode->addChild('driver');
		$driverNode->addAttribute('countryOfResidence', $countryOfResidence);

		return $requestXML;
	}

	private function createGetMultipleRatesArrayFromXML($xmlString)
	{
		$result       = array();
		$responseXML  = new SimpleXMLElement($xmlString);
		$acrissHelper = new AcrissHelper();
		$returnCode   = $responseXML->serviceResponse['returnCode'];

		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$reservationRates = $responseXML->serviceResponse->reservationRateList->reservationRate;

		foreach ($reservationRates as $vehicle) {
			$carCategoryStatusCode = (string) $vehicle['carCategoryStatusCode'];
			$hasAirCondition       = $this->hasAirCondition[(string) $vehicle['carCategoryAirCond']];
			$isAutomatic           = $this->carCategoryTransmissions[(string) $vehicle['carCategoryAutomatic']];
			$transmission          = $this->createTransmissionArray($isAutomatic);
			$minAgeForCategory     = (string) $vehicle->ageLimit['minAgeForCategory'];
			$minAgeForCountry      = (string) $vehicle->ageLimit['minAgeForCountry'];

			$result['data'][] = array(
				'supplierCode'    => $this->supplierCode,
				'hasAirCondition' => $hasAirCondition,
				'transmission'    => $transmission,
				'baggageQty'      => (string) $vehicle['carCategoryBaggageQuantity'],
				'co2Qty'          => (string) $vehicle['carCategoryCO2Quantity'],
				'categoryCode'    => (string) $vehicle['carCategoryCode'],
				'expandedCode'    => $acrissHelper->expandCode((string) $vehicle['carCategoryCode']),
				'doorCount'       => (string) $vehicle['carCategoryDoors'],
				'name'            => (string) $vehicle['carCategorySample'] . ' or similar',
				'seats'           => (string) $vehicle['carCategorySeats'],
				'vehicleStatus'   => array(
					'code'        => $carCategoryStatusCode,
					'description' => $this->carCategoryStatus[$carCategoryStatusCode],
				),
				// 'vehicleType' => array(
				// 	'code'        => 'CR',
				// 	'description' => 'Cars Only',
				// ),
				'maxAge'    => (string) $vehicle->ageLimit['maxAgeForCountry'],
				'minAge'    => ($minAgeForCategory > $minAgeForCountry) ? $minAgeForCategory : $minAgeForCountry,
				'rateId'    => (string) $vehicle['rateId'],
				'basePrice' => (string) $vehicle['basePrice'], // base price node is obsolete
				'currency'  => (string) $vehicle['currency'],
				'bookingCurrencyOfTotalRateEstimate' => (string) $vehicle['bookingCurrencyOfTotalRateEstimate'],
				'xrsBasePrice'                       => (string) $vehicle['xrsBasePrice'],
				'xrsBasePriceInBookingCurrency'      => (string) $vehicle['xrsBasePriceInBookingCurrency'],
				'totalRateEstimate'                  => (string) $vehicle['totalRateEstimate'],
				'totalRateEstimateInBookingCurrency' => (string) $vehicle['totalRateEstimateInBookingCurrency'],
			);
		}

		return $result;
	}

	public function searchVehicles(
		$checkoutDate,
		$checkoutTime,
		$checkinDate,
		$checkinTime,
		$checkoutStationId,
		$checkinStationId,
		$countryCode,
		$driverAge
	) {
		$timeStart = time();
		$result    = array();

		$result = $this->getMultipleRates(
			$checkoutDate,
			$checkoutTime,
			$checkinDate,
			$checkinTime,
			$checkoutStationId,
			$checkinStationId,
			$countryCode
		);

		if (!empty($result['data'])) {
			foreach ($result['data'] as $key => $vehicle) {
				if ((int) $driverAge < $vehicle['minAge'] || (int) $driverAge > $vehicle['maxAge']) {
					unset($result['data'][$key]);
				}
			}
		}

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * returns the list of all open hours for a station code and a date.
	 * @param  string $stationCode the station code.
	 * @param  string $date        the date you want to check.
	 * @return array               the result array containing the status and the data.
	 */
	public function getOpenHours($stationCode, $date)
	{
		// NOTE
		// - there are times when it only returns the return code attribute
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createGetOpenHoursRequestXML($stationCode, $date);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetOpenHoursArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * return the list of all vehicles for a station code and a date.
	 * @param  string $stationCode the station code.
	 * @param  string $date        the date you want to check.
	 * @return array               the result array containing the status and the data.
	 */
	public function getVehicles($stationCode, $date)
	{
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createGetVehiclesRequestXML($stationCode, $date);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetVehiclesArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * returns the list all equipment code for a station code and a date.
	 * @param  string $stationCode the station code.
	 * @param  string $date        the date you want to check.
	 * @return array               the result array containing the status and the data.
	 */
	public function getEquipmentList($stationCode, $date)
	{
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createGetEquipmentListRequestXML($stationCode, $date);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}
		
		$result = $this->createGetEquipmentListArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * returns complete station schedule as set in EC GreenWay database.
	 * @param  string $stationCode the station code.
	 * @return array               the result array containing the status and the data.
	 */
	public function getStationSchedule($stationCode)
	{
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createGetStationScheduleRequestXML($stationCode);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetStationScheduleArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * returns station details for a station code.
	 * @param  string $stationCode the station code.
	 * @return array               the result array containing the status and the data.
	 */
	public function getStation($stationCode)
	{
		$timeStart = time();
		$result    = array();
		
		$requestXML = $this->createGetStationRequestXML($stationCode);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}
		
		$result = $this->createGetStationArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * returns the list of station codes and station description for a city code.
	 * @param  string $countryCode the two letter country code.
	 * @param  string $cityName    the city name.
	 * @return array               the result array containing the status and the data.
	 */
	public function getStations($countryCode, $cityName)
	{
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createGetStationsRequestXML($countryCode, $cityName);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}
		
		$result = $this->createGetStationsArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * returns the list of all countries.
	 * @param  string $countryCode the two letter country code.
	 * @return array               the result array containing the status and the data.
	 */
	public function getCities($countryCode)
	{
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createGetCitiesRequestXML($countryCode);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetCitiesArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * returns the list of all countries.
	 * @return array the result array containing the status and the data.
	 */
	public function getCountriesResidence()
	{
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createGetCountriesResidenceRequestXML();

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetCountriesResidenceArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * returns the list of all countries code where there is one or more Europcar station.
	 * @return array the result array containing the status and the data.
	 */
	public function getCountries()
	{
		$timeStart = time();
		$result    = array();
		
		$requestXML = $this->createGetCountriesRequestXML();

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetCountriesArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	/**
	 * returns the quote for a reservation.
	 * @param  string $checkoutDate         the pick up date
	 * @param  string $checkoutTime         the pick up time
	 * @param  string $checkinDate         the return date
	 * @param  string $checkinTime         the return time
	 * @param  string $checkoutStationId    the pick up station
	 * @param  string $checkinStationId    the return station
	 * @param  string $countryOfResidence the country of residence
	 * @param  string $carCategoryCode    the car category code
	 * @return array                      the result array containing the status and the data.
	 */
	public function getQuote(
		$checkoutDate,
		$checkoutTime,
		$checkinDate,
		$checkinTime,
		$checkoutStationId,
		$checkinStationId,
		$countryOfResidence,
		$carCategoryCode
	) {
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createGetQuoteRequestXML(
			$checkoutDate,
			$checkoutTime,
			$checkinDate,
			$checkinTime,
			$checkoutStationId,
			$checkinStationId,
			$countryOfResidence,
			$carCategoryCode
		);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetQuoteArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	public function getMultipleRates(
		$checkoutDate,
		$checkoutTime,
		$checkinDate,
		$checkinTime,
		$checkoutStationId,
		$checkinStationId,
		$countryOfResidence,
		$carCategoryPatterns = array()
	) {
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createGetMultipleRatesRequestXML(
			$checkoutDate,
			$checkoutTime,
			$checkinDate,
			$checkinTime,
			$checkoutStationId,
			$checkinStationId,
			$countryOfResidence,
			$carCategoryPatterns
		);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetMultipleRatesArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	// IMPORTANT! CHECK FUNCTIONS
	private function createBookReservationRequestXML(
		$checkoutDate,
		$checkoutTime,
		$checkinDate,
		$checkinTime,
		$checkoutStationId,
		$checkinStationId,
		$carCategoryCode,
		$title,
		$firstName,
		$lastName,
		$countryOfResidence,
		$equipmentList = array()
	) {
		$serviceRequestName = 'bookReservation';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$reservationNode = $serviceParamsNode->addChild('reservation');
		$reservationNode->addAttribute('carCategory', $carCategoryCode);

		$checkoutNode = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $checkoutStationId);
		$checkoutNode->addAttribute('date', str_replace('-', '', $checkoutDate));
		$checkoutNode->addAttribute('time', str_replace(':', '', $checkoutTime));

		$checkinNode = $reservationNode->addChild('checkin');
		$checkinNode->addAttribute('stationID', $checkinStationId);
		$checkinNode->addAttribute('date', str_replace('-', '', $checkinDate));
		$checkinNode->addAttribute('time', str_replace(':', '', $checkinTime));

		$driverNode = $serviceParamsNode->addChild('driver');
		$driverNode->addAttribute('countryOfResidence', $countryOfResidence);
		$driverNode->addAttribute('firstName', $firstName);
		$driverNode->addAttribute('lastName', $lastName);
		$driverNode->addAttribute('title', $title);

		if (!empty($equipmentList)) {
			$equipmentListNode = $reservationNode->addChild('equipmentList');

			// max equipment quantity is 4
			foreach ($equipmentList as $equipmentCode => $equipmentQuantity) {
				$equipmentNode = $equipmentListNode->addChild('equipment');
				$equipmentNode->addAttribute('code', $equipmentCode);
				$equipmentNode->addAttribute('qty', (int) $equipmentQuantity);
			}
		}

		return $requestXML;
	}

	private function createBookReservationArrayFromXML($xmlString)
	{
		$result       = array();
		$responseXML  = new SimpleXMLElement($xmlString);
		$acrissHelper = new AcrissHelper();
		$returnCode   = $responseXML->serviceResponse['returnCode'];

		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$reservation = $responseXML->serviceResponse->reservation;

		$hasAirCondition   = $this->hasAirCondition[(string) $reservation['carCategoryAirCond']];
		$isAutomatic       = $this->carCategoryTransmissions[(string) $reservation['carCategoryAutomatic']];
		$transmission      = $this->createTransmissionArray($isAutomatic);
		$minAgeForCategory = (string) $reservation->ageLimit['minAgeForCategory'];
		$minAgeForCountry  = (string) $reservation->ageLimit['minAgeForCountry'];

		$result['data'] = array(
			'confirmation' => (string) $reservation['resNumber'],
			'pickUp' => array(
				'station' => (string) $reservation->checkout['stationID'],
				'date'    => (string) $reservation->checkout['date'],
				'time'    => (string) $reservation->checkout['time'],
			),
			'return' => array(
				'station' => (string) $reservation->checkin['stationID'],
				'date'    => (string) $reservation->checkin['date'],
				'time'    => (string) $reservation->checkin['time'],
			),
			'duration'          => (string) $reservation['duration'],
			'hasAirCondition'   => $hasAirCondition,
			'transmission'      => $transmission,
			'baggageQty'        => (string) $reservation['carCategoryBaggageQuantity'],
			'categoryCode'      => (string) $reservation['carCategory'],
			'expandedCode'      => $acrissHelper->expandCode((string) $reservation['carCategory']),
			'doorCount'         => (string) $reservation['carCategoryDoors'],
			'seats'             => (string) $reservation['carCategorySeats'],
			'minAge'            => ($minAgeForCategory > $minAgeForCountry) ? $minAgeForCategory : $minAgeForCountry,
			'currency'          => (string) $reservation->quote['currency'],
			'totalRateEstimate' => (string) $reservation->quote['totalRateEstimate'],
		);

		if (isset($reservation->equipmentList)) {
			$result['equipments'] = array();

			foreach ($reservation->equipmentList->equipment as $equipment) {
				$price = (float) $equipment['rentalPriceAI'] / (float) $equipment['qty'];

				if ($equipment['per'] == 'D') {
					$price /= (float) $reservation['duration'];
				}

				$result['equipments'][] = array(
					'code'       => (string) $equipment['code'],
					'totalPrice' => (string) $equipment['rentalPriceAI'],
					'quantity'   => (string) $equipment['qty'],
					'per'        => (string) $equipment['per'],
					'price'      => round($price, 2),
				);
			}
		}

		return $result;
	}

	public function bookReservation(
		$checkoutDate,
		$checkoutTime,
		$checkinDate,
		$checkinTime,
		$checkoutStationId,
		$checkinStationId,
		$carCategoryCode,
		$title,
		$firstName,
		$lastName,
		$countryOfResidence,
		$equipmentList = array()
	) {
		// $equipmentList = array('CSB' => 1); //, 'CSI' => 2); // for testing
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createBookReservationRequestXML(
					      $checkoutDate,
					      $checkoutTime,
					      $checkinDate,
					      $checkinTime,
					      $checkoutStationId,
					      $checkinStationId,
					      $carCategoryCode,
					      $title,
					      $firstName,
					      $lastName,
					      $countryOfResidence,
					      $equipmentList
					  );

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createBookReservationArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}

	private function createSearchReservationByIdRequestXML($reservationNumber)
	{
		$serviceRequestName = 'search.searchbyid';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$reservationNode = $serviceParamsNode->addChild('reservation');
		$reservationNode->addAttribute('resNumber', $reservationNumber);

		return $requestXML;
	}

	private function createSearchReservationByIdArrayFromXML($xmlString)
	{
		$result       = array();
		$responseXML  = new SimpleXMLElement($xmlString);
		$acrissHelper = new AcrissHelper();
		$returnCode   = $responseXML->serviceResponse['returnCode'];

		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$reservation = $responseXML->serviceResponse->reservation;
		$driver      = $responseXML->serviceResponse->driver;

		$hasAirCondition   = $this->hasAirCondition[(string) $reservation['carCategoryAirCond']];
		$isAutomatic       = $this->carCategoryTransmissions[(string) $reservation['carCategoryAutomatic']];
		$transmission      = $this->createTransmissionArray($isAutomatic);
		$minAgeForCategory = (string) $reservation->ageLimit['minAgeForCategory'];
		$minAgeForCountry  = (string) $reservation->ageLimit['minAgeForCountry'];

		$result['data'] = array(
			'confirmation' => (string) $reservation['resNumber'],
			'pickUp' => array(
				'station' => (string) $reservation->checkout['stationID'],
				'date'    => (string) $reservation->checkout['date'],
				'time'    => (string) $reservation->checkout['time'],
			),
			'return' => array(
				'station' => (string) $reservation->checkin['stationID'],
				'date'    => (string) $reservation->checkin['date'],
				'time'    => (string) $reservation->checkin['time'],
			),
			'driver' => array(
				'title'     => (string) $driver['title'],
				'firstName' => (string) $driver['firstName'],
				'lastName'  => (string) $driver['lastName'],
			),
			'duration'          => (string) $reservation['duration'],
			'hasAirCondition'   => $hasAirCondition,
			'transmission'      => $transmission,
			'baggageQty'        => (string) $reservation['carCategoryBaggageQuantity'],
			'categoryCode'      => (string) $reservation['carCategory'],
			'expandedCode'      => $acrissHelper->expandCode((string) $reservation['carCategory']),
			'doorCount'         => (string) $reservation['carCategoryDoors'],
			'seats'             => (string) $reservation['carCategorySeats'],
			'minAge'            => ($minAgeForCategory > $minAgeForCountry) ? $minAgeForCategory : $minAgeForCountry,
			'currency'          => (string) $reservation->quote['currency'],
			'totalRateEstimate' => (string) $reservation->quote['totalRateEstimate'],
		);

		return $result;
	}

	public function searchReservationById($reservationNumber)
	{
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createSearchReservationByIdRequestXML($reservationNumber);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createSearchReservationByIdArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}




	private function createCancelReservationRequestXML($reservationNumber)
	{
		$serviceRequestName = ' cancelReservation';

		$requestXML = $this->createRequestXML($serviceRequestName);

		$serviceParamsNode = $requestXML->serviceRequest->addChild('serviceParameters');

		$reservationNode = $serviceParamsNode->addChild('reservation');
		$reservationNode->addAttribute('resNumber', $reservationNumber);

		return $requestXML;
	}

	private function createCancelReservationArrayFromXML($xmlString)
	{
		$result       = array();
		$responseXML  = new SimpleXMLElement($xmlString);
		// $acrissHelper = new AcrissHelper();
		$returnCode   = $responseXML->serviceResponse['returnCode'];

		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$reservation = $responseXML->serviceResponse->reservation;

		$result['data'] = array(
			'reservation' => array(
				'number'       => (string) $reservation['resNumber'],
				'cancelNumber' => (string) $reservation['resCancelNumber'],
			),
		);

		return $result;
	}

	public function cancelReservation($reservationNumber)
	{
		$timeStart = time();
		$result    = array();

		$requestXML = $this->createCancelReservationRequestXML($reservationNumber);

		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createCancelReservationArrayFromXML($response);

		$result['executionTime'] = time() - $timeStart;
		$result['supplierCode']  = $this->supplierCode;

		return $result;
	}
}