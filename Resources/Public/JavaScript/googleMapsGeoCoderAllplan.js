
var findAddress = function(params) {
    // Hier sollte die Logik stehen, die eine Adresse sucht und die Map aktualisiert,
    // z.B. initGeoCoderMap() mit neuen Parametern aufrufen.
    // Platzhalter:
    initAddress = params.address;
    initGeoCoderMap();
};

function updateMarker(address) {
    if (!address.includes(",")) {
        address = address + ",DE";
    }
    if (map && map.getZoom() < 10) {
        initZoom = 9;
    } else if (map) {
        initZoom = map.getZoom();
    }
    findAddress({address: address});
}

function updateMarkerDefault(address, zoom) {
    initZoom = zoom;
    findAddress({address: address});
}

function initGeoCoderMap() {


    var myLatLng = {lat: 48.1148, lng: 11.4712};
    var zoom = (typeof initZoom !== "undefined") ? initZoom : 8;
    var address = (typeof initAddress !== "undefined") ? initAddress : concatAddress();

    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: zoom,
        center: myLatLng,
        streetViewControl: false,
        mapTypeControl: false,
        draggableCursor: "default",
        mapId: "50580b58009c1faf",
        gestureHandling: "auto", // explizit setzen
        scrollwheel: true        // explizit setzen
    });

    var geocoder = new google.maps.Geocoder();

    geocoder.geocode({ address: address }, function(results, status) {
        if ( status === google.maps.GeocoderStatus.OK ) {
            var position = results[0].geometry.location;
            map.setCenter(position);
            map.setZoom(zoom);

            const marker = new google.maps.marker.AdvancedMarkerElement({
                map: map,
                position: position,
                title: address
            });

            function updateFieldsAndCity(pos , map) {
                var lat = (typeof pos.lat === "function") ? pos.lat() : pos.lat;
                var lng = (typeof pos.lng === "function") ? pos.lng() : pos.lng;

                $('#lat').val(Number(lat).toFixed(11));
                $('#lng').val(Number(lng).toFixed(11));

                jQuery("#jv_events_geo").data("lat", lat).data("lng", lng).data("zoom", map.getZoom());
                updateCity();
                updateMinMax(map);
                jv_events_refreshList();
            }
            function updateMinMax(map) {
                if( jQuery('#minlng').length > 0   ) {
                    // now we need to calculate the minlng and maxlng from map bounds
                    var bounds = false ;
                    if ( map && (typeof map.getBounds === "function") ) {
                        bounds = map.getBounds();
                    }
                    if (bounds && (typeof bounds.getNorthEast === "function") ) {
                        var ne = bounds.getNorthEast();
                        var sw = bounds.getSouthWest();
                        var minlng = Math.min(ne.lng(), sw.lng());
                        var maxlng = Math.max(ne.lng(), sw.lng());
                        $('#maxlng').val(Number(maxlng).toFixed(11));
                        $('#minlng').val(Number(minlng).toFixed(11));
                        // and the same for lat
                        var minlat = Math.min(ne.lat(), sw.lat());
                        var maxlat = Math.max(ne.lat(), sw.lat());
                        $('#maxlat').val(Number(maxlat).toFixed(11));
                        $('#minlat').val(Number(minlat).toFixed(11));
                        console.log ("Bounds updated: minlat=" + minlat + ", maxlat=" + maxlat + ", minlng=" + minlng + ", maxlng=" + maxlng);
                    }
                }
            }

            function updateCity() {
                setTimeout(function() {
                    if( jQuery("SELECT#jv_events_filter_citys").length &&  jQuery("SELECT#jv_events_filter_citys").val() != "-") {
                        jQuery("SELECT#jv_events_filter_citys").val("") ;
                    } else if ( jQuery("#jv_events_geo") &&  jQuery("#jv_events_geo").data("zoom") > 0 ) {
                        //    map.setZoom(jQuery("#jv_events_geo").data("zoom"));
                    }
                }, 1500);
            }

            updateFieldsAndCity(position , map);

            map.addListener('center_changed', function()
            {
                updateFieldsAndCity(marker.position , map);
            });

            map.addListener('click', function(e)
            {
                marker.position = e.latLng;
                map.panTo(e.latLng);
                updateFieldsAndCity(e.latLng , map);
            });

            map.addListener('zoom_changed', function() {
                updateFieldsAndCity(marker.position , map);
            });



            map.addListener('dblclick', function(e) {
                marker.position = e.latLng;
                map.panTo(e.latLng);
                var newZoom = map.getZoom() < 15 ? 15 : map.getZoom() + 1;
                map.setZoom(newZoom);
                updateFieldsAndCity(e.latLng , map);
            });

        } else {
            // Fehlerbehandlung
            $("#geosearcherrormessage").show();
        }
    });
}
