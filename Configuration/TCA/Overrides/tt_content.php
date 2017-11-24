<?php
defined('TYPO3_MODE') or die();
$_EXTKEY = 'jve_template' ;


$pluginSignature = str_replace('_','',$_EXTKEY) . '_events';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'pages, recursive,select_key';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_events.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tt_content.pi_flexform.jvevents_events', 'EXT:jv_events/Resources/Private/Language/locallang_csh_flexforms.xlf');

$configuration = \JVE\JvEvents\Utility\EmConfiguration::getEmConf();

if ( $configuration['showImporter'] == 1 ) {
    // Todo add importer to modules
    $EventModules = array('EventBackend' => 'list, show, new, create, edit, update, delete, register, confirm, search' ) ;

} else {
    $EventModules = array('EventBackend' => 'list, show, new, create, edit, update, delete, register, confirm, search' ) ;
}

/*
if (TYPO3_MODE === 'BE') {

    //       * Registers a Backend Module
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'JVE.' . $_EXTKEY,
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
*/

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Eventmanagement');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_event', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_event.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_event');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_organizer', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_organizer.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_organizer');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_location', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_location.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_location');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_registrant', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_registrant.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_registrant');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_category', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_category.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_category');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jvevents_domain_model_tag', 'EXT:jv_events/Resources/Private/Language/locallang_csh_tx_jvevents_domain_model_tag.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jvevents_domain_model_tag');
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder


