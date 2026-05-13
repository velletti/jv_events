<?php
return [
   'ctrl' => [
      'title' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token',
      'label' => 'name',
      'tstamp' => 'tstamp',
      'crdate' => 'crdate',
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
      '1' => ['showitem' => 'name, token, feuser, license, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, starttime, endtime,referrer'],
   ],
   'columns' => [
      'name' => [
         'exclude' => true,
         'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token.name',
         'config' => [
            'type' => 'input',
            'size' => 80,
            'eval' => 'trim',
            'required' => true,
         ],
      ],
      'token' => [
         'exclude' => true,
         'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token.token',
         'config' => [
            'type' => 'input',
            'size' => 80,
            'eval' => 'trim',
            'required' => true,
         ],
      ],
      'referrer' => [
         'exclude' => true,
         'label' => 'Allowed referrers list',
         'config' => [
            'type' => 'input',
            'size' => 255,
            'eval' => 'trim',
         ],
      ],
      'feuser' => [
         'exclude' => true,
         'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token.feuser',
         'config' => [
            'type' => 'number',
            'size' => 11,
         ],
      ],
      'license' => [
         'exclude' => true,
         'label' => 'LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_token.license',
         'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
               ['label' => 'BLOCKED', 'value' => 'BLOCKED'],
               ['label' => 'DEMO', 'value' => 'DEMO'],
               ['label' => 'BASIC', 'value' => 'BASIC'],
               ['label' => 'FULL', 'value' => 'FULL'],
               ['label' => 'ENTERPRISE', 'value' => 'ENTERPRISE'],
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
               ['label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enable'],
            ],
         ],
      ],
      'starttime' => [
         'exclude' => true,
         'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
         'config' => [
            'type' => 'datetime',
         ],
      ],
      'endtime' => [
         'exclude' => true,
         'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
         'config' => [
            'type' => 'datetime',
         ],
      ],
   ],
];