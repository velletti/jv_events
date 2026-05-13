<?php

if (!defined('TYPO3')) {
    die('Access denied.' );
}

/*  

// maybe we can remove this  

$pluginArray = \JVelletti\JvEvents\Utility\PluginUtility::getPluginArray() ;


foreach ( $pluginArray as $plugin ) {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'JvEvents' ,
        $plugin['name'],
        $plugin['title'],
        'jvevents-plugin' ,
        'Events'
    );
}

if (isset($GLOBALS['TYPO3_REQUEST'] ) && TYPO3\CMS\Core\Http\ApplicationType::fromRequest( $GLOBALS['TYPO3_REQUEST'] )->isBackend()) {

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


}

*/ 