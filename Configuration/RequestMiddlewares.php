<?php

use JVE\JvEvents\Middleware\Ajax;
return [
    'frontend' => [
        'jve/jvevents/ajax' => [
            'target' => Ajax::class,
            'after' => [
                'typo3/cms-frontend/content-length-headers' ,
                'sjbr/sr-freecap/eidsr'
            ],
        ],
    ],
];
