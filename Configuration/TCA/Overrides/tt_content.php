<?php
defined('TYPO3') or die();
// EXTKEY is not set here .. see https://docs.typo3.org/typo3cms/ExtbaseFluidBook/b-ExtbaseReference/Index.html

$pluginArray = \JVelletti\JvEvents\Utility\PluginUtility::getPluginArray() ;

foreach ( $pluginArray as $plugin ) {
    $pluginSignature = str_replace('_','','jv_events') . '_' . strtolower( $plugin['name'] ) ;
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'pages, recursive,select_key';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:jv_events/Configuration/FlexForms/flexform_' . $plugin['ff'] . '.xml');
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'JvEvents' ,
        $plugin['name'],
        $plugin['title'],
        'jvevents-plugin' ,
        'Events'
    );
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tt_content.pi_flexform.jvevents_events', 'EXT:jv_events/Resources/Private/Language/locallang_csh_flexforms.xlf');







