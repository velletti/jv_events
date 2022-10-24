<?php
defined('TYPO3_MODE') or die();
// EXTKEY is not set here .. see https://docs.typo3.org/typo3cms/ExtbaseFluidBook/b-ExtbaseReference/Index.html

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Events',
    'Events' ,
    'jvevents-plugin'
);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Ajax',
    'Ajax Event Controller',
    'jvevents-plugin'
);

$pluginSignature = str_replace('_','','jv_events') . '_events';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'pages, recursive,select_key';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:jv_events/Configuration/FlexForms/flexform_events.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tt_content.pi_flexform.jvevents_events', 'EXT:jv_events/Resources/Private/Language/locallang_csh_flexforms.xlf');







