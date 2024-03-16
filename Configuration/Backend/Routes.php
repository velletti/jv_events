<?php

use JVelletti\JvEvents\Wizard\Geocoder;
return [
	'jv_events_wizard_geocoder' => [
		'path' => '/wizard/jv_events_wizard_geocoder',
		'target' => Geocoder::class . '::mainAction'
	],
];
