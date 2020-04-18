<?php


namespace JVE\JvEvents\Utility;


use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\Model\RecordState;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SlugUtility {

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param array $recordData
     * @return string
     */
    static function getSlug($tableName , $fieldName , $recordData   ) {
        $fieldConfig = $GLOBALS['TCA'][$tableName]['columns'][$fieldName]['config'] ?? [];

        if (empty($fieldConfig)) {
            throw new \RuntimeException(
                'No valid field configuration for table ' . $tableName . ' field name ' . $fieldName . ' found.',
                1535379534
            );
        }
        $evalInfo = !empty($fieldConfig['eval']) ? GeneralUtility::trimExplode(',', $fieldConfig['eval'], true) : [];
        $hasToBeUniqueInPid = in_array('uniqueInPid', $evalInfo, true);

        /** @var SlugHelper $slug */
        $slug = GeneralUtility::makeInstance(SlugHelper::class, $tableName, $fieldName, $fieldConfig);
        $proposal = $slug->generate($recordData, $recordData['pid']);

        if ($hasToBeUniqueInPid && ! self::isUniqueInPid( $proposal , $tableName , $fieldName , $recordData['pid'] , $recordData['uid'] , $recordData['sys_language_uid'])) {

            $counter = 0;
            while ( $counter++ < 100 ) {
                $newValue = $proposal. '-' . $counter ;
                if( self::isUniqueInPid( $newValue , $tableName , $fieldName , $recordData['pid'] , $recordData['uid'] , $recordData['sys_language_uid']) ) {
                    return  $newValue ;
                }
            }
            return  $proposal . '-' . GeneralUtility::shortMD5($proposal) ;
        }
        return $proposal ;
    }

    /**
     * Checks if there are other records with the same slug that are located on the same PID.
     *
     * @param string $slug
     * @param string $tableName
     * @param string $field
     * @param integer $pageId
     * @param integer $recordId
     * @param integer $languageId
     * @return bool
     */
     public static function isUniqueInPid(string $slug, $tableName , $field , $pageId , $recordId , $languageId ): bool    {
        /** @var \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable($tableName);
        $userQuery = $queryBuilder->count( '*' )->from($tableName )
            ->where( $queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter( $slug , \TYPO3\CMS\Core\Database\Connection::PARAM_STR )) )
            ->andWhere( $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter( $pageId , \TYPO3\CMS\Core\Database\Connection::PARAM_INT )) )
            ->andWhere( $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter( $languageId , \TYPO3\CMS\Core\Database\Connection::PARAM_INT )) )
            ->andWhere( $queryBuilder->expr()->neq('uid', $queryBuilder->createNamedParameter( $recordId , \TYPO3\CMS\Core\Database\Connection::PARAM_INT )) )
        ;
/*
         $querystr = $userQuery->getSQL() ;
         $queryParams = array_reverse ( $userQuery->getParameters()) ;
         foreach ($queryParams as $key => $value ) {
             $search[] = ":" . $key ;
             $replace[] = "'$value'" ;
         }
         echo str_replace( $search , $replace , $querystr ) ;
die;
*/
        if ( $userQuery->execute()->fetchColumn(0)  < 1 ) {
            return true ;
        }
        return false ;
    }


}