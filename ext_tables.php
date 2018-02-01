<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'JVE.jv_events' ,
	'Events',
	'Events'
);

$configuration = \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();

if ( $configuration['showImporter'] == 1 ) {
    // Todo add importer to modules
    $EventModules = array('EventBackend' => 'list, show, new, create, edit, update, delete, register, confirm, search, resendCitrix' ) ;

} else {
    $EventModules = array('EventBackend' => 'list, show, new, create, edit, update, delete, register, confirm, search, resendCitrix' ) ;
}
if (TYPO3_MODE === 'BE') {

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

}