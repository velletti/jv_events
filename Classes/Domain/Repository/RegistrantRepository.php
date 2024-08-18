<?php
namespace JVelletti\JvEvents\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
 * The repository for Registrants
 */
class RegistrantRepository extends BaseRepository
{
	/**
  * @var QuerySettingsInterface
  */
 protected $defaultQuerySettings = null ;

	protected $respectStoragePage = false;

	protected $respectSysLanguage = false;

	protected $languageOverlayMode = false;

	protected $languageMode = 'content_fallback' ;


	/**
	 * @param string $fingerprint
	 *
	 * @return array|bool|QueryResultInterface
	 */
	public function getByFingerprint($fingerprint = '') {
		if ($fingerprint == '' ) {
			return false ;
		}
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
        $query->getQuerySettings()->setLanguageOverlayMode(FALSE) ;
        $query->getQuerySettings()->setLanguageMode('content_fallback') ;

		// $query->getQuerySettings()->setIgnoreEnableFields(TRUE) ;

		return $query->matching( $query->equals("fingerprint", $fingerprint) )->execute() ;
	}

    /**
     * @param int $id
     *
     * @param bool $alsoHidden
     * @return mixed
     */
    public function getOneById($id, $alsoHidden = false) {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(FALSE);
        $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        if( $alsoHidden ) {
             $query->getQuerySettings()->setIgnoreEnableFields(TRUE) ;
        }

        return $query->matching( $query->equals("uid", $id) )->execute()->getFirst() ;
    }

    /**
     * @param int $uid
     *
     * @return mixed
     */
    public function FindOneByUid($uid ) {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(FALSE);
        $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        $query->getQuerySettings()->setIgnoreEnableFields(TRUE) ;

        return $query->matching( $query->equals("uid", $uid) )->execute()->getFirst() ;
    }

    /**
     * @param string $email
     * @param int $pid
     * @param array $settings
     * @return array
     */
    public function findEventsByFilter($email = '', $pid = 0 , $settings = [] ) {

        $additionalWhere = null;
        $query = $this->createQuery();
        // $query->getQuerySettings()->setReturnRawQueryResult(TRUE);
        $pageIds = GeneralUtility::trimExplode( "," , $settings['storagePids'], true)  ;

        if ( $pid > 0  || (is_countable($pageIds) ? count($pageIds) : 0) > 0 ) {


            if ( (is_countable($pageIds) ? count($pageIds) : 0) > 0 ) {
                $additionalWhere = ' AND find_in_set( pid , "' .  $settings['storagePids'] . '" ) '  ;
            } else {
                $additionalWhere = ' AND pid = ' . $pid  ;
            }
            if ( $settings['filter']['startDate'] < -20 ) {

                $additionalWhere .= ' AND tstamp > ' . mktime( 0 , 0 , 0 , date("m") , intval( date("d") + $settings['filter']['startDate'] ) , date("y") ) ;
            }
        } else {
            if ( $settings['filter']['startDate'] < -20 ) {

                $additionalWhere = ' AND tstamp > ' . mktime( 0 , 0 , 0 , date("m") , intval( date("d") + $settings['filter']['startDate'] ) , date("y") ) ;
            }
        }
       // echo $additionalWhere ;
       // die;
        $query->statement('SELECT event from tx_jvevents_domain_model_registrant where deleted = 0 ' . $additionalWhere . ' Group By event ');
        $regevents = $query->execute(true) ;
        // var_dump($regevents) ;
        $result = []  ;
       foreach ( $regevents as $next ) {
            $result[] = $next['event'] ;
        }
        return $result;
    }
    /**
     * @param string $email
     * @param int $eventID
     * @param int $pid
     * @param array $settings
     * @param int $limit
     * @return QueryResultInterface|array
     */
    public function findByFilter($email = '', $eventID = 0, $pid = 0 , $settings=[] , $limit=1 , array $orderings = ['uid' => QueryInterface::ORDER_DESCENDING] ) {
        $query = $this->createQuery();
        $constraints = [];
        if ( isset( $settings['storagePids'] )) {
            $pageIds = GeneralUtility::trimExplode( "," , $settings['storagePids'], true)  ;
        } else {
            $pageIds = [] ;
        }
        if ( (is_countable($pageIds) ? count($pageIds) : 0) > 0 ) {
            if($pid > 0 ) {
                $pageIds[] = intval( $pid);
            }
            $query->getQuerySettings()->setRespectStoragePage(TRUE);
            $query->getQuerySettings()->setStoragePageIds($pageIds) ;
        } else {
            $query->getQuerySettings()->setRespectStoragePage(FALSE);
            if($pid > 0 ) {
                $constraints[] = $query->equals("pid", $pid);
            }
        }


        $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        $query->getQuerySettings()->setIgnoreEnableFields(TRUE) ;
        $query->getQuerySettings()->setLanguageOverlayMode(FALSE) ;

        $query->getQuerySettings()->setLanguageMode('content_fallback') ;

        $query->setOrderings($orderings) ;


        // $constraints[] = $query->equals("cruser_id", 0);
        if($email <> '' ) {
            $constraints[] = $query->equals("email", $email);
        }

        if($eventID > 0 ) {
            $constraints[] = $query->equals("event", $eventID);
        }
        if ( $settings['filter']['startDate'] < -1 ) {
            $constraints[] = $query->greaterThan("crdate", time() + ( 60*60*24 ) * intval($settings['filter']['startDate'] ));
        }

        $query->setLimit($limit);
        if(count($constraints) > 0 ) {
            if (count($constraints) === 1) {
                $query->matching(reset($constraints));
            } elseif (count($constraints) >= 2) {
                $query->matching($query->logicalAnd(...$constraints));
            }
            $query->matching($query->logicalAnd(...$constraints));
        }
        // new way to debug typo3 db queries
        //  $this->debugQuery($query) ;
        $result = $query->execute();
        return $result;
    }
}