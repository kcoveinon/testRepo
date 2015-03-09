<?php

class CityController extends BaseController
{
	public function __construct() 
	{
		parent::__construct();
	}

	public function anyGet($countryCode = '') 
	{
		if (Request::isMethod('post')) {
			$countryCode = Input::get('countryCode', '');
		}

		$result = array();
		
		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->getCities($countryCode);
		}

		return Response::json($result);
	}
}