<?php

class DataStore
{
	public $allDevices = [];
	public $allTesters = [];
	public $allBugReports = [];
	//public $allDevices = [];

	function SetDevicesArray ( $deviceArray )
	{
		$this->allDevices = $deviceArray;
	}
	function SetTestersArray ( $testerArray )
	{
		$this->allTesters = $testerArray;
	}
	function SetBugReportsArray ( $bugReportArray )
	{
		$this->allBugReports = $bugReportArray;
	}

	function GetDeviceById ( int $deviceId )
	{
		foreach ( $thisAllDevices as $thisDevice )
		{
			if ( $thisDevice->deviceId() === $deviceId )
			{
				return $thisDevice;
				break;
			}
		}
	}
}

class DataFetcher 
{
	function Fetch_TesterData()
	{
		$resultArray = [];
		$contentsAsString = file_get_contents( 'data/testers.csv' );
		$allRows = str_getcsv( $contentsAsString, "\n", ','  );

		foreach ( $allRows as $thisRow )
		{
			$thisRow = str_getcsv( $thisRow, ',' );
			array_push( $resultArray, new Tester( $thisRow[0], $thisRow[1], $thisRow[2], $thisRow[3], $thisRow[4] ) );
		}

		return $resultArray;
	}
	function Fetch_DeviceData()
	{
		$resultArray = [];
		$contentsAsString = file_get_contents( 'data/devices.csv' );
		$allRows = str_getcsv( $contentsAsString, "\n", ','  );

		foreach ( $allRows as $thisRow )
		{
			$thisRow = str_getcsv( $thisRow, ',' );
			array_push( $resultArray, new Device( $thisRow[0], $thisRow[1] ) );
		}

		return $resultArray;
	}
	function Fetch_BugReportData()
	{
		$resultArray = [];
		$contentsAsString = file_get_contents( 'data/bugs.csv' );
		$allRows = str_getcsv( $contentsAsString, "\n", ','  );

		foreach ( $allRows as $thisRow )
		{
			$thisRow = str_getcsv( $thisRow, ',' );
			//array_push( $resultArray, new Device( $thisRow[0], $thisRow[1] ) );
		}

		return $resultArray;
	}
	function Fetch_TesterDeviceData()
	{
		$resultArray = [];
		$contentsAsString = file_get_contents( 'data/tester_device.csv' );
		$allRows = str_getcsv( $contentsAsString, "\n", ','  );

		foreach ( $allRows as $thisRow )
		{
			$thisRow = str_getcsv( $thisRow, ',' );
			array_push( $resultArray, new Device( $thisRow[0], $thisRow[1] ) );
		}

		var_dump( $allRows );
		var_dump( $allRows );
		return $resultArray;
	}

	function Test()
	{

	}
}



class Tester
{
	protected $testerId;
	protected $firstName;
	protected $lastName;
	protected $country;
	protected $lastLogin;
	protected $devices = [];

	function __construct( $testerId, $firstName, $lastName, $country, $lastLogin )
	{
		$this->testerId = $testerId;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->country = $country;
		$this->lastLogin = $lastLogin;
	}
	function attachDevices( $allDevices )
	{
		$this->devices = $allDevices;
	}
}
class Device
{
	protected $deviceId;
	protected $description;

	function __construct( $deviceId, $description )
	{
		$this->deviceId = $deviceId;
		$this->description = $description;
	}
	function Get_Id()
	{
		return $this->deviceId;
	}
	function Get_Description()
	{
		return $this->description;
	}
}
class BugReport
{
	protected $bugId;
	protected $device;
	protected $tester;

	function __construct( $bugId, $device, $tester )
	{
		$this->bugId = $bugId;
		$this->device = $device;
		$this->tester = $tester;
	}
}


$dataGrabber = new DataFetcher();
$dataStore = new DataStore();
$dataStore->SetDevicesArray( $dataGrabber->Fetch_DeviceData() );
$dataStore->SetTestersArray( $dataGrabber->Fetch_TesterData() );



$dataStore->SetTestersArray( $dataGrabber->Fetch_TesterData() );

$allTesterDeviceRelationships = [];
$allTesterDeviceRelationships = $dataGrabber->Fetch_TesterDeviceData();

var_dump($allTesterDeviceRelationships);

//$dataStore->SetBugReportsArray( $dataGrabber->Fetch_BugReportData() );

/*
$dataStore->SetDevicesArray();

$allDevices = $dataGrabber->Fetch_DeviceData();
$allTesters = $dataGrabber->Fetch_TesterData();
$allBugReports = $dataGrabber->Fetch_BugReportData();
$allTesterDeviceRelationships = $dataGrabber->Fetch_TesterDeviceData();
*/

//var_dump(
//	$dataStore->allDevices
//);
//var_dump(
//	$dataStore->allTesters
//);
//var_dump(
//$allTesters
//);
//var_dump(
//$allBugReports
//);
//var_dump(
//$allTesterDeviceRelationships
//);

// test open CSV



$testData = $dataGrabber->Test();

