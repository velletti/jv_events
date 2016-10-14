<?php
namespace JVE\JvEvents\Hooks ;
/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2016 jörg velletti Typo3@velletti.de
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * ************************************************************* */

class ProcessDatamap {

	protected $webserviceErrors;
	protected $pObj;
	protected $table;
	protected $status;
	protected $id;
	protected $deleted;
	protected $fieldArray;

	/** @var  \JVE\JvEvents\Domain\Repository\EventRepository $this->objectManager */
	protected $objectManager ;

	/** @var  \JVE\JvEvents\Domain\Repository\EventRepository $this->eventRepository */
	protected $eventRepository ;

	/** @var  \JVE\JvEvents\Domain\Model\Event $this->event */
	protected $event;

	public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $pObj) {
		$this->pObj = $pObj;
		$this->table = $table;
		$this->status = $status;
		$this->fieldArray = $fieldArray;
		$this->id = (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($id)?$id:$this->pObj->substNEWwithIDs[$id]);
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['jv_events']);
		$this->main();
	}

	public function main() {

		if ($this->table == 'tx_jvevents_domain_model_event') { //do only things when we are in the  event table

			/** @var  \JVE\JvEvents\Domain\Repository\EventRepository $objectManager */
			$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Object\\ObjectManager") ;

			/** @var  \JVE\JvEvents\Domain\Repository\EventRepository $eventRepository */
			$this->eventRepository = $this->objectManager->get('JVE\\JvEvents\\Domain\\Repository\\EventRepository');

			/** @var  \JVE\JvEvents\Domain\Model\Event $event */
			$this->event = $this->eventRepository->findByUid(intval($this->id)) ;
			$allowedError = 0 ;

			if( is_object( $this->event ) ) {


				if ($allowedError >  0 ) {
					$this->event->setWithRegistration(FALSE ) ;
					$this->eventRepository->update($this->event) ;

				}
			}

			$this->showFlashMessage();
		}
	}

	//  ForSF +++++++++++++++++  Salesforce übergabe  ++++++++++++++++++

	private function parseDataArrayForSF($transferData){

		$timeZoneStart = ".000+01:00" ;
		$UnixStarttime = mktime( 10,10,0, substr( $transferData['start_date'],4,2) , substr( $transferData['start_date'],6,2) ,substr( $transferData['start_date'],0,4) ) ;

		if ( date("I" , $UnixStarttime) == "1" ) {
			$timeZoneStart = ".000+02:00" ;
		}
		$this->flashMessage['INFO'][] = $transferData['start_date'] . " UNIX: " . $UnixStarttime . " tZ: " . $timeZoneStart ;
		$timeZoneEnd = ".000+01:00" ;
		if ( date("I" , mktime( 10,10,0, substr( $transferData['end_date'],4,2) , substr( $transferData['end_date'],6,2) ,  substr( $transferData['end_date'],0,4) )) == "1" ) {
			$timeZoneEnd = ".000+02:00" ;
		}

		$dateObj = new tx_cal_date($transferData['start_date'].'000000');
		$dateObj->setTZbyId('UTC');
		$transferData['start_date'] = $dateObj->format("%Y-%m-%d");

		$dateObj = new tx_cal_date($transferData['end_date'].'000000');
		$dateObj->setTZbyId('UTC');
		$transferData['end_date'] = $dateObj->format("%Y-%m-%d");

		date_default_timezone_set('UTC');
		$transferData['start_time'] = date("H:i:s", $transferData['start_time'] );

		$transferData['end_time'] = date("H:i:s", $transferData['end_time']);

		// TODO : now create the SF Array ..
		$SFtransferData  = array() ;
		if ( $transferData['tx_nemcalwebservices_salesforce_eventid'] != '') {
			$SFtransferData['id'] = $transferData['tx_nemcalwebservices_salesforce_eventid'] ;
		}

		$salesForceContactId = $this->getSalesforceContactId($transferData['organizer_id']);
		if ($salesForceContactId !== FALSE) {
			$SFtransferData['OwnerId'] = $salesForceContactId;
		}


		$SFtransferData['TW_Start_Time__c'] =  $transferData['start_date'] . "T" . $transferData['start_time'] .$timeZoneStart;


		$SFtransferData['TW_End_Time__c']   =  $transferData['end_date'] . "T" . $transferData['end_time'] .$timeZoneEnd;


		// RecordTypeId has to be set, otherwise TW_WebinarKeyText will not be set in salesforce
		$SFtransferData['RecordTypeId'] = '01220000000cj8K';

		$SFtransferData['TW_TrainingWebinarName__c'] = str_replace( array("&amp;" , "&" ) , array( "+", "+" ) , $transferData['title'] );

		// 9.7. Store Typo3 Event UID to SF
		$SFtransferData['TW_UID__c'] = $transferData['uid'] ;

		//&Ampersand vorher entfernen sonst kommt eine fehlermeldung!!
		$transferData['description'] = str_replace( "&amp;" , "+" , $transferData['description'] );

		$SFtransferData['TW_Description__c'] =  html_entity_decode( strip_tags( $transferData['description']) , ENT_COMPAT , 'UTF-8');
		return $SFtransferData;
	}


	// 5.12.2012 ForSF +++++++++++++++++  Salesforce übergabe statt notes ++++++++++++++++++

	private function createUpdateEventForSF($transferData) {

		$this->webservice = $this->initWebserviceForSF();
		if(! $this->webservice ){
			$this->flashMessage['ERROR'][] = 'Init Webservice failed';
			$return['tx_nemcalwebservices_error'] = 1;
			return $return ;
		}

		$sObject= new stdclass();
		$sObject->type='TW_TrainingWebinar__c';


		$sObject->fields = $transferData ;
		$this->flashMessage['INFO'][] = 'SF Transfer Object: <pre>'.print_r($sObject, true) . '</pre>';
		try {
			if ( $transferData['id'] == '' ) {
				$response= $this->webservice->create( array($sObject));
				$sussessmess = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.successNew');

			} else {
				$response= $this->webservice->update( array($sObject), '');
				$sussessmess = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.successChange');
			}
		}
		catch (exception $e)
		{
		}
		$this->flashMessage['INFO'][] = 'SF Response: '.print_r($response, true);
		if(!is_array($response)){
			$this->flashMessage['INFO'][] = 'SF Connction Infos: '.print_r($this->webservice, true);

			$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.error');
			$this->flashMessage['ERROR'][] = '<b>'.$GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.errorprefix').'</b>';
			$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.errornoresponse');
			//   $this->flashMessage['ERROR'][] = '<br>Request: ' . htmlspecialchars($this->webservice->getRequest() , ENT_QUOTES);
			$this->flashMessage['ERROR'][] = '!is_array($response)';
			$return['tx_nemcalwebservices_error'] = 1;
		} else {
			if( is_object( $response[0] )){

				if( $response[0]->success ){
					$this->flashMessage['OK'][] = "OK! id: <a href=\"https://eu3.salesforce.com/" . $response[0]->id . "\" target=\"blank\">" . $response[0]->id  . "</a>";
					$return['SALESFORCEID'] = $response[0]->id ;
				} else {
					$this->flashMessage['INFO'][] = 'SF Connction Infos: '.print_r($this->webservice, true);

					$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.error');
					$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.errorprefix');

					if (is_array($response[0]->errors) && count($response[0]->errors > 0)) {
						foreach($response[0]->errors as $salesForceResponseError) {
							$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.' . $salesForceResponseError->statusCode);
							if ($salesForceResponseError->statusCode === 'ENTITY_IS_DELETED') {
								$this->removeSalesforceFieldsAfterDelete($this->record['uid']);
							}
						}
					}

					//        $this->flashMessage['ERROR'][] = $response['errormessage'];
					$return['tx_nemcalwebservices_error'] = 1;
					$this->flashMessage['ERROR'][] = '!is_object($response)';
				}
			} else {
				$this->flashMessage['INFO'][] = 'SF Connction Infos: '.print_r($this->webservice, true);

				$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.error');
				$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.errorprefix');
				//        $this->flashMessage['ERROR'][] = $response['errormessage'];
				$return['tx_nemcalwebservices_error'] = 1;
			}
		}
		return $return;
	}


	// 5.12.2012 ForSF +++++++++++++++++  Salesforce Create Session to Event ++++++++++++++++++

	private function createUpdateSessionForSF($transferData) {
		$this->webservice = $this->initWebserviceForSF();
		$sObject= new stdclass();
		$sObject->type='TW_Session__c';

		$sObject->fields = $transferData ;
		$this->flashMessage['INFO'][] = 'SF Session Transfer Object: <pre>'.print_r($sObject, true) . '</pre>';
		try {
			if ( $transferData['id'] == '' ) {
				$response= $this->webservice->create( array($sObject));
				$successmess = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.successSessionNew');

			} else {
				$response= $this->webservice->update( array($sObject), '');
				$successmess = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.successSessionChange');

			}
		}
		catch (exception $e)
		{

		}
		$this->flashMessage['INFO'][] = 'SF Session Response: '.print_r($response, true);
		if(!is_array($response)){
			$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.error');
			$this->flashMessage['ERROR'][] = '<b>'.$GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.errorprefix').'</b>';
			$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.errornoresponse');
			$this->flashMessage['ERROR'][] = '<br>Request: ' . htmlspecialchars($this->webservice->getRequest() , ENT_QUOTES);
			$this->flashMessage['ERROR'][] = '!is_array($response): '.print_r($response, true);
			$return['tx_nemcalwebservices_error'] = 1;
		} else {
			if( is_object( $response[0] )){

				if( $response[0]->success ){
					$this->flashMessage['OK'][] = "OK: " . $successmess  . " id: <a href=\"https://eu3.salesforce.com/" . $response[0]->id . "\" target=\"blank\">" . $response[0]->id  . "</a>";

					$return['SALESFORCESESSIONID'] = $response[0]->id ;
				} else {
					$this->flashMessage['INFO'][] = 'SF Connction Infos: '.print_r($this->webservice, true);

					$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.error');
					$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.errorprefix');
					//        $this->flashMessage['ERROR'][] = $response['errormessage'];
					$return['tx_nemcalwebservices_error'] = 1;
					$this->flashMessage['ERROR'][] = '!$response->success )';
				}
			} else {
				$this->flashMessage['INFO'][] = 'SF Connection Infos: '.print_r($this->webservice, true);

				$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.error');
				$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservicesSF.errorprefix');
				//        $this->flashMessage['ERROR'][] = $response['errormessage'];
				$this->flashMessage['ERROR'][] = '!is_object($response)';
				$return['tx_nemcalwebservices_error'] = 1;
			}
		}
		return $return;
	}

	private function initWebserviceForSF(){
		//	$this->flashMessage['INFO'][] = 'init SF webservice: ' . t3lib_extMgm::extPath('nem_customizations') .'lib/salesforce/SforcePartnerClient.php' ;
		// ToDO : change this to REST instead of Soap and use latest Rest Version instead of this old stuff

		// require_once(t3lib_extMgm::extPath('nem_customizations').'lib/salesforce/SforcePartnerClient.php');
		// require_once(t3lib_extMgm::extPath('nem_customizations').'lib/salesforce/SforceHeaderOptions.php');
		// toDo : move these settings to a global place !!
		//
		$USERNAME = $_SERVER['NEM_SALESFORCE']['live']['bn']  ;
		// $PASSWORD = "force12JV3ohTG6RcCERBbln62k4lezXt0" ;
		// cahnge on 27.5.2013
		$PASSWORD = $_SERVER['NEM_SALESFORCE']['live']['pw'] . $_SERVER['NEM_SALESFORCE']['live']['hash'] ;

		$WSDLFILE = t3lib_extMgm::extPath('nem_customizations').'lib/salesforce/jul13.live.partner.wsdl.xml' ;

		$this->flashMessage['INFO'][] = "Used WSDLFILE: " . $WSDLFILE;

		try {
			//connect to salesforce
			$client = new SforcePartnerClient();

			$client->createConnection($WSDLFILE);
			$loginResult = $client->login($USERNAME,$PASSWORD);
			$this->flashMessage['INFO'][] = 'config: username: '.$USERNAME  ; // . ' passwd: ' . $PASSWORD ;
			if (!$loginResult) {
				$this->flashMessage['INFO'][] = 'config: username: '.$USERNAME  ; // . ' passwd: ' . $PASSWORD ;
				$this->flashMessage['INFO'][] = 'config: wsdl: '. $WSDLFILE  ;
				$this->flashMessage['ERROR'][] = 'try to connect Error: '.print_r($loginResult, true);
				$client = FALSE ;
			}
		}
		catch (exception $e)
		{
			$this->flashMessage['INFO'][] = 'config: username: '.$USERNAME . ' passwd: ' . $PASSWORD ;
			$this->flashMessage['INFO'][] = 'config: wsdl: '. $WSDLFILE  ;
			$this->flashMessage['ERROR'][] = 'try to connect exception: '.print_r($e, true);
			return false;
			exit;
		}

		return $client ;
	}

	private function showFlashMessage(){
		if(is_array($this->flashMessage)){
			foreach($this->flashMessage as $type => $message){
				switch ($type) {
					case 'NOTICE':
						$type = -2;
						break;
					case 'INFO':
						$type = -1;
						break;
					case 'OK':
						$type = 0;
						break;
					case 'WARNING':
						$type = 1;
						break;
					case 'ERROR':
						$type = 2;
						break;
				}
				if(($type > -1 && $this->pObj->admin) || $type > -1)
				{
					// toDo ad FlashMessage
				}
			}
		}
	}

	/**
	 * if the salesforce event linked to this record is deleted ( in salesforce )
	 * we remove the event and session id so the event can be created again
	 *
	 * @param $uid
	 */
	private function removeSalesforceFieldsAfterDelete($uid) {
		// toDo : rework this with extbase
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table, 'uid=' . intval($uid), array('tx_nemcalwebservices_salesforce_eventid' => '', 'tx_nemcalwebservices_salesforce_sessionid' => ''));
	}

}

?>