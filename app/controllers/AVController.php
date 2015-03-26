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

		$result = $supplierApi->getVehicleRates();
		return Response::json($result);
	}

	public function locations()
	{
		$supplierApi = App::make($this->supplierCode);

		$result = $supplierApi->getLocations();

		return Response::json($result);
	}
}

