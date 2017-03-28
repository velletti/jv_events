<?php
namespace JVE\JvEvents\UserFunc;


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

		/**
		 * @var $db \TYPO3\CMS\Core\Database\DatabaseConnection
		 */
		$db = $GLOBALS['TYPO3_DB'];

		// $GLOBALS['TYPO3_DB']->debugOutput = true;
		// $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;

		// $result = $db->exec_SELECTgetSingleRow('name', 'tx_jvevents_domain_model_event', 'uid=' . intval($eventUid));
		$result = $db->exec_SELECTgetSingleRow("CONCAT (name, ' - ', DATE_FORMAT(FROM_UNIXTIME(start_date), '%d.%m.%Y')) AS `title`", 'tx_jvevents_domain_model_event', 'uid=' . intval($eventUid));

		// Categories
		$resultCategories = $db->exec_SELECTquery(
			'c.title',
			'tx_jvevents_event_category_mm mm LEFT JOIN tx_jvevents_domain_model_category c ON mm.uid_foreign = c.uid',
			'mm.uid_local = ' . intval($eventUid)
		);

		// echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;

		$categories = '';
		$categoriesArray = array();
		while($row = $db->sql_fetch_assoc($resultCategories)){
			$categoriesArray[] = $row['title'];
		}
		$categories = implode(', ', $categoriesArray);

		$title = $result['title'];
		if(!empty($categories)){
			$title.= ' (' . $categories . ')';
		}

		return $title;

	}

	/**
	 * Fetch the event description from DB
	 * @param $eventUid
	 * @return mixed
	 */
	private function getEventDescriptionFromDb($eventUid){

		/**
		 * @var $db \TYPO3\CMS\Core\Database\DatabaseConnection
		 */
		$db = $GLOBALS['TYPO3_DB'];

		$result = $db->exec_SELECTgetSingleRow('teaser', 'tx_jvevents_domain_model_event', 'uid=' . intval($eventUid));
		return $result['teaser'];

	}

}