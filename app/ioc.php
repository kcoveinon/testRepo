<?php

$supplierCodes = Config::get('supplier.codes');

foreach ($supplierCodes as $supplierCode) {
	$supplierApi = new $supplierCode();

	App::instance($supplierCode, $supplierApi);
}