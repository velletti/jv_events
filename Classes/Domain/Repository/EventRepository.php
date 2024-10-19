<?php
namespace JVelletti\JvEvents\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use DateTime;
use DateTimeZone;
use Exception;
use JVelletti\JvEvents\Utility\EmConfigurationUtility;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\CORE\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
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
 * The repository for Events
 */
class EventRepository extends BaseRepository
{

	/**
	 * @var array
	 */
	protected $defaultOrderings = ['start_date' => QueryInterface::ORDER_ASCENDING, 'start_time' => QueryInterface::ORDER_ASCENDING, 'tstamp' => QueryInterface::ORDER_DESCENDING];

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

        // $this->debugQuery($query) ;

        if( $toArray === TRUE ) {
            return $res->toArray();
        } else {
            return $res->getFirst() ;
        }
    }


    /**
     * @param array|boolean $filter
     * @param integer|boolean $limit
     * @param array|boolean $settings
     * @return QueryResultInterface|object[] $query
     * @throws InvalidQueryException
     * @throws Exception
     */
    public function findByFilter($filter = false, $limit = false, $settings=false )
    {
		$constraintsTop = [];
        $constraintsOrg = [];
        $configuration = EmConfigurationUtility::getEmConf();
        if ( is_array($filter)) {
            $settings['filter'] = $filter ;
        }
        if (!isset($settings['filter']) || !is_array($settings['filter'])) {
            $settings['filter'] = [] ;
        }
        $query = $this->createQuery();
        $sortings = false ;
        if( isset($settings['list']['sorting']) && is_array($settings['list']['sorting'])) {
            $sortings= [] ;
            foreach ($settings['list']['sorting'] as $sortField => $sort) {
                if ( in_array($sortField , ['crdate' , 'tstamp'] )) {
                    if( $sort == "ASC") {
                        $sortings[$sortField] = QueryInterface::ORDER_ASCENDING ;
                    } else {
                        $sortings[$sortField] = QueryInterface::ORDER_DESCENDING ;
                    }
                }

            }
        }
        if ( is_array( $sortings ) && count($sortings) > 0 ) {
            $query->setOrderings($sortings);
        } else {
            $query->setOrderings($this->defaultOrderings);
        }




        $constraints = [];
		$query->getQuerySettings()->setRespectStoragePage(false);

		if ( isset($configuration['doNotRespectSyslanguage']) && $configuration['doNotRespectSyslanguage'] == 1 ) {
            $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        }


        $constraintsTagsCat = [] ;
        if( isset($settings['filter']['categories'] ) && $settings['filter']['categories']  ) {
            $constraintsTagsCat = $this->getCatContraints($constraintsTagsCat,  $settings ,  $query );
        }
        if( isset($settings['filter']['tags']) && $settings['filter']['tags']  ) {
            $constraintsTagsCat = $this->getTagContraints($constraintsTagsCat,  $settings ,  $query );
        }

        if( isset($settings['filter']['notAllowedtags']) && $settings['filter']['notAllowedtags']  ) {
            $constraintsTagsCat = $this->getNotAllowedTagContraints($constraintsTagsCat,  $settings ,  $query );
        }

        // Add filter for AND event is marked as TOP Event
        if( isset($settings['filter']['topEvents']) && $settings['filter']['topEvents'] == 1 ) {
            $constraintsTagsCat[] = $query->equals("top_event",  1);
            $constraints[] = $query->logicalAnd(...$constraintsTagsCat) ;
        } else {

            // Add filter for OR event is marked as TOP Event
            // all other TAG or Cat Contraints should be resolved before !!
            if( isset($settings['filter']['topEvents']) &&  $settings['filter']['topEvents'] == 2 ) {
                if( count($constraintsTagsCat) > 0 ) {
                    $constraintsTop[] = $query->logicalAnd(...$constraintsTagsCat);
                    $constraintsTop[] = $query->equals("top_event", 1);
                    $constraints[] = $query->logicalOr(...$constraintsTop);
                } else {
                    $constraints[] = $query->equals("top_event", 1);
                }
            } else {
                if( count($constraintsTagsCat) > 0 ) {
                    $constraints[] = $query->logicalAnd(...$constraintsTagsCat) ;
                }
            }
        }
        // canceledEvent = default 0 = unset .. if set in Filter to 1 show Only canceledEvents
        if( isset( $settings['filter']['canceledEvents']) && $settings['filter']['canceledEvents'] == "1" ) {
            $constraints[] = $query->equals("canceled",  "1");
        }
        // canceledEvent = default 0 = unset .. if set in Filter to 2 hide  canceledEvents needed for corona
        if( isset( $settings['filter']['canceledEvents']) && $settings['filter']['canceledEvents'] == "2" ) {
            $constraints[] = $query->equals("canceled",  "0");
        }

        if( isset( $settings['filter']['masterId']) && $settings['filter']['masterId']  ) {
            $constraints[] = $query->equals("masterId",  $settings['filter']['masterId'] );
        }

        if( isset( $settings['filter']['organizer']) && $settings['filter']['organizer']  ) {
            $constraints[] = $query->equals("organizer",  $settings['filter']['organizer'] );
        }
        if( isset( $settings['filter']['location']) && $settings['filter']['location']  ) {
            $constraints[] = $query->equals("location",  $settings['filter']['location'] );
        }
        //
        if( isset( $settings['filter']['organizers']) && $settings['filter']['organizers']  ) {
            $organizers = GeneralUtility::trimExplode("," , $settings['filter']['organizers']  ) ;
            if (is_array( $organizers) ) {
                if( count($organizers) == 1 ) {
                    $constraints[] = $query->equals("organizer",  $organizers[0] );
                } else {
                    foreach ( $organizers as $organizer ) {
                        $constraintsOrg[]  = $query->equals("organizer",  $organizer );
                    }
                    $constraints[] = $query->logicalOr(...$constraintsOrg) ;
                }
                
            }

        }

        // $query->getQuerySettings()->setIgnoreEnableFields(FALSE) ;
		if( isset($settings['filter']['skipEvent'] ) && $settings['filter']['skipEvent'] > 0 ) {
			$constraints[] = $query->logicalNot( $query->equals("uid" , $settings['filter']['skipEvent'])) ;
			
		}
		if( isset( $settings['storagePid']) && $settings['storagePid'] > 0 ) {
			$constraints = $this->getPidContraints($constraints,  $settings ,  $query );
		}



        if( isset($settings['filter']['citys']) && $settings['filter']['citys']  ) {
            $constraints[] = $query->logicalAnd( $query->in("location" , GeneralUtility::trimExplode( "," , $settings['filter']['citys'])) ) ;
        }
/*
		if ( $configuration['recurring'] == 1 ) {
			// do some magic for recurring events
		}
*/
        $DateTimeZone = $this->getDateTimeZone() ;

		if( array_key_exists( 'startDate' ,  $settings['filter'] ) && ( intval( $settings['filter']['startDate'] ) > -9999  || intval( $settings['filter']['maxDays'] ) > 0  ))  {
			$constraints = $this->getDateContraints($constraints,  $settings ,  $query  , $DateTimeZone);
		}
        // and the normal visibility contrains , including date Time
        $actualTime = new DateTime('now' ,$DateTimeZone ) ;
        $subconstraints = [] ;
        $subconstraints[] = $query->greaterThanOrEqual('endtime', $actualTime );
        $subconstraints[] = $query->lessThanOrEqual('endtime', 1 );
        $constraints[] = $query->logicalOr(...$subconstraints) ;
        $constraints[] = $query->lessThanOrEqual('starttime', $actualTime );





        if (count($constraints) >  0 ) {
            $query->matching($query->logicalAnd(...$constraints));
        }


		if( isset($settings['filter']['maxEvents'] ) && intval( $settings['filter']['maxEvents'] ) > 0 )  {
			$query->setLimit( intval( $settings['filter']['maxEvents'])) ;
		} elseif ($limit > 0) {
            $query->setLimit( intval( $limit )) ;
        } else  {



        }
        $query->setLimit( 4 ) ;

        $result = $query->execute();
       // $this->debugQuery($query) ;

        return $result;
    }


    /**
     * @param integer $uid
     * @return array|QueryInterface $query
     */
    public function findByLocation($uid )
    {
        $query = $this->createQuery();

        $constraints = [];
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->getQuerySettings()->setRespectSysLanguage(FALSE);


        $constraints[] = $query->equals("location",  $uid );
            $query->matching($query->logicalAnd(...$constraints));

        $result = $query->execute();
        if ( 1 == 2 ) {
            $this->debugQuery($query) ;
        }
        return $result;
    }



    /**
     * @param array $settings
     * @param DateTimeZone $DateTimeZone
     *
     * @return array|boolean
     * @throws Exception
     */
    public function getDateArray( $settings , $DateTimeZone )
    {
        if( intval( $settings['filter']['startDate'] ) > -9999 ) {

            /** @var DateTime $startDate */
            /** @var DateTime $endDate */
            switch ($settings['filter']['startDate']) {
                case '0':
                    $startDate = new DateTime( 'NOW' , $DateTimeZone ) ;
                    $endDate = new DateTime( 'NOW +' . (intval( $settings['filter']['maxDays'] - 1 )) . ' Days' , $DateTimeZone) ;
                    break;
                case '-1':
                    $startDate = new DateTime( 'NOW -1 Days', $DateTimeZone) ;
                    $endDate = new DateTime( 'NOW +' . (intval ($settings['filter']['maxDays']) -2 ). ' Days', $DateTimeZone) ;
                    break;
                case '+1':
                    $startDate = new DateTime( 'NOW +1 Days', $DateTimeZone) ;
                    $endDate = new DateTime( 'NOW +' . (intval ($settings['filter']['maxDays']) ). ' Days', $DateTimeZone) ;
                    break;
                default:
                    if( intval( $settings['filter']['startDate'] ) > 9999 ) {
                        $startDate = new DateTime("now" , $DateTimeZone ) ;
                        $startDate->setTimestamp($settings['filter']['startDate']) ;
                        $endDate = new DateTime( 'now' , $DateTimeZone) ;
                        $endDate->setTimestamp($settings['filter']['startDate']) ;
                        $endDate->modify("+" . intval( $settings['filter']['maxDays']-1) . " Days" ) ;

                    } else {
                        $sd = $settings['filter']['startDate'] ?: "0" ;
                        $startDate = new DateTime( 'NOW ' . $sd . ' Days' , $DateTimeZone) ;
                        $endDate = new DateTime( 'NOW +' . (intval ($sd )). ' Days' , $DateTimeZone) ;

                        $endDate->modify("+" . intval( $settings['filter']['maxDays'] -1) . " Days" ) ;

                    }

                    break;
            }
            $startDate->setTime(0,0,0) ;
            $endDate->setTime(23,59,59) ;
            $nextDate = false ;
            $previousDate = false ;

            if ( $settings['filter']['maxDays'] > 0 ) {
                $nextDate = new DateTime() ;
                $nextDate->setTimestamp( $endDate->getTimestamp() + 1) ;

                $diff = $endDate->getTimestamp() - $startDate->getTimestamp() + 1 ;
                $previousDate = new DateTime() ;
                $previousDate->setTimestamp( $startDate->getTimestamp() - $diff ) ;
            }



            return ["startDate" => $startDate, "endDate" => $endDate, "nextDate" => $nextDate, "prevDate" => $previousDate] ;
        }

        return false ;
    }
    public function getDateTimeZone() {
        $DTZ = $GLOBALS['TYPO3_CONF_VARS']['SYS']['oriPhpTimeZone'] == '' ? $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] : $GLOBALS['TYPO3_CONF_VARS']['SYS']['oriPhpTimeZone'] ;

        $DTZ = $DTZ == '' ? @date_default_timezone_get() : $DTZ ;
        $DTZ = $DTZ == '' ? 'UTC' : $DTZ ;
        $DateTimeZone = new DateTimeZone($DTZ)  ;
        return $DateTimeZone ;
    }

    /**
     * @param array $constraints
     * @param array $settings
     * @param QueryInterface $query
     * @param DateTimeZone $DateTimeZone
     *
     * @return array
     * @throws InvalidQueryException
     * @throws Exception
     */
	private function getDateContraints($constraints, $settings ,  $query , $DateTimeZone )
	{
		if( intval( $settings['filter']['startDate'] ) > -9999 ) {

		    $dates = $this->getDateArray( $settings , $DateTimeZone ) ;

            // Now set  the Date values of the Event when it starts or ends
            $constraints[] = $query->greaterThanOrEqual('start_date', $dates['startDate'] );

            if( intval( $settings['filter']['maxDays'] ) > 0 ) {
                $constraints[] = $query->lessThanOrEqual('start_date', $dates['endDate'] );
            }

		}

        return $constraints;
	}

    /**
     * @param array $constraints
     * @param array $settings
     * @param QueryInterface $query
     *
     * @return array
     * @throws InvalidQueryException
     */
    private function getPidContraints($constraints, $settings ,  $query)
    {
		$rGetTreeList = $this->getTreeList( $settings['storagePid'],  $settings['recursive'], 0, 1); //Will be a string

        $pidList = GeneralUtility::intExplode(',', $rGetTreeList, true);
        if (!empty($pidList)) {
            if ((is_countable($pidList) ? count($pidList) : 0) == 1) {
                $constraints[] = $query->equals('pid', $pidList[0]);
            } else {
                $constraints[] = $query->in('pid', $pidList);
            }
        }
        return $constraints;
    }


	/**
	 * @param array $constraints
	 * @param array $settings
	 * @param QueryInterface $query
	 *
	 * @return array
	 */
	private function getCatContraints($constraints, $settings ,  $query)
	{
		$catList = GeneralUtility::intExplode(',', $settings['filter']['categories'], true);

		if( (is_countable($catList) ? count($catList) : 0) < 1 ) {
			return $constraints ;
		}



		if ((is_countable($catList) ? count($catList) : 0) == 1) {
			$constraints[] = $query->equals('eventCategory.uid', $catList[0]);

		} else {
            $catConstraints = [] ;
            foreach ( $catList as $catUid ) {
                if ( $catUid > 0 ) {
                    $catConstraints[] = $query->equals('eventCategory.uid', $catUid);
                }
            }
            $constraints[] = $query->logicalOr(... $catConstraints ) ;
		}

		return $constraints;
	}



    /**
     * @param array $constraints
     * @param array $settings
     * @param QueryInterface $query
     *
     * @return array
     */
    private function getTagContraints($constraints, $settings ,  $query)
    {
        $tagList = GeneralUtility::intExplode(',', $settings['filter']['tags'], true);
        if( (is_countable($tagList) ? count($tagList) : 0) < 1 ) {
            return $constraints ;
        }

        if ((is_countable($tagList) ? count($tagList) : 0) == 1) {
            $constraints[] = $query->equals('tags.uid', $tagList[0]);
        } else {
            $tagConstraints = [] ;

            foreach ( $tagList as $tagUid ) {
                if ( $tagUid > 0 ) {
                    $tagConstraints[] = $query->equals('tags.uid', $tagUid );
                }
            }
            $constraints[] = $query->logicalOr(... $tagConstraints ) ;
        }

        return $constraints;
    }

    /**
     * @param array $constraints
     * @param array $settings
     * @param QueryInterface $query
     *
     * @return array
     */
    private function getNotAllowedTagContraints($constraints, $settings ,  $query)
    {
        $tagList = GeneralUtility::intExplode(',', $settings['filter']['notAllowedtags'], true);
        if( (is_countable($tagList) ? count($tagList) : 0) < 1 ) {
            return $constraints ;
        }

        if ((is_countable($tagList) ? count($tagList) : 0) == 1) {
            $constraints[] = $query->logicalNot($query->contains('tags', $tagList[0] )) ;
        } else {
            $tagConstraints = [] ;

            foreach ( $tagList as $tagUid ) {
                if ( $tagUid > 0 ) {
                    $tagConstraints[] = $query->logicalNot($query->contains('tags', $tagUid ));
                }
            }
            $constraints[] = $query->logicalAnd(... $tagConstraints ) ;
        }

        return $constraints;
    }
}