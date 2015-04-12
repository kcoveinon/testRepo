<?php

class LocationController extends BaseController
{
	public function anyGetSuppliersPopularDepotPair($pickUpLocationId, $returnLocationId)
	{
		$timeStart = time();

		$result = array();

		$depotTableName         = Depot::getTableName();
		$supplierTableName      = Supplier::getTableName();
		$locationDepotTableName = LocationDepot::getTableName();

		$supplierPickUpDepotCodes = $this->getLocationSupplierPopularDepots($pickUpLocationId);

		if ($returnLocationId == $pickUpLocationId) {
			$supplierReturnDepotCodes = $supplierPickUpDepotCodes;
		} else {
			$supplierReturnDepotCodes = $this->getLocationSupplierPopularDepots($returnLocationId);
		}

		foreach ($this->supplierCodes as $supplierCode) {
			if (
				isset($supplierPickUpDepotCodes[$supplierCode]) 
				&& isset($supplierReturnDepotCodes[$supplierCode])
			) {
				reset($supplierPickUpDepotCodes[$supplierCode]);
				reset($supplierReturnDepotCodes[$supplierCode]);

				$pickUpDepotCode = key($supplierPickUpDepotCodes[$supplierCode]);
				$returnDepotCode = key($supplierReturnDepotCodes[$supplierCode]);

				$supplierPopularDepotPair[$supplierCode] = array(
					'pickUpDepot' => $pickUpDepotCode,
					'returnDepot' => $returnDepotCode,
				);
			}
		}

		$result['status'] = 'OK';

		$result['data'] = $supplierPopularDepotPair;

		return Response::json($result);
	}

	private function getLocationSupplierPopularDepots($locationId)
	{
		$depotTableName         = Depot::getTableName();
		$supplierTableName      = Supplier::getTableName();
		$locationDepotTableName = LocationDepot::getTableName();

		$depots = DB::table($depotTableName)
			->join($locationDepotTableName, $locationDepotTableName . '.depotID', '=', $depotTableName . '.depotID')
			->join($supplierTableName, $depotTableName . '.supplierID', '=', $supplierTableName . '.supplierID')
			->select($depotTableName . '.depotCode', $depotTableName . '.popularity', $supplierTableName . '.supplierCode')
			->where($locationDepotTableName . '.locationID', '=', $locationId)
			->get();

		foreach ($depots as $depot) {
			$supplierCode = $depot->supplierCode;
			$depotCode    = $depot->depotCode;
			$popularity   = $depot->popularity;

			$suppliersDepots[$supplierCode][$depotCode] = $popularity;
		}

		foreach ($suppliersDepots as &$supplierDepots) {
			arsort($supplierDepots);
		}

		return $suppliersDepots;
	}
}