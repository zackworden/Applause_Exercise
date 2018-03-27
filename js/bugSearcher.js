
// on DOM load, initiate formManager behavior
window.addEventListener('load', function(){
	formManager.Init();
});

// common interface for all ajax requests, which sends successful / complete requests to a callback
var requestMediator = {
	// send an ajax request
	sendRequest : function ( requestType, requestArg, callback ) {
		var req = new XMLHttpRequest();
		req.onreadystatechange = requestMediator.handleRequestState.bind( req, req, callback ) ;
		req.open('GET', 'index.php?' + requestType + '=' + requestArg);
		req.send();
	},

	// on successful response, return responseText to callback function
	handleRequestState : function ( request, callback ) {
		if ( request.readyState == 4 )
		{
			callback( request.responseText );
		}
	}
};

// builds all HTML
var markupFactory = {
	// build a single <option> element for the country <select>
	buildCountryOption : function( countryObject ) {
		var elem = document.createElement('option');
		elem.innerText = countryObject;
		return elem;
	},

	// build a single <option> element for the device <select>
	buildDeviceOption : function( deviceObject ) {
		var elem = document.createElement('option');
		elem.innerText = deviceObject;
		return elem;
	},

	// build the wrapper element / <table> which all results are added into
	getResultTable : function () {
		var tableElem = document.createElement('table');
		var theadElem = document.createElement('thead');
		var rowElem = document.createElement('tr');
		var cellElem;

		// experience cell
		cellElem = document.createElement('th');
		cellElem.innerText = 'Experience (in bugs reported)';
		rowElem.appendChild(cellElem);

		// tester name
		cellElem = document.createElement('th');
		cellElem.innerText = 'Tester Name (last, first)';
		rowElem.appendChild(cellElem);

		theadElem.appendChild( rowElem );
		tableElem.appendChild( theadElem );

		return tableElem;
	},

	// build the invidiual <tr> for a particular result
	buildResult : function( resultObject ) {
		var rowElem = document.createElement('tr');
		var cellElem;
		rowElem.classList.add('resultItem');

		// experience cell
		cellElem = document.createElement('td');
		cellElem.innerText = resultObject.count;
		rowElem.appendChild( cellElem );

		// tester name
		cellElem = document.createElement('td');
		cellElem.innerText = resultObject.tester.lastName + ', ' + resultObject.tester.firstName;
		rowElem.appendChild( cellElem );
		
		return rowElem;
	},

	// build a header that displays the total number of bugs and any other data about the report itself
	buildResultsHeader : function( resultObject ) {
		var elem = document.createElement('header');
		var totalBugs = 0;

		for ( obj in resultObject )
		{
			totalBugs += resultObject[obj].count;
		}

		elem.innerText = 'Total Bugs: ' + totalBugs;

		return elem;
	}
};


// manage all form behavior, including field population and form submission
var formManager = {
	// poulate country <select> element with a list of all possible countries (from /data/testers.csv)
	Populate_CountryField : function() {
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
	},

	// poulate country <select> element with a list of all possible devices (from /data/devices.csv)
	Populate_DeviceField : function() {
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
	},

	// take a bug search request and build a table out of the results
	Render_Results : function( response ) {
		var elem = document.querySelector( '#resultsContainer' );
		var docFrag = document.createDocumentFragment();
		var tableElem;

		response = JSON.parse( response );
		
		if ( typeof elem != 'undefined' )
		{
			// clear any existing results, so this can be run multiple times
			elem.innerHTML = '';

			// build the header section of results
			docFrag.appendChild( markupFactory.buildResultsHeader( response ) );

			tableElem = markupFactory.getResultTable();

			for ( testerId in response )
			{
				tableElem.appendChild( markupFactory.buildResult( response[ testerId ] ) );
			}

			docFrag.appendChild( tableElem );

			elem.appendChild( docFrag );
		}
	},

	// init all <select> elements and initial functionality
	Init : function() {
		this.Populate_CountryField.call( formManager );
		this.Populate_DeviceField.call( formManager );

		var searchButton = document.querySelector('#getResultsButton');

		if ( typeof searchButton != 'undefined' )
		{
			searchButton.addEventListener('click', this.GetResults.bind( formManager ) );
		}
	},

	// perform actual search request
	GetResults : function() {
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

		requestMediator.sendRequest('getreport', requestBody, this.Render_Results.bind( this ) );
	}
};
