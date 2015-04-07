<?php

class AV extends SupplierApi
{
    private $userID;
    private $requestorID;
    private $password;
    private $target;
    private $primaryLang;
    private $version;
    private $authUrl;

    const TIME_LIMIT                 = 150;
    const VENDOR_CODE                = 'AV';
    const VENDOR_NAME                = 'Avis';
    const VENDOR_CLASS_CODE          = 'AV';

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

    /**
     *
     */
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

        $this->soapClient = new SoapClient( null, array(
            'location' => $this->apiLocation,
            'uri'      => $this->apiUri,
            'trace'    => 1,
            'features' => SOAP_LITERAL
        ) );
    }

    /**
     * @param        $locationCode
     * @param string $countryCode
     *
     * @return array
     */
    public function getLocations($locationCode, $countryCode = 'AU')
    {
        $timeStart = time();
        $soapVar = $this->otaRQNode(self::GET_LOCATION_SEARCH_ACTION, true).
                       "<VehLocSearchCriterion>
                            <Address>
                                <CityName>{$locationCode}</CityName>
                                <CountryName Code='{$countryCode}'/>
                            </Address>
                            <Radius DistanceMax='40' DistanceMeasure='Miles'/>
                        </VehLocSearchCriterion>
                        <Vendor Code='".self::VENDOR_NAME."'/>
                        <TPA_Extensions>
                            <SortOrderType>DESCENDING</SortOrderType>
                            <TestLocationType>NO</TestLocationType>
                            <LocationStatusType>OPEN</LocationStatusType>
                            <LocationType>RENTAL</LocationType>
                        </TPA_Extensions>".
                   $this->otaRQNode(self::GET_LOCATION_SEARCH_ACTION, false);

        if (!empty($locationCode)) {
            $params = new SoapVar($soapVar, XSD_ANYXML);
            $this->setSoapHeader();
            $this->soapClient->Request($params);
            $parseResponse = $this->parseResponse($this->soapClient->__getLastResponse());

            $response = [];
            if (isset($parseResponse->OTA_VehLocSearchRS->Errors)) {
                $response['status'] =  "Failed";
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
    )
    {
        $timeStart = time();

        $pickUpDateTime = $this->convertToDateTimeDefaultFormat($pickUpDate, $pickUpTime);
        $returnDateTime = $this->convertToDateTimeDefaultFormat($returnDate, $returnTime);

        $soapVar = $this->otaRQNode(self::SEARCH_VEHICLE_ACTION, true).
                        "<VehAvailRQCore Status='".self::DEFAULT_REQUEST_STATUS."'>
                            <VehRentalCore PickUpDateTime='{$pickUpDateTime}' ReturnDateTime='{$returnDateTime}'>
                                <PickUpLocation LocationCode='{$pickUpLocationCode}'/>
                                <ReturnLocation LocationCode='{$returnLocationCode}'/>
                            </VehRentalCore>
                            <VendorPrefs>
                                <VendorPref CompanyShortName='".self::VENDOR_NAME."'/>
                            </VendorPrefs>
                            <VehPrefs>
                                <VehPref ClassPref='Preferred' TransmissionPref='Preferred' TransmissionType='Automatic' TypePref='Preferred'>
                                    <VehType VehicleCategory='{$vehicleCategory}'/>
                                    <VehClass Size='{$vehicleClass}'/>
                                </VehPref>
                            </VehPrefs>
                        </VehAvailRQCore>
                        <VehAvailRQInfo>
                            <Customer>
                                <Primary>
                                    <CitizenCountryName Code='{$countryCode}'/>
                                </Primary>
                            </Customer>
                        </VehAvailRQInfo>".
                    $this->otaRQNode(self::SEARCH_VEHICLE_ACTION, false);

        if ($this->validateDate($pickUpDate, $pickUpTime)
            && $this->validateDate($returnDate, $returnTime)
        ) {
            $params = new SoapVar($soapVar, XSD_ANYXML);
            $this->setSoapHeader();
            $this->soapClient->Request($params);
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

        $soapVar = $this->otaRQNode(self::BOOK_VEHICLE_ACTION, true).
                       "<VehResRQCore Status='".self::DEFAULT_REQUEST_STATUS."'>
                            <VehRentalCore PickUpDateTime='{$pickUpDateTime}' ReturnDateTime='{$returnDateTime}'>
                                <PickUpLocation LocationCode='{$pickUpLocationCode}'/>
                                <ReturnLocation LocationCode='{$returnLocationCode}'/>
                            </VehRentalCore>
                            <Customer>
                                <Primary>
                                    <PersonName>
                                        <GivenName>{$firstName}</GivenName>
                                        <Surname>{$lastName}</Surname>
                                    </PersonName>
                                    <CitizenCountryName Code='{$countryCode}'/>
                                </Primary>
                            </Customer>
                            <VendorPref CompanyShortName='".self::VENDOR_NAME."'/>
                            <VehPref TypePref='Only' ClassPref='Only' TransmissionType='Automatic' TransmissionPref='Only' AirConditionPref='Only'>
                                <VehType VehicleCategory='{$vehicleCategory}'/>
                                <VehClass Size='{$vehicleClass}'/>
                            </VehPref>
                            <RateQualifier RateQualifier='2A'/>
                        </VehResRQCore>".
                   $this->otaRQNode(self::BOOK_VEHICLE_ACTION, false);

        if ($this->validateDate($pickUpDate, $pickUpTime)
            && $this->validateDate($returnDate, $returnTime)
        ) {
            $params = new SoapVar($soapVar, XSD_ANYXML);
            $this->setSoapHeader();
            $this->soapClient->Request($params);
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

        $soapVar = $this->otaRQNode(self::GET_BOOKING_INFO_ACTION, true).
                   "<VehRetResRQCore>
                        <UniqueID Type='14' ID='{$bookingId}'/>
                        <PersonName>
                            <Surname>{$surname}</Surname>
                        </PersonName>
                    </VehRetResRQCore>
                    <VehRetResRQInfo>
                        <Vendor CompanyShortName='".self::VENDOR_NAME."'/>
                    </VehRetResRQInfo>".
                   $this->otaRQNode(self::GET_BOOKING_INFO_ACTION, false);

        if (!empty($bookingId)) {
            $params = new SoapVar($soapVar, XSD_ANYXML);
            $this->setSoapHeader();
            $this->soapClient->Request($params);
            $parseResponse = $this->parseResponse($this->soapClient->__getLastResponse());

            $response = [];
            if (isset($parseResponse->OTA_VehRetResRS->Errors)) {
                $response['status'] =  "Failed";
                $response['data']   = (string) $parseResponse->OTA_VehRetResRS->Errors->Error;
            } else {
                $response = $parseResponse->OTA_VehRetResRS; // TODO need to send only required information
            }
        } else {
            $response =  ["result" => "Invalid Parameters"];
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

        $soapVar = $this->otaRQNode(self::MODIFY_BOOKING_ACTION, true).
                   "<VehModifyRQCore ModifyType='Modify'>
                        <UniqueID Type='14' ID='{$bookingId}'/>
                        <VehRentalCore PickUpDateTime='{$pickUpDateTime}' ReturnDateTime='{$returnDateTime}'>
                            <PickUpLocation LocationCode='{$pickUpLocationCode}'/>
                            <ReturnLocation LocationCode='{$returnLocationCode}'/>
                        </VehRentalCore>
                        <Customer>
                            <Primary>
                                <PersonName>
                                    <GivenName>{$firstName}</GivenName>
                                    <Surname>{$lastName}</Surname>
                                </PersonName>
                                <CitizenCountryName Code='{$countryCode}'/>
                            </Primary>
                        </Customer>
                        <VendorPref CompanyShortName='".self::VENDOR_NAME."'/>
                        <VehPref TypePref='Only' ClassPref='Only' TransmissionType='Automatic' TransmissionPref='Only' AirConditionPref='Only'>
                            <VehType VehicleCategory='{$vehicleCategory}'/>
                            <VehClass Size='{$vehicleClass}'/>
                        </VehPref>
                        <RateQualifier RateCategory='3' RateQualifier='2A'/>
                    </VehModifyRQCore>".
                   $this->otaRQNode(self::MODIFY_BOOKING_ACTION, false);

        if (!empty($bookingId)) {
            $params = new SoapVar($soapVar, XSD_ANYXML);
            $this->setSoapHeader();
            $this->soapClient->Request($params);
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

        $soapVar = $this->otaRQNode(self::CANCEL_BOOKING_ACTION, true).
                   "<VehCancelRQCore CancelType='Commit'>
                        <UniqueID Type='15' ID='{$bookingId}'/>
                        <PersonName>
                            <Surname>{$surname}</Surname>
                        </PersonName>
                    </VehCancelRQCore>
                    <VehCancelRQInfo>
                        <Vendor CompanyShortName='".self::VENDOR_NAME."'/>
                    </VehCancelRQInfo>".
                   $this->otaRQNode(self::CANCEL_BOOKING_ACTION, false);

        if (!empty($bookingId)) {
            $params = new SoapVar($soapVar, XSD_ANYXML);
            $this->setSoapHeader();
            $this->soapClient->Request($params);
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

        $soapVar = $this->otaRQNode(self::GET_RATE_RULE_ACTION, true).
                   "<RentalInfo>
                        <VehRentalCore PickUpDateTime='{$pickUpDateTime}' ReturnDateTime='{$returnDateTime}'>
                            <PickUpLocation LocationCode='{$pickUpLocationCode}' />
                            <ReturnLocation LocationCode='{$returnLocationCode}' />
                        </VehRentalCore>
                        <VehicleInfo TypePref='Only' TransmissionPref='Only' TransmissionType='Automatic' AirConditionPref='Only' ClassPref='Only'>
                            <VehType VehicleCategory='{$vehicleCategory}'/>
                            <VehClass Size='{$vehicleClass}'/>
                        </VehicleInfo>
                        <RateQualifier RateQualifier='2A'/>
                        <CustomerID Type='1' ID='{$countryCode}'/>
                    </RentalInfo>".
                   $this->otaRQNode(self::GET_RATE_RULE_ACTION, false);

        if ($this->validateDate($pickUpDate, $pickUpTime)
            && $this->validateDate($returnDate, $returnTime)
        ) {
            $params = new SoapVar($soapVar, XSD_ANYXML);
            $this->setSoapHeader();
            $this->soapClient->Request($params);

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
            return "<{$action} Version='{$this->version}'
                    SequenceNmbr='".self::DEFAULT_SEQUENCE_NUMBER."'
                    MaxResponses='".self::DEFAULT_MAX_RESPONSE."'
                    xmlns:xsi='".self::DEFAULT_XMLNS_XSI."'>
                    <POS>
                        <Source>
                            <RequestorID Type='".self::DEFAULT_REQUEST_TYPE."' ID='{$this->requestorID}' />
                        </Source>
                    </POS>";
        } else {
            return "</{$action}>";
        }
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