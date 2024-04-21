<?php
defined('TYPO3') or die();
// EXTKEY is not set here .. see https://docs.typo3.org/typo3cms/ExtbaseFluidBook/b-ExtbaseReference/Index.html

$pluginArray = \JVelletti\JvEvents\Utility\PluginUtility::getPluginArray() ;

foreach ( $pluginArray as $plugin ) {
    $pluginSignature = str_replace('_','','jv_events') . '_' . strtolower( $plugin['name'] ) ;
    //  $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
    // $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'pages, recursive,select_key';
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.plugin,
            pi_flexform,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ';
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$pluginSignature] =  $plugin['icon'] ;

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue( "*" ,
                    'FILE:EXT:jv_events/Configuration/FlexForms/flexform_' . $plugin['ff'] . '.xml' , $pluginSignature );
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







