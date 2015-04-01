<?php

class AVController extends BaseController
{
	private $supplierCode;

	public function __construct()
	{
		parent::__construct();

		$this->supplierCode = 'AV';
	}

	public function ping()
	{
		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->ping1();
		// return Response::json($result);
		// $xml = simplexml_load_string($result);
		// $xml->registerXPathNamespace('SOAP-ENV', 'http://schemas.xmlsoap.org/soap/envelope/');
		// $xml->registerXPathNamespace('auth', 'http://wsg.avis.com/wsbang');
		// $nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/auth:Response');
	}

	public function locations()
	{
		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getLocations();

		return Response::json($result);
	}
}

