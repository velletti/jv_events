<?php
namespace JVE\JvEvents\Domain\Repository;

use DateTime;
use DateTimeZone;
use Exception;
use JVE\JvEvents\Utility\EmConfigurationUtility;
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
	protected $defaultOrderings = array(
		'start_date' => QueryInterface::ORDER_ASCENDING ,
		'start_time' => QueryInterface::ORDER_ASCENDING ,
        'tstamp' => QueryInterface::ORDER_DESCENDING
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
     * @return array|QueryInterface $query
     * @throws InvalidQueryException
     * @throws Exception
     */
    public function findByFilter($filter = false, $limit = false, $settings=false )
    {
		$configuration = EmConfigurationUtility::getEmConf();
        if ( is_array($filter)) {
            $settings['filter'] = $filter ;
        }
        $query = $this->createQuery();
		$query->setOrderings($this->defaultOrderings);



        $constraints = array();
		$query->getQuerySettings()->setRespectStoragePage(false);

		if ( $configuration['doNotRespectSyslanguage'] == 1 ) {
            $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        }


        $constraintsTagsCat = array() ;
        if( $settings['filter']['categories']  ) {
            $constraintsTagsCat = $this->getCatContraints($constraintsTagsCat,  $settings ,  $query );
        }
        if( $settings['filter']['tags']  ) {
            $constraintsTagsCat = $this->getTagContraints($constraintsTagsCat,  $settings ,  $query );
        }

        // Add filter for AND event is marked as TOP Event
        if( $settings['filter']['topEvents'] == 1 ) {
            $constraintsTagsCat[] = $query->equals("top_event",  1);
            $constraints[] = $query->logicalAnd($constraintsTagsCat) ;
        } else {

            // Add filter for OR event is marked as TOP Event
            // all other TAG or Cat Contraints should be resolved before !!
            if( $settings['filter']['topEvents'] == 2 ) {
                if( count($constraintsTagsCat) > 0 ) {
                    $constraintsTop[] = $query->logicalAnd($constraintsTagsCat);
                    $constraintsTop[] = $query->equals("top_event", 1);
                    $constraints[] = $query->logicalOr($constraintsTop);
                } else {
                    $constraints[] = $query->equals("top_event", 1);
                }
            } else {
                if( count($constraintsTagsCat) > 0 ) {
                    $constraints[] = $query->logicalAnd($constraintsTagsCat) ;
                }
            }
        }

        if( $settings['filter']['masterId']  ) {
            $constraints[] = $query->equals("masterId",  $settings['filter']['masterId'] );
        }

        if( $settings['filter']['organizer']  ) {
            $constraints[] = $query->equals("organizer",  $settings['filter']['organizer'] );
        }

        // $query->getQuerySettings()->setIgnoreEnableFields(FALSE) ;
		if( $settings['filter']['skipEvent'] > 0 ) {
			$constraints[] = $query->logicalNot( $query->equals("uid" , $settings['filter']['skipEvent'])) ;
			
		}
		if( $settings['storagePid'] > 0 ) {
			$constraints = $this->getPidContraints($constraints,  $settings ,  $query );
		}



        if( $settings['filter']['citys']  ) {
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
        $subconstraints = array() ;
        $subconstraints[] = $query->greaterThanOrEqual('endtime', $actualTime );
        $subconstraints[] = $query->lessThanOrEqual('endtime', 1 );
        $constraints[] = $query->logicalOr($subconstraints) ;
        $constraints[] = $query->lessThanOrEqual('starttime', $actualTime );





        if (count($constraints) >  0 ) {
            $query->matching($query->logicalAnd($constraints));
        }


		if( intval( $settings['filter']['maxEvents'] ) > 0 )  {
			$query->setLimit( intval( $settings['filter']['maxEvents'])) ;
		} elseif ($limit > 0) {
            $query->setLimit( intval( $limit )) ;
        }

        $result = $query->execute();
        //    $this->debugQuery($query) ;

        return $result;
    }


    /**
     * @param integer $uid
     * @return array|QueryInterface $query
     */
    public function findByLocation($uid )
    {
        $query = $this->createQuery();

        $constraints = array();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->getQuerySettings()->setRespectSysLanguage(FALSE);


        $constraints[] = $query->equals("location",  $uid );
            $query->matching($query->logicalAnd($constraints));

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

                        $startDate = new DateTime( 'NOW ' . $settings['filter']['startDate'] . ' Days' , $DateTimeZone) ;
                        $endDate = new DateTime( 'NOW +' . (intval ($settings['filter']['startDate'] )). ' Days' , $DateTimeZone) ;

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



            return array( "startDate" => $startDate ,
                          "endDate" => $endDate  ,
                          "nextDate" => $nextDate ,
                          "prevDate" => $previousDate
                        ) ;
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
		/** @var QueryGenerator $queryGenerator */
		$queryGenerator = GeneralUtility::makeInstance( 'TYPO3\\CMS\\Core\\Database\\QueryGenerator' );
		$rGetTreeList = $queryGenerator->getTreeList( $settings['storagePid'],  $settings['recursive'], 0, 1); //Will be a string

        $pidList = GeneralUtility::intExplode(',', $rGetTreeList, true);
        if (!empty($pidList)) {
            if (count($pidList) == 1) {
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

		if( count($catList) < 1 ) {
			return $constraints ;
		}



		if (count($catList) == 1) {
			$constraints[] = $query->equals('eventCategory.uid', $catList[0]);

		} else {
            $catConstraints = array() ;
            foreach ( $catList as $catUid ) {
                if ( $catUid > 0 ) {
                    $catConstraints[] = $query->equals('eventCategory.uid', $catUid);
                }
            }
            $constraints[] = $query->logicalOr( $catConstraints ) ;
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
        if( count($tagList) < 1 ) {
            return $constraints ;
        }

        if (count($tagList) == 1) {
            $constraints[] = $query->equals('tags.uid', $tagList[0]);
        } else {
            $tagConstraints = array() ;

            foreach ( $tagList as $tagUid ) {
                if ( $tagUid > 0 ) {
                    $tagConstraints[] = $query->equals('tags.uid', $tagUid );
                }
            }
            if ( $settings['filter']['combinetags'] ) {
                if ( $settings['ShowFilter'] == 0 ) {
                    $constraints[] = $query->logicalAnd( $tagConstraints ) ;
                } else {
                    $constraints[] = $query->logicalOr( $tagConstraints ) ;
                }
            } else {
                $constraints[] = $query->logicalOr( $tagConstraints ) ;
            }


        }

        return $constraints;
    }
}