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
    ],
];