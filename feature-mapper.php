<?php

/******************************************************************************
*
*	Feature mapper. Maps features from legacvy DB to more streamlined versions
*	for storage in the new DB.
*
******************************************************************************/

class Pair
{
	private $group;
	private $name;

	public  function __construct ($g, $n)
	{
		if (!isset ($g) || !isset ($n))
			throw new Exception ("Cannot create group/name pair because at least one of the arguments is not set");
		if (strpos ($g, "Enter details"))
			throw new Exception ("Group deleted");
		if ($n == "No Pets")
			throw new Exception ("Feature deleted");

		$this->map ($g, $n);
	}

	public function toString ()
	{
		return $this->group . " => " . $this->name;
	}

	public function toArray ()
	{
		$a = array();

		$a ["name"]= $this->name;
		$a ["group"] = $this->group;
		return $a;
	}

	public function getGroup ()
	{
		return $this->group;
	}

	public function getName ()
	{
		return $this->name;
	}

	// Mapping functions follow

	// Replace group 'null' by 'Suitable for'
	private function replace_nulls ($s)
	{
		return strlen ($s) == 0 ? "Suitable For" : $s;
	}

	// General mappings
	function map ($group, $name)
	{
		$g = $group;
		$n = $name;

		// Map the name TV to group 'Furniture'
		if ($name == "TV")
			$g = "Furniture";
		else
			$g = $group;
	
		if ($name == "Gym")
			$g = "Sports/Leisure";
		else
		if (substr_compare ($name, "Bunk", 0, 4) == 0)
		{
			$n = "Bunk beds";
			$g = "Furniture";
		}
		else
		if (substr_compare ($name, "Double Bed", 0, 10) == 0)
		{
			$n = "Double beds";
			$g = "Furniture";
		}
		else
		if ($name == "Dinning Table and Chairs")
			$n = "Dining Table and Chairs";

		if ($group == "Heating" || $name == "Heating")
		{
			$g = "Services";
			$n = "Heating";
		}

		if ($name == "Double Fridge" || $name == "Single Fridge")
			$n = "Fridge";
		else
		if ($name == "Double Oven" || $name == "Single Oven")
			$n = "Oven";
		else
		if ($name == "Sporting Ovals")
			$g = "Sports/Leisure";
		else
		if ($name == "DVD Player" ||
		    $name == "Lounge Suite" ||
		    $name == "Outdoor Setting")
			$g = "Furniture";
		else
		if ($name == "AirConditioning")
			$n = "Air Conditioning";
	
	
		$this->group = ucfirst (strtolower( $this->replace_nulls ($g)));
		$this->name = ucfirst (strtolower( $this->replace_nulls ($n)));
	}
}

$res = array ();

function clearFeatureMap()
{
	$res = array ();
}

function addPair ($g, $n)
{
	global $res;
	try
	{
		$p = new Pair ($g, $n);
		$res [] = $p->toArray();
	}
	catch (Exception $e)
	{
    	echo 'Pair [', $g, ']:[', $n, '] not added: ',  $e->getMessage(), "\n";
	}
}

// Create feature Map. Expected is an array of maps, where each map has two
// elements, "name", and "group". We return the same structure containing the
// mapped grooups and names.

function createFeatureMap ($sqlResArr)
{
	global $res;

	$res = array();
//print_r ($sqlResArr);

	foreach ($sqlResArr as $elem)
		addPair ($elem ["group"], $elem ["name"]);

//echo "\nResults from SQL results array:\n";
//print_r ($res);

	return $res;
}

?>
