<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'JVE.jv_events' ,
	'Events',
	'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JVE.jv_events' ,
    'Ajax',
    'Ajax'
);
$configuration = \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();


if (TYPO3_MODE === 'BE') {
    $_EXTKEY = "jv_events" ;
    $EventModules = array('EventBackend' => 'list, show, new, create, edit, update, delete, register, confirm, search, resendCitrix,resendHubspot' ) ;

    // \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule() ;

    //       * Registers a Backend Module
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'JVE.jv_events' ,
        'web',	 // Make module a submodule of 'web'
        'eventmngt',	// Submodule key
        'after:List',						// Position
        $EventModules ,
        array(
            'access' => 'user,group',
            'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon_importer.gif',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_eventmngt.xlf',
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
                'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_eventmngt.xlf',
            ],
            'navigationFrameModule' => 'web',
            'navigationFrameModuleParameters' => ['currentModule' => 'web_eventmngt'],
        ]
    );
*/
}