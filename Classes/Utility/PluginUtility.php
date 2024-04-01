<?php
namespace JVelletti\JvEvents\Utility;

class PluginUtility
{


	public static function getPluginArray()
    {

	    return [
            ["name" => 'Events' , "title" =>  'Events: List Events with filters', "ff" => 'events' ] ,
            ["name" => 'Event'  , "title" =>  'Events: Single Event - show, create, edit ...', "ff" => 'event' ] ,
            ["name" => 'Organizers' , "title" => 'Organizer: List Organizers with filter', "ff" => 'organizers' ] ,
            ["name" => 'Organizer' , "title" =>  'Organizer: Single Organizer, show, create, edit', "ff" => 'organizer' ] ,
            ["name" => 'Assist' , "title" =>  'Organizer: Assistant', "ff" => 'organizers' ] ,
            ["name" => 'Locations' , "title" => 'Location: List of Locations', "ff" => 'locations' ] ,
            ["name" => 'Location' , "title" =>  'Location: Single Location - show, create, edit', "ff" => 'location' ] ,
            ["name" => 'Registrant' , "title" =>  'Registrant: list, create etc', "ff" => 'registrant' ] ,
            ["name" => 'Ajax' , "title" =>  'Others: Ajax requests', "ff" => 'events' ] ,
            ["name" => 'Curl' , "title" =>  'Others: Load events from other Platform', "ff" => 'curl' ]
        ] ;

	}


}