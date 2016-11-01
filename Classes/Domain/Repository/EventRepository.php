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
		'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING ,
		'start_time' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
	);



	/**
     * @param $filter
     * @param $limit
     * @param $pidList
     * @return array
     */
    public function findByFilter($filter = false, $limit = false, $settings )
    {
		$configuration = \JVE\JvEvents\Utility\EmConfiguration::getEmConf();

        $query = $this->createQuery();
		$query->setOrderings($this->defaultOrderings);


        $constraints = array();
		$query->getQuerySettings()->setRespectStoragePage(false);
		if( $settings['filter']['skipEvent'] > 0 ) {
			$constraints[] = $query->logicalNot( $query->equals("uid" , $settings['filter']['skipEvent'])) ;
			
		}
		if( $settings['storagePid'] > 0 ) {
			$constraints = $this->getPidContraints($constraints,  $settings , $configuration , $query );
		}
		if( $settings['filter']['categories']  ) {

			$constraints = $this->getCatContraints($constraints,  $settings , $configuration , $query );
		}
		if ( $configuration['recurring'] == 1 ) {
			// do some magic for recurring events
		}
		if( intval( $settings['filter']['startDate'] ) > -9999  || intval( $settings['filter']['maxDays'] ) > 0  )  {
			$constraints = $this->getDateContraints($constraints,  $settings , $configuration , $query );
		}


        if (count($constraints) >  0) {
            $query->matching($query->logicalAnd($constraints));
        }

		if( intval( $settings['filter']['maxEvents'] ) > 0 )  {
			$query->setLimit( intval( $settings['filter']['maxEvents'])) ;
		}

        $result = $query->execute();
		// $settings['debug'] = 2 ;
		if ($settings['debug'] == 2 ) {
            $GLOBALS['TYPO3_DB']->debugOutput = 2;
            $GLOBALS['TYPO3_DB']->explainOutput = true;
            $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
            $result->toArray();
            die;
        }
        return $result;
    }


	/**
	 * @param array $constraints
	 * @param array $settings
	 * @param array $configuration
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 *
	 * @return array
	 */
	private function getDateContraints($constraints, $settings , $configuration , $query)
	{
		if( intval( $settings['filter']['startDate'] ) > -9999 ) {
			/** @var \DateTime $startDate */
			switch ($settings['filter']['startDate']) {
				case '0':
					$startDate = new \DateTime( 'NOW') ;
					$endDate = new \DateTime( 'NOW +' . $settings['filter']['maxDays'] . ' Days') ;
					break;
				case '-1':
					$startDate = new \DateTime( 'NOW -1 Days') ;
					$endDate = new \DateTime( 'NOW +' . (intval ($settings['filter']['maxDays']) -1 ). ' Days') ;
					break;
				case '+1':
					$startDate = new \DateTime( 'NOW +1 Days') ;
					$endDate = new \DateTime( 'NOW +' . (intval ($settings['filter']['maxDays']) +1 ). ' Days') ;
					break;
				default:
					if( intval( $settings['filter']['startDate'] ) > 9999 ) {
						$startDate = new \DateTime( ) ;
						$startDate->setTimestamp($settings['filter']['startDate']) ;
						$endDate = new \DateTime(  ) ;
						$endDate->setTimestamp($settings['filter']['startDate']) ;
						$endDate->modify("+" . intval( $settings['filter']['maxDays']) . " Days" ) ;

					} else {

						$startDate = new \DateTime( 'NOW ' . $settings['filter']['startDate'] . ' Days') ;
						$endDate = new \DateTime( 'NOW +' . (intval ($settings['filter']['maxDays']) + $settings['filter']['startDate'] ). ' Days') ;

						$endDate->modify("+" . intval( $settings['filter']['maxDays']) . " Days" ) ;

					}

					break;
			}

			$startDate->setTime(0,0,0) ;
			$endDate->setTime(0,0,0) ;
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
		if( in_array("_ALL" , $catList) ) {
			return $constraints ;
		}

		if (count($catList) == 1) {
			$constraints[] = $query->equals('eventCategory.uid', $catList[0]);

		} else {
			$constraints[] = $query->in('eventCategory.uid', $catList);

		}

		return $constraints;
	}
}