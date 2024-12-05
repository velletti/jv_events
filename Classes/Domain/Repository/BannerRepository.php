<?php
namespace JVelletti\JvEvents\Domain\Repository;

use Doctrine\DBAL\Driver\Exception;
use PDO;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BannerRepository {


    public function getBannerByEventId(int $eventId) : array
    {

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
        try {
            $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sfbanners_domain_model_banner') ;
            $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

            $row = $queryBuilder ->select('uid' , 'title' , 'html' , 'description' , 'starttime', 'endtime', 'impressions','clicks'   ) ->from('tx_sfbanners_domain_model_banner')
                ->where( $queryBuilder->expr()->eq('link', $queryBuilder->createNamedParameter( $eventId , PDO::PARAM_INT)) )
                ->andWhere( $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0 , PDO::PARAM_INT)) )
                ->andWhere( $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0 , PDO::PARAM_INT)) )
                ->orderBy("endtime" , "DESC")->setMaxResults(1)->executeQuery()
                ->fetchAssociative();

            // var_dump( $queryBuilder->getSQL() );
        } catch (\Exception) {
            // ignore
            // var_dump($e->getMessage() );
        } catch (Exception) {
            //var_dump($e->getMessage());
            //ignore
        }

        return(isset($row) && is_array($row))  ? $row :  [] ;
    }

    public function updateBanner( array $row) {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);

        try {
            $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sfbanners_domain_model_banner') ;
            $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

           $queryBuilder->update('tx_sfbanners_domain_model_banner')
                ->where( $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter( $row['uid'] , PDO::PARAM_INT)) ) ;

           foreach ( $row as $field => $value ) {
               if( $field != "uid") {
                   if ( is_int( $value )) {
                       $queryBuilder->set($field , $value ,false ) ;
                   } else {
                       $queryBuilder->set($field , $value ) ;
                   }
               }
           }
            // var_dump( $queryBuilder->getSQL() );
            // var_dump( $queryBuilder->getParameters() );
            // die;
           $queryBuilder->executeStatement();
        } catch ( \Exception) {
            // ignore
            // var_dump($e->getMessage() );
        }
    }
}