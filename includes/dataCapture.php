<?php

class DataStore
{
	public $allDevices = [];
	public $allTesters = [];
	public $allBugReports = [];

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

	function GetDeviceById ( $deviceId )
	{
		foreach ( $this->allDevices as $thisDevice )
		{
			if ( $thisDevice->Get_Id() == $deviceId )
			{
				return $thisDevice;
				break;
			}
		}

		return NULL;
	}
	function GetTesterById ( $testerId )
	{
		foreach ( $this->allTesters as $thisTester )
		{
			if ( $thisTester->Get_Id() == $testerId )
			{
				return $thisTester;
				break;
			}
		}

		return NULL;
	}

	function GetAllUniqueCountries()
	{
		$uniqueCountries = [];

		foreach ( $this->allTesters as $thisTester )
		{
			$country = $thisTester->Get_Country();

			if ( in_array( $country, $uniqueCountries ) == FALSE )
			{
				array_push( $uniqueCountries, $country );
			}
		}

		return $uniqueCountries;
	}
	function GetAllUniqueDevices()
	{
		$uniqueDevices = [];

		foreach ( $this->allDevices as $thisDevice )
		{
			$device = $thisDevice->Get_Description();

			if ( in_array( $device, $uniqueDevices ) == FALSE )
			{
				array_push( $uniqueDevices, $device );
			}
		}

		return $uniqueDevices;
	}
}

class DataFetcher 
{
	function Fetch_CSVData( $path )
	{
		$resultArray = [];
		$contentsAsString = file_get_contents( $path );
		$allRows = str_getcsv( $contentsAsString, "\n", ','  );

		foreach ( $allRows as $thisRow )
		{
			$thisRow = str_getcsv( $thisRow, ',' );
			array_push( $resultArray, $thisRow );
		}

		return $resultArray;
	}
}

class ResultsInterpretter
{
	function Parse_DeviceData( $resultArray )
	{
		$instantiatedResults = [];

		foreach ( $resultArray as $thisRow )
		{
			array_push( $instantiatedResults, new Device( $thisRow[0], $thisRow[1] ) );
		}

		return $instantiatedResults;
	}
	function Parse_TesterData( $resultArray )
	{
		$instantiatedResults = [];

		foreach ( $resultArray as $thisRow )
		{
			array_push( $instantiatedResults, new Tester( $thisRow[0], $thisRow[1], $thisRow[2], $thisRow[3], $thisRow[4] ) );
		}

		return $instantiatedResults;
	}
	function Parse_BugReportData( $resultArray, DataStore $dataStore )
	{
		$allBugReports = [];
		$thisBug = '';
		$thisDevice = '';
		$thisTester = '';

		foreach ( $resultArray as $thisResult )
		{
			$thisTester = $dataStore->GetTesterById( $thisResult[1] );
			$thisDevice = $dataStore->GetDeviceById( $thisResult[2] );

			if ( 
				!empty( $thisTester ) &&
				!empty( $thisDevice )
			)
			{
				array_push( $allBugReports, new BugReport($thisResult[1], $thisDevice, $thisTester ) );
			}
		}

		return $allBugReports;
	}
	function Parse_TesterDeviceRelationshipData( $resultArray, DataStore $dataStore )
	{
		$thisTester = '';
		$thisDevice = '';
		$thisTestersDevices = [];

		foreach ( $resultArray as $thisResult )
		{
			$thisTester = $dataStore->GetTesterById( $thisResult[0] );

			if ( !empty( $thisTester ) )
			{
				$thisDevice = $dataStore->GetDeviceById( $thisResult[1] );

				if ( !empty($thisDevice) )
				{
					$thisTester->addDevice($thisDevice);
				}
			}
		}
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
	function addDevice( Device $device )
	{
		array_push( $this->devices, $device );

		return $this->devices;
	}
	function Get_Id()
	{
		return $this->testerId;
	}
	function Get_Country()
	{
		return $this->country;
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




