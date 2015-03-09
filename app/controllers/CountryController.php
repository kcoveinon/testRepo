<?php

class CountryController extends BaseController 
{	
	public function __construct()
	{
		parent::__construct();
	}

	/*public function anyGet($supplierCode = '') {

		if(Request::isMethod('post')) {
			$supplierCode = Input::get('supplierCode', '');
		}

		$result = array();
		$status = '';
		$error = '';

		if(Supplier::isValid($supplierCode)) {
			$status = 'OK';
		} else {
			$status = 'FAILED';
			$result['error'] = 'Invalid supplier code';
		}

		$result['status'] = $status;
		return Response::json($result);
	}*/

	public function anyGet()
	{
		$result = array();
		
		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->getCountries();
		}

		return Response::json($result);
	}

	public function anyGetResidence()
	{
		$result = array();

		foreach ($this->supplierCodes as $supplierCode) {
			$supplierApi = App::make($supplierCode);

			$result = $supplierApi->getCountriesResidence();
		}

		/*if(Request::isMethod('post')) {
			$supplierCode = Input::get('supplierCode', '');
		}

		if(!empty($supplierCode)) {
			$supplierCode = strtoupper($supplierCode);
		}
		$result = array();
		$status = '';
		$error = '';

		if(Supplier::isValid($supplierCode)) {											
			$functionName = debug_backtrace()[0]['function'];
			$functionName = lcfirst(str_replace('any', '', $functionName));
			$result = $supplierCode::getInstance()->$functionName();
		} else {				
			$result['error'] = 'Invalid supplier code';
			$result['status'] = 'FAILED';
		}*/

		return Response::json($result);
	}
}