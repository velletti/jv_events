<?php


namespace JVelletti\JvEvents\Utility;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
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
        $hasToBeUniqueInSite = in_array('uniqueInSite', $evalInfo, true);

        /** @var SlugHelper $slug */
        $slug = GeneralUtility::makeInstance(SlugHelper::class, $tableName, $fieldName, $fieldConfig);
        $proposal = $slug->generate($recordData, $recordData['pid']);
        $uniquePid = 0 ;
        if ($hasToBeUniqueInPid) {
            $uniquePid = $recordData['pid'] ;
        }
        $objectId = $recordData['uid'] ? $recordData['uid'] : 0 ;
        if (!self::isUnique( $proposal , $tableName , $fieldName , $uniquePid , $objectId, $recordData['sys_language_uid'])) {

            $counter = 0;
            while ( $counter++ < 100 ) {
                $newValue = $proposal. '-' . $counter ;
                if( self::isUnique( $newValue , $tableName , $fieldName , $uniquePid, $objectId , $recordData['sys_language_uid']) ) {
                    return  $newValue ;
                }
            }
            return  $proposal . '-' . ( $objectId ?? substr(md5(time()), 0, 8)) ;
        }
        return $proposal ;
    }

    /** remove time string from start_date (t00000z ) from slug
     * @param array $data
     * @return string
     */
    public static function modifySlug($data) : string
    {
        /*
        [
           'slug', // ...  the slug to be used
           'workspaceId', // ...  the workspace ID, "0" if in live workspace
           'configuration', // ...  the configuration of the TCA field
           'record', // ...  the full record to be used
           'pid', // ...  the resolved parent page ID
           'prefix', // ...  the prefix that was added
           'tableName', // ...  the table of the slug field
           'fieldName', // ...  the field name of the slug field
        ];
    */
         return  str_replace( "t000000z" , "" ,  ($data['slug'] ?? '' ) ) ;
    }
    /**
     * Checks if there are other records with the same slug that are located on the same PID or in db table if pid = 0
     *
     * @param string|null $slug
     * @param string $tableName
     * @param string $field
     * @param integer|null $pageId
     * @param integer|null $recordId
     * @param integer|null $languageId
     * @return bool
     */
     public static function isUnique(?string $slug, string $tableName , string $field , ?int $pageId , ?int $recordId , ?int $languageId): bool    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable($tableName);
        $userQuery = $queryBuilder->count( '*' )->from($tableName )
           ->where( $queryBuilder->expr()->neq('uid', $queryBuilder->createNamedParameter( $recordId , Connection::PARAM_INT )) ) ;

        $pageId = intval($pageId) ;
        if( $pageId > 0) {
             $userQuery = $queryBuilder->andWhere( $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter( $pageId , Connection::PARAM_INT )) ) ;
         }
         $languageId = intval($languageId) ;
         if( $languageId > -1 ) {
             $userQuery = $queryBuilder->andWhere( $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter( $languageId , Connection::PARAM_INT )) );
         }
         $slug = trim($slug) ;
         if( $slug ) {
             $userQuery = $queryBuilder->andWhere( $queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter( $slug , Connection::PARAM_STR )) ) ;
         }
        if ( $userQuery->executeQuery()->fetchFirstColumn()[0]  < 1 ) {
            return true ;
        }
        return false ;
    }


}