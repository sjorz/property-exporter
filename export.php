<?php

//**********************************************************************
//
//  Main script. Sets up connection and runs the required SQLs.
//  Outputs Jason objects to stdout for piping through to new DB processor.
//
//  History:
//  -------
//  1.0     20Jul11 GB      Original
//
//**********************************************************************

require_once "table.php";
require_once "sql.php";
require_once "feature-mapper.php";

$utf8_errors = array();
$report = '';

//**********************************************************************
//
//  Some helpers.
//
//**********************************************************************

function error ($msg)
{
    Logger::logError ($msg);
}

function warning ($msg)
{
    Logger::logWarning ($msg);
}

function info ($msg)
{
    Logger::logInfo ($msg);
}

//**********************************************************************
//
//  Reporting
//
//**********************************************************************

function reportAppend ($msg)
{
	global $report;

	$report .= "\n" . $msg;
}

function reportWrite ($fn)
{
	global $report;
	$res = false;

    if ($fp = fopen ($fn, "w+"))
    {
        $res = fputs ($fp, $report);
		fclose ($fp);
        chmod ($fn, 0666);
		if ($res)
        	info ('Report written');
		else
        	error ('Could not write report');
    }
    return $res;
}

//**********************************************************************
//
//  Print JSON encoded array of rows
//
//**********************************************************************

function toJson($pid, $rows)
{
	global $utf8_errors;

	$res = json_encode($rows);

	if (json_last_error() === JSON_ERROR_UTF8)
	{
    	error ("JSON UTF-8 Error in property [%d]", $pid);
		$utf8_errors[] = $pid;
	}

	return $res;
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
	printf ("%s\n", toJson ($pid, array ("legacy_property_id" => $pid)));
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

	// Clear feature map

	clearFeatureMap();

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

	// Get the laundry features entries and merge with feature rel rows

	$featureRelTable->executeSQL ( getSqlForLaundryFeature($pid));
	$featureLaundryRows = $featureRelTable->asArray();

	// Get the features detail table entries

	$featureRelTable->executeSQL (getSqlForFeatures($pid));
	$aa = $featureRelTable->asArray();
	if (count ($aa) > 0)
		$aa = createFeatureMap ($aa);
	$featureRelRows = array_merge($aa, $featureLaundryRows);

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

	echo toJson ($pid, $theProperty), "\n";
}

//**********************************************************************
//
//  Main
//
//**********************************************************************

function main($keepTransactionLog)
{
	//$lvl = Logger::$DEBUG;
	$lvl = Logger::$ERROR;
	Logger::open ('export.log', $lvl);

	info ("************************");
	info ("Start integration script");
	info ("************************");

	if ($keepTransactionLog)
		info ("The transaction log will be kept");
	else
		info ("The transaction log will be cleared");

	global $utf8_errors;

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
	$nDeleted = 0;
	$nAdded = 0;
	foreach ($rows as $row)
	{
		$n++;
		$pid = $row ['PropertyId'];
		$action = $row ['action'];
		if ($action == 'D')
		{
			remove ($pid);
			$nDeleted++;
		}
		else
		{
			process ($pid);
			$nAdded++;
		}
	}

	// Delete transactions we just processed:

	$sql = sprintf ("delete from dbo.PropertyTransactionLog where created_at <= '%s'", $lastdt);
	if (!$keepTransactionLog)
	{
		$transactionLog->executeSQL($sql);
		info ("Deleted old transactions");
	}
	else
		info ("KEPT Deleted old transactions");

	unset ($transactionLog);

	if (count($utf8_errors) > 0)
	{
		info ("UTF8 Conversion errors in the folowing property id's:");
		foreach ($utf8_errors as $s)
			info (sprintf("%d", $s));
	}

	reportAppend ("LEGACY DB EXPORT SUMMARY:\n");
	reportAppend (sprintf ("Processed %d transactions", $n));
	reportAppend (sprintf ("Deleted %d properties", $nDeleted));
	reportAppend (sprintf ("Added %d properties", $nAdded));
	reportAppend (sprintf ("There were %d UTF8 Conversion errors\n",
			count($utf8_errors)));
	reportWrite ("export.report");

	info ("Integration script complete");
}

main($argc > 1 && substr (strtolower ($argv [1]), 0, 1) == 'k');

?>
