<?php 

class RSController extends BaseController
{
    const DEFAULT_SUPPLIER_CODE = "RS";

    public function searchVehicles()
    {
        $redSpotAPI = App::make(self::DEFAULT_SUPPLIER_CODE);
        $result = $redSpotAPI->search();
        return Response::json($result);
    }

}
