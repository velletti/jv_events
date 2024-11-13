<?php

return [
    'dependencies' => [
        'backend',
        'core',
    ],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        'showEventInFrontend.js' => 'EXT:jv_events/Resources/Public/JavaScript/Backend/ShowEventInFrontend.js',
        'jvEventsLeafLetBase.js' => 'EXT:jv_events/Resources/Public/JavaScript/Backend/leaflet-1-7-1.js',
        'jvEventsLeafLetBackend.js' => 'EXT:jv_events/Resources/Public/JavaScript/Backend/ShowEventInFrontend.js',
       'jv-events-leaflet-src' => 'EXT:jv_events/Resources/Public/JavaScript/esm/leaflet-src.esm.js',
       'jv-events-leaflet-backend' => 'EXT:jv_events/Resources/Public/JavaScript/esm/leaflet-backend.js',
       '@jvelletti/jv-events' => 'EXT:jv_events/Resources/Public/JavaScript/esm/',
    ],
];
