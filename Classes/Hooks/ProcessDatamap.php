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

	/** @var  array */
	protected $flashMessage ;

    /** @var  \JVE\JvEvents\Classes\Utility\SalesforceWrapper */
    public $sfConnect ;


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
            $this->flashMessage['OK'][] = 'Hook ProcessDatamap is active ..' ;
			if( is_object( $this->event ) ) {

                if ($this->event->getNotifyRegistrant() ==  0 && $this->event->getNeedToConfirm() == 1 ) {
                    $this->event->setNeedToConfirm( 0 ) ;
                    $this->flashMessage['WARNING'][] = 'You can not set Need to confirm without sending Email to the participant !' ;
                    $allowedError ++ ;
                }

				if ($allowedError >  0 ) {
					// $this->event->setWithRegistration(FALSE ) ;


                } else {
                    // j.v. : Kopiert von altem Plugin: wenn Store in Citrix "An" Ist, das Event NICHT als "TW Webinar /Event " nach Salesforce Schreiben !
                    if( $this->event->getStoreInSalesForce() ) {
                        if( $this->event->getStoreInCitrix() ) {
                            $this->flashMessage['WARNING'][] = 'You can not set "Store in Salesforce" together with "Store in Citrix"! (store in salesforce is disabled)!' ;
                            $this->event->setStoreInSalesForce(0) ;
                        } else {
                            $this->createUpdateEventForSF()  ;
                        }
                    }

                }
                $this->eventRepository->update($this->event) ;

                /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
                $persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
                $persistenceManager->persistAll() ;
			}

			$this->showFlashMessage();
		}
	}

	//  ForSF +++++++++++++++++  Salesforce übergabe  ++++++++++++++++++


	private function createUpdateEventForSF() {
        /** @var  \JVE\JvEvents\Classes\Utility\SalesforceWrapper */
        $this->sfConnect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JVE\\JvEvents\\Classes\\Utility\\SalesforceWrapper');

        // With next Line you can force to write to a specific Salesforce system "PROD" / "STAGE" or "DEV"
        // $this->sfConnect->forceEnv = "PROD" ;
        $settings = $this->sfConnect->contactSF() ;
        // var_dump($settings) ;

        if( $settings['faultstring'] ) {
            $this->flashMessage['ERROR'][] = 'Store in Salesforce: could not Connect ! : ' . var_export( $settings , true ) ;
            return ;
        }

        /// ++++++++++++  first we genereate the data Array
        $start = $this->event->getStartDate() ;
        $end = $this->event->getStartDate() ;
        $startD =  $this->sfConnect->convertDate2SFdate( $start ,  $this->event->getStartTime() ) ;

        $endD =  $this->sfConnect->convertDate2SFdate( $end ,  $this->event->getEndTime() ) ;
        $this->flashMessage['NOTICE'][] = 'StartDate Converted for SalesForce to ! : ' . $startD ;


        $SummerTime = new \DateTime( $start->format("Y-M-d" ) . ' Europe/Berlin');
        $this->flashMessage['NOTICE'][] = "Date Formated : " . $start->format("d-M-Y" ) . ' as NEW Date for SummerTime ! : ' . $SummerTime->format( "d.m.Y H:i:s (I) ") ;
        $data = array(
            'TW_TrainingWebinarName__c' => html_entity_decode( strip_tags(  $this->event->getName() ) , ENT_COMPAT , 'UTF-8') ,

            // RecordTypeId has to be set, otherwise TW_WebinarKeyText will not be set in salesforce
            'RecordTypeId' => '01220000000cj8K' ,

            // Store Typo3 Event UID to SF
            'TW_UID__c' => $this->event->getUid()  ,
            'TW_Start_Time__c' =>  $startD   ,
            'TW_End_Time__c' =>  $endD ,

            'TW_Description__c' =>  html_entity_decode( strip_tags( $this->event->getDescription() ) , ENT_COMPAT , 'UTF-8') ,
            // Schulungsanmeldungen wien

        ) ;
        if( trim( $this->event->getMarketingProcessId()) != '' ) {
            $data['Marketing_Process__c'] = trim( $this->event->getMarketingProcessId() ) ;
        }

        if(  is_object( $this->event->getOrganizer() ) ) {
            $data['OwnerId']  =    trim( $this->event->getOrganizer()->getSalesForceUserId())  ;
        } else {
            $this->flashMessage['ERROR'][] = 'Store in Salesforce: No Organizer set in Relations ! : '  ;
        }
        if( $data['OwnerId']  == "" ) {
            $this->flashMessage['ERROR'][] = 'Store in Salesforce: No Salesforce User ID set in Organizer Data ! : '  ;
            return ;

        }
        $this->flashMessage['NOTICE'][] = 'Data  : ' . var_export( $data , true ) ;
        // ++++++++++++++++++   now update or create the Webinar

        if( $this->event->getSalesForceEventId() ) {
            // Update
            $url = $settings['SFREST']['instance_url'] . "/services/data/v20.0/sobjects/TW_TrainingWebinar__c/" . $this->event->getSalesForceEventId() ;
            $sfResponse = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "204" ,  $data , true  , false )  ;
            if( is_int( $sfResponse ) && $sfResponse == 204 ) {
                $this->flashMessage['OK'][] = "Updated in Salesforce: ! : " . $settings['SFREST']['instance_url'] . "/" . $this->event->getSalesForceEventId()   ;
                $this->createUpdateSessionInSF($settings , $this->event->getSalesForceEventId() , $data ) ;
            } else {

                $this->flashMessage['WARNING'][] = 'Response  : ' . var_export( $sfResponse , true ) ;
                $sfResponse = json_decode($sfResponse) ;
            }

        } else {
            // Insert
            $url = $settings['SFREST']['instance_url'] . "/services/data/v20.0/sobjects/TW_TrainingWebinar__c/" ;
            $sfResponse = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "" ,  $data , false , false )  ;
            $sfResponse = json_decode($sfResponse) ;

            if( is_object($sfResponse)) {
                if ($sfResponse->success && $sfResponse->id) {
                    $this->flashMessage['OK'][] = "Inserted in Salesforce: ! : " . $settings['SFREST']['instance_url'] . "/" . $sfResponse->id   ;

                    $this->event->setSalesForceEventId($sfResponse->id);
                    $this->createUpdateSessionInSF($settings , $sfResponse->id , $data  ) ;
                }
            }
        }

        if( is_array( $sfResponse)) {
            if( is_object( $sfResponse[0])) {
                if( $sfResponse[0]->errorCode == "MALFORMED_ID") {
                    $this->flashMessage['ERROR'][] = 'Store in Salesforce: MALFORMED_ID ! Please check the ID of the Field : Fields: ' .var_export( $sfResponse[0]->fields , true )  ;
                } else {
                    if( $sfResponse[0]->errorCode == "FIELD_INTEGRITY_EXCEPTION") {
                        $this->flashMessage['ERROR'][] = 'Store in Salesforce: ' . $sfResponse[0]->errorCode  . " - Please check the Salesforce ID of the Organizer and the Event -  " . $sfResponse[0]->message ;
                    } else {
                        $this->flashMessage['ERROR'][] = 'Store in Salesforce: ' . $sfResponse[0]->errorCode  . " - " . $sfResponse[0]->message ;
                    }
                }
            }
        }
	}

    public function createUpdateSessionInSF($settings , $id , $data ) {

        $sessionData = array(
            'TW_TrainingWebinar__c' => $id  ,
            'TW_Start_Time__c' => $data['TW_Start_Time__c'] ,
            'TW_End_Time__c' => $data['TW_End_Time__c'] ,
        ) ;

        // Now Insert or Update Session

        if( $this->event->getSalesForceSessionId() ) {
            // Update
            $url = $settings['SFREST']['instance_url'] . "/services/data/v20.0/sobjects/TW_Session__c/" . $this->event->getSalesForceSessionId() ;
            $sfSessionResponse = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "" ,  $sessionData , true  , false )  ;

        } else {
            // Insert
            $url = $settings['SFREST']['instance_url'] . "/services/data/v20.0/sobjects/TW_Session__c/" ;
            $sfSessionResponse = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "" ,  $sessionData , false , false )  ;
            $sfSessionResponse = json_decode($sfSessionResponse) ;
            if( is_object($sfSessionResponse)) {
                if ($sfSessionResponse->success && $sfSessionResponse->id) {
                    $this->event->setSalesForceSessionId($sfSessionResponse->id);

                }
            }

        }
        $this->flashMessage['NOTICE'][] = var_export( $sfSessionResponse , true ) ;


    }



	private function showFlashMessage(){
		if(is_array($this->flashMessage)){
			foreach($this->flashMessage as $type => $messageArray){
				switch ($type) {
					case 'NOTICE':
						$typeInt = \TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE;
						break;
					case 'INFO':
                        $typeInt = \TYPO3\CMS\Core\Messaging\FlashMessage::INFO;
						break;
					case 'OK':
                        $typeInt = \TYPO3\CMS\Core\Messaging\FlashMessage::OK;
						break;
					case 'WARNING':
                        $typeInt = \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING;
						break;
					case 'ERROR':
                        $typeInt = \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR;
						break;
				}
				if(($typeInt > -1 && $this->pObj->admin) || $typeInt > -1)
				{
				    foreach ( $messageArray as $messageText ) {
                        $tempText = ( is_string( $messageText )) ? $messageText : var_export( $messageText , true )  ;
                        $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                            $tempText ,
                            $type , // [optional] the header
                            $typeInt , // [optional] the severity defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
                            true // [optional] whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is false)
                        );
                        $flashMessageService = $this->objectManager->get(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
                        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                        $messageQueue->addMessage($message);
                    }

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