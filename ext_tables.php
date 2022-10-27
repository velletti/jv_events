<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'JvEvents' ,
	'Events',
	'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents' ,
    'Ajax',
    'Ajax'
);

if (TYPO3_MODE === 'BE') {

    //       * Registers a Backend Module
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'JvEvents' ,
        'web',	 // Make module a submodule of 'web'
        'eventmngt',	// Submodule key
        'after:List',						// Position
        [  \JVE\JvEvents\Controller\EventBackendController::class  => 'list, show, new, create, edit, update, delete, register, confirm, search, resendCitrix,resendHubspot'] ,
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
           'routeTarget' => JVE\JvEvents\Controller\EventBackendController::class . '::listAction',
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

ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_event', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_event.xlf');
ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_event');

ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_subevent', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_subevent.xlf');
ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_subevent');

ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_organizer', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_organizer.xlf');
ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_organizer');

ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_location', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_location.xlf');
ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_location');

ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_registrant', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_registrant.xlf');
ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_registrant');

ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_category', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_category.xlf');
ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_category');

ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_tag', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_tag.xlf');
ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_tag');