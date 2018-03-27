<!DOCTYPE html>
<html>
	<head>
		<style type="text/css">

		</style>
		<script type="text/javascript" async src="js/bugSearcher.js"></script>
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
					<button id="getResultsButton">Get Results</button>
				</div>
			</div>
		</header>
		<main id="resultsContainer" class="resultsContainer">
		</main>
	</body>
</html>