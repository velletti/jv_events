<?php

if (!defined('TYPO3')) {
	die('Access denied.' );
}
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'JvEvents' ,
	'Events',
	array(
		\JVelletti\JvEvents\Controller\EventController::class => 'list, show, new, create, edit, update, delete, register, confirm, search,copy,cancel',
		\JVelletti\JvEvents\Controller\OrganizerController::class => 'list, show, new, create, edit, update, delete,assist,activate',
		\JVelletti\JvEvents\Controller\LocationController::class => 'list, show, new, create, edit, update, delete,setDefault',
		\JVelletti\JvEvents\Controller\RegistrantController::class => 'list, show,new,create,delete,confirm,checkQrcode',
		\JVelletti\JvEvents\Controller\TagController::class => 'list',
	),
	// non-cacheable actions
	array(
		\JVelletti\JvEvents\Controller\EventController::class => 'show, search, new, create, edit, update, register, confirm, delete,copy,cancel',
        \JVelletti\JvEvents\Controller\OrganizerController::class => 'show, new, create, edit, update, delete,assist,activate',
		\JVelletti\JvEvents\Controller\RegistrantController::class => 'list,new,create,delete,confirm,checkQrcode',
		\JVelletti\JvEvents\Controller\LocationController::class => 'new, create, edit, update, delete,setDefault',
		
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JvEvents' ,
    'Ajax',
    array(
        \JVelletti\JvEvents\Controller\AjaxController::class  => 'eventMenu,locationList,organizerList,eventList,eventDisable,eventUnlink',
    ),
    array(
        \JVelletti\JvEvents\Controller\AjaxController::class  => 'eventMenu,locationList,organizerList,eventList,eventDisable,eventUnlink',
    )
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JvEvents' ,
    'search',
    array(
        \JVelletti\JvEvents\Controller\SearchController::class  => 'search',
    ),
    // non-cacheable actions
    array(
    )
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JvEvents' ,
    'Curl',
    array(
        \JVelletti\JvEvents\Controller\CurlController::class  => 'externalEvents',
    ),
    // non-cacheable actions
    array(
        \JVelletti\JvEvents\Controller\CurlController::class  => 'externalEvents',
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
	\JVelletti\JvEvents\Hooks\ProcessCmdmap::class;

/**
 * Register Hook on save (new/update/copy) record ( event) Unset registrations end so on
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['jv_events'] =
	\JVelletti\JvEvents\Hooks\ProcessDatamap::class;



$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\JVelletti\JvEvents\Scheduler\CleanEventsTask::class] = array(
    'extension'        =>  "jv_events" ,
    'title'            => 'Clean Events Extensions Data (remove registrations and old Events)',
    'description'      => 'set only frequency ',
    'additionalFields' => \JVelletti\JvEvents\Scheduler\CleanEventsTaskAdditionalFieldProvider::class
);

// Register a node in ext_localconf.php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1609762346] = [
    'nodeName' => 'jvEventsCustomLayoutElement',
    'priority' => 40,
    'class' => \JVelletti\JvEvents\FormEngine\Element\JvEventsCustomLayoutElement::class,
];
// Add wizard with map for setting geo location

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1609762347] = [
    'nodeName' => 'eventLocationMapWizard',
    'priority' => 42,
    'class' => \JVelletti\JvEvents\FormEngine\FieldControl\EventLocationMapWizard::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1655213081] = [
    'nodeName' => 'showEventInFrontend',
    'priority' => 42,
    'class' => \JVelletti\JvEvents\FormEngine\FieldControl\ShowEventInFrontend::class
];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1655213082] = [
    'nodeName' => 'getIcalLink',
    'priority' => 44,
    'class' => \JVelletti\JvEvents\FormEngine\FieldControl\GetIcalLink::class
];




