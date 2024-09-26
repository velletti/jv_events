<?php
namespace JVelletti\JvEvents\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Jörg velletti <jVelletti@allplan.com>, Allplan GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * The repository for Locations
 */
class LocationRepository extends BaseRepository
{

    /**
     * @var array
     */
    protected $defaultOrderings = ['crdate' => QueryInterface::ORDER_DESCENDING, 'sorting' => QueryInterface::ORDER_ASCENDING];

    public function findByUidAllpages($uid , $toArray=TRUE , $ignoreEnableFields = TRUE )
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(FALSE);
        $querySettings->setIgnoreEnableFields($ignoreEnableFields) ;
        $query->setQuerySettings($querySettings) ;

        $query->setLimit(1) ;

        $query->matching( $query->equals('uid', $uid ) ) ;
        $res = $query->execute() ;

        if( $toArray === TRUE ) {
            return $res->toArray();
        } else {
            return $res->getFirst() ;
        }
    }

    /** takes UIDs of $organizers  as array
     * @param array $organizers
     * @param bool $toArray
     * @param bool $ignoreEnableFields
     * @param bool $onlyDefault
     * @param bool $orderby
     * @param bool|string $lastModified  // f.e. '-30 day'
     * @return array|object
     */

    public function findByOrganizersAllpages($organizers , $toArray=TRUE , $ignoreEnableFields = TRUE , $onlyDefault = FALSE , $orderby = false , $lastModified = false )
    {
        $constraints = [];
        $sortings = [];
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(FALSE);
        $querySettings->setIgnoreEnableFields($ignoreEnableFields) ;
        $query->setQuerySettings($querySettings) ;
        if ( $ignoreEnableFields ) {
        //    $constraints[] = $query->equals('organizer.uid', $organizers ) ;
        }
        // $query->setLimit($limit) ;
        if ( $onlyDefault ) {
            $constraints[] = $query->equals('default_location', 1 ) ;
        }
        if ( count( $organizers) > 1 ) {
            $constraints[] = $query->in('organizer', $organizers ) ;
        } else {
            $constraints[] = $query->equals('organizer', $organizers[0] ) ;
        }
        if( $lastModified ) {
            /** @var \DateTime $actualTime */
            $actualTime = new \DateTime('now' ) ;
            $actualTime->modify($lastModified ) ;

            $constraints[] = $query->logicalOr(...[$query->greaterThanOrEqual('tstamp', $actualTime ), $query->greaterThanOrEqual('latest_event', $actualTime )]);
        }
        if (count($constraints) === 1) {
            $query->matching(reset($constraints));
        } elseif (count($constraints) >= 2) {
            $query->matching($query->logicalAnd(...$constraints));
        }

        $query->matching( $query->logicalAnd(... $constraints ) ) ;
        if (  $orderby  ) {
            switch ($orderby) {
                case "latestEventDESC":
                    $sortings['latestEvent'] = QueryInterface::ORDER_DESCENDING ;
                    $query->setOrderings($sortings);
                    break;
            }
        }
        $res = $query->execute() ;

        if( $toArray === TRUE ) {
            return $res->toArray();
        } else {
            return $res ;
        }
    }

    public function findByFilterAllpages($filter=FALSE , $toArray=FALSE , $ignoreEnableFields = FALSE , $limit=FALSE, $lastModified = '-1 YEAR')
    {
        $constraintsOr = [];
        $query = $this->createQuery();
        $query->setOrderings( [ 'organizer.sorting' => QueryInterface::ORDER_ASCENDING ] );

        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(FALSE);
        $querySettings->setIgnoreEnableFields($ignoreEnableFields) ;
        $query->setQuerySettings($querySettings) ;

        $constraints = [] ;
        if ( $filter && (is_countable($filter) ? count($filter) : 0) > 0 ) {
            foreach ( $filter as $field => $value) {
                if ( $field == "lng" || $field == "lat") {
                    $constraints[] = $query->greaterThanOrEqual(  $field  ,  $value[0] ) ;
                    $constraints[] = $query->lessThanOrEqual(  $field  ,  $value[1] ) ;
                } else {
                    if( is_array( $value ) ) {
                        $constraints[] = $query->in($field ,  $value ) ;
                    } else {
                        $constraints[] = $query->equals($field ,  $value ) ;
                    }
                }
            }
        }

        // and the normal visibility contrains , including date Time
        if( $lastModified ) {
            /** @var \DateTime $actualTime */
            $actualTime = new \DateTime('now' ) ;
            $actualTime->modify($lastModified ) ;

            $constraintsOr[] = $query->greaterThanOrEqual('tstamp', $actualTime );
            $constraintsOr[] = $query->greaterThanOrEqual('latest_event', $actualTime );
            $constraints[] = $query->logicalOr(...$constraintsOr);
        }

        if( $limit) {
            $query->setLimit(intval($limit));
        }

        if ( $ignoreEnableFields ) {
            $constraints[] =  $query->equals('deleted',  0 )  ;
            $constraintsOr = [];
            $constraintsOr[] = $query->equals('organizer.hidden', 0 ) ;
            $constraintsOr[] = $query->equals('organizer.uid', null );
            $constraints[] = $query->logicalOr(...$constraintsOr ) ;
        }
        if (count($constraints) === 1) {
            $query->matching(reset($constraints));
        } elseif (count($constraints) >= 2) {
            $query->matching($query->logicalAnd(...$constraints));
        }
        $query->matching( $query->logicalAnd(...$constraints)) ;
        $res = $query->execute() ;

        // new way to debug typo3 db queries
        // $this->debugQuery( $query) ;
        if( $toArray === TRUE ) {
            return $res->toArray();
        } else {
            return $res ;
        }
    }
    public function getBoundingBox($lat_degrees,$lon_degrees,$distance) {

           $topLeft = $this->getBoundingBoxCoords($lat_degrees,$lon_degrees,315, $distance)  ;
           $bottomRight = $this->getBoundingBoxCoords($lat_degrees,$lon_degrees,135, $distance)  ;
           if ( $topLeft['lat'] > $bottomRight['lat'] ) {
               $lat = [$bottomRight['lat'], $topLeft['lat']]  ;
           } else {
               $lat = [$topLeft['lat'], $bottomRight['lat']]  ;
           }
            if ( $topLeft['lng'] > $bottomRight['lng'] ) {
                $lng = [$bottomRight['lng'], $topLeft['lng']]  ;
            } else {
                $lng = [$topLeft['lng'], $bottomRight['lng']]  ;
            }
       return ["lat" => $lat, "lng" => $lng];

            // old Code.. Does not work with Augsburg even with 200 km

           //  $radius = 3963.1; // of earth in miles
            $radius = 6371; // of earth in Km

            // bearings
            $due_north = 0;
            $due_south = 180;
            $due_east = 90;
            $due_west = 270;

            // convert latitude and longitude into radians
            $lat_r = deg2rad($lat_degrees);
            $lon_r = deg2rad($lon_degrees);

            // find the northmost, southmost, eastmost and westmost corners $distance away
            // original formula from
            // http://www.movable-type.co.uk/scripts/latlong.html

            $northmost  = asin(sin($lat_r) * cos($distance/$radius) + cos($lat_r) * sin ($distance/$radius) * cos($due_north));
            $southmost  = asin(sin($lat_r) * cos($distance/$radius) + cos($lat_r) * sin ($distance/$radius) * cos($due_south));

            $eastmost = $lon_r + atan2(sin($due_east)*sin($distance/$radius)*cos($lat_r),cos($distance/$radius)-sin($lat_r)*sin($lat_r));
            $westmost = $lon_r + atan2(sin($due_west)*sin($distance/$radius)*cos($lat_r),cos($distance/$radius)-sin($lat_r)*sin($lat_r));


            $northmost = rad2deg($northmost);
            $southmost = rad2deg($southmost);
            $eastmost = rad2deg($eastmost);
            $westmost = rad2deg($westmost);

            // sort the lat and long so that we can use them for a between query
            if ($northmost > $southmost) {
                $lat1 = $southmost;
                $lat2 = $northmost;

            } else {
                $lat1 = $northmost;
                $lat2 = $southmost;
            }


            if ($eastmost > $westmost) {
                $lon1 = $westmost;
                $lon2 = $eastmost;

            } else {
                $lon1 = $eastmost;
                $lon2 = $westmost;
            }

            return ["lat" => [$lat1, $lat2], "lng" => [$lon1, $lon2]];
    }

    /**
     * Get coords for a bounding box by providing center coords and a distance
     *
     * ## Usage
     *
     * // Figure out the corners of a box surrounding our lat/lng.
     * $d = 0.3;  // distance
     * $path_top_right = getBoundingBoxCoords($lat, $lng, 45, $d);
     * $path_bottom_right = getBoundingBoxCoords($lat, $lng, 135, $d);
     * $path_bottom_left = getBoundingBoxCoords($lat, $lng, 225, $d);
     * $path_top_left = getBoundingBoxCoords($lat, $lng, 315, $d);
     *
     * ## Sources
     * <https://gist.github.com/marcus-at-localhost/39a346e7d7f872187124af9cd582f833>
     * <http://www.sitepoint.com/forums/showthread.php?656315-adding-distance-gps-coordinates-get-bounding-box>
     * <http://richardpeacock.com/sites/default/files/getDueCoords.php__0.txt>
     * <http://stackoverflow.com/a/8195239/814031>
     *
     * @param  float   $latitude
     * @param  float   $longitude
     * @param  int     $bearing        315 = top left , 135 = bottom right  0 = north, 180 = south, 90 = east, 270 = west
     * @param  int     $distance
     * @param  string  $distance_unit   m = miles or km = kilometer
     * @param  boolean $return_as_array
     * @return mixed   string or array
     */
    function getBoundingBoxCoords($latitude, $longitude, $bearing, $distance ) {

        // distance is in km.
        $radius = 6378.1;

        //	New latitude in degrees.
        $new_latitude = rad2deg(asin(sin(deg2rad($latitude)) * cos($distance / $radius) + cos(deg2rad($latitude)) * sin($distance / $radius) * cos(deg2rad($bearing))));

        //	New longitude in degrees.
        $new_longitude = rad2deg(deg2rad($longitude) + atan2(sin(deg2rad($bearing)) * sin($distance / $radius) * cos(deg2rad($latitude)), cos($distance / $radius) - sin(deg2rad($latitude)) * sin(deg2rad($new_latitude))));

        return [ 'lat' => $new_latitude , 'lng' => $new_longitude];

    }
}