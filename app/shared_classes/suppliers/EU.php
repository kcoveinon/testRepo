<?php

/*namespace Supplier;*/

class EU extends SupplierApi
{
	private $apiUsernameVariable;
	private $apiPasswordVariable;
	private $days;
	private $scheduleTypes;
	private $deliveryDescriptions;
	private $equipmentStatus;
	private $openHoursTypes;
	private $afterHours;
	private $hasAirCondition;
	private $carCategoryTransmissions;
	private $carCategoryStatus;
	private $carCategoryCarTypes;
	private $defaultStationStatus;
	private $defaultCurlOptions;

	public function __construct()
	{
		$this->apiUrl              = Config::get(get_class() . '.api.url');
		$this->apiUsernameVariable = Config::get(get_class() . '.api.usernameVariable');
		$this->apiPasswordVariable = Config::get(get_class() . '.api.passwordVariable');
		$this->apiUsername         = Config::get(get_class() . '.api.username');
		$this->apiPassword         = Config::get(get_class() . '.api.password');

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

	private function createRequestXML()
	{
		return new SimpleXMLElement('<message></message>');
	}

	private function createGetOpenHoursRequestXML($stationCode, $date)
	{
		$serviceRequestName = 'getOpenHours';

		$requestXML = $this->createRequestXML();

		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		$serviceParams   = $serviceRequestNode->addChild('serviceParameters');
		$reservationNode = $serviceParams->addChild('reservation');
		$checkoutNode    = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $stationCode);
		$checkoutNode->addAttribute('date', str_replace('-', '', $date));

		return $requestXML;
	}

	private function createGetOpenHoursArrayFromXML($data)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($data);
		// /station/get-open-hours/BNEW02/2015-12-13
		// this station does not have any attributes
		$returnCode  = $responseXML->serviceResponse->attributes()->returnCode;
		
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
		
		$tmpBeginTime = str_split((string) $openHours->attributes()->beginTime, 2);
		$beginTime    = $tmpBeginTime[0] . ':' . $tmpBeginTime[1];

		$tmpEndTime = str_split((string) $openHours->attributes()->endTime, 2);
		$endTime    = $tmpEndTime[0] . ':' . $tmpEndTime[1];

		$type     = (string) $openHours->attributes()->type;
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

	private function createGetVehiclesRequestXML($stationCode, $date)
	{
		$serviceRequestName = 'getCarCategories';

		// preprare request xml object
		$requestXML = $this->createRequestXML();

		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		$serviceParams   = $serviceRequestNode->addChild('serviceParameters');
		$reservationNode = $serviceParams->addChild('reservation');
		$checkoutNode    = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $stationCode);
		$checkoutNode->addAttribute('date', str_replace('-', '', $date));

