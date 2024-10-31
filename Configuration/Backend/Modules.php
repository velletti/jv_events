<?php

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JVelletti\JvEvents\Controller\EventBackendController;

return [
    'jvevents_eventmngt' => [
        'parent' => 'web',
        'access' => 'user',
        'path' => '/module/jvevents/eventmngt',
        'iconIdentifier' => 'jvevents-plugin',
        'labels' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_eventmngt.xlf',
        'extensionName' => 'JvEvents',
        'controllerActions' => [
            EventBackendController::class => [
                'list',
                'show',
                'confirm',
                'search',
                'resendCitrix',
                ',resendHubspot',
            ],
        ],
    ],
];
