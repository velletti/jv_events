function AddressLeaflet() {
    var obj = {};

    obj.map = null;
    obj.markers = [];

    obj.run = function () {
        let lat = 51.505 ,
            lng = 11.09 ,
            zoom = 7  ,
            autoFit = true ,
            latCookie = AddressGetCookie('tx_events_default_lat') ,
            lngCookie = AddressGetCookie('tx_events_default_lng') ,
            zoomCookie = AddressGetCookie('tx_events_default_zoom')
            ;
        if ( (  latCookie != 'undefined' && latCookie != null )
            && ( lngCookie != 'undefined' && lngCookie != null )
            &&  ( zoomCookie != 'undefined' && zoomCookie != null )
        ) {
            autoFit = false ;
            lat = parseFloat(latCookie) ;
            lng = parseFloat(lngCookie) ;
            zoom = parseInt(zoomCookie) ;
         //   console.log( "Auto fit false and use cookies: lat:" + lat + " Lng:"+ lng + " Zoom:" + zoom ) ;
        } else {
            jQuery("#jv_events-unset-cookies").addClass("d-none") ;
        }
        obj.map = L.map('map').setView([lat, lng], zoom);
        var mapBounds = L.latLngBounds();
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
                '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
                'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            id: 'mapbox.streets'
        }).addTo(obj.map);

        var records = document.getElementById("jv_events_location_list").children;

        if( records.length  ) {
            for (var i = 0; i < records.length; i++) {
                var item = records[i];
                if ( item.getAttribute('data-lat').length && item.getAttribute('data-lng').length ) {

                    let myIcon = AddressLeafletGetIcon( false , false ) ;
                    var marker = L.marker([item.getAttribute('data-lat'), item.getAttribute('data-lng')], { icon: myIcon } )
                        .addTo(obj.map)
                        .bindPopup(document.getElementById('map-marker-content-' + item.getAttribute('data-uid')).innerHTML);
                    obj.markers.push(marker);
                }
            }
            var group = new L.featureGroup(obj.markers);



            if(autoFit)  {
                obj.map.fitBounds(group.getBounds() , { padding: [50, 50] });
            }
        }

        if(  jQuery("#jv_events-store-as-cookie").length  ) {
            $('#jv_events-store-as-cookie').bind("click", function () {
                AddressStoreCookie(obj.map );
                jQuery("#jv_events-unset-cookies").removeClass("d-none") ;
            });
        }
        if(  jQuery("#jv_events-unset-cookies").length  ) {
            $('#jv_events-unset-cookies').bind("click", function () {
                AddressUnsetCookie(obj.map );
            });
        }
        if(  jQuery("#jv_events-ask-position").length  ) {
            $('#jv_events-ask-position').bind("click", function() {
                AddressAskPosition(obj.map );
            });
        }


    };

    obj.openMarker = function (markerId) {
        obj.markers[markerId].openPopup();
    };

    return obj;
}
AddressAskPosition = function( map ) {
    if( location.protocol == "https:") {
        //    console.log("location.protocol == \"https:\" ") ;
        if (navigator.geolocation) {
            showSpinner() ;
            //   console.log("navigator.geolocation ") ;
            navigator.geolocation.getCurrentPosition(AddressInitPosition , AddressErrorPosition );
        } else {
            jQuery('#jv_events_geo_disp_sub').removeClass("d-none") ;
            jQuery('#jv_events_geo_disp_spinner').addClass("d-none") ;
        }
    }
}
AddressInitPosition  = function( position ) {
    if( position && position.coords && AddressMapInstance && AddressMapInstance.map ) {
        console.log( position.coords  )
        AddressMapInstance.map.panTo([ position.coords.latitude, position.coords.longitude]);
    }
    hideSpinner() ;
}
AddressErrorPosition  = function( ) {
    hideSpinner() ;
}
AddressUnsetCookie = function( map ) {
    var d = new Date();
    d.setTime(d.getTime() - ( 24*60*60*1000 ));
    var expires = 'expires=' + d.toUTCString();
    document.cookie = 'tx_events_filter_north=' +  '; ' + expires + ';path=/';
    document.cookie = 'tx_events_filter_west='  +  '; ' + expires + ';path=/';
    document.cookie = 'tx_events_filter_south=' +  '; ' + expires + ';path=/';
    document.cookie = 'tx_events_filter_east='  +  '; ' + expires + ';path=/';
    document.cookie = 'tx_events_default_zoom=' +  '; ' + expires + ';path=/';
    document.cookie = 'tx_events_default_lat='  +  '; ' + expires + ';path=/';
    document.cookie = 'tx_events_default_lng='  +  '; ' + expires + ';path=/';
    alert("success!") ;
    document.location.reload() ;

}