		return $requestXML;
	}

	private function createGetVehiclesArrayFromXML($data)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($data);
		$returnCode  = $responseXML->serviceResponse->attributes()->returnCode;
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$carCategory = $responseXML->serviceResponse->carCategoryList->carCategory;

		foreach ($carCategory as $index => $category) {
			$carType         = (string) $category->attributes()->carType;
			$hasAirCondition = $this->hasAirCondition[(string) $category->attributes()->carCategoryAirCond];
			$isAutomatic     = $this->carCategoryTransmissions[(string) $category->attributes()->carCategoryAutomatic];
			$transmission    = $this->createTransmissionArray($isAutomatic);
			$carCategoryStatusCode = (string) $category->attributes()->carCategoryStatusCode;

			$result['data'][] = array(
				'hasAirCondition' => $hasAirCondition,
				'transmission'    => $transmission,
				'baggageQty'      => (string) $category->attributes()->carCategoryBaggageQuantity,
				'co2Qty'          => (string) $category->attributes()->carCategoryCO2Quantity,
				'categoryCode'    => (string) $category->attributes()->carCategoryCode,
				'doorCount'       => (string) $category->attributes()->carCategoryDoors,
				'name'            => (string) $category->attributes()->carCategorySample . ' or similar',
				'seats'           => (string) $category->attributes()->carCategorySeats,
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

	private function createGetEquipmentListRequestXML($stationCode, $date)
	{
		$serviceRequestName = 'getEquipmentList';

		// preprare request xml object
		$requestXML = $this->createRequestXML();

		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		$serviceParams   = $serviceRequestNode->addChild('serviceParameters');
		$reservationNode = $serviceParams->addChild('reservation');
		$checkoutNode    = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $stationCode);
		$checkoutNode->addAttribute('date', str_replace('-', '', $date));

		return $requestXML;
	}

	private function createGetEquipmentListArrayFromXML($data)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($data);
		$returnCode  = $responseXML->serviceResponse->attributes()->returnCode;
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$equipments = $responseXML->serviceResponse->equipmentList->equipment;

		foreach ($equipments as $equipment) {
			$equipmentStatusCode = (string) $equipment->attributes()->statusCode;

			$result['data'][] = array(
				'code'            => (string) $equipment->attributes()->code,
				'description'     => (string) $equipment->attributes()->descr,
				'equipmentStatus' => array(
					'code'        => $equipmentStatusCode,
					'description' => $this->equipmentStatus[$equipmentStatusCode]
				)
			);
		}

		return $result;
	}

	private function createGetStationScheduleRequestXML($stationCode)
	{
		$serviceRequestName = 'getStationSchedule';

		// preprare request xml object
		$requestXML = $this->createRequestXML();

		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		$serviceParams = $serviceRequestNode->addChild('serviceParameters');
		$stationNode   = $serviceParams->addChild('station');
		$stationNode->addAttribute('stationCode', $stationCode);

		return $requestXML;
	}

	private function createGetStationScheduleArrayFromXML($data)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($data);
		$returnCode  = $responseXML->serviceResponse->attributes()->returnCode;

		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$stationDaySched = $responseXML->serviceResponse->openHoursList->stationDaySched;

		foreach ($stationDaySched as $sched) {
			$afterHours       = (string) $sched->attributes()->afterHours;
			$dayNumber        = (string) $sched->attributes()->dayNumber;
			$schedType        = (string) $sched->attributes()->schedType;
			$status           = $this->defaultStationStatus;
			$timeBegin        = '';
			$timeEnd          = '';
			$validPeriodBegin = '';
			$validPeriodEnd   = '';
			$schedTypeName    = $this->scheduleTypes[$schedType];

			if (isset($sched->attributes()->desc) && $sched->attributes()->desc == 'CLOSED') {
					$status = $sched->attributes()->desc;
			} else {
				$tmpTimeBegin = str_split((string) $sched->attributes()->timeBegin, 2);
				$timeBegin    = $tmpTimeBegin[0] . ':' . $tmpTimeBegin[1];

				$tmpTimeEnd = str_split((string) $sched->attributes()->timeEnd, 2);
				$timeEnd    = $tmpTimeEnd[0] . ':' . $tmpTimeEnd[1];

				$tmpValidPeriodBegin = str_split((string) $sched->attributes()->validPeriodBegin, 2);
				$validPeriodBegin    = $tmpValidPeriodBegin[0] . $tmpValidPeriodBegin[1] . '-' . $tmpValidPeriodBegin[2] . '-' . $tmpValidPeriodBegin[3];
				
				$tmpValidPeriodEnd = str_split((string) $sched->attributes()->validPeriodEnd, 2);
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

	private function createGetStationRequestXML($stationCode)
	{
		$serviceRequestName = 'getStation';

		// preprare request xml object
		$requestXML = $this->createRequestXML();

		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		$serviceParams = $serviceRequestNode->addChild('serviceParameters');
		$stationNode   = $serviceParams->addChild('station');
		$stationNode->addAttribute('stationCode', $stationCode);

		return $requestXML;
	}

	private function createGetStationArrayFromXML($data)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($data);
		$returnCode  = $responseXML->serviceResponse->attributes()->returnCode;
		
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

	private function createGetStationsRequestXML($countryCode, $cityName)
	{
		$serviceRequestName = 'getStations';

		// preprare request xml object
		$requestXML = $this->createRequestXML();
		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		$serviceParams = $serviceRequestNode->addChild('serviceParameters');
		$stationNode   = $serviceParams->addChild('station');
		$stationNode->addAttribute('countryCode', $countryCode);
		$stationNode->addAttribute('cityName', $cityName);

		return $requestXML;
	}

	private function createGetStationsArrayFromXML($data)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($data);
		$returnCode  = $responseXML->serviceResponse->attributes()->returnCode;
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$stations = $responseXML->serviceResponse->stationList->station;

		foreach ($stations as $station) {
			$result['data'][] = array(
				'code' => (string) $station->attributes()->stationCode,
				'name' => (string) $station->attributes()->stationName,
			);
		}

		return $result;
	}

	private function createGetCitiesRequestXML($countryCode)
	{
		$serviceRequestName = 'getCities';

		// preprare request xml object
		$requestXML = $this->createRequestXML();

		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		$serviceParams = $serviceRequestNode->addChild('serviceParameters');		
		$countryNode   = $serviceParams->addChild('country');
		$countryNode->addAttribute('countryCode', $countryCode);

		return $requestXML;
	}

	private function createGetCitiesArrayFromXML($data)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($data);
		$returnCode  = $responseXML->serviceResponse->attributes()->returnCode;

		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$cities   = $responseXML->serviceResponse->cityList->city;

		foreach ($cities as $city) {
			$result['data'][] = array(
				'code'        => (string) $city->attributes()->cityCode,
				'description' => (string) $city->attributes()->cityDescription,
			);
		}

		return $result;
	}

	private function createGetCountriesResidenceRequestXML()
	{
		$serviceRequestName = 'getCountriesOfResidence';

		// preprare request xml object
		$requestXML = $this->createRequestXML();

		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		return $requestXML;
	}

	private function createGetCountriesResidenceArrayFromXML($data)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($response);
		$returnCode  = $responseXML->serviceResponse->attributes()->returnCode;
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$countries = $responseXML->serviceResponse->countryList->country;

		foreach ($countries as $country) {
			$result['data'][] = array(
				'code'        => (string) $country->attributes()->countryCode,
				'description' => (string) $country->attributes()->countryDescription,
			);
		}

		return $result;
	}

	private function createGetCountriesRequestXML()
	{
		$serviceRequestName = 'getCountries';

		// preprare request xml object
		$requestXML = $this->createRequestXML();

		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		return $requestXML;
	}

	private function createGetCountriesArrayFromXML($data)
	{
		$result      = array();
		$responseXML = new SimpleXMLElement($data);
		$returnCode  = $responseXML->serviceResponse->attributes()->returnCode;
		
		if ($returnCode != 'OK') {
			$result['status'] = '';

			return $result;
		}

		$result['status'] = 'OK';

		$countries = $responseXML->serviceResponse->countryList->country;

		foreach ($countries as $country) {
			$result['data'][] = array(
				'code'        => (string) $country->attributes()->countryCode,
				'description' => (string) $country->attributes()->countryDescription,
			);
		}

		return $result;
	}

	public function searchVehicles($pickUpDate, $pickUpTime, $returnDate, $returnTime, $pickUpLocationId, $returnLocationId, $countryCode, $driverAge)
	{
		$timeStart = time();

		ini_set('max_execution_time', 120);

		$result = array();

		$location = Location::find($pickUpLocationId);

		if (empty($location)) {
			$result['status'] = 'Unable to get pick up location details.';

			return $result;
		}

		$cityName = $location->getCity();

		$getStationsResult = $this->getStations($countryCode, $cityName);

		if ($getStationsResult['status'] != 'OK') {
			$result['status'] = 'Unable to get stations details.';

			return $result;
		}

		$stations = $getStationsResult['data'];

		// temporary for testing
		// $stations = array(
		// 	array('code' => 'BNEC02'),
		// 	array('code' => 'BNET01'),
		// 	array('code' => 'BNET03'),
		// 	// array('code' => 'BNEC03'),
		// 	array('code' => 'BNEW02'), // doesn't have an open time
		// );

		$stationOpenHours = array();
		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();

		foreach ($stations as $station) {
			// preprare request xml object
			$requestXML = $this->createGetOpenHoursRequestXML($station['code'], $pickUpDate);

			// prepare curl options
			$curlOptions = $this->defaultCurlOptions;
			$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();
			$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($curlOptions[CURLOPT_POSTFIELDS]);

		    $curlHandlers[$station['code']] = curl_init();
		    
		    curl_setopt_array($curlHandlers[$station['code']], $curlOptions);
		    curl_multi_add_handle($curlMultiHandler, $curlHandlers[$station['code']]);
		}

		do {
			curl_multi_select($curlMultiHandler);
		    curl_multi_exec($curlMultiHandler, $isRunning);
		} while ($isRunning > 0);

		// get content and remove handles.
		foreach ($curlHandlers as $stationCode => $curlHandler) {
		    $stationOpenHours[$stationCode] = $this->createGetOpenHoursArrayFromXML(curl_multi_getcontent($curlHandler));

		    curl_multi_remove_handle($curlMultiHandler, $curlHandler);
		}

		curl_multi_close($curlMultiHandler);

		// curl multi for get vehicles
		$curlMultiHandler = curl_multi_init();
		$curlHandlers     = array();

		foreach ($stationOpenHours as $stationCode => $stationOpenHour) {
			if ($stationOpenHour['status'] != 'OK') {
				$result['status'] = "Unable to get station's open hours.";

				return $result;
			} elseif (empty($stationOpenHour['data'])) {
				continue;
			}

			$stationOpenHourTime = $stationOpenHour['data']['time'];

			$pickUpTimeInSeconds       = strtotime('1970-01-01 ' . $pickUpTime . ':00 UTC');
			$stationOpenTimeInSeconds  = strtotime('1970-01-01 ' . $stationOpenHourTime['begin'] . ':00 UTC');
			$stationCloseTImeInSeconds = strtotime('1970-01-01 ' . $stationOpenHourTime['end'] . ':00 UTC');

			if (!($pickUpTimeInSeconds > $stationOpenTimeInSeconds) || !($pickUpTimeInSeconds < $stationCloseTImeInSeconds)) {
				continue;
			}

			// prepare request xml object
			$requestXML = $this->createGetVehiclesRequestXML($stationCode, $pickUpDate);

			// prepare curl options
			$curlOptions = $this->defaultCurlOptions;
			$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();
			$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($curlOptions[CURLOPT_POSTFIELDS]);

			$curlHandlers[$stationCode] = curl_init();

			curl_setopt_array($curlHandlers[$stationCode], $curlOptions);
			curl_multi_add_handle($curlMultiHandler, $curlHandlers[$stationCode]);
		}

		do {
			curl_multi_select($curlMultiHandler);
			curl_multi_exec($curlMultiHandler, $isRunning);
		} while ($isRunning > 0);

		foreach ($curlHandlers as $stationCode => $curlHandler) {
			$stationVehicles = $this->createGetVehiclesArrayFromXML(curl_multi_getcontent($curlHandler));

			curl_multi_remove_handle($curlMultiHandler, $curlHandler);

			if ($stationVehicles['status'] != 'OK') {
				$result['status'] = "Unable to get station's vehicles. $stationCode";

				return $result;
			}

			$result['data'][$stationCode] = $stationVehicles['data'];
		}

		curl_multi_close($curlMultiHandler);

		// echo 'Total execution time is ' . (time() - $timeStart) . 'seconds<br>';

		return $result;
	}

	/**
	* getOpenHours
	*
	* returns the list of all open hours for a station code and a date.
	* @author Jaime A. Hing III
	* @return array
	*
	*/
	public function getOpenHours($stationCode = '', $date = '')
	{
		// NOTE
		// - there are times when it only returns the return code attribute
		$result = array();

		// prepare request xml
		$requestXML = $this->createGetOpenHoursRequestXML($stationCode, $date);

		// prepare curl options
		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		// execute curl request and check response
		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetOpenHoursArrayFromXML($response);

		return $result;
	}

	/**
	* getVehicles
	*
	* return the list of all vehicles for a station code and a date.
	* @author Jaime A. Hing III
	* @return array
	*
	*/
	public function getVehicles($stationCode = '', $date = '')
	{
		$result = array();
		
		// prepare request xml
		$requestXML = $this->createGetVehiclesRequestXML($stationCode, $date);

		// prepare curl options
		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		// execute curl request and check response
		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetVehiclesArrayFromXML($response);

		return $result;
	}

	/**
	* getEquipmentList
	*
	* returns the list all equipment code for a station code and a date.
	* @author Jaime A. Hing III
	* @return array
	* 
	*/
	public function getEquipmentList($stationCode = '', $date = '')
	{
		$result = array();

		// prepare request xml
		$requestXML = $this->createGetEquipmentListRequestXML($stationCode, $date);

		// prepare curl options
		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		// execute curl request and check response
		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}
		
		$result = $this->createGetEquipmentListArrayFromXML($response);

		return $result;
	}

	/**
	* getStationSchedule
	*
	* returns complete station schedule as set in EC GreenWay database.
	* @author Jaime A. Hing III
	* @return array
	*
	*/
	public function getStationSchedule($stationCode = '')
	{
		$result = array();

		// prepare request xml
		$requestXML = $this->createGetStationScheduleRequestXML($station);

		// prepare curl options
		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		// execute curl request and check response
		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetStationScheduleArrayFromXML($response);

		return $result;
	}


	/**
	* getStation
	*
	* returns station details for a station code.
	* @author Jaime A. Hing III
	* @return array
	*
	*/
	public function getStation($stationCode = '')
	{
		$result = array();
		
		// prepare request xml
		$requestXML = $this->createGetStationRequestXML($stationCode);

		// prepare curl options
		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		// execute curl request and check response
		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}
		
		$result = $this->createGetStationArrayFromXML($response);

		return $result;
	}

	/**
	* getStations
	*
	* returns the list of station codes and station description for a city code.
	* @author Jaime A. Hing III
	* @return array
	*
	*/
	public function getStations($countryCode = '', $cityName = '')
	{
		$result = array();

		// prepare request xml
		$requestXML = $this->createGetStationsRequestXML($countryCode, $cityName);

		echo '<pre>';
		print_r($requestXML);
		exit();
		// prepare curl options
		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		// execute curl request and check response
		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}
		
		$result = $this->createGetStationsArrayFromXML($response);

		return $result;
	}

	/**
	* getCities
	*
	* returns the list of all countries.
	* @author Jaime A. Hing III
	* @return array
	*
	*/
	public function getCities($countryCode = '')
	{
		$result = array();
		
		// prepare request xml
		$requestXML = $this->createGetCitiesRequestXML($countryCode);

		// prepare curl options
		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		// execute curl request and check response
		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetCitiesArrayFromXML($response);

		return $result;
	}


	/**
	* getCountriesResidence
	*
	* returns the list of all countries.
	* @author Jaime A. Hing III
	* @return array
	*
	*/
	public function getCountriesResidence()
	{
		$result = array();

		// prepare request xml
		$requestXML = $this->createGetCountriesResidenceRequestXML();

		// prepare curl options
		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		// execute curl request and check response
		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetCountriesResidenceArrayFromXML($response);

		return $result;
	}

	/**
	* getCountries
	*
	* returns the list of all countries code where there is one or more Europcar station.
	* @author Jaime A. Hing III
	* @return array
	*
	*/
	public function getCountries()
	{
		$result = array();
		
		// prepare request xml
		$requestXML = $this->createGetCountriesRequestXML();

		// prepare curl options
		$curlOptions = $this->defaultCurlOptions;
		$curlOptions[CURLOPT_POSTFIELDS]['XML-Request'] = $requestXML->asXML();

		// execute curl request and check response
		$response = $this->executeCurl($curlOptions);

		if ($response === false) {
			$result['status'] = '';

			return $result;
		}

		$result = $this->createGetCountriesArrayFromXML($response);

		return $result;
	}

	/**
	* createBooking
	*
	* create a booking
	* @author Jaime A. Hing III
	* @return array
	*
	*/
	public function createBooking($params = array())
	{

		// contractID: 51271674
		// reservation		carCategory, contractID
		// checkout 		stationCode,date,time
		// checkin     	stationCode,date,time
		// meanOfPayment
		// driver			countryOfResidence, lastName, firstName*/
		// typeCode: CC | VCH | EP | CHQ | CSH | DEB*/
		$date = str_replace('-', '', $date);
		$serviceRequestName = 'bookReservation';

		// preprare request xml object
		$requestXML = $this->createRequestXML();

		$serviceRequestNode = $requestXML->addChild('serviceRequest');
		$serviceRequestNode->addAttribute('serviceCode', $serviceRequestName);

		$serviceParams   = $serviceRequestNode->addChild('serviceParameters');
		$reservationNode = $serviceParams->addChild('reservation');
		$reservationNode->addAttribute('carCategory', $params['vehicleCategoryCode']);
		$reservationNode->addAttribute('contractID', 51271674);
		
		$checkoutNode = $reservationNode->addChild('checkout');
		$checkoutNode->addAttribute('stationID', $params['stationCodes']['checkout']);
		$checkoutNode->addAttribute('date', $date);
		$checkoutNode->addAttribute('time', $time);

		$checkinNode = $reservationNode->addChild('checkin');
		$checkinNode->addAttribute('stationID', $params['stationCodes']['checkin']);
		$checkinNode->addAttribute('date', $date);
		$checkinNode->addAttribute('time', $time);

		// prepayment based on a business account(PPBA)
		$meanOfPaymentNode = $reservationNode->addChild('meanOfPayment');
		$meanOfPaymentNode->addAttribute('typeCode', 'CC');

		$driverNode = $reservationNode->addChild('driver');
	}
}