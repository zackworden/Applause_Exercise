<?php

// data handling classes and logic related to reading CSVs into memory and creating relationships from this
require_once 'includes/dataCapture.php';

$dataGrabber = new DataFetcher();

// read CSV data from file, convert to proper objects
$allTesters = $dataGrabber->Fetch_CSVData('data/testers.csv');
$allDevices = $dataGrabber->Fetch_CSVData('data/devices.csv');
$allBugReports = $dataGrabber->Fetch_CSVData('data/bugs.csv');
$allTesterDeviceRelationships = $dataGrabber->Fetch_CSVData('data/tester_device.csv');

// row 0 of result arrays are the CSV headers, so remove them
unset($allTesters[0]);
unset($allDevices[0]);
unset($allBugReports[0]);
unset($allTesterDeviceRelationships[0]);


$dataStore = new DataStore();

$interpretter = new ResultsInterpretter();


$dataStore->SetDevicesArray( $interpretter->Parse_DeviceData( $allDevices ) );
$dataStore->SetTestersArray( $interpretter->Parse_TesterData( $allTesters ) );

$interpretter->Parse_TesterDeviceRelationshipData( $allTesterDeviceRelationships, $dataStore );
$dataStore->SetBugReportsArray( $interpretter->Parse_BugReportData( $allBugReports, $dataStore ) );













/*
var_dump( 
	$dataStore->GetBugReportsWithCountries( 
		//$dataStore->GetAllUniqueCountries() 
		['JP','US']
	)
);
*/

/*
var_dump( 
	$dataStore->GetBugReportsWithDevices( 
		//$dataStore->GetAllUniqueDevices() 
		['Galaxy S3']
	)
);

exit;
*/




// UI
$_REQUEST['getreport'] = '{"countries":["all"],"devices":["all"]}';

if ( !empty( $_GET['getall'] ) )
{
	switch ( $_GET['getall'] )
	{
		case 'country':
			echo json_encode( $dataStore->GetAllUniqueCountries() );
		break;
		case 'device':
			echo json_encode( $dataStore->GetAllUniqueDevices() );
		break;
		default:
			echo json_encode( 'no results found');
		break;
	}
}
else if ( !empty( $_REQUEST['getreport'] ) )
{
	$request = json_decode( $_REQUEST['getreport'] );

	if ( in_array( 'all', $request->countries )  )
	{
		$countries = $dataStore->GetAllUniqueCountries();
	}
	else if ( !empty( $request->countries ) )
	{
		$countries = $request->countries;
	}

	if ( in_array( 'all', $request->devices ) )
	{
		$devices = $dataStore->GetAllUniqueDevices();
	}
	else if ( !empty( $request->devices ) )
	{
		$devices = $request->devices;
	}

	$totalResults = array();
	$countryResults = array();
	$deviceResults = array();

	$countryResults = $dataStore->GetBugReportsWithCountries( $countries );
	$deviceResults = $dataStore->GetBugReportsWithDevices( $devices );

	foreach ( $countryResults as $thisResult )
	{
		array_push( 
			$totalResults, $thisResult->Jsonify()
		);
	}

	foreach ( $deviceResults as $thisResult )
	{
		array_push( 
			$totalResults, $thisResult->Jsonify()
		);
	}

	echo json_encode( $totalResults );
}
else
{
	echo json_encode('unkknowable');
}







/*
var_dump( 
	$interpretter->Parse_BugReportData( $allBugReports, $dataStore )
);
var_dump( $dataStore->allBugReports );
exit;
*/