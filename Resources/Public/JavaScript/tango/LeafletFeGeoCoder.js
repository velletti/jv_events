
    let LeafFE = {
        $element: null,
        $gLatitude: null,
        $gLongitude: null,
        $latitude: null,
        $longitude: null,
        $fieldLat: null,
        $fieldLng: null,
        $fieldLatActive: null,
        $fieldZip: null,
        $fieldCity: null,
        $fieldStreet: null,
        $fieldCountry: null,
        $mapTab: null ,

        $geoCodeUrl: null,
        $geoCodeUrlShort: null,
        $geoCodeBase: null ,
        $geoCodeOptions: null ,
        $tilesUrl: null,
        $tilesCopy: null,
        $zoomLevel: 12 ,
        $marker: null,
        $map: null,
        $iconClose: null
    };

    LeafFE.init = function (element) {
        // basic variable initialisation, uses data vars on the trigger button
        LeafFE.$element = element;
        LeafFE.$latitude = LeafFE.$element.attr('data-lat');
        LeafFE.$longitude = LeafFE.$element.attr('data-lng');
        LeafFE.$gLatitude = LeafFE.$element.attr('data-glat');
        LeafFE.$gLongitude = LeafFE.$element.attr('data-glon');
        LeafFE.$tilesUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
        LeafFE.$tilesCopy = LeafFE.$element.attr('data-copy');
        LeafFE.$geoCodeBase = LeafFE.$element.attr('data-geocodebase');
        LeafFE.$geoCodeOptions  = LeafFE.$element.attr('data-geocodeoptions');
        LeafFE.$geoCodeUrl      = LeafFE.$element.attr('data-geocodeurl');
        LeafFE.$geoCodeUrlShort = LeafFE.$element.attr('data-geocodeurlshort');
        LeafFE.$fieldLat        = LeafFE.$element.attr('data-namelat');
        LeafFE.$fieldLng        = LeafFE.$element.attr('data-namelng');
        LeafFE.$fieldZip        = LeafFE.$element.attr('data-namezip');
        LeafFE.$fieldCity       = LeafFE.$element.attr('data-namecity');
        LeafFE.$fieldStreet     = LeafFE.$element.attr('data-namestreet');
        LeafFE.$fieldCountry    = LeafFE.$element.attr('data-namecountry');
        LeafFE.$mapTab          = LeafFE.$element.attr('data-maptab');
        LeafFE.$fieldLatActive  = LeafFE.$element.attr('data-namelat-active');

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
            LeafFE.$zoomLevel = 5;
        }
        LeafFE.$map = L.map('t3js-location-map-container', {
            center: [LeafFE.$latitude, LeafFE.$longitude],
            zoom: LeafFE.$zoomLevel
        });
        L.tileLayer(LeafFE.$tilesUrl, {
            attribution: LeafFE.$tilesCopy
        }).addTo(LeafFE.$map);

        var myIcon = L.icon({
            iconUrl: '/typo3conf/ext/jv_events/Resources/Public/Css/images/marker-icon.png' ,
            iconRetinaUrl: '/typo3conf/ext/jv_events/Resources/Public/Css/images/marker-icon-2x.png' ,
            shadowUrl: '/typo3conf/ext/jv_events/Resources/Public/Css/images/marker-shadow.png' ,
            iconSize: [21, 40],
            iconAnchor: [10, 40],
            draggable: true
        });

        LeafFE.$marker = L.marker([LeafFE.$latitude, LeafFE.$longitude], { icon: myIcon }).addTo(LeafFE.$map);

        let position = LeafFE.$marker.getLatLng();

        LeafFE.$marker.on('dragend', function (event) {
            LeafFE.$marker = event.target;
        });
        LeafFE.$map.on('click', function (event) {
            LeafFE.$marker.setLatLng(event.latlng);
            if( $( LeafFE.$fieldLat ).length && $( LeafFE.$fieldLng ).length ) {
                $( LeafFE.$fieldLat ).val(LeafFE.$marker.getLatLng().lat);
                $( LeafFE.$fieldLng ).val(LeafFE.$marker.getLatLng().lng);
            }

            LeafFE.$map.panTo( event.latlng ) ;
            LeafFE.$map.setZoom(14) ;
            LeafFE.$zoomLevel = 14 ;
        }) ;



        $('#jvevents-geo-update').on('click', function () {
            LeafFE.updateAddress() ;

            $( LeafFE.$fieldLat ).val(LeafFE.$latitude);
            $( LeafFE.$fieldLng ).val(LeafFE.$longitude);
            // enable also latitude, if not already done by user.

        });
        if ( $( LeafFE.$fieldCity ).length ) {
            $(LeafFE.$fieldCity).on('change blur ', function() {
                LeafFE.updateAddress();

            } ) ;
        }

        if ( $( LeafFE.$fieldStreet ).length ) {
            $(LeafFE.$fieldStreet).on('change blur ', function() {
                LeafFE.updateAddress()  ;
            } ) ;
        }
        if ( $( LeafFE.$fieldCountry ).length ) {
            $(LeafFE.$fieldCountry).on('change blur ', function() {
                LeafFE.updateAddress()  ;
            } ) ;
        }

        if ( $( LeafFE.$mapTab ).length ) {
            $(LeafFE.$mapTab).on('click', function () {   // When tab is displayed...
                if( LeafFE.$map ) {
                    LeafFE.$map.off() ;
                    LeafFE.$map.remove() ;
                    if ($('#t3js-location-map-wrap').length) {
                        $('#t3js-location-map-wrap').remove() ;
                    }
                    $('div#map').html("") ;

                }
                refreshIntervalId = setInterval(function () {
                    LeafFE.addMapMarkup();
                    LeafFE.createMap() ;
                    clearInterval(refreshIntervalId);

                }, 300);

            });
        }
    };

    LeafFE.geocode = function () {
        if ( !LeafFE.$geoCodeUrl.length || LeafFE.$geoCodeUrl == false ) {
            return ;
        }
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
                                $( LeafFE.$fieldLat ).val(LeafFE.$latitude);
                                $( LeafFE.$fieldLng ).val(LeafFE.$longitude);
                                if(  LeafFE.$map ) {
                                    LeafFE.$map.panTo( { lat: LeafFE.$latitude ,lon: LeafFE.$longitude } ) ;
                                    if( LeafFE.$marker ) {
                                        LeafFE.$marker.setLatLng({lat: LeafFE.$latitude, lon: LeafFE.$longitude})
                                    }
                                    LeafFE.$map.setZoom(11)
                                }

                                LeafFE.$zoomLevel = 11 ;
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
                    $( LeafFE.$fieldLat ).val(LeafFE.$latitude);
                    $( LeafFE.$fieldLng ).val(LeafFE.$longitude);
                    if(  LeafFE.$map ) {
                        if( LeafFE.$marker ) {
                            LeafFE.$marker.setLatLng({lat: LeafFE.$latitude, lon: LeafFE.$longitude})
                        }
                        LeafFE.$map.panTo({lat: LeafFE.$latitude, lon: LeafFE.$longitude});
                        LeafFE.$map.setZoom(12)
                    }
                    LeafFE.$zoomLevel = 12 ;
                }

            }
        });
    };

    LeafFE.updateAddress = function ( ) {
        let address = $( LeafFE.$fieldCity ).length ?  $( LeafFE.$fieldCity ).val() : '' ;
        LeafFE.$geoCodeUrlShort = LeafFE.$geoCodeBase + encodeURI( address.trim() ) + LeafFE.$geoCodeOptions ;
        address +=  $( LeafFE.$fieldZip ).length ? ' ' + $( LeafFE.$fieldZip ).val() : '' ;
        address += $( LeafFE.$fieldStreet ).length ? ' ' + $( LeafFE.$fieldStreet ).val() : '' ;
        address +=  $( LeafFE.$fieldCountry ).length ? ' ' + $( LeafFE.$fieldCountry ).val() : '' ;
        $( LeafFE.$fieldLat ).val('');
        $( LeafFE.$fieldLng ).val('');
        LeafFE.$geoCodeUrl = LeafFE.$geoCodeBase + encodeURI( address.trim() ) + LeafFE.$geoCodeOptions ;
        LeafFE.geocode();

    } ;


    LeafFE.init($('div#map'));
    LeafFE.createMap();
