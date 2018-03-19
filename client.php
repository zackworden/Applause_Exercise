<!DOCTYPE html>
<html>
	<head>
		<style type="text/css">

		</style>
		<script type="text/javascript">
			function SendRequest( requestArg, callback )
			{
				var req = new XMLHttpRequest();
				req.onreadystatechange = RequestListener.bind( req, req, callback ) ;
				req.open('GET', 'index.php?getall=' + requestArg);
				req.send();
			}
			window.addEventListener('DOMContentLoaded', function(){
				
				Populate_CountryField();
				Populate_DeviceField();
			});

			function RequestListener( request, callback ) {
				if ( request.readyState == 4 )
				{
					callback( request.responseText );
				}
			}
			function Populate_CountryField()
			{
				var selectElem = document.querySelector('#country_select');

				SendRequest('country', HandleResponse.bind( this ) );

				function HandleResponse( responseText )
				{
					var allCountries = JSON.parse( responseText );
					allCountries.sort();
					var docFrag = document.createDocumentFragment();
					var thisOptionElem;
					var counter = 0;
					var numOf = allCountries.length;

					for ( counter = 0; counter < numOf; counter ++ )
					{
						thisOptionElem = document.createElement('option');
						thisOptionElem.innerText = allCountries[counter];
						docFrag.appendChild( thisOptionElem );
					}

					selectElem.appendChild( docFrag );
				}
			}
			function Populate_DeviceField()
			{
				var selectElem = document.querySelector('#device_select');

				SendRequest('device', HandleResponse.bind( this ) );

				function HandleResponse( responseText )
				{
					var allDevices = JSON.parse( responseText );
					allDevices.sort();
					var docFrag = document.createDocumentFragment();
					var thisOptionElem;
					var counter = 0;
					var numOf = allDevices.length;

					for ( counter = 0; counter < numOf; counter ++ )
					{
						thisOptionElem = document.createElement('option');
						thisOptionElem.innerText = allDevices[counter];
						docFrag.appendChild( thisOptionElem );
					}

					selectElem.appendChild( docFrag );
				}
			}
			function Populate_Results()
			{

			}

			function Get_Results( requestArgs, callback )
			{
				var req = new XMLHttpRequest();
				//req.onreadystatechange = RequestListener.bind( req, req, callback ) ;
				req.open('POST', 'index.php');
				req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				req.send( JSON.stringify(requestArgs) );



				console.log(98);
			}

			function GetAllResults()
			{
				var allCountries = [];
				var allDevices = [];
				var counter = 0;
				var numOf = 0;
				var countrySelect = document.querySelector('#country_select');
				var deviceSelect = document.querySelector('#device_select');

				// get all country inputs
				numOf = deviceSelect.selectedOptions.length;

				if ( deviceSelect.selectedOptions.length > 0 )
				{
					for ( counter = 0; counter < numOf; counter ++ )
					{
						if ( deviceSelect.selectedOptions[counter].value == 'All' )
						{
							allDevices = 'All';
							break;
						}
						else
						{
							allDevices.push( deviceSelect.selectedOptions[counter].value );
						}
					}
				}
				
				// get all country inputs
				numOf = countrySelect.selectedOptions.length;

				if ( countrySelect.selectedOptions.length > 0 )
				{
					for ( counter = 0; counter < numOf; counter ++ )
					{
						if ( countrySelect.selectedOptions[counter].value == 'All' )
						{
							allCountries = 'All';
							break;
						}
						else
						{
							allCountries.push( countrySelect.selectedOptions[counter].value );
						}

					}
				}

				var requestBody = {
					'countries'	: allCountries,
					'devices'	: allDevices
				};

				Get_Results( requestBody, Populate_Results );
				//console.log( requestBody );
			}
		</script>
	</head>
	<body>
		<div>
			<div>
				<label>
					Country
				</label>
				<select name="country" id="country_select" multiple="multiple">
					<option value="all">All Countries</option>
				</select>
			</div>
			<div>
				<label>
					Device
				</label>
				<select name="device" id="device_select" multiple="multiple">
					<option value="all">All Devices</option>
				</select>
			</div>
			<div>
				<button onclick="GetAllResults();">Get Results</button>
			</div>
		</div>
	</body>
</html>