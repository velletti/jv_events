<?php
// Extension manager configuration
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

$returnArray = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'type' => 'event_type',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'requestUpdate' => 'all_day,event_type,with_registration,is_recurring,store_in_sales_force,store_in_citrix,notify_organizer,notify_registrant' ,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'fe_group' => 'access' ,
		),
		'searchFields' => 'event_type,name,teaser,description,images,files,start_date,start_time,end_date,marketing_process_id,sales_force_record_type,sales_force_event_id,sales_force_session_id,subject_organizer,text_organizer,subject_registrant,introtext_registrant,text_registrant,organizer,location,',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('jv_events') . 'Resources/Public/Icons/tx_jvevents_domain_model_event.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, event_type, teaser, description, images, files, all_day, start_date, start_time, end_date, end_time, access, with_registration, registration_until, registration_access, store_in_citrix, citrix_uid, store_in_sales_force, marketing_process_id, sales_force_record_type, sales_force_event_id, sales_force_session_id, available_seats,available_waiting_seats, registered_seats, unconfirmed_seats, notify_organizer, notify_registrant, subject_organizer, text_organizer, subject_registrant,introtext_registrant, text_registrant, need_to_confirm, is_recurring, frequency, freq_exception, is_exception_for, organizer, location, registrant, event_category, tags, url,',
	),
	'types' => array(
		'0' => array('showitem' => 'event_type,url,--palette--;;dates,--palette--;;infos,
		--div--;Advanced, --palette--;;language, --palette--;;frequent,
		--div--;Relations, --palette--;;relations,
		--div--;Files, teaser_image, files,
		--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, --palette--;;access,'),
		'2' => array('showitem' => 'event_type,--palette--;;dates,--palette--;;infos,description;;;richtext:rte_transform[mode=ts_links],
		--div--;Advanced, --palette--;;language, --palette--;;frequent,
		--div--;Relations, --palette--;;relations,
		--div--;Files, teaser_image,images, files,
		--div--;Registration, --palette--;;register,
		--div--;Notifications, --palette--;;notification, --palette--;Email;notifyOrg, --palette--;Email;notifyReg,
		--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, --palette--;;access,'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
		'dates' => array('showitem' => 'all_day,--linebreak--,start_date,start_time,end_date,end_time'),
		'infos' => array('showitem' => 'name, --linebreak--, teaser '),
		'relations' => array('showitem' => 'organizer, --linebreak--, location, --linebreak--,event_category, --linebreak--,tags '),
		'frequent' => array('showitem' => 'is_recurring, --linebreak--, frequency, freq_exception, --linebreak--, is_exception_for,  '),
		'language' => array('showitem' => 'sys_language_uid;;;;1-1-1, ,l10n_parent,--linebreak--,l10n_diffsource,' ),

		'access' =>  array('showitem' =>  'hidden;;1,--linebreak--,access' ),
		'notification' =>  array('showitem' =>  'notify_organizer;;1,notify_registrant;;1,need_to_confirm;;1,--linebreak--' ),
		'notifyOrg' =>  array('showitem' =>  'subject_organizer,--linebreak--,text_organizer' ),
		'notifyReg' =>  array('showitem' =>  'subject_registrant,--linebreak--,introtext_registrant,--linebreak--,introtext_registrant_confirmed,--linebreak--,text_registrant' ),
		'register' =>  array('showitem' =>  'with_registration;;1,registration_until, --linebreak--,registration_url, --linebreak--,registration_form_pid,registration_pid,--linebreak--,registration_access, --linebreak--,store_in_citrix, citrix_uid, --linebreak--,store_in_sales_force, --linebreak--,marketing_process_id, sales_force_record_type, sales_force_event_id, sales_force_session_id, --linebreak--,available_seats, available_waiting_seats, registered_seats, unconfirmed_seats' ),
	),
	'columns' => array(
	
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('', 0),
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
                'type' => 'input',
                'eval' => 'int',
            )
        ),
		
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
	
		'hidden' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),

		'event_type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_type',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => array(
					array('LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_type.link', 0),
					array('LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_type.default', 2),
				),
				'size' => 1,
				'maxitems' => 1,
				'default' => 2,
				'eval' => 'required'
			),
			'default' => 2,
		),
		'name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'url' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_type.link',
			'config' => [
				'type' => 'input',
				'size' => '30',
				'max' => '255',
				'eval' => 'trim,required',
				'wizards' => [
					'_PADDING' => 2,
					'link' => [
						'type' => 'popup',
						'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_link_formlabel',
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
						'module' => [
							'name' => 'wizard_link',
						],
						'params' => array(
							'blindLinkOptions' => 'mail,spec,folder',
						),
						'JSopenParams' => 'height=600,width=800,status=0,menubar=0,scrollbars=1'
					]
				],
				'softref' => 'typolink'
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
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim',
				'wizards' => array(
					'RTE' => array(
						'icon' => 'wizard_rte2.gif',
						'notNewRecords'=> 1,
						'RTEonly' => 1,
						'module' => array(
							'name' => 'wizard_rich_text_editor',
							'urlParameters' => array(
								'mode' => 'wizard',
								'act' => 'wizard_rte.php'
							)
						),
						'title' => 'LLL:EXT:cms/locallang_ttc.xlf:bodytext.W.RTE',
						'type' => 'script'
					)
				)
			),
		),
		'teaser_image' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.teaserImage',
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
				'teaser_image',
				array(
					'appearance' => array(
						'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
					),
					'foreign_types' => array(
						'0' => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
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
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
				'images',
				array(
					'appearance' => array(
						'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
					),
					'foreign_types' => array(
						'0' => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
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
			'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
				'files',
				array(
					'appearance' => array(
						'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:media.addFileReference'
					),
					'foreign_types' => array(
						'0' => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						),
						\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
							'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
						)
					),
					'maxitems' => 10
				) ,
				'pdf'
			),
		),
		'all_day' => array(
			'exclude' => 0,
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
				'type' => 'input',
				'size' => 7,
				'eval' => 'date,required',
				'checkbox' => 1,
				'default' => time()
			),
		),
		'start_time' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:all_day:REQ:FALSE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.start_time',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'time',
				'checkbox' => 1,
				'default' => time()
			)
		),
		'end_date' => array(
			'exclude' => 0,

			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.end_date',
			'config' => array(
				'type' => 'input',
				'size' => 7,
				'eval' => 'date',
				'checkbox' => 1,
			),
		),
		'end_time' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:all_day:REQ:FALSE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.end_time',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'time',
				'checkbox' => 1,
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
						'LLL:EXT:lang/locallang_general.xlf:LGL.hide_at_login',
						-1
					),
					array(
						'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
						-2
					),
					array(
						'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
						'--div--'
					)
				),
				'exclusiveKeys' => '-1,-2',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title',
				'enableMultiSelectFilterTextfield' => true
			)
		),
		'with_registration' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.with_registration',
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
				'default' => '' ,
				 'softref' => 'typolink' ,
				'wizards' => [
					'_PADDING' => 2,
					'link' => [
						'type' => 'popup',
						'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header_link_formlabel',
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
						'module' => [
							'name' => 'wizard_link',
						],
						'JSopenParams' => 'height=600,width=800,status=0,menubar=0,scrollbars=1'
					]
				],
			)
		),
		'registration_form_pid' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registrationFormPid',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'foreign_table' => 'pages',
				'size' => 1,
                'show_thumbs' => '1',
				'multiple' => 0,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => array(
					'_VERTICAL' => 1,
					'suggest' => array(
						'type' => 'suggest'
					),

				),
			),
		),
		'registration_pid' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registrationPid',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'foreign_table' => 'pages',
				'size' => 1,
                'show_thumbs' => '1',
				'multiple' => 0,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => array(
					'_VERTICAL' => 1,
					'suggest' => array(
						'type' => 'suggest'
					),

				),
			),
		),
		'registration_until' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registration_until',
			'config' => array(
				'type' => 'input',
				'size' => 14,
				'eval' => 'datetime',

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
						'LLL:EXT:lang/locallang_general.xlf:LGL.hide_at_login',
						-1
					),
					array(
						'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
						-2
					),
					array(
						'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
						'--div--'
					)
				),
				'exclusiveKeys' => '-1,-2',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title',
				'enableMultiSelectFilterTextfield' => true
			)
		),
		'store_in_citrix' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.store_in_citrix',
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
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'available_waiting_seats' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.available_waiting_seats',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'registered_seats' => array(
			'exclude' => 1,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.registered_seats',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'unconfirmed_seats' => array(
			'exclude' => 1,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.unconfirmed_seats',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int',
			),
		),
		'notify_organizer' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.notify_organizer',
			'config' => array(
				'type' => 'check',
				'default' => 0
			)
		),
		'notify_registrant' => array(
			'exclude' => 0,
			'displayCond' => 'FIELD:with_registration:REQ:TRUE' ,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.notify_registrant',
			'config' => array(
				'type' => 'check',
				'default' => 0
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
					array('-- Label --', 0),
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
					array('-- Label --', 0),
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
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'organizer' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.organizer',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',

				'allowed' => 'tx_jvevents_domain_model_organizer',
                'fieldControl' => array (
                    'recordsOverview' => array(
                        'renderType' => 'recordsOverview',
                    ) ,
                    'listModule' => [
                        'disabled' => false,
                    ],
                ) ,
				'size' => 1,
                'show_thumbs' => '1',
				'multiple' => 0,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => array(
					'_VERTICAL' => 1,
					'suggest' => array(
						'type' => 'suggest',
                        'default' => array(
                            'additionalSearchFields' => 'name, city, zip',
                        )
					),
					'edit' => array(
						'type' => 'popup',
						'title' => 'Edit template',

						'module' => array(
							'name' => 'wizard_edit',
						),
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif',
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1'
					),
					'add' => array(
						'type' => 'script',
						'title' => 'LLL:EXT:cms/locallang_tca.xlf:sys_template.basedOn_add',
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif',
						'params' => array(
							'table' => 'tx_jvevents_domain_model_organizer',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
						),
						'module' => array(
							'name' => 'wizard_add'
						)
					)
				)
			),
		),
		'location' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.location',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',

				'allowed' => 'tx_jvevents_domain_model_location',
				'size' => 1,
				'multiple' => 0,
                'show_thumbs' => '1',
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => array(
					'_VERTICAL' => 1,
					'suggest' => array(
						'type' => 'suggest',
                        'default' => array(
                            'additionalSearchFields' => 'name, city, zip',
                        )
					),
					'edit' => array(
						'type' => 'popup',
						'title' => 'Edit template',
						'module' => array(
							'name' => 'wizard_edit',
						),
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif',
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1'
					),
					'add' => array(
						'type' => 'script',
						'title' => 'LLL:EXT:cms/locallang_tca.xlf:sys_template.basedOn_add',
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif',
						'params' => array(
							'table' => 'tx_jvevents_domain_model_location',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
						),
						'module' => array(
							'name' => 'wizard_add'
						)
					)
				)
			),
		),
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
		'event_category' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.event_category',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_jvevents_domain_model_category',
				// 'foreign_table_where' => ' AND tx_jvevents_domain_model_category.type = 0 AND tx_jvevents_domain_model_category.sys_language_uid in (-1, 0)',
				'foreign_table_where' => ' AND tx_jvevents_domain_model_category.type = 0 AND (tx_jvevents_domain_model_category.sys_language_uid = 0 OR tx_jvevents_domain_model_category.l10n_parent = 0) ORDER BY tx_jvevents_domain_model_category.title',

				'MM' => 'tx_jvevents_event_category_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
				'wizards' => array(
					'_PADDING' => 1,
					'_VERTICAL' => 1,
					'edit' => array(
						'module' => array(
							'name' => 'wizard_edit',
						),
						'type' => 'popup',
						'title' => 'Edit',
						'icon' => 'edit2.gif',
						'popup_onlyOpenIfSelected' => 1,
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
						),
					'add' => Array(
						'module' => array(
							'name' => 'wizard_add',
						),
						'type' => 'script',
						'title' => 'Create new',
						'icon' => 'add.gif',
						'params' => array(
							'table' => 'tx_jvevents_domain_model_category',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
						),
					),
				),
			),
		),
		'tags' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.tags',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_jvevents_domain_model_tag',
				// 'foreign_table_where' => ' AND tx_jvevents_domain_model_tag.sys_language_uid in (-1, ###REC_FIELD_sys_language_uid###)',
				'foreign_table_where' => ' AND (tx_jvevents_domain_model_tag.sys_language_uid = 0 OR tx_jvevents_domain_model_tag.l10n_parent = 0) ORDER BY tx_jvevents_domain_model_tag.name',

				'MM' => 'tx_jvevents_event_tag_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 9999,
				'multiple' => 0,
				'wizards' => array(
					'_PADDING' => 1,
					'_VERTICAL' => 1,
					'edit' => array(
						'module' => array(
							'name' => 'wizard_edit',
						),
						'type' => 'popup',
						'title' => 'Edit',
						'icon' => 'edit2.gif',
						'popup_onlyOpenIfSelected' => 1,
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
						),
					'add' => Array(
						'module' => array(
							'name' => 'wizard_add',
						),
						'type' => 'script',
						'title' => 'Create new',
						'icon' => 'add.gif',
						'params' => array(
							'table' => 'tx_jvevents_domain_model_tag',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
						),
					),
				),
			),
		),
		
	),
);

