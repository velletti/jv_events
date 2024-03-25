<?php
defined('TYPO3') or die();
// EXTKEY is not set here .. see https://docs.typo3.org/typo3cms/ExtbaseFluidBook/b-ExtbaseReference/Index.html

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Events',
    'Events: List Events' ,
    'jvevents-plugin' ,
    'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Event',
    'Events: Single Event - show, create, edit, update ... ' ,
    'jvevents-plugin' ,
    'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Organizer',
    'Events: List Organizer, show, create, edit' ,
    'jvevents-plugin' ,
    'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Location',
    'Events: List Locations, show, create, edit' ,
    'jvevents-plugin' ,
    'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Registrant',
    'Events: List Registrants, show, create, edit' ,
    'jvevents-plugin' ,
    'Events'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JvEvents',
    'Ajax',
    'Events: Ajax Event Controller',
    'jvevents-plugin' ,
    'Events'
);

foreach ( ['events' , 'event' , 'organizer' , 'location' , 'registrant' , 'curl'  ] as $plugin ) {
    $pluginSignature = str_replace('_','','jv_events') . '_' . $plugin ;
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'pages, recursive,select_key';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:jv_events/Configuration/FlexForms/flexform_' . $plugin . '.xml');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tt_content.pi_flexform.jvevents_events', 'EXT:jv_events/Resources/Private/Language/locallang_csh_flexforms.xlf');







