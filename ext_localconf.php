<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'JVE.jv_events' ,
	'Events',
	array(
		'Event' => 'list, show, new, create, edit, update, delete, register, confirm, search',
		'Organizer' => 'list, show, new, create, edit, update, delete',
		'Location' => 'list, show, new, create, edit, update, delete',
		'Registrant' => 'list, show,new,create,delete,confirm',
		'Tag' => 'list',
		
	),
	// non-cacheable actions
	array(
		'Event' => 'show, search, new, create, edit, update, register, confirm, delete',
        'Organizer' => 'show, new, create, edit, update, delete',
		'Registrant' => 'list,new,create,delete,confirm',
		'Location' => 'new, create, edit, update, delete',
		
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

    // Feel Free to add your Own extension , and add your Own signal Slot to handle a registration
    // if you need more slots, contact me .. typo3@velletti.de
}