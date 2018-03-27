<?php

// data handling classes related to reading CSVs into memory and creating relationships from this
require_once 'includes/dataCapture.php';

// all utilities, although this is really just a custom sorting function right now
require_once 'includes/utilities.php';

// create an instance of a CSV reading data class
$dataGrabber = new DataFetcher();

// read CSV data from file, convert to proper objects
$allTesters = $dataGrabber->Fetch_CSVData('data/testers.csv');
$allDevices = $dataGrabber->Fetch_CSVData('data/devices.csv');
$allBugReports = $dataGrabber->Fetch_CSVData('data/bugs.csv');
$allTesterDeviceRelationships = $dataGrabber->Fetch_CSVData('data/tester_device.csv');

// create an interpretter, which can map the different CSV structures into the correct class formats
$interpretter = new ResultsInterpretter();

// create a storage class for all of the data we're bringing in
$dataStore = new DataStore();

// send parsed CSV data into the datastore
$dataStore->SetDevicesArray( $interpretter->Parse_DeviceData( $allDevices ) );
$dataStore->SetTestersArray( $interpretter->Parse_TesterData( $allTesters ) );
$interpretter->Parse_TesterDeviceRelationshipData( $allTesterDeviceRelationships, $dataStore );
$dataStore->SetBugReportsArray( $interpretter->Parse_BugReportData( $allBugReports, $dataStore ) );

/*
Request handling
*/
	// handle request to get all unique values for the different select dropdowns (country, device)
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
	// handle a search request, where a device/s and country/ies have been sent over and we need to match results
	else if ( !empty( $_REQUEST['getreport'] ) )
	{
		$request = json_decode( $_REQUEST['getreport'] );

		// determine if 'all' was passed over as one of the country options. If so, gather a list of all unique countries
		if ( in_array( 'all', $request->countries )  )
		{
			$countries = $dataStore->GetAllUniqueCountries();
		}
		// if countries were sent over, gather a list of the countries that were sent over
		else if ( !empty( $request->countries ) )
		{
			$countries = $request->countries;
		}
		// if no countries were chosen, assume UX mistake and default to 'all'
		else
		{
			$countries = $dataStore->GetAllUniqueCountries();
		}

		// determine if 'all' was passed over as one of the device options. If so, gather a list of all unique devices
		if ( in_array( 'all', $request->devices ) )
		{
			$devices = $dataStore->GetAllUniqueDevices();
		}
		// if devices were sent over, gather a list of the devices that were sent over
		else if ( !empty( $request->devices ) )
		{
			$devices = $request->devices;
		}
		// if no devices were chosen, assume UX mistake and default to 'all'
		else
		{
			$devices = $dataStore->GetAllUniqueDevices();
		}

		// gather all bug reports that match the request criteria
		$totalResults = array();
		$countryResults = array();
		$deviceResults = array();

		$countryResults = $dataStore->GetBugReportsWithCountries( $countries );
		$deviceResults = $dataStore->GetBugReportsWithDevices( $devices );

		if ( !empty($countryResults) )
		{
			foreach ( $countryResults as $thisResult )
			{
				array_push( 
					$totalResults, $thisResult
				);
			}
		}

		if ( !empty($deviceResults) )
		{
			foreach ( $deviceResults as $thisResult )
			{
				array_push( 
					$totalResults, $thisResult
				);
			}
		}

		// aggregate the results by user
		$aggregatedResults = array();

		foreach ( $totalResults as $thisResult )
		{
			if ( empty( $aggregatedResults[ $thisResult->tester->Get_Id() ] ) )
			{
				$aggregatedResults[ $thisResult->tester->Get_Id() ]['count'] = 1;
				$aggregatedResults[ $thisResult->tester->Get_Id() ]['tester'] = $thisResult->tester;
			}
			else
			{
				$aggregatedResults[ $thisResult->tester->Get_Id() ]['count'] ++;
			}
			
		}

		// sort
		usort( $aggregatedResults, 'customSortByCount');

		// output json results
		echo json_encode( $aggregatedResults );
	}
	// handle all other requests with a generic error
	else
	{
		echo 'request not recognized';
	}

	// just in case
	exit;