<?php

class VehicleClassificationController extends BaseController
{
	private $categories;
	private $types;
	private $transmissions;
	private $fuelTypes;
	private $classifications;

	public function __construct()
	{
		$this->categories      = $this->getCategories();
		$this->types           = $this->getTypes();
		$this->transmissions   = $this->getTransmissions();
		$this->fuelTypes       = $this->getFuelTypes();
		$this->classifications = array(
			'category'     => $this->categories,
			'type'         => $this->types,
			'transmission' => $this->transmissions,
			'fuelType'     => $this->fuelTypes
		);
	}

	public function anyGetCategories()
	{
		return Response::json(array(
			'categories' => $this->categories	
		));
	}

	public function anyGetTypes()
	{
		return Response::json(array(
			'types' => $this->types
		));
	}

	public function anyGetTransmissions()
	{
		return Response::json(array(
			'transmissions' => $this->transmissions
		));
	}

	public function anyGetFuelTypes()
	{
		return Response::json(array(
			'fuelTypes' => $this->fuelTypes
		));
	}

	public function anyGet()
	{
		return Response::json(array(
			'classifications' => $this->classifications
		));
	}

	private function getCategories()
	{
		return array(
			array('code' => 'M', 'description' => 'Mini'),
			array('code' => 'N', 'description' => 'Mini Elite'),
			array('code' => 'E', 'description' => 'Economy'),
			array('code' => 'H', 'description' => 'Economy Elite'),
			array('code' => 'C', 'description' => 'Compact'),
			array('code' => 'D', 'description' => 'Compact Elite'),
			array('code' => 'I', 'description' => 'Intermediate'),
			array('code' => 'J', 'description' => 'Intermediate Elite'),
			array('code' => 'S', 'description' => 'Standard'),
			array('code' => 'R', 'description' => 'Standard Elite'),
			array('code' => 'F', 'description' => 'Fullsize'),
			array('code' => 'G', 'description' => 'Fullsize Elite'),
			array('code' => 'P', 'description' => 'Premium'),
			array('code' => 'U', 'description' => 'Premium Elite'),
			array('code' => 'L', 'description' => 'Luxury'),
			array('code' => 'W', 'description' => 'Luxury Elite'),
			array('code' => 'O', 'description' => 'Oversize'),
			array('code' => 'X', 'description' => 'Special'),
		);
	}

	private function getTypes()
	{
		return array(
			array('code' => 'B', 'description' => '2-3 Door'),
			array('code' => 'C', 'description' => '2/4 Door'),
			array('code' => 'D', 'description' => '4-5 Door'),
			array('code' => 'W', 'description' => 'Wagon/Estate'),
			array('code' => 'V', 'description' => 'Passenger Van'),
			array('code' => 'L', 'description' => 'Limousine'),
			array('code' => 'S', 'description' => 'Sport'),
			array('code' => 'T', 'description' => 'Convertible'),
			array('code' => 'F', 'description' => 'SUV'),
			array('code' => 'J', 'description' => 'Open Air All Terrain'),
			array('code' => 'X', 'description' => 'Special'),
			array('code' => 'P', 'description' => 'Pick up Regular Cab'),
			array('code' => 'Q', 'description' => 'Pick up Extended Cab'),
			array('code' => 'Z', 'description' => 'Special Offer Car'),
			array('code' => 'E', 'description' => 'Coupe'),
			array('code' => 'M', 'description' => 'Monospace'),
			array('code' => 'R', 'description' => 'Recreational Vehicle'),
			array('code' => 'H', 'description' => 'Motor Home'),
			array('code' => 'Y', 'description' => '2 Wheel Vehicle'),
			array('code' => 'N', 'description' => 'Roadster'),
			array('code' => 'G', 'description' => 'Crossover'),
			array('code' => 'K', 'description' => 'Commercial Van/Truck'),
		);
	}

	private function getTransmissions()
	{
		return array(
			array('code' => 'M', 'description' => 'Manual Unspecified Drive'),
			array('code' => 'N', 'description' => 'Manual 4WD'),
			array('code' => 'C', 'description' => 'Manual AWD'),
			array('code' => 'A', 'description' => 'Auto Unspecified Drive'),
			array('code' => 'B', 'description' => 'Auto 4WD'),
			array('code' => 'D', 'description' => 'Auto AWD'),
		);
	}

	private function getFuelTypes()
	{
		return array(
			array('code' => 'R', 'description' => 'Unspecified Fuel/Power With Air'),
			array('code' => 'N', 'description' => 'Unspecified Fuel/Power Without Air'),
			array('code' => 'D', 'description' => 'Diesel Air'),
			array('code' => 'Q', 'description' => 'Diesel No Air'),
			array('code' => 'H', 'description' => 'Hybrid Air'),
			array('code' => 'I', 'description' => 'Hybrid No Air'),
			array('code' => 'E', 'description' => 'Electric Air'),
			array('code' => 'C', 'description' => 'Electric No Air'),
			array('code' => 'L', 'description' => 'LPG/Compressed Gas Air'),
			array('code' => 'S', 'description' => 'LPG/Compressed Gas No Air'),
			array('code' => 'A', 'description' => 'Hydrogen Air'),
			array('code' => 'B', 'description' => 'Hydrogen No Air'),
			array('code' => 'M', 'description' => 'Multi Fuel/Power Air'),
			array('code' => 'F', 'description' => 'Multi Fuel/Power No Air'),
			array('code' => 'V', 'description' => 'Petrol Air'),
			array('code' => 'Z', 'description' => 'Petrol No Air'),
			array('code' => 'U', 'description' => 'Ethanol Air'),
			array('code' => 'X', 'description' => 'Ethanol No Air'),
		);
	}
}