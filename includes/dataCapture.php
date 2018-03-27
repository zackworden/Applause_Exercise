<?php

// stores and disperses all data (bugs, testers, etc)
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
	function GetBugReportsWithCountries( $arrayOfCountries )
	{
		$results = array();

		foreach ( $this->allBugReports as $thisBugReport )
		{
			$thisCountry = $thisBugReport->Get_TesterCountry();

			if ( in_array( $thisCountry, $arrayOfCountries ) )
			{
				if ( in_array( $thisCountry, $results ) == FALSE )
				{
					array_push( $results, $thisBugReport );
				}
			}
		}

		return $results;
	}
	function GetBugReportsWithDevices( $arrayOfDevices )
	{
		$results = array();

		foreach ( $this->allBugReports as $thisBugReport )
		{
			$thisDevice = $thisBugReport->Get_TesterDevice()->Get_Description();

			if ( in_array( $thisDevice, $arrayOfDevices ) )
			{
				if ( in_array( $thisDevice, $results ) == FALSE )
				{
					array_push( $results, $thisBugReport );
				}
			}
		}

		return $results;
	}
}

// reads CSV file into memory
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

		// row 0 is the CSV header, so remove
		unset( $resultArray[0] );

		return $resultArray;
	}
}

// each CSV has a slightly different structure, so we parse the resulting data slightly different depending on which file we're reading
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

// a person that has / is able to report bugs. Ultimately fed from /data/testers.csv
class Tester
{
	public $testerId;
	public $firstName;
	public $lastName;
	public $country;
	public $lastLogin;
	public $devices = [];

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
	function Jsonify()
	{
		return json_encode( $this );
	}
}

// a device that can be associated with a person or bug report. Fed from /data/devices.csv
class Device
{
	public $deviceId;
	public $description;

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
	function Jsonify()
	{
		return json_encode( $this );
	}
}

// a recorded by, with an association between a Tester and a Device
class BugReport
{
	public $bugId;
	public $device;
	public $tester;

	function __construct( $bugId, $device, $tester )
	{
		$this->bugId = $bugId;
		$this->device = $device;
		$this->tester = $tester;
	}
	function Get_TesterCountry()
	{
		return $this->tester->Get_Country();
	}
	function Get_TesterDevice()
	{
		return $this->device;
	}
	function Jsonify()
	{
		return json_encode( $this );
	}
}
