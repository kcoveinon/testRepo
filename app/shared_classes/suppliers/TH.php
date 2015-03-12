<?php

class TH extends SupplierApi 
{    
    private $soapRequest;

    const TIME_LIMIT = 150;

    public function __construct()
    {
        $this->soapRequest = array(            
            'xmlns'         => 'http://www.opentravel.org/OTA/2003/05',
            'xmlns:xsi'     => "http://www.w3.org/2001/XMLSchema-instance",                
            'Target'        => Config::get('TH.api.target'),
            'Version'       => "2.000" ,
            'PrimaryLangID' => Config::get('TH.api.primaryLangID'),
            'POS'           => array(
                'Source' => array(                                            
                    'RequestorID' => array(
                        'Type' => Config::get('TH.api.requestorIdType'),
                        'ID'   => Config::get('TH.api.accountNumber'),
                    ),                                            
                )
            ),
        );
    }

    private function getSoapClient($url)
    {
        if (empty($url)) {
            return false;
        }
        
        try {

            $opts = array(
                'http'=>array(
                    'user_agent' => 'PHPSoapClient'
                    )
                );

            $context = stream_context_create($opts);
            $client = new SoapClient(Config::get('TH.api.url') . $url, array(
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'trace' => 1
            )); 

            return $client;

        } catch (Exception $e) { 
            echo $e->getMessage();
        }

    }

    /**
     * Source : http://stackoverflow.com/questions/21861077/soap-error-parsing-wsdl-couldnt-load-from-but-works-on-wamp
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
        $timeStart = time();
        $result = array( 
            'data' => array() 
        );

        set_time_limit(self::TIME_LIMIT);
                
        $client = $this->getSoapClient('RateService.svc?wsdl');

        if(empty($client)) {
            return false;
        }

        $requestCore = array(
            'VehAvailRQCore' =>array(
                'VehRentalCore' => array(
                    'PickUpDateTime' => date('Y-m-d\TH:i:s', strtotime( $pickUpDate . ' ' . $pickUpTime )),
                    'ReturnDateTime' => date('Y-m-d\TH:i:s', strtotime( $returnDate . ' ' . $returnTime )),    
                    'PickUpLocation' => array(
                        'LocationCode' => $pickUpLocationCode
                    ),
                    'ReturnLocation' => array(
                        'LocationCode' => $returnLocationCode
                    )
                ),               
                'VendorPrefs' => array(
                    'VendorPref' =>  array(
                        'Code' => 'ZT'
                    )
                )
            )
        );

        $data = $client->GetRates(
            array(
                'OTA_VehAvailRateRQ' => array_merge(
                    $this->soapRequest,
                    $requestCore                     
                )
            )
        );
                    
        if ( isset($data->OTA_VehAvailRateRS->Errors) ) {
            return array(
                'error' => $data->OTA_VehAvailRateRS->Errors->Error->ShortText
            );
        }

        $vehicleList = $data->OTA_VehAvailRateRS->VehAvailRSCore->VehVendorAvails->VehVendorAvail->VehAvails->VehAvail;

        foreach ($vehicleList as $key => $value) {
            $result['data'][] = array(                                
                'hasAirCondition' => $value->VehAvailCore->Vehicle->AirConditionInd,
                'transmission' => array(
                    'code'        => 'NA',
                    'description' => $value->VehAvailCore->Vehicle->TransmissionType,
                ),
                'baggageQty'   => $value->VehAvailCore->Vehicle->BaggageQuantity,
                'co2Qty'       => 'NA',
                'categoryCode' => $value->VehAvailCore->Vehicle->VendorCarType,
                'doorCount'    => 'NA',
                'name'         => $value->VehAvailCore->Vehicle->VehMakeModel->Name,
                'seats'        => $value->VehAvailCore->Vehicle->PassengerQuantity,
                'vehicleStatus' => array(
                    'code'        => 'NA',
                    'description' => $value->VehAvailCore->Status,
                ),
                'vehicleType' => array(
                    'code'        => $value->VehAvailCore->Vehicle->VehClass,
                    'description' => 'NA',
                ),
            );            
        }
        
        //$timeEnd = ((time() - $timeStart) . ' seconds');
        
        return $result;
    }   

    public function getLocations()
    {            
        $client = $this->getSoapClient('LocationService.svc?wsdl');

        if(empty($client)){
            return false;
        }

        // Set time limit for this one
        set_time_limit(self::TIME_LIMIT);

        $request = array(
            'Vendor' => array(
                'Code' => 'ZT'
            ), 
        );

        $data = $client->GetAllLocations(
            array(
                'OTA_VehLocSearchRQ' => array_merge(
                    $this->soapRequest,
                    $request
                )
            )
        );
                
        return $data;
    }

    public function sendSoapAsString()
    {

        $xml = '<?xml version="1.0" encoding="utf-8"?>'.
            '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
            ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"'.
            ' xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'.
                '<ConvertWeight xmlns="http://www.webserviceX.NET/">'.
                ' <Weight>20</Weight>'.
                '<FromUnit>Grams</FromUnit>'.
                '<ToUnit>Grains</ToUnit>'.
            '</soap:Envelope>';

        $url = "http://www.webserviceX.NET/ConvertWeight.asmx/ConvertWeight";

       
        $headers = array();
        array_push($headers, "Content-Type: applica/xml; charset=utf-8");
        array_push($headers, "Accept: text/xml");
        array_push($headers, "Cache-Control: no-cache");
        array_push($headers, "Pragma: no-cache");
        array_push($headers, "Pragma: no-cache");
        array_push($headers, "SOAPAction: \"http://www.webserviceX.NET/ConvertWeight\"");
        if($xml != null) {
            //curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml");
            array_push($headers, "Content-Length: 0" . strlen($xml));
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        //curl_setopt($ch, CURLOPT_USERPWD, "user_name:password"); /* If required */
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        print_r($xml);

    }

}