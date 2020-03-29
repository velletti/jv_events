<?php
namespace JVE\JvEvents\UserFunc;


use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageProperties{

	/**
	 * Reference to the parent (calling) cObject set from TypoScript
	 */
	public $cObj;

	/**
	 * Get the title from tx_jvevents_domain_model_event
	 * @param $content
	 * @param $conf
	 *
	 * @return string
	 */
	public function getEventTitle($content, $conf) {

		/**
		 * @var $db \TYPO3\CMS\Core\Database\DatabaseConnection
		 */
		$db = $GLOBALS['TYPO3_DB'];

		$parameters = $this->getParameters();


		// Case 1: Default language
		if($parameters['sysLanguageUid'] === 0){

			return $this->getEventTitleFromDb($parameters['eventUid']);

		}

		// Case 2: Not default language and l10n_parent > 0
		if($parameters['sysLanguageUid'] > 0){

			// Check if this news is translated from a parent news
			$result = $db->exec_SELECTgetSingleRow('uid', 'tx_jvevents_domain_model_event', 'l10n_parent=' . $parameters['eventUid']);

			// Event is translated from parent page => take the title from the result
			if(!empty($result)){

				return $this->getEventTitleFromDb($result['uid']);

			// Event is not translated from parent page => take the title from this data record
			}else{

				return $this->getEventTitleFromDb($parameters['eventUid']);

			}

		}

		// Fallback, never reached
		return 'Event';

	}

	/**
	 * Get the description (teaser) from tx_jvevents_domain_model_event
	 * @param $content
	 * @param $conf
	 *
	 * @return string
	 */
	public function getEventDescription($content, $conf) {

		/**
		 * @var $db \TYPO3\CMS\Core\Database\DatabaseConnection
		 */
		$db = $GLOBALS['TYPO3_DB'];

		$parameters = $this->getParameters();

		$eventDescription = '';

		// Case 1: Default language
		if($parameters['sysLanguageUid'] === 0){
			$eventDescription = $this->getEventDescriptionFromDb($parameters['eventUid']);
		}

		// Case 2: Not default language and l10n_parent > 0
		if($parameters['sysLanguageUid'] > 0){

			// Check if this news is translated from a parent news
			$result = $db->exec_SELECTgetSingleRow('uid', 'tx_jvevents_domain_model_event', 'l10n_parent=' . $parameters['eventUid']);

			// Event is translated from parent page => take the title from the result
			if(!empty($result)){

				$eventDescription = $this->getEventDescriptionFromDb($result['uid']);

				// Event is not translated from parent page => take the title from this data record
			}else{

				$eventDescription = $this->getEventDescriptionFromDb($parameters['eventUid']);

			}

		}

		if(empty($eventDescription)){
			$eventDescription = $conf['defaultDescription'];
		}

		return $eventDescription;

	}



	/**
	 * Get the parameters given by URL
	 * @return array
	 */
	private function getParameters() {

		/**
		 * @var $db \TYPO3\CMS\Core\Database\DatabaseConnection
		 */
		$db = $GLOBALS['TYPO3_DB'];

		// Get the parameters
		$sysLanguageUid = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('L'));

		$tx_jvevents_events = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_jvevents_events');
		$eventUid = intval($tx_jvevents_events['event']);

		unset($tx_news_pi1, $tx_jvevents_events);

		return [
			'sysLanguageUid' => $sysLanguageUid,
			'eventUid' => $eventUid
		];

	}


	/**
	 * Fetch the event title from DB
	 * @param $eventUid
	 * @return mixed
	 */
	private function getEventTitleFromDb($eventUid){


        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_event')->createQueryBuilder();
        $queryBuilder->select('name','start_date') ->from('tx_jvevents_domain_model_event') ;
        $expr = $queryBuilder->expr();
        $queryBuilder->where( $expr->eq('uid', $queryBuilder->createNamedParameter(intval($eventUid), Connection::PARAM_INT)) ) ;
        $result  = $queryBuilder->execute()->fetch();

        if( $result) {
            return $result['title'] . " - " . date( "d.m.Y", $result['start_date']);
        } else {
            return "No Event with  ID: " . $eventUid ;
        }


	}

	/**
	 * Fetch the event description from DB
	 * @param $eventUid
	 * @return mixed
	 */
	private function getEventDescriptionFromDb($eventUid){

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_event')->createQueryBuilder();
        $queryBuilder->select('teaser') ->from('tx_jvevents_domain_model_event') ;
        $expr = $queryBuilder->expr();
        $queryBuilder->where( $expr->eq('uid', $queryBuilder->createNamedParameter(intval($eventUid), Connection::PARAM_INT)) ) ;
        $result  = $queryBuilder->execute()->fetch();

        if( $result) {
            return $result['teaser'] ;
        } else {
            return "No Event with  ID: " . $eventUid ;
        }

	}

}