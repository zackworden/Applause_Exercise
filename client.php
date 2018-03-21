<!DOCTYPE html>
<html>
	<head>
		<style type="text/css">

		</style>
		<script type="text/javascript">

			var requestMediator = {
				sendRequest : function ( requestType, requestArg, callback ) {
					var req = new XMLHttpRequest();
					req.onreadystatechange = requestMediator.handleRequestState.bind( req, req, callback ) ;
					req.open('GET', 'index.php?' + requestType + '=' + requestArg);
					req.send();
				},
				handleRequestState : function ( request, callback ) {
					if ( request.readyState == 4 )
					{
						callback( request.responseText );
					}
				}
			};

			var markupFactory = {
				buildCountryOption : function( countryObject ) {
					var elem = document.createElement('option');
					elem.innerText = countryObject;
					return elem;
				},
				buildDeviceOption : function( deviceObject ) {
					var elem = document.createElement('option');
					elem.innerText = deviceObject;
					return elem;
				},
				buildResult : function( resultObject ) {
					var elem = document.createElement('div');
					elem.innerText = resultObject;//'test result';

					return elem;
				}
			};


			window.addEventListener('DOMContentLoaded', function(){
				
				Populate_CountryField();
				Populate_DeviceField();
			});


			function Populate_CountryField()
			{
				var selectElem = document.querySelector('#country_select');

				requestMediator.sendRequest('getall', 'country', HandleResponse.bind( this ) );

				function HandleResponse( responseText )
				{
					var allCountries = JSON.parse( responseText );
					allCountries.sort();
					var docFrag = document.createDocumentFragment();
					var counter = 0;
					var numOf = allCountries.length;

					for ( counter = 0; counter < numOf; counter ++ )
					{
						docFrag.appendChild( markupFactory.buildCountryOption( allCountries[counter] ) );
					}

					selectElem.appendChild( docFrag );
				}
			}
			function Populate_DeviceField()
			{
				var selectElem = document.querySelector('#device_select');

				requestMediator.sendRequest('getall', 'device', HandleResponse.bind( this ) );

				function HandleResponse( responseText )
				{
					var allDevices = JSON.parse( responseText );
					allDevices.sort();
					var docFrag = document.createDocumentFragment();
					var counter = 0;
					var numOf = allDevices.length;

					for ( counter = 0; counter < numOf; counter ++ )
					{
						docFrag.appendChild( markupFactory.buildDeviceOption( allDevices[counter] ) );
					}

					selectElem.appendChild( docFrag );
				}
			}

			function Render_Results( response )
			{
				var elem = document.querySelector( '#resultsContainer' );
				var counter = 0;
				var numOf = 0;
				var docFrag = document.createDocumentFragment();

				response = JSON.parse( response );
				numOf = response.length;

				if ( typeof elem != 'undefined' )
				{
					elem.innerHTML = '';
					
					for ( counter = 0; counter < numOf; counter ++ )
					{
						docFrag.appendChild( markupFactory.buildResult( response[counter] ) );
					}

					elem.appendChild( docFrag );
				}
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

				requestBody = JSON.stringify( requestBody );


				requestMediator.sendRequest('getreport', requestBody, Render_Results.bind( this ) );

			}
		</script>
		<link rel="stylesheet" href="styles/style.css" />
	</head>
	<body>
		<header class="ui_header">
			<div class="header_options">
				<div class="header_item header_country">
					<label>
						Country
					</label>
					<select name="country" id="country_select" multiple="multiple" size="6">
						<option value="all">All Countries</option>
					</select>
				</div>
				<div class="header_item header_device">
					<label>
						Device
					</label>
					<select name="device" id="device_select" multiple="multiple" size="6">
						<option value="all">All Devices</option>
					</select>
				</div>
			</div>
			<div class="header_actions">
				<div class="header_item header_getResults">
					<button onclick="GetAllResults();">Get Results</button>
				</div>
			</div>
		</header>
		<main id="resultsContainer" class="resultsContainer">
		</main>
	</body>
</html>