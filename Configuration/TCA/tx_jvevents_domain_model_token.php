<?php
return [
   'ctrl' => [
      'title' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token',
      'label' => 'name',
      'tstamp' => 'tstamp',
      'crdate' => 'crdate',
      'cruser_id' => 'cruser_id',
      'delete' => 'deleted',
      'enablecolumns' => [
         'disabled' => 'hidden',
         'starttime' => 'starttime',
         'endtime' => 'endtime',
      ],
      'searchFields' => 'name,toke,feuser',
      'iconfile' => 'EXT:jv_events/Resources/Public/Icons/tx_jvevents_domain_model_token.svg',
   ],
   'types' => [
      '1' => ['showitem' => 'name, token, feuser, license, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, starttime, endtime'],
   ],
   'columns' => [
      'name' => [
         'exclude' => true,
         'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token.name',
         'config' => [
            'type' => 'input',
            'size' => 80,
            'eval' => 'trim,required',
         ],
      ],
      'token' => [
         'exclude' => true,
         'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token.token',
         'config' => [
            'type' => 'input',
            'size' => 80,
            'eval' => 'trim,required',
         ],
      ],
      'feuser' => [
         'exclude' => true,
         'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token.feuser',
         'config' => [
            'type' => 'input',
            'size' => 11,
            'eval' => 'int',
         ],
      ],
      'license' => [
         'exclude' => true,
         'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token.license',
         'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
               ['BLOCKED', 'BLOCKED'],
               ['DEMO', 'DEMO'],
               ['BASIC', 'BASIC'],
               ['FULL', 'FULL'],
            ],
            'default' => 'BLOCKED',
         ],
      ],
      'hidden' => [
         'exclude' => true,
         'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
         'config' => [
            'type' => 'check',
            'items' => [
               ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enable'],
            ],
         ],
      ],
      'starttime' => [
         'exclude' => true,
         'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
         'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'eval' => 'datetime',
         ],
      ],
      'endtime' => [
         'exclude' => true,
         'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
         'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'eval' => 'datetime',
         ],
      ],
   ],
];