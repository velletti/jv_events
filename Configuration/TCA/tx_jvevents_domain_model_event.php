<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Resource\File;
use JVelletti\JvEvents\Utility\EmConfigurationUtility;
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
		'title'	=> 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event',
		'label' => 'start_date',

        'label_alt' => 'name',
        'label_alt_force' => 1,

		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
     //   'sortby' => 'sorting',
		'default_sortby' => 'start_date DESC',
		'type' => 'event_type',
		'versioningWS' => False,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'fe_group' => 'access' ,
		),
		'searchFields' => 'event_type,name,teaser,description,images,files,start_date,start_time,end_date,marketing_process_id,sales_force_record_type,sales_force_campaign_id,sales_force_event_id,sales_force_session_id,subject_organizer,text_organizer,subject_registrant,introtext_registrant,text_registrant,organizer,location,slug,',
		'iconfile' => 'EXT:jv_events/Resources/Public/Icons/tx_jvevents_domain_model_event.gif'
	),
	'types' => array(
		'0' => array('showitem' => 'event_type,url,event_button_text,--palette--;;dates,--palette--;LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.entry_time_help;entryTime,
		   --palette--;;infos,
		--div--;Advanced, --palette--;;language, --palette--;;advanced, --palette--;;frequent, 
		   price,currency,--linebreak--,--palette--;LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.priceReducedHeader;priceReduced,
		--div--;Relations, --palette--;;relations,
		--div--;Files, teaser_image, files, files_after_reg, files_after_event,
		--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:be_users.tabs.access, --palette--;;access,'),
		'2' => array('showitem' => 'event_type,event_button_text,--palette--;;dates,--palette--;LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.entry_time_help;entryTime,
		   --palette--;;infos,description,
		--div--;Advanced, --palette--;;language, --palette--;;advanced, --palette--;;frequent,
		  price,currency,--linebreak--,--palette--;LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.priceReducedHeader;priceReduced,
		--div--;Relations, --palette--;;relations,
		--div--;Files, teaser_image,images, files,files_after_reg, files_after_event,
		--div--;Registration, --palette--;;register,
		--div--;Notifications, --palette--;;notification, --palette--;Email;notifyOrg, --palette--;Email;notifyReg,
		--div--;Old, --palette--;;old,
		--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:be_users.tabs.access, --palette--;;access,'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
		'dates' => array('showitem' => 'all_day,--linebreak--,start_date,end_date,--linebreak--,start_time,end_time,--linebreak--,subevent'),
		'entryTime' => array('showitem' => 'entry_time'),
		'infos' => array('showitem' => 'name, --linebreak--, teaser ,'),
		'priceReduced' => array('showitem' => 'price_reduced,--linebreak--, price_reduced_text,'),
		'relations' => array('showitem' => 'organizer, --linebreak--, location, --linebreak--,youtube_link, --linebreak--, event_category, --linebreak--,tags '),
		'frequent' => array('showitem' => 'is_recurring, --linebreak--, frequency, freq_exception, --linebreak--, is_exception_for,  '),
		'language' => array('showitem' => 'sys_language_uid, ,l10n_parent,--linebreak--,l10n_diffsource,' ),
		'advanced' => array('showitem' => 'top_event, --linebreak--,slug,' ),
		'old' => array('showitem' => 'store_in_citrix, citrix_uid, --linebreak--,store_in_sales_force, --linebreak--,marketing_process_id, sales_force_record_type, sales_force_event_id, sales_force_session_id' ),

		'access' =>  array('showitem' =>  'hidden,--palette--;;1,canceled,--linebreak--,access,--linebreak--,starttime,endtime' ),
		'notification' =>  array('showitem' =>  'notify_organizer;;1,notify_registrant;;1,need_to_confirm;;1,--linebreak--' ),
		'notifyOrg' =>  array('showitem' =>  'subject_organizer,--linebreak--,text_organizer' ),
		'notifyReg' =>  array('showitem' =>  'subject_registrant,--linebreak--,introtext_registrant,--linebreak--,introtext_registrant_confirmed,--linebreak--,text_registrant' ),
		'register' =>  array('showitem' =>  'with_registration;;1,registration_until, --linebreak--,registration_url, --linebreak--,registration_form_pid,registration_pid,--linebreak--,registration_gender,--linebreak--,registration_show_status,--linebreak--,registration_access, ,store_in_hubspot,  sales_force_campaign_id, --linebreak--,available_seats, available_waiting_seats, registered_seats, unconfirmed_seats' ),
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
                'eval' => 'int',
                'default' => 0,
				'renderType' => 'selectSingle',
				'items' => array(
					array('label' => '', 'value' => 0),
				),
				'foreign_table' => 'tx_jvevents_domain_model_event',
				'foreign_table_where' => 'AND tx_jvevents_domain_model_event.pid=###CURRENT_PID### AND tx_jvevents_domain_model_event.sys_language_uid IN (-1,0)',
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
        'tstamp' => Array (
            'exclude' => 1,
            'label' => 'Last modification',
            'config' => Array (
                'type' => 'passthrough',
            )
        ),
        'last_updated' => Array (
            'exclude' => 1,
            'label' => 'Last modification in Frontend',
            'config' => Array (
                'type' => 'passthrough',
            )
        ),
        'last_updated_by' => Array (
            'exclude' => 1,
            'label' => 'Last modification in Frontend by frontenduser UID',
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
        'canceled' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.canceled',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'top_event' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.top_event',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'viewed' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.viewed',
            'config' => array(
                'type' => 'number',
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ) ,
                'size' => 13,
                'default' => 0,

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
				'size' => 18,
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

		'event_type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_type',
            'onChange' => 'reload' ,
            'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_type.link', 'value' => 0),
					array('label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_type.default', 'value' => 2),
				),
				'size' => 1,
				'maxitems' => 1,
				'default' => 2,
				'required' => true
			),
			'default' => 2,
		),
		'name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
    'required' => true,

			),
		),
        'event_button_text' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_button_text',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ),
        ),

        'price' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.price',
            'config' => array(
                'type' => 'number',
                'size' => 10,
                'format' => 'decimal'
            ),
        ),
        'currency' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:tx_jvevents_domain_model_event.currency.label',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array('label' => '€ (Euro)', 'value' => '€'),
                    array('label' => '$ (Dollar)', 'value' => '$'),
                    array('label' => '£ (GDP)', 'value' => '£'),
                    array('label' => 'CHF', 'value' => 'CHF'),
                ),
                'size' => 1,
                'maxitems' => 1,
            ),
        ),
        'price_reduced' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.priceReduced',
            'config' => array(
                'type' => 'number',
                'size' => 10,
                'format' => 'decimal'
            ),
        ),
        'price_reduced_text' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.priceReducedText',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => ''
            ),
        ),
		'url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_type.link',
			'config' => [
				'type' => 'input',
                'renderType' => 'inputLink' ,
                'size' => 30,
				'max' => 255,
				'eval' => 'trim',

				'softref' => 'typolink',
    'required' => true
			]
		],

		'teaser' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.teaser',
			'config' => array(
				'type' => 'text',
				'size' => 30,
				'rows' => 3,
				'eval' => 'trim'
			),
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.description',
			'config' => array(
				'type' => 'text',
                'enableRichtext' => true,
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim',

			),
		),
		'teaser_image' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.teaserImage',
			'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
				'teaser_image',
				array(
					'appearance' => array(
						'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
					),
					'foreign_types' => array(
						'0' => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_TEXT => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_IMAGE => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_AUDIO => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_VIDEO => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_APPLICATION => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						)
					),
					'maxitems' => 1
				),
				"jpg,jpeg,gif,png"
			),
		),
		'images' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.images',
			'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
				'images',
				array(
					'appearance' => array(
						'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
					),
					'foreign_types' => array(
						'0' => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_TEXT => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_IMAGE => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_AUDIO => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_VIDEO => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_APPLICATION => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						)
					),
					'maxitems' => 10
				),
				"jpg,jpeg,gif,png"
			),
		),
		'files' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.files',
			'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
				'files',
				array(
					'appearance' => array(
						'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference'
					),
					'foreign_types' => array(
						'0' => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_TEXT => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_IMAGE => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_AUDIO => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_VIDEO => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						File::FILETYPE_APPLICATION => array(
							'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						)
					),
					'maxitems' => 10
				) ,
				'pdf,zip,jpeg,jpg,mp4,doc,docx,ppt'
			),
		),
        'files_after_reg' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.filesAfterReg',
            'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
                'files_after_reg',
                array(
                    'appearance' => array(
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference'
                    ),
                    'foreign_types' => array(
                        '0' => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_TEXT => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_IMAGE => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_AUDIO => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_VIDEO => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_APPLICATION => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        )
                    ),
                    'maxitems' => 10
                ) ,
                'pdf,zip,jpeg,jpg,mp4,doc,docx,ppt'
            ),
        ),
        'files_after_event' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.filesAfterEvent',
            'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
                'files_after_event',
                array(
                    'appearance' => array(
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference'
                    ),
                    'foreign_types' => array(
                        '0' => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_TEXT => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_IMAGE => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_AUDIO => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_VIDEO => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ),
                        File::FILETYPE_APPLICATION => array(
                            'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        )
                    ),
                    'maxitems' => 10
                ) ,
                'pdf,zip,jpeg,jpg,mp4,doc,docx,ppt'
            ),
        ),
		'all_day' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.all_day',
            'onChange' => 'reload' ,
			'config' => array(
				'type' => 'check',
				'default' => 0,

			)
		),
		'start_date' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.start_date',
			'config' => array(
				'type' => 'datetime',
				'size' => 13,
				'checkbox' => 1,
    'format' => 'date',
    'required' => true,

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
            'fieldControl' => [
                'showEvent' => [
                    'renderType' => 'showEventInFrontend'
                ] ,
                'dowloadIcal' => [
                    'renderType' => 'getIcalLink'
                ]
            ],
            'format' => 'time'
			)
		),
        'entry_time' => array(
            'exclude' => 1,
            'displayCond' => 'FIELD:all_day:REQ:FALSE' ,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.entry_time',
            'config' => array(
                'type' => 'datetime',
                'size' => 4,
                'checkbox' => 1,
                'format' => 'time',

            )
        ),
		'end_date' => array(
			'exclude' => 0,

			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.end_date',
			'config' => array(
				'type' => 'datetime',
				'size' => 7,
				'checkbox' => 1,
    'format' => 'date',
			),
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
		'access' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.access',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'size' => 5,
				'maxitems' => 20,
				'items' => array(
					array(
						'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
						'value' => -1
					),
					array(
						'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
						'value' => -2
					),
					array(
						'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
						'value' => '--div--'
					)
				),
				'exclusiveKeys' => '-1,-2',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title'
			)
		),
		'with_registration' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.with_registration',
            'onChange' => 'reload' ,
			'config' => array(
				'type' => 'check',

				'default' => 0
			)
		),
		'registration_url' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:FALSE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registrationUrl',
			'config' => array(
				'type' => 'input',
                'renderType' => 'inputLink' ,
                'default' => '' ,
				'softref' => 'typolink' ,

			)
		),
		'registration_form_pid' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registrationFormPid',
			'config' => array(
				'type' => 'group',
				'allowed' => 'pages',
				'foreign_table' => 'pages',
				'size' => 1,
				'multiple' => 0,
				'minitems' => 0,
				'maxitems' => 1,
                'suggestOptions' => array(
                    'default' => array(
                    ) ,
                ) ,
			),
		),
		'registration_pid' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registrationPid',
			'config' => array(
				'type' => 'group',
				'allowed' => 'pages',
				'foreign_table' => 'pages',
				'size' => 1,
				'multiple' => 0,
				'minitems' => 0,
				'maxitems' => 1,
                'suggestOptions' => array(
                    'default' => array(

                    ) ,
                ) ,
			),
		),
		'registration_until' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_until',
			'config' => array(
				'type' => 'datetime',
				'size' => 14,

			),
		),
        'registration_show_status' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_show_status',
            'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
            'config' => array(
                'type' => 'check',

                'default' => 0
            )
        ),
        'registration_gender' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_gender',
            'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array(
                        'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_gender.none',
                        'value' => 0
                    ),
                    array(
                        'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_gender.male',
                        'value' => 1
                    ),
                    array(
                        'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_gender.female',
                        'value' => 2
                    ),
                    array(
                        'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_gender.couples',
                        'value' => 3
                    ),
                    array(
                        'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_gender.single',
                        'value' => -1
                    ),
                ),
                'size' => 1,
                'maxitems' => 1,
            ),
        ),


		'registration_access' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_access',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'size' => 5,
				'maxitems' => 20,
				'items' => array(
					array(
						'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
						'value' => -1
					),
					array(
						'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
						'value' => -2
					),
					array(
						'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
						'value' => '--div--'
					)
				),
				'exclusiveKeys' => '-1,-2',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title'
			)
		),

        'youtube_link' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:tx_jvevents_domain_model_organizer.youtube_link',
            'config' => array(
                'type' => 'input',
                'eval' => 'trim',
                'size' => '30',
                'max' => '255',
                'softref' => 'typolink,url',
                'renderType' => 'inputLink' ,

                'fieldControl' => array(
                    'linkPopup' => array(
                        'options' => array(
                            'blindLinkOptions' => 'mail,file,spec,folder' ,
                            'title' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:tx_jvevents_domain_model_organizer.youtube_link' ,
                            'windowOpenParameters' => 'height=300,width=500,status=0,menubar=0,scrollbars=1' ,
                        ),

                    ),
                ) ,
            ) ,

        ),

        'store_in_hubspot' => array(
            'exclude' => 1,
            'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.store_in_hubspot',
            'onChange' => 'reload' ,
            'config' => array(
                'type' => 'check',
                'default' => 0
            )
        ),
        'sales_force_campaign_id' => array(
            'exclude' => 1,
            'displayCond' => 'FIELD:store_in_hubspot:REQ:TRUE' ,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.sales_force_campaign_id',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'readOnly' => 1,
                'eval' => 'trim'
            ),
        ),
		'store_in_citrix' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.store_in_citrix',
            'onChange' => 'reload' ,
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),
		'citrix_uid' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:store_in_citrix:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.citrix_uid',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'store_in_sales_force' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.store_in_sales_force',
            'onChange' => 'reload' ,
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),
		'marketing_process_id' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:store_in_sales_force:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.marketing_process_id',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'sales_force_record_type' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:store_in_sales_force:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.sales_force_record_type',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'sales_force_event_id' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:store_in_sales_force:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.sales_force_event_id',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'sales_force_session_id' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:store_in_sales_force:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.sales_force_session_id',
			'config' => array(
				'type' => 'input',
				'size' => 7,
				'eval' => 'string',
				'checkbox' => 1,
				'default' => ''
			),
		),
		'available_seats' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.available_seats',
			'config' => array(
				'type' => 'number',
				'size' => 4
			)
		),
		'available_waiting_seats' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.available_waiting_seats',
			'config' => array(
				'type' => 'number',
				'size' => 4
			)
		),
		'registered_seats' => array(
			'exclude' => 1,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registered_seats',
			'config' => array(
				'type' => 'number',
				'size' => 4 ,
              'fieldControl' => [
                   'dowloadCSV' => [
                      'renderType' => 'downloadRegistrations'
                   ]
            ],
			)
		),
		'unconfirmed_seats' => array(
			'exclude' => 1,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.unconfirmed_seats',
			'config' => array(
				'type' => 'number',
				'size' => 4,
			),
		),
		'notify_organizer' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.notify_organizer',
			'config' => array(
				'type' => 'check',
				'default' => 1
			)
		),
		'notify_registrant' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.notify_registrant',
			'config' => array(
				'type' => 'check',
				'default' => 1
			)
		),
		'subject_organizer' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:notify_organizer:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.subject_organizer',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'text_organizer' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:notify_organizer:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.text_organizer',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim'
			)
		),
		'subject_registrant' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:notify_registrant:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.subject_registrant',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'text_registrant' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:notify_registrant:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.text_registrant',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim'
			)
		),
		'introtext_registrant' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:notify_registrant:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.introtext_registrant',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 10,
				'eval' => 'trim'
			)
		),
        'introtext_registrant_confirmed' => array(
            'exclude' => 0,
            'displayCond' => 'FIELD:notify_registrant:REQ:TRUE' ,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.introtext_registrant_confirmed',
            'config' => array(
                'type' => 'text',
                'cols' => 40,
                'rows' => 10,
                'eval' => 'trim'
            )
        ),
		'need_to_confirm' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.need_to_confirm',
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),
		'is_recurring' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.is_recurring',
            'onChange' => 'reload' ,
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),
		'frequency' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:is_recurring:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.frequency',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('label' => '-- Label --', 'value' => 0),
				),
				'size' => 1,
				'maxitems' => 1,
				'eval' => ''
			),
		),
		'freq_exception' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:is_recurring:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.freq_exception',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('label' => '-- Label --', 'value' => 0),
				),
				'size' => 1,
				'maxitems' => 1,
				'eval' => ''
			),
		),
		'is_exception_for' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:is_recurring:REQ:FALSE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.is_exception_for',
			'config' => array(
				'type' => 'number',
				'size' => 4
			)
		),
        'master_id' => array(
            'exclude' => 1,
            'label' => 'Id of Master Event',
            'config' => array(
                'type' => 'number',
                'size' => 11
            ),
        ),
        'subevent' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_subevent',

            'config' => array(
                'type' => 'inline',
                'allowed' => 'tx_jvevents_domain_model_subevent',
                'foreign_table' => 'tx_jvevents_domain_model_subevent',
                'foreign_sortby' => 'sorting',
                'foreign_field' => 'event',
                'size' => 5,
                'minitems' => 0,
                'maxitems' => 100,
                'appearance' => [
                    'collapseAll' => true,
                    'expandSingle' => true,
                    'levelLinksPosition' => 'bottom',
                    'useSortable' => true,
                    'showPossibleLocalizationRecords' => true,
                    'showRemovedLocalizationRecords' => true,
                    'showAllLocalizationLink' => true,
                    'showSynchronizationLink' => true,
                    'enabledControls' => [
                        'info' => false,
                    ]
                ]
            ),
        ),
		'organizer' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.organizer',
			'config' => array(
				'type' => 'group',

				'allowed' => 'tx_jvevents_domain_model_organizer',

				'size' => 1,
				'multiple' => 0,
				'minitems' => 0,
				'maxitems' => 1,
                'suggestOptions' => array(
                    'default' => array(
                        "additionalSearchFields" => "name" ,
                    ) ,
                ) ,
                'fieldControl' => array(
                    'addRecord' => array(
                        'disabled' => false ,
                        'options' => array(
                            'pid' => '###CURRENT_PID###' ,
                            'setValue' => 'prepend' ,
                            'icon' => 'actions-add',
                            'table' => 'tx_jvevents_domain_model_organizer' ,
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
		'location' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.location',
			'config' => array(
				'type' => 'group',

				'allowed' => 'tx_jvevents_domain_model_location',
				'size' => 1,
				'multiple' => 0,
				'minitems' => 0,
				'maxitems' => 1,
                'suggestOptions' => array(
                    'default' => array(
                        "additionalSearchFields" => "name, city, zip" ,
                    ) ,
                ) ,
                'fieldControl' => array(
                    'addRecord' => array(
                        'disabled' => false ,
                        'options' => array(
                            'pid' => '###CURRENT_PID###' ,
                            'setValue' => 'prepend' ,
                            'icon' => 'actions-add',
                            'table' => 'tx_jvevents_domain_model_location' ,
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
		/*
		 *  // activating the next line will copy registrations of an event, if you copy the event. This is NOT wanted
		 */
		/*
		'registrant' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registrant',
			'config' => array(
				'type' => 'inline',
				'foreign_table' => 'tx_jvevents_domain_model_registrant',
				'foreign_field' => 'event',
				'maxitems' => 9999,
				'appearance' => array(
					'collapseAll' => 1,
					'levelLinksPosition' => 'top',
					'showSynchronizationLink' => 1,
					'showPossibleLocalizationRecords' => 0,
					'showAllLocalizationLink' => 0
				),
			),

		),
		*/
		'event_category' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_category',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_jvevents_domain_model_category',

				// 'foreign_table_where' => ' AND tx_jvevents_domain_model_category.type = 0 AND tx_jvevents_domain_model_category.sys_language_uid in (-1, 0)',
				 'foreign_table_where' => " AND tx_jvevents_domain_model_category.type = 0 AND ( tx_jvevents_domain_model_category.l10n_parent = 0 AND tx_jvevents_domain_model_category.sys_language_uid in(-1, 0 , cast(###REC_FIELD_sys_language_uid### as SIGNED ) )) ORDER BY tx_jvevents_domain_model_category.title",
				// 'foreign_table_where' => " AND ( tx_jvevents_domain_model_category.l10n_parent = 0 AND find_in_set( tx_jvevents_domain_model_category.sys_language_uid , '-1,0,###REC_FIELD_sys_language_uid###')) ORDER BY tx_jvevents_domain_model_category.title",
                // nicht übersetzte Datensätze in default spräche werden in der function TranslateMMvalues gelöscht..
                'itemsProcFunc' => 'JVelletti\\JvEvents\\UserFunc\\Flexforms->TranslateMMvalues' ,

				'MM' => 'tx_jvevents_event_category_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,

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
		'tags' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.tags',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_jvevents_domain_model_tag',
                'itemsProcFunc' => 'JVelletti\\JvEvents\\UserFunc\\Flexforms->TranslateMMvalues' ,
                //'foreign_table_where' => ' hole alle default, non translated oder alle für alle Sprachen. die nicht lokalsiereten englischen werden in der Translate Function gelöscht!',
                'foreign_table_where' => " AND tx_jvevents_domain_model_tag.type = 0 AND (tx_jvevents_domain_model_tag.l10n_parent = 0 AND tx_jvevents_domain_model_tag.sys_language_uid in(-1, 0, CAST( ###REC_FIELD_sys_language_uid### as SIGNED)) ) ORDER BY tx_jvevents_domain_model_tag.name",

				'MM' => 'tx_jvevents_event_tag_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
                'fieldControl' => array(
                    'addRecord' => array(
                        'disabled' => false ,
                        'options' => array(
                            'pid' => '###CURRENT_PID###' ,
                            'setValue' => 'prepend' ,
                            'icon' => 'actions-add',
                            'table' => 'tx_jvevents_domain_model_tag' ,
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

$configuration = EmConfigurationUtility::getEmConf();

if ( ! $configuration['notifyOrganizer'] == 1 ) {
	$returnArray['columns']['notify_organizer']['config']['default'] = 1  ;
}
if ( $configuration['allDayEvent'] == 1 ) {
	$returnArray['columns']['all_day']['config']['default'] = 1  ;
}
if ( ! $configuration['notifyRegistrant'] == 1 ) {
	$returnArray['columns']['notify_registrant']['config']['default'] = 1  ;
} else {
    if ( ! $configuration['needToConfirm'] == 1 ) {
        $returnArray['columns']['need_to_confirm']['config']['default'] = 1  ;
    }
}
// actually the follow Up Process is not defined (emails an so on .. ) so remove this from input ..
unset($returnArray['columns']['need_to_confirm'] ) ;

if ( ! $configuration['recurring'] == 1 ) {
	unset($returnArray['columns']['is_recurring'] ) ;
	unset($returnArray['columns']['freq_exception'] ) ;
	unset($returnArray['columns']['frequency'] ) ;
	unset($returnArray['columns']['is_exception_for'] ) ;
}

if ( ! $configuration['showIndividualMailTemplatesPerEvent'] == 1 ) {
	unset($returnArray['columns']['subject_organizer'] ) ;
	unset($returnArray['columns']['text_organizer'] ) ;
	unset($returnArray['columns']['subject_registrant'] ) ;
	unset($returnArray['columns']['text_registrant'] ) ;

    unset($returnArray['columns']['notify_organizer']['onChange']) ;
    unset($returnArray['columns']['notify_registrant']['onChange']) ;

}

if ( ! $configuration['enableCitrix'] == 1 ) {
	unset($returnArray['columns']['store_in_citrix'] ) ;
	unset($returnArray['columns']['citrix_uid'] ) ;
}
if ( ! $configuration['enableSalesForce'] == 1 ) {
	unset($returnArray['columns']['store_in_sales_force'] ) ;
	unset($returnArray['columns']['sales_force_session_id'] ) ;
	unset($returnArray['columns']['sales_force_event_id'] ) ;
	unset($returnArray['columns']['sales_force_record_type'] ) ;
	unset($returnArray['columns']['marketing_process_id'] ) ;
}

//    unset( $returnArray['columns']['sales_force_campaign_id']['config']['readOnly'] ) ;

if ( ! $configuration['enableHubspot'] == 1 ) {
    unset($returnArray['columns']['store_in_hubspot'] ) ;
    unset($returnArray['columns']['sales_force_campaign_id'] ) ;
}




if ( ! $configuration['hasLoginUser'] == 1 ) {
	unset($returnArray['columns']['access'] ) ;
	unset($returnArray['columns']['registration_access'] ) ;
}

if ( $configuration['hideEndDate'] == 1 ) {
    unset($returnArray['columns']['end_date'] ) ;
}
if ( $configuration['hideAllLanguages'] == 1 ) {
    unset($returnArray['columns']['sys_language_uid']['config']['items'][0] ) ;
}


if ( $configuration['RegistrationFormPid'] > 0 ) {
    $returnArray['columns']['registration_form_pid']['config']['default'] = $configuration['RegistrationFormPid'] ;
    $returnArray['columns']['with_registration']['config']['default'] = 1 ;
}

if ( $configuration['RegistrationPid'] > 0 ) {
    $returnArray['columns']['registration_pid']['config']['default'] = $configuration['RegistrationPid'] ;
}


    $returnArray['columns']['slug']  = [
        'exclude' => true,
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:pages.slug',
        'config' => [
            'type' => 'slug',
            'size' => 50,
            'generatorOptions' => [
                'fields' => [['name'],['start_date']],
                'fieldSeparator' => '-',

                'replacements' => [
                    '/' => '-'
                ],
            ],
            'prependSlash' => false,
            'fallbackCharacter' => '-',
            'eval' => 'unique',
            'default' => 'event'
        ]
    ] ;

return $returnArray ;
