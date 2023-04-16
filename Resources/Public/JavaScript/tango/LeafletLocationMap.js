function AddressLeaflet() {
    var obj = {};

    obj.map = null;
    obj.markers = [];

    obj.run = function () {
        obj.map = L.map('map').setView([51.505, 11.09], 7);
        var mapBounds = L.latLngBounds();
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
                '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
                'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            id: 'mapbox.streets'
        }).addTo(obj.map);

        var records = document.getElementById("jv_events_location_list");
        for (var i = 0; i < records.childNodes.length; i++) {
            var item = records.childNodes[i];
            if ( item.getAttribute('data-lat').length && item.getAttribute('data-lng').length ) {

                let myIcon = AddressLeafletGetIcon( false , false ) ;
                var marker = L.marker([item.getAttribute('data-lat'), item.getAttribute('data-lng')], { icon: myIcon } )
                    .addTo(obj.map)
                    .bindPopup(document.getElementById('map-marker-content-' + item.getAttribute('data-uid')).innerHTML);
                obj.markers.push(marker);
            }

        }
        var group = new L.featureGroup(obj.markers);

        obj.map.fitBounds(group.getBounds() , { padding: [50, 50] });
    };

    obj.openMarker = function (markerId) {
        obj.markers[markerId].openPopup();
    };

    return obj;
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

document.addEventListener("DOMContentLoaded", function () {
    if( $('#map').length ) {
        var AddressMapInstance = AddressLeaflet();
        AddressMapInstance.run();

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
