<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Resource\File;
defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'Media',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:jv_events/Resources/Public/Icons/tx_jvevents_domain_model_media.gif',
    ],
    'types' => [
        '1' => ['showitem' => 'name, media_category, teaser_image, teaser_text, description, --div--;Access, sys_language_uid, organizer, release_date, link, hidden, starttime, endtime'],
    ],
    'columns' => [
        'name' => [
            'exclude' => true,
            'label' => 'Name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'media_category' => [
            'exclude' => true,
            'label' => 'Media Category',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['None', 0],
                ],
                'foreign_table' => 'tx_jvevents_domain_model_category',
                'foreign_table_where' => " AND tx_jvevents_domain_model_category.type = 3 AND ( tx_jvevents_domain_model_category.l10n_parent = 0 AND tx_jvevents_domain_model_category.sys_language_uid in(-1, 0 , cast(###REC_FIELD_sys_language_uid### as SIGNED ) )) ORDER BY tx_jvevents_domain_model_category.title",

            ],
        ],
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
                'foreign_table' => 'tx_jvevents_domain_model_location',
                'foreign_table_where' => 'AND tx_jvevents_domain_model_location.pid=###CURRENT_PID### AND tx_jvevents_domain_model_location.sys_language_uid IN (-1,0)',
            ),
        ),
        'l10n_diffsource' => array(
            'config' => array(
                'type' => 'passthrough',
            ),
        ),
        'teaser_image' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_location.teaserImage',
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

        'teaser_text' => [
            'exclude' => true,
            'label' => 'Teaser Text',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 5,
                'eval' => 'trim',
            ],
        ],
        'description' => [
            'exclude' => true,
            'label' => 'Description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'fieldControl' => [
                    'fullScreenRichtext' => [
                        'disabled' => false,
                    ],
                ],
            ],
        ],
        'release_date' => [
            'exclude' => true,
            'label' => 'Release Date',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'checkbox' => 1,
                'format' => 'date',
            ],
        ],
        'organizer' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_location.organizer',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'No linked Organizer', 'value' => 0],
                    ['label' => 'Registered Organizers', 'value' => '--div--'],
                ],
                'foreign_table' => 'tx_jvevents_domain_model_organizer',
                'minitems' => 0,
                'maxitems' => 1,
                'allowNonIdValues' => true
            ),
        ),
        'link' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_media.link',
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
                            'title' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_location.link' ,
                            'windowOpenParameters' => 'height=300,width=500,status=0,menubar=0,scrollbars=1' ,
                        ),

                    ),
                ) ,
            ) ,

        ),
        'hidden' => [
            'exclude' => true,
            'label' => 'Hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'Start Time',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'eval' => 'datetime',
                'default' => 0,
            ],
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'End Time',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'eval' => 'datetime',
                'default' => 0,
            ],
        ],
    ],
];