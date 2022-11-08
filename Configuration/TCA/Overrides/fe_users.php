<?php

defined('TYPO3') or die();
$GLOBALS['TCA']['fe_users']['columns']['crdate'] = [
                'exclude' => true,
                'label' => 'Creation Date',
                'config' => [
                    'type' => 'passthrough',
                    'size' => 13,
                    'default' => 0
                ]
            ] ;
