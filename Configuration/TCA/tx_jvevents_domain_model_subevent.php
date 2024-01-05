<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use JVE\JvEvents\UserFunc\InlineLabelService;
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

$returnArray = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_subevent',
		'label' => '',
		'label_alt' => 'start_date, start_time , end_time',
		'label_alt_force' => TRUE ,
        'formattedLabel_userFunc' => InlineLabelService::class . '->getInlineLabel',
        'formattedLabel_userFunc_options' => [
            'tx_jvevents_domain_model_subevent' => [
                'start_date' => "\K\W W: D d.M.Y \\f\\r\o\m",
                'start_time' => " H:i - ",
                'end_time' => "H:i",
            ]
        ],
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
  //      'sortby' => 'sorting',
		'default_sortby' => 'start_date DESC',
		'versioningWS' => TRUE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'searchFields' => 'start_date,start_time,end_date,event',
		'iconfile' => 'EXT:jv_events/Resources/Public/Icons/tx_jvevents_domain_model_subevent.gif'
	),
	'types' => array(
		'1' => array('showitem' => '--palette--;;dates, --palette--;;access,'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
		'dates' => array('showitem' => 'start_date,all_day,--linebreak--,start_time,end_time'),
	//	'access' =>  array('showitem' =>  'hidden,--palette--;;1' ),
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
                'eval' => 'int',
                'default' => 0,
				'renderType' => 'selectSingle',
				'items' => array(
					array('label' => '', 'value' => 0),
				),
				'foreign_table' => 'tx_jvevents_domain_model_subevent',
				'foreign_table_where' => 'AND tx_jvevents_domain_model_subevent.pid=###CURRENT_PID### AND tx_jvevents_domain_model_subevent.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
        'crdate' => Array (
            'exclude' => 1,
            'label' => 'Creation date',
            'config' => Array (
                'type' => 'number',
            )
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
			'exclude' => 0,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'datetime' ,
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ) ,
				'size' => 13,
				'checkbox' => 0,
				'default' => 0,

			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'datetime' ,
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ) ,
				'size' => 13,
				'checkbox' => 0,
				'default' => 0,

			),
		),

		'all_day' => array(
			'exclude' => 0,
            'onChange' => 'reload' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.all_day',
			'config' => array(
				'type' => 'check',

				'default' => 0
			)
		),
		'start_date' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.start_date',
			'config' => array(
				'type' => 'datetime',
				'size' => 7,
				'checkbox' => 1,
				'default' => 0,
    'format' => 'date',
    'required' => true
			),
		),
		'start_time' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:all_day:REQ:FALSE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.start_time',
			'config' => array(
				'type' => 'datetime',
				'size' => 4,
				'checkbox' => 1,
    'format' => 'time',
			)
		),
		'end_time' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:all_day:REQ:FALSE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.end_time',
			'config' => array(
				'type' => 'datetime',
				'size' => 4,
				'checkbox' => 1,
    'format' => 'time',
			)
		),

		'event' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event',
			'config' => array(
				'type' => 'input',

			),
		),


	),
);

// $configuration = \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();
// if ( $configuration['SubEvent'] )
//     $returnArray['columns']['access'] = '' ;
// }

return $returnArray ;
