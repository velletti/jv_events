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
		'title'	=> 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
        'default_sortby' => 'crdate DESC',
		'versioningWS' => TRUE,

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'name,subname,email,email_cc,phone,sales_force_user_id,sales_force_user_id2,images,description,organizer_category,registration_info,slug,',
		'iconfile' =>  'EXT:jv_events/Resources/Public/Icons/tx_jvevents_domain_model_organizer.gif'
	),
	'types' => array(
		'1' => array('showitem' => '--palette--;;data, --div--;Relations, --palette--;;relations, --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, --palette--;;access,'),
	),
	'palettes' => array(
		'data' => array('showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, --linebreak--, top , --linebreak--, name, --linebreak--,subname,--linebreak--,slug, --linebreak--,email, --linebreak--,email_cc,--linebreak--,link, --linebreak--,charity_link, --linebreak--,phone, --linebreak--,sales_force_user_id,  --linebreak--, sales_force_user_id2, sales_force_user_org,--linebreak--,description,--linebreak--,registration_info,'),
		'relations' => array('showitem' => 'teaser_image, --linebreak--,images,  --linebreak--,youtube_link , --linebreak--,organizer_category, --linebreak--, tags,'),
	    'access' => array('showitem' => ' starttime, endtime, latest_event, --linebreak--,sorting, --linebreak--,access_users,--linebreak--, access_groups,')
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
				'foreign_table' => 'tx_jvevents_domain_model_organizer',
				'foreign_table_where' => 'AND tx_jvevents_domain_model_organizer.pid=###CURRENT_PID### AND tx_jvevents_domain_model_organizer.sys_language_uid IN (-1,0)',
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
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ) ,
				'size' => 13,
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'datetime',
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ) ,
				'size' => 13,
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
        'crdate' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.crdate',
            'config' => array(
                'type' => 'datetime',
                'behaviour' => array(
                    'allowLanguageSynchronization' => true ,
                ) ,
                'size' => 13,
                'checkbox' => 0,
                'default' => 0,
            ),
        ),
        'tstamp' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.tstamp',
            'config' => array(
                'type' => 'passthrough',
            ),
        ),
        'sorting' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.sorting',
            'config' => array(
                'type' => 'number',
                'size' => 13,
            ),
        ),



		'name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
    'required' => true
			),
		),
        'subname' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.subname',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),
        'top' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.top',
            'config' => array(
                'type' => 'check',
            ),
        ),
		'email' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.email',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
    'required' => true
			),
		),
        'email_cc' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.email_cc',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),
        'lat' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_location.lat',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim' ,

            ),
        ),
        'lng' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_location.lng',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim' ,
                'wizards' => array(
                    'jv_events_wizard_geocoder' => array(
                        'type' => 'popup',
                        'title' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:jv_events_model_location.geocoder.title',
                        'icon' => 'EXT:jv_events/Resources/Public/Icons/wizard_geocoder.png',
                        'module' => array(
                            'name' => 'jv_events_wizard_geocoder',
                        ),
                        'params' => array(
                            'mode' => 'point',
                        ),
                        'JSopenParams' => 'height=600,width=800,status=0,menubar=0,scrollbars=yes',
                    )
                ),
            ),
        ),
        'latest_event' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.latest_event',
            'config' => array(
                'type' => 'datetime',
                'size' => 7,
                'checkbox' => 1,
                'format' => 'date'
            ),
        ),
        'link' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.link',
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
                            'title' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.link' ,
                            'windowOpenParameters' => 'height=300,width=500,status=0,menubar=0,scrollbars=1' ,
                        ),

                    ),
                ) ,
            ) ,

        ),
        'charity_link' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:tx_jvevents_domain_model_organizer.charity_link',
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
                            'title' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:tx_jvevents_domain_model_organizer.charity_link' ,
                            'windowOpenParameters' => 'height=300,width=500,status=0,menubar=0,scrollbars=1' ,
                        ),

                    ),
                ) ,
            ) ,

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

		'phone' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.phone',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'sales_force_user_id' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.sales_force_user_id',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
        'sales_force_user_id2' => array(
            'exclude' => 0,
            'label' => 'sales_force_user_id NEW since 2019',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ),
        ),
        'sales_force_user_org' => array(
            'exclude' => 0,
            'label' => 'Allplan Organisation (Mandant) ',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array('label' => 'please select', 'value' => ''),
                    array('label' => 'Allplan Deutschland (300)', 'value' => '300'),
                    array('label' => 'Allplan International (301)', 'value' => '301'),
                    array('label' => 'Allplan Switzerland (400)', 'value' => '400'),
                    array('label' => 'Allplan France (410)', 'value' => '410'),
                    array('label' => 'Allplan Austria (420)', 'value' => '420'),
                    array('label' => 'Allplan Italy (430)', 'value' => '430'),
                    array('label' => 'Allplan Spain (440)', 'value' => '440'),
                    array('label' => 'Allplan UK (460)', 'value' => '460'),
                    array('label' => 'Allplan USA (470)', 'value' => '470'),
                    array('label' => 'Allplan Czech Republic (610)', 'value' => '610'),
                    array('label' => 'Allplan Slovakia (660)', 'value' => '660'),
                    array('label' => 'Allplan Infrastructure (680)', 'value' => '680'),
                    array('label' => 'Allplan Global (000)', 'value' => '000'),
                ),
                'size' => 1,
                'maxitems' => 1,
            ),
        ),
		'images' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.images',
			'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
				'images',
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
					'maxitems' => 1
				),
				$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
			),
		),
		'description' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'fieldControl' => [
                    'fullScreenRichtext' => [
                        'disabled' => false,
                    ],
                ],
            ],
		),
        'registration_info' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.registration_info',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'fieldControl' => [
                    'fullScreenRichtext' => [
                        'disabled' => false,
                    ],
                ],
            ],
        ),
        'access_groups' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.accessGroups',
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
        'access_users' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.accessUsers',
            'config' => array(
                'type' => 'group',
                'allowed' => 'fe_users',
                'foreign_table' => 'fe_users',
                'size' => 8,
                'multiple' => 1,
                'minitems' => 0,
                'maxitems' => 20,
            ),
        ),
		'organizer_category' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.organizer_category',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_jvevents_domain_model_category',
				'foreign_table_where' => ' AND tx_jvevents_domain_model_category.type = 2 AND tx_jvevents_domain_model_category.sys_language_uid IN (-1,0) ORDER BY tx_jvevents_domain_model_category.title ',
				'MM' => 'tx_jvevents_organizer_category_mm',
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
        'teaser_image' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_organizer.teaserImage',
            'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
                'teaser_image',
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
                    'maxitems' => 1
                ),
                "jpg,jpeg,gif,png"
            ),
        ),
        'tags' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.tags',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_jvevents_domain_model_tag',
                //'foreign_table_where' => ' AND tx_jvevents_domain_model_tag.sys_language_uid in (-1, ###REC_FIELD_sys_language_uid###)',
                'itemsProcFunc' => 'JVelletti\\JvEvents\\UserFunc\\Flexforms->TranslateMMvalues' ,
                'foreign_table_where' => ' AND tx_jvevents_domain_model_tag.type = 2 AND (tx_jvevents_domain_model_tag.sys_language_uid = 0 OR tx_jvevents_domain_model_tag.l10n_parent = 0) ORDER BY tx_jvevents_domain_model_tag.name',

                'MM' => 'tx_jvevents_organizer_tag_mm',
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

        'module_sys_dmail_html' => [
            'label' => 'LLL:EXT:direct_mail/Resources/Private/Language/locallang_tca.xlf:module_sys_dmail_group.htmlemail',
            'exclude' => '1',
            'config' => [
                'type' => 'check'
            ]
        ]
		
	),
);


    $returnArray['columns']['slug']  = [
        'exclude' => 1,
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:pages.slug',
        'config' => [
            'type' => 'slug',
            'size' => 50,
            'generatorOptions' => [
                'fields' => [['name'],['uid']],
                'fieldSeparator' => '-',
                'replacements' => [
                    '/' => '-'
                ],
            ],
            'prependSlash' => false,
            'fallbackCharacter' => '-',
            'eval' => 'unique',
            'default' => 'organizer'
        ]
    ] ;

$configuration = EmConfigurationUtility::getEmConf();

if ( $configuration['hasLoginUser'] != 1 ) {
    unset($returnArray['columns']['access'] ) ;
    unset($returnArray['columns']['registration_access'] ) ;
}
if ( ! $configuration['enableOrganizerSorting'] == "1" ) {
    unset( $returnArray['ctrl']['sortby'] ) ;
}
if ( $configuration['enableSalesForce'] != 1 && $configuration['enableHubspot'] != 1  ) {
    unset($returnArray['columns']['sales_force_user_id'] ) ;
    unset($returnArray['columns']['sales_force_user_id2'] ) ;
    unset($returnArray['columns']['sales_force_user_org'] ) ;
}

if ( intval( $configuration['MaxLengthOrgTitle'] ) > 1 ) {
    $returnArray['columns']['name']['config']['max'] =  intval( $configuration['MaxLengthOrgTitle'] )  ;
}

// needed to direct Mail
$returnArray['columns']['module_sys_dmail_category'] = array (
    'exclude' => 1,
    'label' => "module_sys_dmail_category",
    'config' => array(
        'type' => "passthrough" ,
        'MM' => "tx_jvevents_organizer_category_mm"
    )
);

return $returnArray ;
