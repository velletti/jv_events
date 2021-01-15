<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder
$_EXTKEY = "jv_events" ;

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'JVE.' . $_EXTKEY,
	'Events',
	array(
		'Event' => 'list, show, new, create, edit, update, delete, register, confirm, search,copy,cancel',
		'Organizer' => 'list, show, new, create, edit, update, delete,assist',
		'Location' => 'list, show, new, create, edit, update, delete,setDefault',
		'Registrant' => 'list, show,new,create,delete,confirm,checkQrcode',
		'Tag' => 'list',
	),
	// non-cacheable actions
	array(
		'Event' => 'show, search, new, create, edit, update, register, confirm, delete,copy,cancel',
        'Organizer' => 'show, new, create, edit, update, delete,assist',
		'Registrant' => 'list,new,create,delete,confirm,checkQrcode',
		'Location' => 'new, create, edit, update, delete,setDefault',
		
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JVE.' .$_EXTKEY,
    'Ajax',
    array(
        'Ajax'  => 'eventMenu,locationList,organizerList,eventList,eventDisable,eventUnlink',
    ),
    array(
        'Ajax'  => 'eventMenu,locationList,organizerList,eventList,eventDisable,eventUnlink',
    )
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JVE.' .$_EXTKEY,
    'search',
    array(
        'Search'  => 'search',
    ),
    // non-cacheable actions
    array(
    )
);

// Register icons
$icons = [
    'jvevents-plugin' => 'ContentElementWizard.svg',
    'jvevents-location-map-wizard' => 'actions-geo.svg',
];
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
foreach ($icons as $identifier => $path) {
    $iconRegistry->registerIcon(
        $identifier,
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:jv_events/Resources/Public/Icons/' . $path]
    );
}
/**
 * Register Hook on delete/copy/move record ( event) - unset registrations and so on
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['jv_events'] =
	'JVE\\JvEvents\\Hooks\\ProcessCmdmap';

/**
 * Register Hook on save (new/update/copy) record ( event) Unset registrations end so on
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['jv_events'] =
	'JVE\\JvEvents\\Hooks\\ProcessDatamap';

/** @var \TYPO3\CMS\Core\Information\Typo3Version $version */
$version = GeneralUtility::makeInstance('TYPO3\CMS\Core\Information\Typo3Version');

if ($version->getMajorVersion()  < 10) {
    // to Check if we need this
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['jv_events'] = 'JVE\JvEvents\Controller\AjaxController::dispatcher';
}


if (TYPO3_MODE === 'FE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
    /**
     * Signal slot dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
     */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
    );
    $signalSlotDispatcher->connect(
        \JVE\JvEvents\Controller\RegistrantController::class,
        'createAction',
        \JVE\JvEvents\Signal\RegisterCitrixSignal::class,
        'createAction'
    );

    $signalSlotDispatcher->connect(
        \JVE\JvEvents\Controller\RegistrantController::class,
        'createAction',
        \JVE\JvEvents\Signal\RegisterSalesforceSignal::class,
        'createAction'
    );

    $signalSlotDispatcher->connect(
        \JVE\JvEvents\Controller\RegistrantController::class,
        'createAction',
        \JVE\JvEvents\Signal\RegisterHubspotSignal::class,
        'createAction'
    );

    // Feel Free to add your Own extension , and add your Own signal Slot to handle a registration
    // if you need more slots, contact me .. typo3@velletti.de
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['JVE\JvEvents\Scheduler\CleanEventsTask'] = array(
    'extension'        =>  $_EXTKEY,
    'title'            => 'Clean Events Extensions Data (remove registrations and old Events)',
    'description'      => 'set only frequency ',
    'additionalFields' => 'JVE\JvEvents\Scheduler\CleanEventsTaskAdditionalFieldProvider'
);

// Register a node in ext_localconf.php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1609762346] = [
    'nodeName' => 'jvEventsCustomLayoutElement',
    'priority' => 40,
    'class' => \JVE\JvEvents\FormEngine\Element\JvEventsCustomLayoutElement::class,
];
// Add wizard with map for setting geo location
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1609762347] = [
    'nodeName' => 'eventLocationMapWizard',
    'priority' => 42,
    'class' => \JVE\JvEvents\FormEngine\FieldControl\EventLocationMapWizard::class
];



