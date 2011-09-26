<?php

//**********************************************************************
//
//  View what is in the transaction log.
//
//  History:
//  -------
//  1.0     12Sep11 GB      Original
//
//**********************************************************************

require_once "table.php";
require_once "sql.php";

//**********************************************************************
//
//  Some helpers.
//
//**********************************************************************

function error ($msg)
{
    echo ($msg);
}

function warning ($msg)
{
    echo ($msg);
}

function info ($msg)
{
    echo ($msg);
}

//**********************************************************************
//
//  Print JSON encoded array of rows
//
//**********************************************************************

function toJson($rows)
{
	return json_encode($rows);
}

//**********************************************************************
//
//  Print array of rows
//
//**********************************************************************

function printRows($rows)
{
	$nRows = 0;
	foreach ($rows as $row)
	{
		$nRows++;
		printCols($row);
	}
}

function printCols($row)
{
	$nCols = 0;

	$colNames = array_keys ($row);
	foreach ($row as $col)
	{
		if ($nCols > 0)
			echo "|";
		echo (sprintf ("%s=>%s", $colNames[$nCols], $col));
		$nCols++;
	}
	echo "\n";
}

//**********************************************************************
//
//  Delete one property
//
//**********************************************************************

function remove ($pid)
{
	info (sprintf ("Remove property %d", $pid));
	printf ("%s\n", toJson (array ("legacy_property_id" => $pid)));
}

//**********************************************************************
//
//  Process one property
//
//**********************************************************************

function process ($pid)
{
	info (sprintf ("Update property %d", $pid));
	$propertyTable = new Table ('dbo.Property');
	$addressTable = new Table ('dbo.Address');
	$personTable = new Table ('dbo.Person');
	$photoTable = new Table ('dbo.PropertyImages');
	$inspectionTable = new Table ('dbo.Inspections');
	$inspectionDetailsTable = new Table ('dbo.InspectionDetails');
	$featureRelTable = new Table ('dbo.PropertyFeatureRel');

	// Read the property. We expect only one row.

	$propertyTable->executeSQL (getSqlForProperty($pid));
	$propertyRows = $propertyTable->asArray();

	// The description and byline may contain non-utf8 if the user wrote it
	// using MS Word. The json encoder cannot handle that, so we need to force
	// utf8 encoding:

	if (count($propertyRows) == 0)
	{
		info ("Property not found");
		return;
	}

	$personTable->executeSQL (getSqlForContact ($pid));
	$contactRows = $personTable->asArray();

	$personTable->executeSQL (getSqlForLandlord ($pid));
	$landlordRows = $personTable->asArray();
//echo "LANDLORD\n";
//print_r (array_values ($landlordRows));

	$personTable->executeSQL (getSqlForCompany ($pid));
	$companyRows = $personTable->asArray();
//echo "COMPANY\n";
//print_r (array_values ($companyRows));

	// Get the photo table entries

	$photoTable->executeSQL (getSqlForPhotos($pid));
	$photoRows = $photoTable->asArray();

	// Get the inspection table entries

	$inspectionTable->read (sprintf ("PropertyId=%d", $pid));
	$inspectionRows = $inspectionTable->asArray();

	// Get the inspection detail table entries

	$inspectionDetailsTable->read (sprintf ("intPropertyId=%d", $pid));
	$inspectionDetailsRows = $inspectionDetailsTable->asArray();

	// Get the features detail table entries

	$featureRelTable->executeSQL (getSqlForFeatures($pid));
	$featureRelRows = $featureRelTable->asArray();

	// Get the inspection date/times

	$inspectionTable->executeSQL (getSqlForInspectionTimes($pid));
	$inspectionTimes = $inspectionTable->asArray();

	$theProperty = array_shift ($propertyRows);

    // If there are no properties with id $pid the property is no
    // longer there, skip.

	if (isset ($theProperty ['byline']))
		$theProperty ['byline'] =
			utf8_encode ( $theProperty ['byline']);

	if (isset ($theProperty ['description']))
		$theProperty ['description'] =
			utf8_encode ( $theProperty ['description']);
//print_r ($theProperty);
	$theProperty ['contact'] = array_shift ($contactRows);

	if (count ($landlordRows) > 0)
	{
		$theProperty ['landlord'] = array_shift ($landlordRows);
	}
	if (count ($companyRows) > 0)
	{
		$theProperty ['company'] = array_shift ($companyRows);
	}
	$theProperty ['photos'] = array_values ($photoRows);
	$theProperty ['features'] = array_values ($featureRelRows);
	$theProperty ['inspection_times'] = array_values ($inspectionTimes);

	echo toJson ($theProperty), "\n";
}

//**********************************************************************
//
//  Main
//
//**********************************************************************

function main()
{
	//$lvl = Logger::$DEBUG;
	$lvl = Logger::$ERROR;

	$transactionLog = new Table ('dbo.PropertyTransactionLog',
            array ('created_at', 'PropertyId'), 'PropertyId');

	// Remember the date/time of the last row for deletion of transactions

	$sql = "select max (created_at) as count from dbo.PropertyTransactionLog";
	$transactionLog->executeSQL($sql);
	$row = array_shift ($transactionLog->asArray());
	$lastdt = $row['count'];

	$sql = "select max(created_at) as created_at, PropertyId, [action]
			from dbo.PropertyTransactionLog
			group by PropertyId,[action]
			order by created_at,[action]";

	$transactionLog->executeSQL($sql);
	$rows = $transactionLog->asArray();

	$n = 0;
	foreach ($rows as $row)
	{
		$n++;
		$pid = $row ['PropertyId'];
		$action = $row ['action'];
		info (sprintf ("%d: %s\n", $pid, $action));
	}
	info (sprintf ("%d transactions found\n", $n));
}

main();

?>
