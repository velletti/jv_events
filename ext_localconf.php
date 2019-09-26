<?php
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
		'Registrant' => 'list, show,new,create,delete,confirm',
		'Tag' => 'list',
	),
	// non-cacheable actions
	array(
		'Event' => 'show, search, new, create, edit, update, register, confirm, delete,copy,cancel',
        'Organizer' => 'show, new, create, edit, update, delete,assist',
		'Registrant' => 'list,new,create,delete,confirm',
		'Location' => 'new, create, edit, update, delete,setDefault',
		
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JVE.' .$_EXTKEY,
    'Ajax',
    array(
        'Ajax'  => 'eventMenu,locationList,organizerList,eventList,eventDisable',
    ),
    array(
        'Ajax'  => 'eventMenu,locationList,organizerList,eventList,eventDisable',
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

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['jv_events'] = 'JVE\JvEvents\Controller\AjaxController::dispatcher';

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