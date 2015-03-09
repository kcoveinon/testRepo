<?php

class BaseController extends Controller 
{
	protected $supplierCodes;

	public function __construct() 
	{
		// fetch from the db later on
		$this->supplierCodes = Config::get('supplier.codes');
	}

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}
}
