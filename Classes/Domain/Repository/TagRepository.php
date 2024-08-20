<?php
namespace JVelletti\JvEvents\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
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
 * The repository for Tags
 */
class TagRepository extends BaseRepository
{

    /**
     * @var array
     */
    protected $defaultOrderings = ['name' => QueryInterface::ORDER_ASCENDING];

    /**
     * @param int $type default -1 means all Types
     *                  0 = events 1 locations 2 = Organizer
     *                  see TCA of event model
     *
     * @return array|QueryResultInterface
     */
    public function findAllonAllPages($type= -1 , $filter=[] )
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
     //   $querySettings->setRespectSysLanguage(false);
        if( ($type > -1 )) {
            if( isset( $filter['respectTagVisibility']) && $filter['respectTagVisibility'] == 1 ) {
                $query->matching(
                    $query->logicalAnd(...[$query->equals('type', $type), $query->equals('visibility', 0 )])
                ) ;
            } else {
                $query->matching(  $query->equals('type', $type)  ) ;
            }
        } elseif( isset( $filter['respectTagVisibility']) && $filter['respectTagVisibility'] == 1 ) {
            $query->matching(   $query->equals('visibility', 0 ) ) ;
        }

        // $querySettings->setRespectSysLanguage(FALSE);
        $query->setQuerySettings($querySettings) ;
        $res = $query->execute() ;

        return $res ;
    }

    /**
     * @return array|QueryResultInterface
     */
    public function findAllonAllPagesByUids( array $tagUids)
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        //   $querySettings->setRespectSysLanguage(false);
        if(  count($tagUids) > 0  )  {

            $query->matching(
                    $query->in('uid', $tagUids)  ,
            ) ;
        }
        // $querySettings->setRespectSysLanguage(FALSE);
        $query->setQuerySettings($querySettings) ;
        $res = $query->execute() ;
        // $this->debugQuery($query) ;

        return $res ;
    }





    
}