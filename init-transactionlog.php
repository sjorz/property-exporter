<?php

//**********************************************************************
//
//  Set up the transaction log table for initial export.
//
//  History:
//  -------
//  1.0     26Sep11 GB      Original
//
//**********************************************************************

require_once "table.php";
require_once "sql.php";

$utf8_errors = array();
$report = '';

//**********************************************************************
//
//  Some helpers.
//
//**********************************************************************

function error ($msg)
{
    echo "Error: ", $msg, "\n";
}

function warning ($msg)
{
    echo "Warning: ", $msg, "\n";
}

function info ($msg)
{
    echo "Info: ", $msg, "\n";
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
//  Main
//
//**********************************************************************

function main()
{
	$lvl = Logger::$ERROR;
	Logger::open ('transaction_init.log', $lvl);

	info ("***********************************************");
	info ("Set up transaction log table for initial export");
	info ("***********************************************");

	$transactionLog = new Table ('dbo.PropertyTransactionLog',
            array ('created_at', 'PropertyId'), 'PropertyId');

	$sql = "IF EXISTS (SELECT 1 
    			FROM INFORMATION_SCHEMA.TABLES 
    			WHERE TABLE_TYPE='BASE TABLE' 
    			AND TABLE_NAME='PropertyTransactionLog')
					drop table dbo.PropertyTransactionLog;
		create table dbo.PropertyTransactionLog (
		created_at  datetime    not null,
		PropertyId   integer    not null,
		action varchar(1)       not null);

		insert into dbo.PropertyTransactionLog (created_at,PropertyId,[action]) select getdate(),intPropertyId,'U' from Property where intStatusID=1";

	if (!$transactionLog->executeSQL($sql))
	{
		error ("Initialisation failed: %\n");
	}
	else
	{
		info ("Initialisation successful");
		//$rows = $transactionLog->asArray();

		$sql = "select count(*) from dbo.PropertyTransactionLog";
		$transactionLog->executeSQL($sql);
		$rows = $transactionLog->asArray();
		$rows = array_values ($rows [0]);
		info (sprintf ("%d rows created", $rows [0]));
	}
	info ("Set up complete");
}

main();

?>
