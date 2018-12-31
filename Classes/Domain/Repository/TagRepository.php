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
 * The repository for Tags
 */
class TagRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllonAllPages()
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        // $querySettings->setRespectSysLanguage(FALSE);
        $query->setQuerySettings($querySettings) ;
        $res = $query->execute() ;

        // new way to debug typo3 db queries
        // $queryParser = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);
        // var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL());
        // var_dump($queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters()) ;
        // die;

        return $res ;
    }

    
}