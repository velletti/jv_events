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
 * The repository for Subevents
 */
class SubeventRepository extends \JVE\JvEvents\Domain\Repository\BaseRepository
{

	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'start_date' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING ,
		'start_time' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
	);

    public function findByUidAllpages($uid , $toArray=false )
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        $query->matching( $query->equals('uid', $uid ) ) ;
        $query->setLimit( 1 ) ;

        if( $toArray === TRUE ) {
            return $query->execute()->toArray();
        } else {
            return $query->execute()->getFirst() ;
        }
    }

    /**
     * @param integer $event
     * @param bool $toArray
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByEventAllpages($event , $toArray=TRUE  )
    {
        $query = $this->createQuery();
    //    $query->getQuerySettings()->setRespectStoragePage(FALSE);
        $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        $query->matching( $query->equals('event', $event ) ) ;

        // enalbe Debug feature with next line
        // $this->showSql( $query , __FILE__ , __LINE__ ) ;

        if( $toArray === TRUE ) {
            return $query->execute()->toArray() ;
        } else {
            return $query->execute() ;
        }
    }

}