<?php

class AcrissHelper {
	private $categories;
	private $types;
	private $transmissions;
	private $drives;
	private $fuels;
	private $airConditions;

	public function __construct()
	{
		$this->categories = array(
			'M' => 'Mini',
			'N' => 'Mini Elite',
			'E' => 'Economy',
			'H' => 'Economy Elite',
			'C' => 'Compact',
			'D' => 'Compact Elite',
			'I' => 'Intermediate',
			'J' => 'Intermediate Elite',
			'S' => 'Standard',
			'R' => 'Standard Elite',
			'F' => 'Fullsize',
			'G' => 'Fullsize Elite',
			'P' => 'Premium',
			'U' => 'Premium Elite',
			'L' => 'Luxury',
			'W' => 'Luxury Elite',
			'O' => 'Oversize',
			'X' => 'Special',
		);

		$this->types = array(
			'B' => '2-3 Door',
			'C' => '2/4 Door',
			'D' => '4-5 Door',
			'W' => 'Wagon/Estate',
			'V' => 'Passenger Van',
			'L' => 'Limousine',
			'S' => 'Sport',
			'T' => 'Convertible',
			'F' => 'SUV',
			'J' => 'Open Air All Terrain',
			'X' => 'Special',
			'P' => 'Pick up Regular Cab',
			'Q' => 'Pick up Extended Cab',
			'Z' => 'Special Offer Car',
			'E' => 'Coupe',
			'M' => 'Monospace',
			'R' => 'Recreational Vehicle',
			'H' => 'Motor Home',
			'Y' => '2 Wheel Vehicle',
			'N' => 'Roadster',
			'G' => 'Crossover',
			'K' => 'Commercial Van/Truck',
		);

		$this->transmissions = array(
			'M' => 'Manual',
			'N' => 'Manual',
			'C' => 'Manual',
			'A' => 'Auto',
			'B' => 'Auto',
			'D' => 'Auto',
		);

		$this->drives = array(
			'M' => 'Unspecified Drive',
			'N' => '4WD',
			'C' => 'AWD',
			'A' => 'Unspecified Drive',
			'B' => '4WD',
			'D' => 'AWD',
		);

		$this->fuels = array(
			'R' => 'Unspecified Fuel/Power',
			'N' => 'Unspecified Fuel/Power',
			'D' => 'Diesel',
			'Q' => 'Diesel',
			'H' => 'Hybrid',
			'I' => 'Hybrid',
			'E' => 'Electric',
			'C' => 'Electric',
			'L' => 'LPG/Compressed Gas',
			'S' => 'LPG/Compressed Gas',
			'A' => 'Hydrogen',
			'B' => 'Hydrogen',
			'M' => 'Multi Fuel/Power',
			'F' => 'Multi Fuel/Power',
			'V' => 'Petrol',
			'Z' => 'Petrol',
			'U' => 'Ethanol',
			'X' => 'Ethanol',
		);

		$this->airConditions = array(
			'R' => true,
			'N' => true,
			'D' => true,
			'Q' => false,
			'H' => true,
			'I' => false,
			'E' => true,
			'C' => false,
			'L' => true,
			'S' => false,
			'A' => true,
			'B' => false,
			'M' => true,
			'F' => false,
			'V' => true,
			'Z' => false,
			'U' => true,
			'X' => false,
		);
	}

	public function getCategories($code = '')
	{
		if (!empty($code)) {
			return isset($this->categories[$code]) ? $this->categories[$code] : '';
		}

		return $this->categories;
	}

	public function getTypes($code = '')
	{
		if (!empty($code)) {
			return isset($this->types[$code]) ? $this->types[$code] : '';
		}

		return $this->types;
	}

	public function getTransmissions($code = '')
	{
		if (!empty($code)) {
			return isset($this->transmissions[$code]) ? $this->transmissions[$code] : '';
		}

		return $this->transmissions;
	}

	public function getDrives($code = '')
	{
		if (!empty($code)) {
			return isset($this->drives[$code]) ? $this->drives[$code] : '';
		}

		return $this->drives;
	}

	public function getFuels($code = '')
	{
		if (!empty($code)) {
			return isset($this->fuels[$code]) ? $this->fuels[$code] : '';
		}

		return $this->fuels;
	}

	public function getAirConditions($code = '')
	{
		if (!empty($code)) {
			return isset($this->airConditions[$code]) ? $this->airConditions[$code] : '';
		}

		return $this->airConditions;
	}

	public function expandCode($code)
	{
		$expandedCode = array();

		$expandedCode['category']     = $this->getCategories($code[0]);
		$expandedCode['type']         = $this->getTypes($code[1]);
		$expandedCode['transmission'] = $this->getTransmissions($code[2]);
		$expandedCode['drive']        = $this->getDrives($code[2]);
		$expandedCode['fuel']         = $this->getFuels($code[3]);
		$expandedCode['airCondition'] = $this->getAirConditions($code[3]);

		return $expandedCode;
	}
}