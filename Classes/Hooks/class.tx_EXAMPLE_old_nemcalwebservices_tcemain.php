<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Marco Huber <typo3-extension@marit.ag>
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

class tx_nemcalwebservices_tcemain {
  
  protected $webserviceErrors;
  protected $pObj;
  protected $table;
  protected $id;
  protected $deleted;
  protected $fieldArray;

  
  public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $pObj) {
    $this->pObj = $pObj;
    $this->table = $table;
    $this->id = (TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($id)?$id:$this->pObj->substNEWwithIDs[$id]);
    $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nem_calwebservices']); 
    
//    $this->flashMessage['INFO'][] = 'Hook: processDatamap_afterDatabaseOperations';
//	$this->flashMessage['INFO'][] = 'Webservice to use: '.  $this->extConf['webservice_to_use']  ;	     							
    $this->main();		
	} 

  public function processCmdmap_preProcess($command, $table, $id, $value, $pObj) {
    if($command == 'delete'){
      $this->pObj = $pObj;
      $this->table = $table;
      $this->id = $id;	    
      $this->deleted = 1;	    
      $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nem_calwebservices']); 
      
//      $this->flashMessage['INFO'][] = 'Hook: processCmdmap_preProcess';
//	  $this->flashMessage['INFO'][] = 'Webservice to use: '.  $this->extConf['webservice_to_use']  ;	 
//      $this->main();
    }
	  if ($this->table == 'tx_cal_event') {
		  if( $this->pObj->admin ) {
			  $this->flashMessage['INFO'][] = 'Hook: processCmdmap_preProcess - Command: ' . $command . " - Table:" . $table . " - ID: " . $id ;
			  $this->showFlashMessage();
		    }
	  }

  }
  
  public function main() {
    if ($this->table == 'tx_cal_event') { //do only things when we are in the cal event table                     
		 $this->record = TYPO3\CMS\Backend\Utility\BackendUtility::getRecord($this->table, intval($this->id));
		// repair this strange "D" effect
		if (trim($this->record['tx_nemcalwebservices_salesforce_sessionid']) == "D") {
			 $this->record['tx_nemcalwebservices_salesforce_sessionid'] = "" ;
		 }
		if (trim($this->record['tx_nemcalwebservices_salesforce_eventid']) == "D") {
			$this->record['tx_nemcalwebservices_salesforce_eventid'] = "" ;
		}
		// from now on ONLY Store in Citrix OR in Salesforce/site .. CITRIX is Leading
		if ( $this->record['tx_nemcalwebservices_storeincitrix'] == 0  ) {
			$this->record['tx_nemcalwebservices_storeinsite'] = 1 ;
		} else {
			$this->record['tx_nemcalwebservices_storeinsite'] = 0 ;

		}


			if(intval($this->record['l18n_parent'])==0){ //do only things, if the record is not the translation of another record
  		 //   $this->flashMessage['INFO'][] = 'Record: '.print_r($this->record, true);	    

          	if( $this->record['tx_nemcalwebservices_salesforce_sessionid'] || $this->record['tx_nemcalwebservices_registration'] || $this->record['tx_nemcalwebservices_storeincitrix'] == 1){
            	if(
            	// allow to save registrationform but 0 Seats to show messages: no more seats available!
            	// intval($this->record['tx_nemcalwebservices_seats']) > 0 &&
            	strpos($this->record['tx_nemcalwebservices_formfields'], 'email')>-1 && strpos($this->record['tx_nemcalwebservices_formfieldsrequired'], 'email')>-1 
            	&& strpos($this->record['tx_nemcalwebservices_formfields'], 'privacy')>-1 && strpos($this->record['tx_nemcalwebservices_formfieldsrequired'], 'privacy')>-1 
            	// && strlen($this->record['tx_nemcalwebservices_email_subject'])>0 && strlen($this->record['tx_nemcalwebservices_email_text'])>0
				&& (strlen($this->record['tx_nemcalwebservices_citrixuid'])>0 || $this->record['tx_nemcalwebservices_storeincitrix'] == 0 ))
				{
    		  	if ( intval($this->record['tx_nemcalwebservices_seats']) < 1 ) {
    		  		 $this->flashMessage['ERROR'][] =  $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.tx_nemcalwebservices_seats')  
    		  		 . " = " .  intval($this->record['tx_nemcalwebservices_seats'] ) . " !! ";
    		  	}

				// check if Required Fields are all also in the List of avaiable Fields.. !
				// if one is missing or misspelled, registration will fail with NO error message!
				$required    = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', 	$this->record['tx_nemcalwebservices_formfieldsrequired'] ) ;
				$existFields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', 	$this->record['tx_nemcalwebservices_formfields'] ) ;
				$requiredError = '' ;
				if( is_array($required))	 {
					if ( !is_array($existFields)) {
						$existFields = array() ;
					}
					foreach($required as $fieldReq) {
						if ( !in_array($fieldReq , $existFields)) {
							$requiredError .=  $fieldReq . ", " ;
						}
					}
				}
				if ( $requiredError <> '' ) {
					//
					$this->flashMessage['ERROR'][] = "Registration is NOT possible: Missing, or misspelled Field(s) in required field list: " .  $requiredError ;
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,' uid = '.intval($this->record['uid']).' OR l18n_parent = '.intval($this->record['uid']),array('tx_nemcalwebservices_registration'=>0));

				}

				// now check if existing Fields are in List of allowed fields
				$allowedError = '' ;
				$allowedFields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',',
						'username, firstname, lastname, title, gender, department, companyname, companysize, streetaddress, zip, city, country, phonenumber, email, additionalinformation, privacy, newsletter, customerid, profession, recall, contactid, usedsoftware') ;


				if( is_array($existFields))	 {

					foreach($existFields as $field) {
						if ( !in_array($field , $allowedFields)) {
							$allowedError .=  $field . ", " ;
						}
					}
				}
				if ( $allowedError <> '' ) {
					//
					$this->flashMessage['ERROR'][] = "Registration is NOT possible: Missing, or misspelled Field(s) in List of inputfields: " .  $allowedError ;
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,' uid = '.intval($this->record['uid']).' OR l18n_parent = '.intval($this->record['uid']),array('tx_nemcalwebservices_registration'=>0));

				}

    		      
//				if ( strtolower ( $this->extConf['webservice_to_use']) == "salesforce" && $this->record['tx_nemcalwebservices_storeinsite'] == 1 ) {
				if ( $this->record['tx_nemcalwebservices_storeincitrix'] == 0  && $this->record['tx_nemcalwebservices_storeinsite'] == 1 ) {
					
					if ( $this->record['tx_nemcalwebservices_childof_eventid'] > 0 ) {
						
						$transferTemp = $this->parseDataArrayForSF($this->record);    
						
						// todo Get tx_nemcalwebservices_salesforce_eventid from ParentId
						$Parentrecord = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord($this->table, intval($this->record['tx_nemcalwebservices_childof_eventid']));
						$this->flashMessage['INFO'][] = "Parent Event uid: " . $this->record['tx_nemcalwebservices_childof_eventid'] . " - SF Webinar ID: " . $Parentrecord['tx_nemcalwebservices_salesforce_eventid'];
						
						// $this->flashMessage['INFO'][] = 'Parent Record: '.print_r($Parentrecord2, true);
						$webserviceResponse = array() ; 
						if ( $Parentrecord['tx_nemcalwebservices_salesforce_eventid'] <> '' ) {
							$transfertemp['tx_nemcalwebservices_salesforce_eventid'] = $Parentrecord['tx_nemcalwebservices_salesforce_eventid'] ;
							$transferSessiontemp['TW_TrainingWebinar__c'] = $Parentrecord['tx_nemcalwebservices_salesforce_eventid'] ;
							$webserviceResponse['tx_nemcalwebservices_error'] = 0 ; 
						} else {
							$webserviceResponse['tx_nemcalwebservices_error'] = 1 ;
							$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.ParentHasNoID.error');
						}
					} else {
						$transferTemp = $this->parseDataArrayForSF($this->record);      	
						$webserviceResponse = $this->createUpdateEventForSF($transferTemp); 
						$transferSessiontemp['TW_TrainingWebinar__c'] = $webserviceResponse['SALESFORCEID'] ;
					}	  
					
					
					if(!$webserviceResponse['tx_nemcalwebservices_error'] == 1) {
//						$this->flashMessage['INFO'][] = 'debug: '.__LINE__;
						
						$transferSessiontemp['TW_End_Time__c'] = $transferTemp['TW_End_Time__c'] ;
						$transferSessiontemp['TW_Start_Time__c'] = $transferTemp['TW_Start_Time__c'] ;
						if ( $this->record['tx_nemcalwebservices_salesforce_sessionid'] ) {
							$transferSessiontemp['id'] = $this->record['tx_nemcalwebservices_salesforce_sessionid']  ;
//							$this->flashMessage['INFO'][] = 'debug: '.__LINE__;
						}
						$webserviceResponse2 = $this->createUpdateSessionForSF($transferSessiontemp); 
						$webserviceResponse['tx_nemcalwebservices_error'] = $webserviceResponse2['tx_nemcalwebservices_error'] ;
						 
					}
					$webserviceResponse['SALESFORCEID'] = $webserviceResponse['SALESFORCEID'] ;
					$webserviceResponse['SALESFORCESESSIONID'] = $webserviceResponse2['SALESFORCESESSIONID'] ;
					  	
				} else {
					// $transfertemp = $this->record ;
					// $transferData = $this->parseDataArray($transfertemp);
					// $webserviceResponse = $this->createUpdateEvent($transferData);
					$webserviceResponse = array (
						'message' => "We do not store Citrix Events to SalesForce!" ,
						'tx_nemcalwebservices_error' => 0 ,
						'SALESFORCEID' => "",
						'SALESFORCESESSIONID' => "",
					)
					;
				}    



              	$this->flashMessage['INFO'][] = 'Webservice Response: '.print_r($webserviceResponse, true);
              	if($webserviceResponse['tx_nemcalwebservices_error'] == 1 ) {
                	//deactivate registration
                	$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,' uid = '.intval($this->record['uid']).' OR l18n_parent = '.intval($this->record['uid']),array('tx_nemcalwebservices_registration'=>0));
             	 } else {
	              	$updateArray = array() ;
					
						$updateArray['tx_nemcalwebservices_salesforce_eventid'] =  $GLOBALS['TYPO3_DB']->fullQuoteStr($webserviceResponse['SALESFORCEID'],'tx_cal_event') ;
						$updateArray['tx_nemcalwebservices_salesforce_eventid'] = str_replace("'" , "" , $updateArray['tx_nemcalwebservices_salesforce_eventid']) ;
						$this->flashMessage['INFO'][] = 'Salesforce ID ' . $updateArray['tx_nemcalwebservices_salesforce_eventid'] ;
						$updateArray['tx_nemcalwebservices_salesforce_sessionid'] =  $GLOBALS['TYPO3_DB']->fullQuoteStr($webserviceResponse['SALESFORCESESSIONID'],'tx_cal_event') ;
						$updateArray['tx_nemcalwebservices_salesforce_sessionid'] = str_replace("'" , "" , $updateArray['tx_nemcalwebservices_salesforce_sessionid']) ;

					if ( $this->record['tx_nemcalwebservices_storeinsite'] == 0  ) {
						$updateArray['tx_nemcalwebservices_storeinsite'] = "1" ;
						$this->flashMessage['INFO'][] = 'Store in Salesforce must be 1';
					}



					if ( count($updateArray) > 0 ) {
						$this->flashMessage['INFO'][] = 'UpdateArray TYPO3: '.print_r($updateArray, true);
						//insert Notes-Id Event or/and Session ID
	    	            $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,' uid = '.intval($this->record['uid']).' OR l18n_parent = '.intval($this->record['uid']),
	    	             $updateArray );

						if ( $GLOBALS['TYPO3_DB']->sql_error() <> '' ) {
							$this->flashMessage['ERROR'][] = "ERROR updating TYPO3 event Record: " . $GLOBALS['TYPO3_DB']->sql_error() ;
						}
					}
					//$this->flashMessage['INFO'][] = 'record Typo3: '.print_r($this->record, true);

				}
            } else {

				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,' uid = '.intval($this->record['uid']).' OR l18n_parent = '.intval($this->record['uid']),
				  array('tx_nemcalwebservices_registration'=>0

				  ));
				if ( (strlen($this->record['tx_nemcalwebservices_citrixuid'])==0 || $this->record['tx_nemcalwebservices_storeincitrix'] == 1 )) {
					$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.missingCitrixID');
				} else {
					$this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.missingEmail');
				}

            }
          }
        }
        
        $this->showFlashMessage();
    }
  }
  // 5.12.2012 ForSF +++++++++++++++++  Salesforce übergabe statt notes ++++++++++++++++++
  
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

	  // now create the SF Array ..
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
	
    require_once(t3lib_extMgm::extPath('nem_customizations').'lib/salesforce/SforcePartnerClient.php');
	require_once(t3lib_extMgm::extPath('nem_customizations').'lib/salesforce/SforceHeaderOptions.php');
	// toDo : move these settings to a global place !!	  
	switch ($_SERVER['SERVER_ADDR']) {
		case '10.1.6.35':
		case '10.1.6.34':
		case '10.1.6.36':
		case '10.1.6.37':
		case '192.168.116.110':
		case '192.168.116.111':
		case '192.168.116.112':

			$USERNAME = $_SERVER['NEM_SALESFORCE']['live']['bn']  ;
			// $PASSWORD = "force12JV3ohTG6RcCERBbln62k4lezXt0" ;
			// cahnge on 27.5.2013
			$PASSWORD = $_SERVER['NEM_SALESFORCE']['live']['pw'] . $_SERVER['NEM_SALESFORCE']['live']['hash'] ;

		
			// $WSDLFILE = t3lib_extMgm::extPath('nem_customizations').'lib/salesforce/sl.live.partner.wsdl.xml' ;
			$WSDLFILE = t3lib_extMgm::extPath('nem_customizations').'lib/salesforce/jul13.live.partner.wsdl.xml' ;

		break;
		default:	
			$USERNAME = $_SERVER['NEM_SALESFORCE']['test']['bn']  ;
			//$PASSWORD = "force13JV" . "MXsc4tuOxlpMMufgBfjuQhQx" ;
			// cahnge with new sandbox on 2.7.2013
			$PASSWORD = $_SERVER['NEM_SALESFORCE']['test']['pw'] . $_SERVER['NEM_SALESFORCE']['test']['hash'];
			$WSDLFILE = t3lib_extMgm::extPath('nem_customizations').'lib/salesforce/sl.test.partner.wsdl.xml' ;

			break ;
	}


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

  // 5.12.2012 ForSF ------ END Salesforce übergabe statt notes ++++++++++++++++++
 
  private function parseDataArray($transferData){      
    $transferData['eventid'] = $transferData['tx_nemcalwebservices_notes_eventid']?$transferData['tx_nemcalwebservices_notes_eventid']:'';
    $transferData['mainCategory'] = $this->getMainCategory($transferData['category_id']);
    $transferData['language'] = $this->getLanguage($transferData['sys_language_uid']);
		
	$format = str_replace(array('d','m','y','Y'),array('%d','%m','%y','%Y'),$this->extConf['dateFormat']);
    $dateObj = new tx_cal_date($transferData['start_date'].'000000');
	$dateObj->setTZbyId('UTC');
    $transferData['start_date'] = $dateObj->format($format);
    
    $dateObj = new tx_cal_date($transferData['end_date'].'000000');
		$dateObj->setTZbyId('UTC');
    $transferData['end_date'] = $dateObj->format($format);
    
    date_default_timezone_set('UTC');
    /*
    Echo "<br>starttime:" . $transferData['start_time'] ;
    echo "<br> Format: " . $this->extConf['timeFormat'] ;
    Echo "<br>startdate conf:" . date( $this->extConf['timeFormat'] , $transferData['start_time'] ) ;
    die() ;
    */
    $transferData['start_time'] = date($this->extConf['timeFormat'], $transferData['start_time'] );
    
    $transferData['end_time'] = date($this->extConf['timeFormat'], $transferData['end_time']);
    
    
    $transferData['subCategory'] = $this->getSubCategory($transferData['category_id']);
    $transferData['organizer'] = $this->getOrganizer($transferData['organizer_id']);
    
    $transferData['status'] = (($transferData['deleted']==1 || $this->deleted == 1)?'99': //deleted
                                ($transferData['hidden']==1?'0': //unconfirmed
                                          (strtotime($transferData['start_date'])<time()?'2': //outdated
                                                    '1'))); //active
                                                    
    $transferData['tx_nemcalwebservices_seats'] = intval($transferData['tx_nemcalwebservices_seats']);
    
    $this->flashMessage['INFO'][] = 'TransferData: '.print_r($transferData, true);
    return $transferData;
  }
	
  private function createUpdateEvent($transferData) {	 
    
    $this->webservice = $this->initWebservice();
    $response = $this->webservice->fncCreateUpdateEvent($transferData);   
    //   $this->flashMessage['INFO'][] = '<pre>' . htmlspecialchars($this->client->request, ENT_QUOTES) . '</pre>';
  	
    if(!is_array($response)){
      $this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservices.error');
      $this->flashMessage['ERROR'][] = '<b>'.$GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservices.errorprefix').'</b>';
      $this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservices.errornoresponse');
      $this->flashMessage['ERROR'][] = '<br>Request: ' . htmlspecialchars($this->webservice->getRequest() , ENT_QUOTES);
      $this->flashMessage['ERROR'][] = '!is_array($response)' .print_r($response, true);;
      $response['tx_nemcalwebservices_error'] = 1;
    } elseif($response['faultcode']!=''){
      $this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservices.error');
      $this->flashMessage['ERROR'][] = '<b>'.$GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservices.errorprefix').'</b>';
      foreach($response as $key=>$value){
        $this->flashMessage['ERROR'][] = $key.': '.$value;
      }
      $this->flashMessage['ERROR'][] = htmlspecialchars($this->webservice->getRequest());      
      $this->flashMessage['ERROR'][] = '$response[\'faultcode\']!=\'\' =>' . $response['faultcode'];
      $response['tx_nemcalwebservices_error'] = 1;
    } else {
      if(intval($response['INTERROR']) > 0 || $response['STRERROR']!='') {
        $this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservices.error');
        $this->flashMessage['ERROR'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservices.errorprefix');
        $this->flashMessage['ERROR'][] = $response['errormessage'];   
        $this->flashMessage['ERROR'][] = $response['STRERROR'];   
        $this->flashMessage['ERROR'][] = 'intval($response[\'INTERROR\']) > 0 || $response[\'STRERROR\']!=\'\'';
        $response['tx_nemcalwebservices_error'] = 1;
      } elseif(intval($response['INTERROR']) == 0 && $response['STRERROR']==''){
        $this->flashMessage['OK'][] = $GLOBALS['LANG']->sL('LLL:EXT:nem_calwebservices/Locallang/locallang_db.xml:tx_cal_event.webservices.success');
      }    
    }
    return $response;
  }
 
  
  private function getMainCategory($category_id) {
    $category = '';
	  $mainCategoryTitles = '' ;
    $categoryRecords = $this->getCategoryRecord($this->id);
    for($i=0; $i<count($categoryRecords); $i++){
      if($this->getCategoryRootline($categoryRecords[$i])) {
        $categoryRootline = $this->getCategoryRootline($categoryRecords[$i]);
        $mainCategoryTitles .=  ', '.$categoryRootline[count($categoryRootline)-1]['title'];
      }
    }   
    return ltrim($mainCategoryTitles, ', ');
  }
  
  private function getSubCategory($category_id) {
    $categoryTitles = '';
    
    $categoryRecords = $this->getCategoryRecord($this->id);
    for($i=0; $i<count($categoryRecords); $i++){
      if($this->getCategoryRootline($categoryRecords[$i])){
        $categoryTitles .= ', '.$categoryRecords[$i]['title'];
      }
    }

    return ltrim($categoryTitles, ', ');
  }
  
  private function getCategoryRecord($eventId){
    $res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tx_cal_category.*', 'tx_cal_event', 'tx_cal_event_category_mm', 'tx_cal_category', ' AND tx_cal_event.uid = '.intval($eventId).' '.t3lib_befunc::BEenableFields('tx_cal_category'));
    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
      //$categoryRecs[] = $row;
      $categoryRecords[] = $row;
    }
    $GLOBALS['TYPO3_DB']->sql_free_result($res);
    return($categoryRecords);
  }
  
  private function getCategoryRootline($categoryRecord){
    $actCategoryRecord = $categoryRecord;
    $i=0;
    while(intval($actCategoryRecord['parent_category']) > 0 && //absolute root category reached
          !in_array(intval($actCategoryRecord['parent_category']), explode(',', $this->extConf['rootCategories'])) && //configured root category reached
          intval($actCategoryRecord['parent_category']) != intval($actCategoryRecord['uid']) && //error in database, category can't be its own parent
          $i<999 //crazyrecursionlimit, should never happen
    ) {
      $actCategoryRecord = t3lib_BEfunc::getRecord('tx_cal_category', intval($actCategoryRecord['parent_category']), 'uid,pid,parent_category,title');
      $categoryRootline[] = $actCategoryRecord;
      $i++;
    } 

    if(in_array(intval($categoryRootline[count($categoryRootline)-1]['parent_category']), explode(',', $this->extConf['rootCategories']))){
      return $categoryRootline;
    }    
  }
  
  private function getLanguage($sys_language_uid) {
    if($sys_language_uid>0) {
      $languageRec = t3lib_BEfunc::getRecord('sys_language', intval($sys_language_uid));
      $language = $languageRec['title'];
    } else {
      $language = 'international';
    }
    return $language;
  }
  
  private function getOrganizer($organizer_id) {
    
    $id = explode('_', $organizer_id);
    $organizerRec = t3lib_BEfunc::getRecord('tt_address', intval($id[count($id)-1]));
    
    $organizer = array(
      'city' => $organizerRec['city'],
      'zip' => $organizerRec['zip'],
      'address' => $organizerRec['address'],
      'company' => $organizerRec['company'],
      'last_name' => $organizerRec['last_name'],
      'first_name' => $organizerRec['first_name'],
      'email' => $organizerRec['email'],
      'phone' => $organizerRec['phone']
    );
    
    return $organizer;
  }
  
  private function initWebservice(){
  	
	    $this->flashMessage['INFO'][] = 'Settings: WSDL: (' . t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/ ) ' .$this->extConf['pathToWSDL'] 
        						. '<br>Endpoint: ' .  $this->extConf['pathToWebService'] 
    							. '<br>Namespace: ' . $this->extConf['namespace']
    							. '<br>cainfofile: ' . $this->extConf['caInfoFile']
        						. ' - sslcertfile: ' .  $this->extConf['sslCertFile']
       							. ' - sslkeyfile: ' . $this->extConf['sslKeyFile']
    							;
	
   	
    require_once(t3lib_extMgm::extPath('nem_calwebservices').'/Service/NotesWrapper.php');
    if($this->extConf['caInfoFile']!='' && $this->extConf['caInfoFile']!='' && $this->extConf['caInfoFile']!=''){
      $certRequest = array(
        'cainfofile' => $this->extConf['caInfoFile'],
        'sslcertfile' => $this->extConf['sslCertFile'],
        'sslkeyfile' => $this->extConf['sslKeyFile']
      );
	  if (is_readable( $this->extConf['caInfoFile'] ))  {
    	$this->flashMessage['INFO'][] = "Info: caInfoFile '" . $this->extConf['caInfoFile'] . "' readable" ;
	    } else {
	    	$this->flashMessage['ERROR'][] = "Error: caInfoFile '" . $this->extConf['caInfoFile'] . "' NOT readable" ;
	    }
	 	if (is_readable( $this->extConf['sslCertFile'] ))  {
	    	$this->flashMessage['INFO'][] = "Info: Certfile '" . $this->extConf['sslCertFile'] . "' readable" ;
	    } else {
	    	$this->flashMessage['ERROR'][] = "Error: Certfile '" . $this->extConf['sslCertFile'] . "' NOT readable" ;
	    }
	
	  	if (is_readable( $this->extConf['sslKeyFile'] ))  {
	    	$this->flashMessage['INFO'][] = "Info: sslKeyFile '" . $this->extConf['sslKeyFile'] . "' readable" ;
	    } else {
	    	$this->flashMessage['ERROR'][] = "Error: sslKeyFile '" . $this->extConf['sslKeyFile'] . "' NOT readable" ;
	    }
    } else {
      $certRequest = false;
	  $this->flashMessage['INFO'][] = "Info: Sercer do not use Certification files " ;
	  
    }
    if(strpos($this->extConf['pathToWSDL'], 'http')===0){
      $wsdl = $this->extConf['pathToWSDL'];
      
    } else if(strpos($this->extConf['pathToWSDL'], 'file')===0){
      $wsdl = $this->extConf['pathToWSDL'];
      if (is_readable( $this->extConf['pathToWSDL'] ))  {
	  	$this->flashMessage['INFO'][] = "Info: WSDL File '" . $this->extConf['pathToWSDL'] . "' readable" ;
	  } else {
	  	$this->flashMessage['ERROR'][] = "Error: WSDL File '" . $this->extConf['pathToWSDL'] . "' NOT readable" ;
	  }
    } else {
      $wsdl = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST').'/'.$this->extConf['pathToWSDL'];
    }
    $this->flashMessage['INFO'][] = "Final Path to WSDL:  " . $wsdl ;

    return t3lib_div::makeInstance('Tx_NemCalwebservices_Service_NotesWrapper', $wsdl, $this->extConf['pathToWebService'], $this->extConf['namespace'], $certRequest);
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
        if(($type==-1 && $this->pObj->admin) || $type!=-1){
          $message = t3lib_div::makeInstance(
    							't3lib_FlashMessage',
    							implode('<hr />', $message),
    							'',
    							$type,
    							true
    			);
    		  t3lib_FlashMessageQueue::addMessage($message);
        }	
      }
    }
  }

	/**
	 * fetch the salesforce contact id from tt_address
	 * for the given uid
	 *
	 * @param integer $uid
	 *
	 * @return string|boolean the contactId if found or false otherwise
	 */
	private function getSalesforceContactId($uid) {
		$salesForceContactId = FALSE;
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('tx_nemcalwebservices_salesforce_customerid', 'tt_address', 'uid=' . intval($uid));
		if (is_array($result) && $result['tx_nemcalwebservices_salesforce_customerid'] !== '') {
			$salesForceContactId = $result['tx_nemcalwebservices_salesforce_customerid'];
		}
		return $salesForceContactId;
	}

	/**
	 * if the salesforce event linked to this record is deleted ( in salesforce )
	 * we remove the event and session id so the event can be created again
	 *
	 * @param $uid
	 */
	private function removeSalesforceFieldsAfterDelete($uid) {
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table, 'uid=' . intval($uid), array('tx_nemcalwebservices_salesforce_eventid' => '', 'tx_nemcalwebservices_salesforce_sessionid' => ''));
	}

}

?>