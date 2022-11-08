<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "jv_events"
 *
 * Auto generated by Extension Builder 2016-09-20
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF['jv_events'] = array(
	'title' => 'Eventmanagement',
	'description' => 'List and show Events with filters. Including registration',
	'category' => 'plugin',
	'author' => 'Jörg Velletti',
	'author_email' => 'typo3@velletti.de',
	'state' => 'stable',
	'clearCacheOnLoad' => true,
	'version' => '10.4.2',
	'constraints' => array(
		'depends' => array(
			'typo3' => '10.4.0-11.5.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
            'static_info_tables' => '6.4.0-6.99.99',
		),
	),
);