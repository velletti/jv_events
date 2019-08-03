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
 * The Base repository for event extension .
 */
class BaseRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     */
    public function showSql($query , $file , $line ) {
        $queryParser = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);

        $sqlquery = $queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL() ;
        echo "<html><body><h2>See File" . $file  . " Line :" . $line ." </h2><div>";
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
    }

}