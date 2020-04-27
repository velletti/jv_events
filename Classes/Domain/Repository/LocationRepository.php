<?php
namespace JVE\JvEvents\Domain\Repository;

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
class LocationRepository extends \JVE\JvEvents\Domain\Repository\BaseRepository
{

    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'crdate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING ,
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING

    );

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

        // new way to debug typo3 db queries
        // $queryParser = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);
        // var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL());
        // var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters()) ;
        // die;
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
     * @return array|object
     */

    public function findByOrganizersAllpages($organizers , $toArray=TRUE , $ignoreEnableFields = TRUE , $onlyDefault = FALSE )
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(FALSE);
        $querySettings->setIgnoreEnableFields($ignoreEnableFields) ;
        $query->setQuerySettings($querySettings) ;

        // $query->setLimit($limit) ;
        if ( $onlyDefault ) {
            $constraints[] = $query->in('organizer.uid', $organizers ) ;
            $constraints[] = $query->equals('default_location', 1 ) ;
            $query->matching( $query->logicalAnd( $constraints ) ) ;
        } else {
            $query->matching( $query->in('organizer.uid', $organizers ) ) ;
        }

        $res = $query->execute() ;

        // new way to debug typo3 db queries
        $debug = false ;
        if ( $debug == true ) {
            $queryParser = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);
            var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL());
             var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters()) ;
            die;
        }
        if( $toArray === TRUE ) {
            return $res->toArray();
        } else {
            return $res ;
        }
    }

    public function findByFilterAllpages($filter=FALSE , $toArray=FALSE , $ignoreEnableFields = FALSE , $limit=FALSE)
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(FALSE);
        $querySettings->setIgnoreEnableFields($ignoreEnableFields) ;
        $query->setQuerySettings($querySettings) ;

        $constraints = array() ;
        if ( $filter ) {
            foreach ( $filter as $field => $value) {
                if( is_array( $value ) ) {
                    $constraints[] = $query->in($field ,  $value ) ;
                } else {
                    $constraints[] = $query->equals($field ,  $value ) ;
                }

            }
        }

        // and the normal visibility contrains , including date Time
        /** @var \DateTime $actualTime */
        $actualTime = new \DateTime('now' ) ;
        $actualTime->modify('-1 YEAR') ;
        $constraints[] = $query->logicalOr( [
                                                $query->greaterThanOrEqual('tstamp', $actualTime ),
                                                $query->greaterThanOrEqual('latest_event', $actualTime )
                                            ]
        );
        if( $limit) {
            $query->setLimit(intval($limit));
        }

        if ( $ignoreEnableFields ) {
            $constraints[] =  $query->equals('deleted',  0 )  ;
        }
        $query->matching( $query->logicalAnd($constraints)) ;
        $res = $query->execute() ;

        // new way to debug typo3 db queries
        $debug = false ;
        if ( $debug == true ) {
            $queryParser = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);
            var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL());
            var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters()) ;
            die;
        }
        if( $toArray === TRUE ) {
            return $res->toArray();
        } else {
            return $res ;
        }
    }
}