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
		'title'	=> 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_tag',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'versioningWS' => FALSE ,
        'default_sortby' => 'name ASC',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'name,',
		'iconfile' => '/typo3conf/ext/jv_events/Resources/Public/Icons/tx_jvevents_domain_model_tag.gif'
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid, l10n_parent, name,type, tag_category,--div--;access, hidden, starttime, endtime, visibility, nocopy'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
	
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
			'config' => $lngConfig,
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
				'foreign_table' => 'tx_jvevents_domain_model_tag',
				'foreign_table_where' => 'AND tx_jvevents_domain_model_tag.pid=###CURRENT_PID### AND tx_jvevents_domain_model_tag.sys_language_uid IN (-1,0)',
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
	
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'datetime',
				'size' => 13,
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ),
				'checkbox' => 0,
				'default' => 0,
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'datetime',
				'size' => 13,
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ),
				'checkbox' => 0,
				'default' => 0,
			),
		),

		'name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_tag.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
        'nocopy' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_tag.nocopy',
            'config' => array(
                'type' => 'check',
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ) ,
            ),
        ),
        'visibility' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_tag.visibility',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array('label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_tag.visibility.default', 'value' => 0),
                    array('label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_tag.visibility.hiddenInFilter', 'value' => 1),
                    array('label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_tag.visibility.onlyVisibleInBackend', 'value' => 2),
                ),
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ) ,

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
                    array('label' => 'Event Tag', 'value' => '0'),
                    array('label' => 'Location Tag', 'value' => '1'),
                    array('label' => 'Organizer Tag', 'value' => '2'),

                ),
                'size' => 1,
                'maxitems' => 1,
                'eval' => 'trim'
            ),
        ),
        'tag_category' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_tag.tag_category',
         //   'displayCond' => 'FIELD:type:==:0',

            'config' => array(
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'items' => [
                    ['label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_tag.all_categories', 'value' => 0],
                    ['label' => '-------', 'value' => '--div--'],
                ],
                'foreign_table' => 'tx_jvevents_domain_model_category',

                // 'foreign_table_where' => ' AND tx_jvevents_domain_model_category.type = 0 AND tx_jvevents_domain_model_category.sys_language_uid in (-1, 0)',
                'foreign_table_where' => ' AND (tx_jvevents_domain_model_category.type = ###REC_FIELD_type### )  AND (tx_jvevents_domain_model_category.sys_language_uid = 0 OR tx_jvevents_domain_model_category.l10n_parent = 0) ORDER BY tx_jvevents_domain_model_category.title',
                'itemsProcFunc' => 'JVelletti\\JvEvents\\UserFunc\\Flexforms->TranslateMMvalues' ,

                'MM' => 'tx_jvevents_tag_category_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'exclusiveKeys' => '0',
                'allowNonIdValues' => true ,

                'fieldControl' => array(
                    'addRecord' => array(
                        'disabled' => false ,
                        'options' => array(
                            'pid' => '###CURRENT_PID###' ,
                            'setValue' => 'prepend' ,
                            'icon' => 'actions-add',
                            'table' => 'tx_jvevents_domain_model_category' ,
                            'title' => 'Create new' ,
                        ),

                    ) ,
                    'editPopup' => array(
                        'disabled' => false ,
                        'options' => array(
                            'icon' => 'actions-open',
                            'windowOpenParameters' => 'height=350,width=580,status=0,menubar=0,scrollbars=1' ,
                            'title' => 'Edit' ,
                        ),
                    ) ,
                ) ,

            ),
        ),
		
	),
);
