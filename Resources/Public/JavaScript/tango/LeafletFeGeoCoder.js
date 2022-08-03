
    let LeafFE = {
        $element: null,
        $gLatitude: null,
        $gLongitude: null,
        $latitude: null,
        $longitude: null,
        $fieldLat: null,
        $fieldLon: null,
        $fieldLatActive: null,
        $geoCodeUrl: null,
        $geoCodeUrlShort: null,
        $tilesUrl: null,
        $tilesCopy: null,
        $zoomLevel: 10 ,
        $marker: null,
        $map: null,
        $iconClose: null
    };

    LeafFE.init = function (element) {
        // basic variable initialisation, uses data vars on the trigger button
        LeafFE.$element = element;
        LeafFE.$latitude = LeafFE.$element.attr('data-lat');
        LeafFE.$longitude = LeafFE.$element.attr('data-lon');
        LeafFE.$gLatitude = LeafFE.$element.attr('data-glat');
        LeafFE.$gLongitude = LeafFE.$element.attr('data-glon');
        LeafFE.$tilesUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
        LeafFE.$tilesCopy = LeafFE.$element.attr('data-copy');
        LeafFE.$geoCodeUrl = LeafFE.$element.attr('data-geocodeurl');
        LeafFE.$geoCodeUrlShort = LeafFE.$element.attr('data-geocodeurlshort');
        LeafFE.$fieldLat = LeafFE.$element.attr('data-namelat');
        LeafFE.$fieldLon = LeafFE.$element.attr('data-namelng');
        LeafFE.$fieldLatActive = LeafFE.$element.attr('data-namelat-active');

        // add the container to display the map as a nice overlay
        if (!$('#t3js-location-map-wrap').length) {
            LeafFE.addMapMarkup();
        }
    };

    LeafFE.addMapMarkup = function () {
        $('div#map').append(
            '<div id="t3js-location-map-wrap">' +
            '<div class="t3js-location-map-container" id="t3js-location-map-container">' +
            '</div>' +
            '</div>'
        );
    };

    LeafFE.createMap = function () {

        if (((!LeafFE.$latitude || !LeafFE.$longitude) || (LeafFE.$latitude == 0 && LeafFE.$longitude == 0)) && LeafFE.$geoCodeUrl != null) {
            LeafFE.geocode();
        }

        // The ultimate fallback: if one of the coordinates is empty, fallback to München.

        if (LeafFE.$latitude == null || LeafFE.$longitude == null) {
            LeafFE.$latitude = LeafFE.$gLatitude;
            LeafFE.$longitude = LeafFE.$gLongitude;
            LeafFE.$zoomLevel = 10;
        }
        LeafFE.$map = L.map('t3js-location-map-container', {
            center: [LeafFE.$latitude, LeafFE.$longitude],
            zoom: LeafFE.$zoomLevel
        });
        L.tileLayer(LeafFE.$tilesUrl, {
            attribution: LeafFE.$tilesCopy
        }).addTo(LeafFE.$map);

        LeafFE.$marker = L.marker([LeafFE.$latitude, LeafFE.$longitude], {
            draggable: true
        }).addTo(LeafFE.$map);

        let position = LeafFE.$marker.getLatLng();

        LeafFE.$marker.on('dragend', function (event) {
            LeafFE.$marker = event.target;
            position = LeafFE.$marker.getLatLng();
        });
        LeafFE.$map.on('click', function (event) {
            LeafFE.$marker.setLatLng(event.latlng);
            $( LeafFE.$fieldLat ).val(LeafFE.$marker.getLatLng().lat);
            $( LeafFE.$fieldLon ).val(LeafFE.$marker.getLatLng().lng);
        });

        $('#jvevents-geo-update').on('click', function () {
            // set visual coordinates to LAT LNG Fields in location Form
            console.log( "Klicked") ;
            console.log( "Lat Field " + LeafFE.$fieldLat ) ;
            console.log( "Lng Field " + LeafFE.$fieldLon ) ;
            console.log( "marker LNG: " + LeafFE.$marker.getLatLng().lat ) ;
            console.log( "Map LNG: " + LeafFE.$longitude ) ;

            $( LeafFE.$fieldLat ).val(LeafFE.$latitude);
            $( LeafFE.$fieldLon ).val(LeafFE.$longitude);
            // enable also latitude, if not already done by user.

        });
    };

    LeafFE.geocode = function () {
        $.ajax({
            type: 'GET',
            url: LeafFE.$geoCodeUrl,
            async: false,
            dataType: 'json',
            success: function (data) {
                if (data.length == 0) {
                    $.ajax({
                        type: 'GET',
                        url: LeafFE.$geoCodeUrlShort,
                        async: false,
                        dataType: 'json',
                        success: function (data) {
                            if (data.length != 0) {
                                $.each(data[0], function (key, value) {
                                    if (key == "lat") {
                                        LeafFE.$latitude = value;
                                    }
                                    if (key == "lon") {
                                        LeafFE.$longitude = value;
                                    }
                                });
                            }
                        }
                    });
                } else {
                    $.each(data[0], function (key, value) {
                        if (key == "lat") {
                            LeafFE.$latitude = value;
                        }
                        if (key == "lon") {
                            LeafFE.$longitude = value;
                        }
                    });
                }
                $( LeafFE.$fieldLat ).val(LeafFE.$latitude);
                $( LeafFE.$fieldLng ).val(LeafFE.$longitude);
            }
        });
    };



    LeafFE.init($('div#map'));
    LeafFE.createMap();
