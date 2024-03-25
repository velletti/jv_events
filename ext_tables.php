<?php

if (!defined('TYPO3')) {
    die('Access denied.' );
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents' ,
    'Events',
    'Events: List Events with filters',
    'jvevents-plugin' ,
    'Events'
);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents' ,
    'Event',
    'Events: Single Event - show, create, edit ...',
    'jvevents-plugin' ,
    'Events'
);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents' ,
    'Organizers',
    'Organizer: List Organizers with filter ' ,
     'jvevents-plugin' ,
    'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Organizer',
    'Organizer: Single Organizer, show, create, edit' ,
    'jvevents-plugin' ,
    'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Assist',
    'Organizer Assistant' ,
    'jvevents-plugin' ,
    'Events'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents' ,
    'Locations',
    'Location: List of Locations ',
    'jvevents-plugin' ,
    'Events'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents' ,
    'Location',
    'Location: Single Location - show, create, edit',
    'jvevents-plugin' ,
    'Events'
);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents' ,
    'Registrant',
    'List Registrant ',
    'jvevents-plugin' ,
    'Events'
);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents' ,
    'Ajax',
    'Ajax',
    'jvevents-plugin' ,
    'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents' ,
    'Curl',
    'Load events from other website via Curl',
    'jvevents-plugin' ,
    'Events'
);

if ($GLOBALS['TYPO3_REQUEST'] && TYPO3\CMS\Core\Http\ApplicationType::fromRequest( $GLOBALS['TYPO3_REQUEST'] )->isBackend()) {

    //       * Registers a Backend Module
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'JvEvents' ,
        'web',	 // Make module a submodule of 'web'
        'eventmngt',	// Submodule key
        'after:List',						// Position
        [  \JVelletti\JvEvents\Controller\EventBackendController::class  => 'list, show, new, create, edit, update, delete, register, confirm, search, resendCitrix,resendHubspot'] ,
        array(
            'access' => 'user,group',
            'icon'   => 'EXT:jv_events/ext_icon_importer.gif',
            'labels' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_eventmngt.xlf',
        )
    );

    /*
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
            'web',
            'eventmngt',
            'after:List',
            '',
            [
               'routeTarget' => JVelletti\JvEvents\Controller\EventBackendController::class . '::listAction',
                'access' => 'user,group',
                'name' => 'web_eventmngt',
                'workspaces' => 'online',
                'icon' => 'EXT:jv_events/ext_icon_importer.gif',
                'labels' => [
                    'LLL:EXT:jv_events/Resources/Private/Language/locallang_eventmngt.xlf',
                ],
                'navigationFrameModule' => 'web',
                'navigationFrameModuleParameters' => ['currentModule' => 'web_eventmngt'],
            ]
        );
    */
}

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_event', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_event.xlf');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_event');

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_subevent', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_subevent.xlf');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_subevent');

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_organizer', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_organizer.xlf');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_organizer');

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_location', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_location.xlf');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_location');

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_registrant', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_registrant.xlf');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_registrant');

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_category', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_category.xlf');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_category');

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_tag', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_tag.xlf');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_tag');