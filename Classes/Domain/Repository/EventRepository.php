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
 * The repository for Events
 */
class EventRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'start_date' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING ,
		'start_time' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING ,
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
        //  var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL());
        // var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters()) ;
        // die;
        if( $toArray === TRUE ) {
            return $res->toArray(); // TODO: Change the autogenerated stub
        } else {
            return $res->getFirst() ;
        }
    }


    /**
     * @param array|boolean $filter
     * @param integer|boolean $limit
     * @param array|boolean $settings
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     */
    public function findByFilter($filter = false, $limit = false, $settings=false )
    {
		$configuration = \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();
        if ( is_array($filter)) {
            $settings['filter'] = $filter ;
        }
        $query = $this->createQuery();
		$query->setOrderings($this->defaultOrderings);



        $constraints = array();
		$query->getQuerySettings()->setRespectStoragePage(false);
       // $query->getQuerySettings()->setIgnoreEnableFields(FALSE) ;
		if( $settings['filter']['skipEvent'] > 0 ) {
			$constraints[] = $query->logicalNot( $query->equals("uid" , $settings['filter']['skipEvent'])) ;
			
		}
		if( $settings['storagePid'] > 0 ) {
			$constraints = $this->getPidContraints($constraints,  $settings , $configuration , $query );
		}
		if( $settings['filter']['categories']  ) {
			$constraints = $this->getCatContraints($constraints,  $settings , $configuration , $query );
		}
        if( $settings['filter']['tags']  ) {
            $constraints = $this->getTagContraints($constraints,  $settings , $configuration , $query );
        }
        if( $settings['filter']['organizer']  ) {
            $constraints[] = $query->equals("organizer",  $settings['filter']['organizer'] );
        }

        if( $settings['filter']['citys']  ) {
            $constraints[] = $query->logicalAnd( $query->in("location" , \TYPO3\CMS\CORE\Utility\GeneralUtility::trimExplode( "," , $settings['filter']['citys'])) ) ;
        }

		if ( $configuration['recurring'] == 1 ) {
			// do some magic for recurring events
		}
        $DTZ = $GLOBALS['TYPO3_CONF_VARS']['SYS']['oriPhpTimeZone'] == '' ? $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] : $GLOBALS['TYPO3_CONF_VARS']['SYS']['oriPhpTimeZone'] ;

        $DTZ = $DTZ == '' ? @date_default_timezone_get() : $DTZ ;
        $DTZ = $DTZ == '' ? 'UTC' : $DTZ ;
        $DateTimeZone = new \DateTimeZone($DTZ)  ;

		if( intval( $settings['filter']['startDate'] ) > -9999  || intval( $settings['filter']['maxDays'] ) > 0  )  {
			$constraints = $this->getDateContraints($constraints,  $settings , $configuration , $query  , $DateTimeZone);
		}
        // and the normal visibility contrains , including date Time
        $actualTime = new \DateTime('now' ,$DateTimeZone ) ;
        $subconstraints = array() ;
        $subconstraints[] = $query->greaterThanOrEqual('endtime', $actualTime );
        $subconstraints[] = $query->lessThanOrEqual('endtime', 1 );
        $constraints[] = $query->logicalOr($subconstraints) ;
        $constraints[] = $query->lessThanOrEqual('starttime', $actualTime );

        if (count($constraints) >  0) {
            $query->matching($query->logicalAnd($constraints));
        }

		if( intval( $settings['filter']['maxEvents'] ) > 0 )  {
			$query->setLimit( intval( $settings['filter']['maxEvents'])) ;
		} elseif ($limit > 0) {
            $query->setLimit( intval( $limit )) ;
        }

        $result = $query->execute();
		// $settings['debug'] = 2 ;
		if ($settings['debug'] == 2 ) {
            $queryParser = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);

            $sqlquery = $queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL() ;
            echo "<html><body><div>";
            echo $sqlquery ;
            echo "<hr>Values: <br>" ;
            $values = ($queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters()) ;
            echo "<pre>" ;
            echo var_export($values , true ) ;
            echo "</pre>" ;
            $from = array() ;
            $to = array() ;
            foreach (array_reverse( $values ) as $key => $value) {
                $from[] = ":" .$key ;
                $to[] = $value ;
            }
            $sqlFinalQuery = str_replace($from , $to , $sqlquery ) ;
            echo "<hr>Final: <br>" ;
            echo str_replace( array( "(" , ")" )  , array("<br>(" , ")<br>" ) , $sqlFinalQuery ) ;
            echo "<br><hr><br></div></body></html>" ;

            die;
        }
        return $result;
    }


	/**
	 * @param array $constraints
	 * @param array $settings
	 * @param array $configuration
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @param \DateTimeZone $DateTimeZone
	 *
	 * @return array
	 */
	private function getDateContraints($constraints, $settings , $configuration , $query , $DateTimeZone )
	{
		if( intval( $settings['filter']['startDate'] ) > -9999 ) {


			/** @var \DateTime $startDate */
			/** @var \DateTime $endDate */
			switch ($settings['filter']['startDate']) {
				case '0':
                    $startDate = new \DateTime( 'NOW' , $DateTimeZone ) ;
                    $endDate = new \DateTime( 'NOW +' . (intval( $settings['filter']['maxDays'] - 1 )) . ' Days' , $DateTimeZone) ;
					break;
				case '-1':
                    $startDate = new \DateTime( 'NOW -1 Days', $DateTimeZone) ;
                    $endDate = new \DateTime( 'NOW +' . (intval ($settings['filter']['maxDays']) -2 ). ' Days', $DateTimeZone) ;
					break;
				case '+1':
                    $startDate = new \DateTime( 'NOW +1 Days', $DateTimeZone) ;
                    $endDate = new \DateTime( 'NOW +' . (intval ($settings['filter']['maxDays']) ). ' Days', $DateTimeZone) ;
					break;
				default:
					if( intval( $settings['filter']['startDate'] ) > 9999 ) {
                        $startDate = new \DateTime("now" , $DateTimeZone ) ;
                        $startDate->setTimestamp($settings['filter']['startDate']) ;
                        $endDate = new \DateTime( 'now' , $DateTimeZone) ;
                        $endDate->setTimestamp($settings['filter']['startDate']) ;
                        $endDate->modify("+" . intval( $settings['filter']['maxDays']-1) . " Days" ) ;

					} else {

                        $startDate = new \DateTime( 'NOW ' . $settings['filter']['startDate'] . ' Days' , $DateTimeZone) ;
                        $endDate = new \DateTime( 'NOW +' . (intval ($settings['filter']['startDate'] )). ' Days' , $DateTimeZone) ;

                        $endDate->modify("+" . intval( $settings['filter']['maxDays'] -1) . " Days" ) ;

					}

					break;
			}
            $startDate->setTime(0,0,0) ;

            $endDate->setTime(23,59,59) ;

            // Now set  the Date values of the Event when it starts or ends
            $constraints[] = $query->greaterThanOrEqual('start_date', $startDate );

            if( intval( $settings['filter']['maxDays'] ) > 0 ) {
                $constraints[] = $query->lessThanOrEqual('start_date', $endDate );
            }



		}

        return $constraints;
	}

	/**
	 * @param array $constraints
	 * @param array $settings
	 * @param array $configuration
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 *
	 * @return array
	 */
    private function getPidContraints($constraints, $settings , $configuration , $query)
    {
		/** @var \TYPO3\CMS\Core\Database\QueryGenerator $queryGenerator */
		$queryGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( 'TYPO3\\CMS\\Core\\Database\\QueryGenerator' );
		$rGetTreeList = $queryGenerator->getTreeList( $settings['storagePid'],  $settings['recursive'], 0, 1); //Will be a string

        $pidList = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $rGetTreeList, true);
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
	 * @param array $configuration
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 *
	 * @return array
	 */
	private function getCatContraints($constraints, $settings , $configuration , $query)
	{
		$catList = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $settings['filter']['categories'], true);

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
     * @param array $configuration
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     *
     * @return array
     */
    private function getTagContraints($constraints, $settings , $configuration , $query)
    {
        $tagList = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $settings['filter']['tags'], true);
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
            $constraints[] = $query->logicalOr( $tagConstraints ) ;
        }

        return $constraints;
    }
}