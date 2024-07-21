<?php
namespace JVelletti\JvEvents\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
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
 * The Base repository for event extension .
 */
class BaseRepository extends Repository
{
    /**
     * @param QueryInterface $query
     */
    public function showSql($query , $file , $line ) {
        $queryParser = GeneralUtility::makeInstance(Typo3DbQueryParser::class);

        $sqlquery = $queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL() ;
        echo "<html><body><h2>See File" . $file  . " Line :" . $line ." </h2><div>";
        echo $sqlquery ;
        echo "<hr>Values: <br>" ;
        $values = ($queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters()) ;
        echo "<pre>" ;
        echo var_export($values , true ) ;
        echo "</pre>" ;
        $from = [] ;
        $to = [] ;
        foreach (array_reverse( $values ) as $key => $value) {
            $from[] = ":" .$key ;
            $to[] = $value ;
        }
        $sqlFinalQuery = str_replace($from , $to , (string) $sqlquery ) ;
        echo "<hr>Final: <br>" ;
        echo str_replace( ["(", ")"]  , ["<br>(", ")<br>"] , $sqlFinalQuery ) ;
        echo "<br><hr><br></div></body></html>" ;
    }

    function debugQuery($query) {
        $search = [];
        $replace = [];
        // new way to debug typo3 db queries
        $queryParser = GeneralUtility::makeInstance(Typo3DbQueryParser::class);
        $querystr = $queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL() ;
        echo $querystr ;
        echo "<hr>" ;
        $queryParams = array_reverse ( $queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters()) ;
        var_dump($queryParams);
        echo "<hr>" ;

        foreach ($queryParams as $key => $value ) {
            $search[] = ":" . $key ;
            $replace[] = "'$value'" ;

        }
        echo str_replace( $search , $replace , (string) $querystr ) ;
        /** @var QueryResult $result */
        $result = $query->executeQuery() ;
        echo "<hr>Anzahl: " .  $result->count() ;

        die;
    }

    public function getTYPO3QuerySettings(): QuerySettingsInterface
    {
        return $this->createQuery()->getQuerySettings() ;
    }


    /**
     * Recursively fetch all descendants of a given page
     *
     * @param int $id uid of the page
     * @param int $depth
     * @param int $begin
     * @param string $permClause
     * @return string comma separated list of descendant pages
     */
    public function getTreeList($id, $depth, $begin = 0, $permClause = '')
    {
        $depth = (int)$depth;
        $begin = (int)$begin;
        $id = (int)$id;
        if ($id < 0) {
            $id = abs($id);
        }
        if ($begin == 0) {
            $theList = (string)$id;
        } else {
            $theList = '';
        }
        if ($id && $depth > 0) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
            $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $queryBuilder->select('uid')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($id, Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('sys_language_uid', 0)
                )
                ->orderBy('uid');
            if ($permClause !== '') {
                $queryBuilder->andWhere(QueryHelper::stripLogicalOperatorPrefix($permClause));
            }
            $statement = $queryBuilder->executeQuery();
            while ($row = $statement->fetchAssociative()) {
                if ($begin <= 0) {
                    $theList .= ',' . $row['uid'];
                }
                if ($depth > 1) {
                    $theSubList = $this->getTreeList($row['uid'], $depth - 1, $begin - 1, $permClause);
                    if (!empty($theList) && !empty($theSubList) && ($theSubList[0] !== ',')) {
                        $theList .= ',';
                    }
                    $theList .= $theSubList;
                }
            }
        }
        return $theList;
    }

}