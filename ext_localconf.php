<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'JVE.' . $_EXTKEY,
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
		'Event' => 'search, new, create, edit, update, register, confirm, delete',
		'Registrant' => 'new,create,delete,confirm',
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
