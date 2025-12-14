<?php
namespace JVelletti\JvEvents\Utility;

class PluginUtility
{


	public static function getPluginArray()
    {

	    return [
            ["name" => 'Events' , "title" =>  'Events: List Events with filters', "ff" => 'events' , 'icon' => 'jvevents-plugin'] ,
            ["name" => 'Event'  , "title" =>  'Events: Single Event - show, create, edit ...', "ff" => 'event'  , 'icon' => 'jvevents-plugin'] ,
            ["name" => 'Organizers' , "title" => 'Organizer: List Organizers with filter', "ff" => 'organizers' , 'icon' => 'jvevents-plugin' ] ,
            ["name" => 'Organizer' , "title" =>  'Organizer: Single Organizer, show, create, edit', "ff" => 'organizer' , 'icon' => 'jvevents-plugin' ] ,
            ["name" => 'Assist' , "title" =>  'Organizer: Assistant', "ff" => 'organizers'  , 'icon' => 'jvevents-plugin'] ,
            ["name" => 'Locations' , "title" => 'Location: List of Locations', "ff" => 'locations' ] ,
            ["name" => 'Location' , "title" =>  'Location: Single Location - show, create, edit', "ff" => 'location'  , 'icon' => 'jvevents-plugin'] ,
            ["name" => 'Registrant' , "title" =>  'Registrant: list, create etc', "ff" => 'registrant' , 'icon' => 'jvevents-plugin' ] ,
            ["name" => 'Medias' , "title" =>  'Media: list', "ff" => 'media' , 'icon' => 'jvevents-plugin' ] ,
            ["name" => 'Media' , "title" =>  'Media: single, create etc', "ff" => 'media' , 'icon' => 'jvevents-plugin' ] ,
            ["name" => 'Ajax' , "title" =>  'Others: Ajax requests', "ff" => 'events'  , 'icon' => 'jvevents-plugin'] ,
            ["name" => 'Curl' , "title" =>  'Others: Load events from other Platform', "ff" => 'curl'  , 'icon' => 'jvevents-plugin']
        ] ;

	}


}