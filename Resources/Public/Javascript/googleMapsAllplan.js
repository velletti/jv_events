/**
 * Creates the google map
 * Called by: https://maps.googleapis.com/maps/api/js?key={GOOGLE-API-KEY}&callback=initMap
 */
function initMap() {

	// Set the center of the map (value not important, because we bound the markers to set the center of the map)
	var myLatLng = {lat: -25.363, lng: 131.044};

	// Bounding for centering the map
	var bounds = new google.maps.LatLngBounds();

	// Info window for the markers
	var infoWindow = new google.maps.InfoWindow();

	// See also: https://mapstyle.withgoogle.com/
	var styleArray = [
		{
			"elementType": "geometry",
			"stylers": [
				{
					"color": "#212121"
				}
			]
		},
		{
			"elementType": "labels.icon",
			"stylers": [
				{
					"visibility": "off"
				}
			]
		},
		{
			"elementType": "labels.text.fill",
			"stylers": [
				{
					"color": "#757575"
				}
			]
		},
		{
			"elementType": "labels.text.stroke",
			"stylers": [
				{
					"color": "#212121"
				}
			]
		},
		{
			"featureType": "administrative",
			"elementType": "geometry",
			"stylers": [
				{
					"color": "#757575"
				}
			]
		},
		{
			"featureType": "administrative.country",
			"elementType": "labels.text.fill",
			"stylers": [
				{
					"color": "#9e9e9e"
				}
			]
		},
		{
			"featureType": "administrative.locality",
			"elementType": "labels.text.fill",
			"stylers": [
				{
					"color": "#bdbdbd"
				}
			]
		},
		{
			"featureType": "poi",
			"elementType": "labels.text.fill",
			"stylers": [
				{
					"color": "#757575"
				}
			]
		},
		{
			"featureType": "poi.park",
			"elementType": "geometry",
			"stylers": [
				{
					"color": "#181818"
				}
			]
		},
		{
			"featureType": "poi.park",
			"elementType": "labels.text.fill",
			"stylers": [
				{
					"color": "#616161"
				}
			]
		},
		{
			"featureType": "poi.park",
			"elementType": "labels.text.stroke",
			"stylers": [
				{
					"color": "#1b1b1b"
				}
			]
		},
		{
			"featureType": "road",
			"elementType": "geometry.fill",
			"stylers": [
				{
					"color": "#2c2c2c"
				}
			]
		},
		{
			"featureType": "road",
			"elementType": "labels.text.fill",
			"stylers": [
				{
					"color": "#8a8a8a"
				}
			]
		},
		{
			"featureType": "road.arterial",
			"elementType": "geometry",
			"stylers": [
				{
					"color": "#373737"
				}
			]
		},
		{
			"featureType": "road.highway",
			"elementType": "geometry",
			"stylers": [
				{
					"color": "#3c3c3c"
				}
			]
		},
		{
			"featureType": "road.highway.controlled_access",
			"elementType": "geometry",
			"stylers": [
				{
					"color": "#4e4e4e"
				}
			]
		},
		{
			"featureType": "road.local",
			"elementType": "labels.text.fill",
			"stylers": [
				{
					"color": "#616161"
				}
			]
		},
		{
			"featureType": "transit",
			"elementType": "labels.text.fill",
			"stylers": [
				{
					"color": "#757575"
				}
			]
		},
		{
			"featureType": "water",
			"elementType": "geometry",
			"stylers": [
				{
					"color": "#000000"
				}
			]
		},
		{
			"featureType": "water",
			"elementType": "labels.text.fill",
			"stylers": [
				{
					"color": "#3d3d3d"
				}
			]
		}
	];

	// Create the map
	var map = new google.maps.Map(document.getElementById('map'), {
		zoom: 6,
		center: myLatLng,
		styles: styleArray
	});

	for (var i = 0; i < addresses.length; i++) {

		// Only if lat and lng exist
		if (addresses[i].lat && addresses[i].lng) {

			// Set the marker
			var point = new google.maps.LatLng(
				parseFloat(addresses[i].lat),
				parseFloat(addresses[i].lng)
			);
			var marker = new google.maps.Marker({
				position: point,
				map: map,
				title: addresses[i].title
			});

			// Bound the marker for centering the map
			bounds.extend(marker.position);

			// Create the info window on click (here: self invoking anonymous function)
			google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
					infoWindow.setContent(addresses[i].markerText);
					infoWindow.open(map, marker);
				}
			})(marker, i));

		}

	}
	google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
		if (this.getZoom() > 14) {
			this.setZoom(14);
		}
		if (this.getZoom() < 3) {
			this.setZoom(3);
		}
	});
	// Center the map to the bounds
	map.fitBounds(bounds);


	$('a[href="#jv_events_map"]').on('click', function() {   // When tab is displayed...
		refreshIntervalId = setInterval(function () { updateMapTimer(map) }, 300);
	});
	$('.nav-tabs a').click(function(event) {   // When tab is displayed...
		navigationFn.goToSection(event.target.id);
	});


	var navigationFn = {
		goToSection: function(id) {
			if ( id && id.length > 4 &&  $("#" + id).length ) {
				$('html, body').animate({
					scrollTop: $("#" + id).offset().top
				}, 10);
			}

		}
	}

}
function updateMapTimer(map) {
	clearInterval(refreshIntervalId);
	var center = map.getCenter() ;
	google.maps.event.trigger(map, 'resize');
	map.setCenter(center) ;
}

