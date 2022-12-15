
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
        $movemarkerallowed: false ,
        $addMarkerFrom: false ,

        $geoCodeUrl: null,
        $geoCodeUrlShort: null,
        $geoCodeBase: null ,
        $geoCodeOptions: null ,
        $tilesUrl: null,
        $tilesCopy: null,
        $zoomLevel: 12 ,
        $zoomAfterGeocode: true ,
        $marker: null,
        $map: null,
        $iconClose: null
    };

    LeafFE.init = function (element) {
        // basic variable initialisation, uses data vars on the trigger button
        LeafFE.$element = element;
        LeafFE.$latitude        = LeafFE.$element.attr('data-lat');
        LeafFE.$longitude       = LeafFE.$element.attr('data-lng');
        LeafFE.$gLatitude       = LeafFE.$element.attr('data-glat');
        LeafFE.$gLongitude      = LeafFE.$element.attr('data-glon');
        LeafFE.$tilesUrl        = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
        LeafFE.$tilesCopy       = LeafFE.$element.attr('data-copy');
        LeafFE.$geoCodeBase     = LeafFE.$element.attr('data-geocodebase');
        LeafFE.$geoCodeOptions  = LeafFE.$element.attr('data-geocodeoptions');
        LeafFE.$geoCodeUrl      = LeafFE.$element.attr('data-geocodeurl');
        LeafFE.$geoCodeUrlShort = LeafFE.$element.attr('data-geocodeurlshort');
        LeafFE.$fieldLat        = LeafFE.$element.attr('data-namelat');
        LeafFE.$fieldLng        = LeafFE.$element.attr('data-namelng');
        LeafFE.$movemarkerallowed   = LeafFE.$element.attr('data-movemarkerallowed');
        if( LeafFE.$element.attr('data-mapzoom')) {
            LeafFE.$zoomLevel     = LeafFE.$element.attr('data-mapzoom');
            LeafFE.$zoomAfterGeocode   = false;

        }
        if( LeafFE.$element.attr('data-addmarkerfrom')) {
            LeafFE.$addMarkerFrom     = $( LeafFE.$element.attr('data-addmarkerfrom')) ;
        }


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
            console.log( " fallback ") ;
            LeafFE.$latitude = LeafFE.$gLatitude;
            LeafFE.$longitude = LeafFE.$gLongitude;
            LeafFE.$zoomLevel = 5;
        }
        LeafFE.$map = L.map('t3js-location-map-container', {
            center: [LeafFE.$latitude , LeafFE.$longitude] ,
            zoom: LeafFE.$zoomLevel
        });

            L.tileLayer(LeafFE.$tilesUrl, {
                attribution: LeafFE.$tilesCopy
            }).addTo(LeafFE.$map);

            let myIcon = LeafFE.getIcon( false , false ) ;

            LeafFE.$marker = L.marker([LeafFE.$latitude, LeafFE.$longitude], { icon: myIcon }).addTo(LeafFE.$map);

            LeafFE.$marker.on('dragend', function (event) {
                LeafFE.$marker = event.target;
            });

            if( LeafFE.$movemarkerallowed ) {
                LeafFE.$map.on('click touch', function (event) {


                    LeafFE.$marker.setLatLng(event.latlng);

                    if( $( LeafFE.$fieldLat ).length && $( LeafFE.$fieldLng ).length ) {

                        $( LeafFE.$fieldLat ).val(LeafFE.$marker.getLatLng().lat);
                        $( LeafFE.$fieldLng ).val(LeafFE.$marker.getLatLng().lng);
                    }
                    LeafFE.$map.setView( event.latlng , 12 ) ;
                    //LeafFE.$map.panTo( event.latlng ) ;
                    //LeafFE.$map.setZoom(12) ;

                    LeafFE.$zoomLevel = 12 ;
                    // LeafFE.reInitMap() ;

                }) ;
            }




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
                    LeafFE.reInitMap() ;

                });
            }
            LeafFE.getBounds() ;
            if ( LeafFE.$addMarkerFrom ) {
                let myIcon2 = LeafFE.getIcon( [12,21] , [6,21] ) ;
                // toDo add marker for each address
                //  jQuery('.tx-jv-events DIV.jv-events-row').each( function (i) {
                  //   L.marker([jQuery(this).data("latitude"),jQuery(this).data("longitude")], { icon: myIcon2 }).addTo(LeafFE.$map);
                //  }) ;
            }


            LeafFE.$map.on( 'zoomstart zoom zoomend movestart move moveend' , function() {
                LeafFE.getBounds() ;
                jv_events_refreshList() ;
            }) ;

    };

    LeafFE.reInitMap = function() {
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

            LeafFE.getBounds();

            clearInterval(refreshIntervalId);

        }, 300);
    } ;

    LeafFE.getIcon = function( size , ancor ) {
        if( !size ) {
            size = [21, 40] ;
        }
        if( !ancor ) {
            ancor = [10, 40];
        }
        return L.icon({
            iconUrl: '/typo3conf/ext/jv_events/Resources/Public/Css/images/marker-icon.png' ,
            iconRetinaUrl: '/typo3conf/ext/jv_events/Resources/Public/Css/images/marker-icon-2x.png' ,
            shadowUrl: '/typo3conf/ext/jv_events/Resources/Public/Css/images/marker-shadow.png' ,
            iconSize: size,
            iconAnchor: ancor,
            draggable: true
        });
    };

    LeafFE.getBounds = function() {
        if( jQuery( "#filterType7body").length ) {
            var bounds = LeafFE.$map.getBounds();
            if ( bounds ) {
                // if map not visible or not ready, size of boundary will be 1 pixel ..
                minLat = Math.min( bounds.getNorth() , bounds.getSouth() ) ;
                maxLat = Math.max( bounds.getNorth() , bounds.getSouth() ) ;
                if( minLat == maxLat) {
                    LeafFE.reInitMap() ;
                }
                minLng = Math.min( bounds.getWest() , bounds.getEast()  ) ;
                maxLng = Math.max( bounds.getWest() , bounds.getEast()  ) ;
            }

            // console.log("In Leaflet: minlat:" + minLat + " maxlat: " +  maxLat + " minlng: " + minLng + " maxlng" + maxLng ) ;
            jQuery( "#filterType7body").data("minlat" , minLat ).data("maxlat" , maxLat ).data("minlng" , minLng ).data("maxlng" , maxLng ) ;
        }
    } ;


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
                                    if( LeafFE.$zoomAfterGeocode  ) {
                                        LeafFE.$map.setZoom(11) ;
                                        LeafFE.$zoomLevel = 11 ;
                                    }

                                }

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
                        if( LeafFE.$zoomAfterGeocode  ) {
                            LeafFE.$map.setZoom(12) ;
                            LeafFE.$zoomLevel = 12 ;
                        }

                    }

                }

            }
        });
    };

    LeafFE.updateAddress = function ( ) {
        let address = $( LeafFE.$fieldCity ).length ?  '&city=' + encodeURI( $( LeafFE.$fieldCity ).val()) : '' ;
        address +=  $( LeafFE.$fieldCountry ).length ? '&country=' +  encodeURI($( LeafFE.$fieldCountry ).val()) : '' ;
        LeafFE.$geoCodeUrlShort = LeafFE.$geoCodeBase + LeafFE.$geoCodeOptions +  address.trim()  ;

        address +=  $( LeafFE.$fieldZip ).length ? '&postalcode=' +   encodeURI($( LeafFE.$fieldZip ).val()) : '' ;
        address += $( LeafFE.$fieldStreet ).length ? '&street=' +     encodeURI($( LeafFE.$fieldStreet ).val()) : '' ;

        $( LeafFE.$fieldLat ).val('');
        $( LeafFE.$fieldLng ).val('');
        LeafFE.$geoCodeUrl = LeafFE.$geoCodeBase + LeafFE.$geoCodeOptions + address.trim()   ;
        LeafFE.geocode();

    } ;


    LeafFE.init($('div#map'));
    LeafFE.createMap();
