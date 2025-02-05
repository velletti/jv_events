<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
defined('TYPO3') or die();

/** @var Typo3Version $version */
$version = GeneralUtility::makeInstance(Typo3Version::class);

if ($version->getMajorVersion()  < 11) {
    // to Check if we need this
    $lngConfig = [	'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => [
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value', 0]
                ]
    ] ;
} else {
    $lngConfig =  ['type' => 'language'] ;
}

return array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_category',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'sortby' => 'sorting',
		'versioningWS' => false,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',

		),
		'searchFields' => 'title,type,',
		'iconfile' =>  'EXT:jv_events/Resources/Public/Icons/tx_jvevents_domain_model_category.gif'
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden,--palette--;;1, title, type, description, block_registration,all_day' ,
                     'columnsOverrides' => array(
                         'sys_language_uid' => array(
                            // 'defaultExtras' => ';;;1-1-1'
                         )
                     ) ,
               ) ,
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
			'config' => $lngConfig ,
		),



		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('label' => '', 'value' => 0),
				),
				'foreign_table' => 'tx_jvevents_domain_model_category',
				'foreign_table_where' => 'AND tx_jvevents_domain_model_category.pid=###CURRENT_PID### AND tx_jvevents_domain_model_category.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),


		't3ver_label' => array(
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
        'sorting' => array(
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:show_item.php.sorting',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            )
        ),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),

		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_category.title',
            'l10n_mode' => 'prefixLangTitle' ,
			'config' => array(
				'type' => 'input',
                'behaviour' =>
                    ['allowLanguageSynchronization' => true],
				'size' => 30,
				'eval' => 'trim',
                'required' => true
			),
		),
        'description' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_category.description',
            'l10n_mode' => 'prefixLangTitle' ,
            'config' => array(
                'type' => 'text',
                'size' => 30,
                'rows' => 5,
            ),
        ),
		'type' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_category.type',
			'config' => array(
				'type' => 'select',
                'onChange' => 'reload' ,
				'renderType' => 'selectSingle',
				'items' => array(
					array('label' => 'Event Category', 'value' => '0'),
					array('label' => 'Location Category', 'value' => '1'),
					array('label' => 'Organizer Category', 'value' => '2'),

				),
				'size' => 1,
				'maxitems' => 1,
				'eval' => 'int'
			),
		),
        'all_day' => array(
            'exclude' => 1,
            'displayCond' => 'FIELD:type:=:0' ,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_category.all_day',
            'config' => array(
                'type' => 'select',
                'default' => '0' ,
                'renderType' => 'selectSingle',
                'items' => array(
                    array('label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_category.all_day.default', 'value' => '0'),
                    array('label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_category.all_day.not_allowed', 'value' => '-1'),
                    array('label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_category.all_day.only_allowed', 'value' => '1'),

                ),
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'trim'
            ),
        ),
		'block_registration' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:type:=:0' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_category.block_registration',
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),

	),
);



## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder
