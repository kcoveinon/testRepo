<?php

class THController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
       
    }

    public function search(
        $pickUpDate, 
        $pickUpTime, 
        $returnDate, 
        $returnTime, 
        $pickUpLocationCode, 
        $returnLocationCode, 
        $countryCode, 
        $driverAge
    ){
        $result = array();
        $supplierApi = App::make('TH');
        
        $result = $supplierApi->searchVehicles(
            $pickUpDate, 
            $pickUpTime, 
            $returnDate, 
            $returnTime, 
            $pickUpLocationCode, 
            $returnLocationCode, 
            $countryCode, 
            $driverAge
        );

        return Response::json($result);
    }
    
    
    /*
        Test function to test getting locations
    */
    public function getlocations()
    {    
        $supplierApi = App::make('TH');
        $response = $supplierApi->getLocations();
        
        return Response::json($response);
    }


}