AddressStoreCookie = function( map ) {
    // Set cookie for 365 days
    if ( AddressGetCookie('tx_cookies_accepted') == "1" && map.getBounds()  ) {
        var d = new Date();
        d.setTime(d.getTime() + ( 24*60*60*1000 * 365));
        var expires = 'expires=' + d.toUTCString();
        document.cookie = 'tx_events_filter_north=' + map.getBounds().getNorth()  + '; ' + expires + ';path=/';
        document.cookie = 'tx_events_filter_west='  + map.getBounds().getWest()   + '; ' + expires + ';path=/';
        document.cookie = 'tx_events_filter_south=' + map.getBounds().getSouth()  + '; ' + expires + ';path=/';
        document.cookie = 'tx_events_filter_east='  + map.getBounds().getEast()  + '; ' + expires + ';path=/';
        document.cookie = 'tx_events_default_zoom=' + map.getZoom()  + '; ' + expires + ';path=/';
        document.cookie = 'tx_events_default_lat='  + map.getCenter()['lat']  + '; ' + expires + ';path=/';
        document.cookie = 'tx_events_default_lng='  + map.getCenter()['lng']  + '; ' + expires + ';path=/';
        if( $( "#jv_events-store-as-cookie-div").length ) {
            $( "#jv_events-store-as-cookie-div").html( "<b>Success!</b><br>"
                + 'Zoom: ' +  map.getZoom() + " | " + map.getCenter()['lat'] + " / "+ map.getCenter()['lng'] );
        }


    }
}
AddressGetCookie = function(name) {
    let v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    return v ? v[2] : null;
}

AddressLeafletGetIcon = function( size , ancor ) {
    if( !size ) {
        size = [21, 40] ;
    }
    if( !ancor ) {
        ancor = [10, 40];
    }
    return L.icon({
        iconUrl: '/typo3conf/ext/jv_events/Resources/Public/Css/tango/marker-icon.png' ,
        iconRetinaUrl: '/typo3conf/ext/jv_events/Resources/Public/Css/tango/marker-icon-2x.png' ,
        shadowUrl: '/typo3conf/ext/jv_events/Resources/Public/Css/tango/marker-shadow.png' ,
        iconSize: size,
        iconAnchor: ancor,
        draggable: true
    });
};
AddressLeafletSetBounds = function( map ) {

    $('#map').data("east", map.getBounds().getEast())
        .data("west", map.getBounds().getWest())
        .data("north", map.getBounds().getNorth())
        .data("south", map.getBounds().getSouth())
        .data("zoom", map.getZoom() )
    ;
}
document.addEventListener("DOMContentLoaded", function () {
    if( $('#map').length ) {
        AddressMapInstance = AddressLeaflet();
        AddressMapInstance.run();

    //    AddressLeafletSetBounds(AddressMapInstance.map) ;

        AddressMapInstance.map.on('dragend zoomend moveend', function () {
      //     AddressLeafletSetBounds(AddressMapInstance.map) ;

        });
        document.addEventListener('click', function (event) {
            if (!event.target.matches('.address__markerlink')) {
                return;
            }
            event.preventDefault();
            var element = event.target;
            $('html, body').animate({ scrollTop: 0 }, 600);
            AddressMapInstance.openMarker(parseInt(element.getAttribute('data-iteration-id')));
        }, false);
    }


});
