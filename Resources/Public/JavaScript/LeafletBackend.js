define(['jquery', 
    'TYPO3/CMS/Backend/Icons', 
    'TYPO3/CMS/Backend/FormEngine', 
    'JVelletti/JvEvents/Leaflet',
    'JVelletti/JvEvents/LeafletBackend'
], function ($, Icons, FormEngine) {
    'use strict';

    let LeafletBackend = {
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
        $zoomLevel: 13,
        $marker: null,
        $map: null,
        $iconClose: null
    };

    // Load icon via TYPO3 Icon-API and requireJS
    Icons.getIcon('actions-close', Icons.sizes.small).done(function (actionsClose) {
        LeafletBackend['$iconClose'] = actionsClose;
    });

    LeafletBackend.init = function (element) {
        // basic variable initialisation, uses data vars on the trigger button
        LeafletBackend.$element = element;
        LeafletBackend.$labelTitle = LeafletBackend.$element.attr('data-label-title');
        LeafletBackend.$labelClose = LeafletBackend.$element.attr('data-label-close');
        LeafletBackend.$labelImport = LeafletBackend.$element.attr('data-label-import');
        LeafletBackend.$latitude = LeafletBackend.$element.attr('data-lat');
        LeafletBackend.$longitude = LeafletBackend.$element.attr('data-lon');
        LeafletBackend.$gLatitude = LeafletBackend.$element.attr('data-glat');
        LeafletBackend.$gLongitude = LeafletBackend.$element.attr('data-glon');
        LeafletBackend.$tilesUrl = LeafletBackend.$element.attr('data-tiles');
        LeafletBackend.$tilesCopy = LeafletBackend.$element.attr('data-copy');
        LeafletBackend.$geoCodeUrl = LeafletBackend.$element.attr('data-geocodeurl');
        LeafletBackend.$geoCodeUrlShort = LeafletBackend.$element.attr('data-geocodeurlshort');
        LeafletBackend.$fieldLat = LeafletBackend.$element.attr('data-namelat');
        LeafletBackend.$fieldLon = LeafletBackend.$element.attr('data-namelon');
        LeafletBackend.$fieldLatActive = LeafletBackend.$element.attr('data-namelat-active');

        // add the container to display the map as a nice overlay
        if (!$('#t3js-location-map-wrap').length) {
            LeafletBackend.addMapMarkup();
        }
    };

    LeafletBackend.addMapMarkup = function () {
        $('body').append(
            '<div id="t3js-location-map-wrap">' +
            '<div class="t3js-location-map-title">' +
            '<div class="btn-group"><a href="#" class="btn btn-icon btn-default" title="' + LeafletBackend.$labelClose + '" id="t3js-jvevents-close-map">' +
            LeafletBackend.$iconClose +
            '</a>' +
            '<a class="btn btn-default" href="#" title="Import marker position to form" id="t3js-jvevents-import-position">' +
            LeafletBackend.$labelImport +
            '</a></div>' +
            LeafletBackend.$labelTitle +
            '</div>' +
            '<div class="t3js-location-map-container" id="t3js-location-map-container">' +
            '</div>' +
            '</div>'
        );
    };

    LeafletBackend.createMap = function () {

        if (((!LeafletBackend.$latitude || !LeafletBackend.$longitude) || (LeafletBackend.$latitude == 0 && LeafletBackend.$longitude == 0)) && LeafletBackend.$geoCodeUrl != null) {
            LeafletBackend.geocode();
        }

        // The ultimate fallback: if one of the coordinates is empty, fallback to Kopenhagen.
        // Thank you Kaspar for TYPO3 and its great community! ;)
        if (LeafletBackend.$latitude == null || LeafletBackend.$longitude == null) {
            LeafletBackend.$latitude = LeafletBackend.$gLatitude;
            LeafletBackend.$longitude = LeafletBackend.$gLongitude;
            // set zoomlevel lower for faster navigation
            LeafletBackend.$zoomLevel = 4;
        }
        LeafletBackend.$map = L.map('t3js-location-map-container', {
            center: [LeafletBackend.$latitude, LeafletBackend.$longitude],
            zoom: LeafletBackend.$zoomLevel
        });
        L.tileLayer(LeafletBackend.$tilesUrl, {
            attribution: LeafletBackend.$tilesCopy
        }).addTo(LeafletBackend.$map);

        LeafletBackend.$marker = L.marker([LeafletBackend.$latitude, LeafletBackend.$longitude], {
            draggable: true
        }).addTo(LeafletBackend.$map);

        let position = LeafletBackend.$marker.getLatLng();

        LeafletBackend.$marker.on('dragend', function (event) {
            LeafletBackend.$marker = event.target;
            position = LeafletBackend.$marker.getLatLng();
        });
        LeafletBackend.$map.on('click', function (event) {
            LeafletBackend.$marker.setLatLng(event.latlng);
        });
        // import coordinates and close overlay
        $('#t3js-jvevents-import-position').on('click', function () {
            // set visual coordinates
            $('input[data-formengine-input-name="' + LeafletBackend.$fieldLat + '"]').val(LeafletBackend.$marker.getLatLng().lat);
            $('input[data-formengine-input-name="' + LeafletBackend.$fieldLon + '"]').val(LeafletBackend.$marker.getLatLng().lng);
            // set hidden fields values
            $('input[name="' + LeafletBackend.$fieldLat + '"]').val(LeafletBackend.$marker.getLatLng().lat);
            $('input[name="' + LeafletBackend.$fieldLon + '"]').val(LeafletBackend.$marker.getLatLng().lng);
            // enable also latitude, if not already done by user.
            $('input[id="' + LeafletBackend.$fieldLatActive + '"]').parentsUntil('.form-group').removeClass('disabled');
            $('input[id="' + LeafletBackend.$fieldLatActive + '"]').prop('checked', true);

            // mark fields as changed for re-evaluation and revalidate the form,
            // this is e.g. needed when this wizard is used on inline elements
            FormEngine.Validation.markFieldAsChanged($('input[name="' + LeafletBackend.$fieldLat + '"]'));
            FormEngine.Validation.markFieldAsChanged($('input[name="' + LeafletBackend.$fieldLon + '"]'));
            FormEngine.Validation.validate();

            // close map after import of coordinates.
            $('#t3js-location-map-wrap').removeClass('active');
        });
        // close overlay without any further action
        $('#t3js-jvevents-close-map').on('click', function () {
            $('#t3js-location-map-wrap').removeClass('active');
        });
    };

    LeafletBackend.geocode = function () {
        $.ajax({
            type: 'GET',
            url: LeafletBackend.$geoCodeUrl,
            async: false,
            dataType: 'json',
            success: function (data) {
                if (data.length == 0) {
                    $.ajax({
                        type: 'GET',
                        url: LeafletBackend.$geoCodeUrlShort,
                        async: false,
                        dataType: 'json',
                        success: function (data) {
                            if (data.length != 0) {
                                $.each(data[0], function (key, value) {
                                    if (key == "lat") {
                                        LeafletBackend.$latitude = value;
                                    }
                                    if (key == "lon") {
                                        LeafletBackend.$longitude = value;
                                    }
                                });
                            }
                        }
                    });
                } else {
                    $.each(data[0], function (key, value) {
                        if (key == "lat") {
                            LeafletBackend.$latitude = value;
                        }
                        if (key == "lon") {
                            LeafletBackend.$longitude = value;
                        }
                    });
                }
            }
        });
    };

    LeafletBackend.initializeEvents = function (element) {
        $(element).on('click', function () {
            if (LeafletBackend.$map !== null) {
                LeafletBackend.$map.remove();
                LeafletBackend.$map = null;
            }
            LeafletBackend.init($(this));
            LeafletBackend.createMap();
            $('#t3js-location-map-wrap').addClass('active');
        });
    };

    // reinit when form has changes, e.g. inline relations loaded using ajax
    LeafletBackend.reinitialize = FormEngine.reinitialize;
    FormEngine.reinitialize = function () {
        LeafletBackend.reinitialize();
        if ($('.locationMapWizard').length) {
            LeafletBackend.initializeEvents('.locationMapWizard');
        }
    };
    //LeafletBackend.addMapMarkup();
    LeafletBackend.initializeEvents('.locationMapWizard');
    return LeafletBackend;
});
