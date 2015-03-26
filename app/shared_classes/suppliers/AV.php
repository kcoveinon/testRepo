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
        $this->apiURI              = Config::get($this->supplierCode . '.api.uri');
        $this->apiLocation         = Config::get($this->supplierCode . '.api.location');
        $this->apiUsernameVariable = Config::get($this->supplierCode . '.api.usernameVariable');
        $this->apiPasswordVariable = Config::get($this->supplierCode . '.api.passwordVariable');
        $this->apiUsername         = Config::get($this->supplierCode . '.api.username');
        $this->apiPassword         = Config::get($this->supplierCode . '.api.password');
        $this->requestorType       = Config::get($this->supplierCode . '.api.requestorType');
        $this->requestorID         = Config::get($this->supplierCode . '.api.requestorID');
        $this->primaryLang         = Config::get($this->supplierCode . '.api.primaryLang');
        $this->version             = Config::get($this->supplierCode . '.api.version');

        $this->soapClient = new SoapClient(
            null,
            array(
                'location'   => $this->apiLocation,
                'uri'        => $this->apiURI,
                'trace'      => 1,
                'use'        => SOAP_LITERAL,
                'cache_wsdl' => WSDL_CACHE_NONE
            )
        );        
    }

    /**
     * @return bool
     */
    public function getVehicleRates()
    {
        $xml = "<SOAP-ENV:Envelope xmlns:SOAP-ENV='http://www.w3.org/2001/12/soap-envelope' ReqRespVersion='medium' xmlns:xsi='http://www.w3.org/1999/XMLSchema-instance' xmlns:xsd='http://www.w3.org/1999/XMLSchema'>
                    <SOAP-ENV:Header>
                        <ns:credentials xmlns:ns='http://wsg.avis.com/wsbang/authInAny'>
                            <ns:userID ns:encodingType='xsd:string'>vroom</ns:userID>
                            <ns:password ns:encodingType='xsd:string'>09vroom15</ns:password>
                        </ns:credentials>
                        <ns:WSBang-Roadmap xmlns:ns='http://wsg.avis.com/wsbang'/>
                    </SOAP-ENV:Header>
                    <SOAP-ENV:Body> 
                        <ns:Request xmlns:ns='http://wsg.avis.com/wsbang'>
                            <OTA_VehAvailRateRQ Version='1.0' MaxResponses='10' xmlns:xsi='http://www.w3.org/2008/XMLSchema-instance'>
                                  <POS>
                                    <Source>
                                      <RequestorID ID='vroom' Type='1'/>
                                    </Source>
                                  </POS>                                  
                                <VehAvailRQCore Status='All'>
                                    <VehRentalCore PickUpDateTime='2015-05-14T09:00:00' ReturnDateTime='2015-05-15T09:00:00'>
                                        <PickUpLocation LocationCode='JFK'/>
                                        <ReturnLocation LocationCode='JFK'/>
                                    </VehRentalCore>
                                    <VendorPrefs>
                                        <VendorPref CompanyShortName='Avis'/>
                                    </VendorPrefs>
                                <VehPrefs>
                                    <VehPref ClassPref='Preferred' TransmissionPref='Preferred' TransmissionType='Automatic' TypePref='Preferred'>
                                        <VehType VehicleCategory='1'/>
                                        <VehClass Size='3'/>
                                    </VehPref>
                                </VehPrefs>
                                </VehAvailRQCore>
                                <VehAvailRQInfo>
                                    <Customer>
                                        <Primary>
                                            <CitizenCountryName Code='US'/>
                                        </Primary>
                                    </Customer>
                                </VehAvailRQInfo>                                
                            </OTA_VehAvailRateRQ>
                        </ns:Request>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>"; 

        $params = new SoapVar($xml, \XSD_ANYXML);

        return $this->soapClient->__SoapCall('OTA_VehAvailRateRQ', array($params));
    }


    /**
     * @return bool
     */
    public function getLocationSearch()
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
                            <OTA_VehLocSearchRQ xmlns:xsi='http://www.w3.org/2008/XMLSchema-instance' MaxResponses='10' Version='1.0'>
                                <POS>
                                    <Source/>
                                </POS>
                            <VehLocSearchCriterion>
                                <Address>
                                    <AddressLine>52 Narra Drive</AddressLine>
                                    <CityName>Antiplo City</CityName>
                                    <PostalCode>1870</PostalCode>
                                    <County>Philippines</County>
                                    <CountryName Code='PH'/>
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
                </SOAP-ENV:Envelope>"; 


        $params = new SoapVar(trim($xml), XSD_ANYXML);

        return $this->soapClient->VehAvailRate($params);
    }

}
