<?php

use JVelletti\JvEvents\Middleware\Ajax;
use JVelletti\JvEvents\Middleware\Nocache;
return [
    'frontend' => [
        'jve/jvevents/nocache' => [
            'target' => Nocache::class,
            'before' => [
                'typo3/cms-frontend/tsfe'
            ],
        ],
        'jve/jvevents/ajax' => [
            'target' => Ajax::class,
            'after' => [
                'typo3/cms-frontend/content-length-headers' ,
                'sjbr/sr-freecap/eidsr'
            ],
        ],
    ],
];