$configuration = \JVE\JvEvents\Utility\EmConfiguration::getEmConf();

if ( ! $configuration['notifyOrganizer'] == 1 ) {
	$returnArray['columns']['notify_organizer']['config']['default'] = 1  ;
}
if ( $configuration['allDayEvent'] == 1 ) {
	$returnArray['columns']['all_day']['config']['default'] = 1  ;
}
if ( ! $configuration['notifyRegistrant'] == 1 ) {
	$returnArray['columns']['notify_registrant']['config']['default'] = 1  ;
}
if ( ! $configuration['needToConfirm'] == 1 ) {
	$returnArray['columns']['need_to_confirm']['config']['default'] = 1  ;
}


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
	$returnArray['ctrl']['requestUpdate'] = str_replace( "notify_organizer" , ""  , $returnArray['ctrl']['requestUpdate'] ) ;
	$returnArray['ctrl']['requestUpdate'] = str_replace( "notify_registrant" , ""  , $returnArray['ctrl']['requestUpdate'] ) ;
	$returnArray['ctrl']['requestUpdate'] = str_replace( " " , ""  , $returnArray['ctrl']['requestUpdate'] ) ;
	$returnArray['ctrl']['requestUpdate'] = str_replace( ",," , ","  , $returnArray['ctrl']['requestUpdate'] ) ;
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
if ( ! $configuration['hasLoginUser'] == 1 ) {
	unset($returnArray['columns']['access'] ) ;
	unset($returnArray['columns']['registration_access'] ) ;
}

if ( $configuration['hideEndDate'] == 1 ) {
    unset($returnArray['columns']['end_date'] ) ;
    unset($returnArray['columns']['end_time'] ) ;
}

if ( $configuration['RegistrationFormPid'] > 0 ) {
    $returnArray['columns']['registration_form_pid']['config']['default'] = $configuration['RegistrationFormPid'] ;
    $returnArray['columns']['with_registration']['config']['default'] = 1 ;
}

if ( $configuration['Registrationid'] > 0 ) {
    $returnArray['columns']['registration_pid']['config']['default'] = $configuration['RegistrationPid'] ;
}

return $returnArray ;
