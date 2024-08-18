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
 * The repository for Categories
 */
class CategoryRepository extends BaseRepository
{

    /**
     * @var array
     */
    protected $defaultOrderings = ['sorting' => QueryInterface::ORDER_ASCENDING];

    /**
     * @param int $type default -1 means all Types
     *                  1 = events 2 locations 3 = Organizer
     *                  see TCA of event model
     * @return array|QueryResultInterface
     */
    public function findAllonAllPages($type= -1)
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
       // $querySettings->setRespectSysLanguage(FALSE);
        $query->setQuerySettings($querySettings) ;
        if( ($type > -1 )) {
            $query->matching( $query->equals('type', $type) ) ;
        }
        $res = $query->execute() ;

        return $res ;
    }



}