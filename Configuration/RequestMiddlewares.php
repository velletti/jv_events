<?php

return [
    'frontend' => [
        'jve/jvevents/ajax' => [
            'target' => \JVE\JvEvents\Middleware\Ajax::class,
            'after' => [
                'typo3/cms-frontend/content-length-headers' ,
                'sjbr/sr-freecap/eidsr'
            ],
        ],
    ],
];
