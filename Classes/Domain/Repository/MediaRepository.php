<?php
namespace JVelletti\JvEvents\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for Media
 */
class MediaRepository extends BaseRepository
{
    // Custom query methods can be added here

    /**
     * @var array
     */
    protected $defaultOrderings = ['release_date' => QueryInterface::ORDER_DESCENDING];

    public function findByUidAllpages($uid , $toArray=TRUE , $ignoreEnableFields = TRUE )
    {
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(FALSE);
        $querySettings->setIgnoreEnableFields($ignoreEnableFields) ;
        $query->setQuerySettings($querySettings) ;

        $query->setLimit(1) ;

        $query->matching( $query->equals('uid', $uid ) ) ;
        $res = $query->execute() ;

        if( $toArray === TRUE ) {
            return $res->toArray();
        } else {
            return $res->getFirst() ;
        }
    }


    public function findByFilterAllpages($filter=FALSE , $toArray=FALSE , $ignoreEnableFields = FALSE , $limit=FALSE, $lastModified = '-1 YEAR')
    {
        $constraintsOr = [];
        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(FALSE);
        $querySettings->setIgnoreEnableFields($ignoreEnableFields) ;
        $query->setQuerySettings($querySettings) ;

        $constraints = [] ;
        if ( $filter && (is_countable($filter) ? count($filter) : 0) > 0 ) {
            foreach ( $filter as $field => $value) {
                if ( $field == "lng" || $field == "lat") {
                    $constraints[] = $query->greaterThanOrEqual(  $field  ,  $value[0] ) ;
                    $constraints[] = $query->lessThanOrEqual(  $field  ,  $value[1] ) ;
                } else {
                    if( is_array( $value ) ) {
                        $constraints[] = $query->in($field ,  $value ) ;
                    } else {
                        $constraints[] = $query->equals($field ,  $value ) ;
                    }
                }
            }
        }

        if( $limit) {
            $query->setLimit(intval($limit));
        }

        if ( $ignoreEnableFields ) {
            $constraints[] =  $query->equals('deleted',  0 )  ;
            $constraintsOr = [];
            $constraintsOr[] = $query->equals('organizer.hidden', 0 ) ;
            $constraintsOr[] = $query->equals('organizer.uid', null );
            $constraints[] = $query->logicalOr(...$constraintsOr ) ;
        }
        if (count($constraints) === 1) {
            $query->matching(reset($constraints));
        } elseif (count($constraints) >= 2) {
            $query->matching($query->logicalAnd(...$constraints));
        }
        $query->matching( $query->logicalAnd(...$constraints)) ;
        $res = $query->execute() ;

        // new way to debug typo3 db queries
       // $this->debugQuery( $query) ;
        if( $toArray === TRUE ) {
            return $res->toArray();
        } else {
            return $res ;
        }
    }
}