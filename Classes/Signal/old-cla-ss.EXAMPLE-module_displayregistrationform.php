<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2010 Marco Huber
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries 
 * International (http://CTMIinc.org). The WEC is developing TYPO3-based
 * (http://typo3.org) free software for churches around the world. Our desire
 * is to use the Internet to help offer new life through Jesus Christ. Please
 * see http://WebEmpoweredChurch.org/Jesus.
 *
 * You can redistribute this file and/or modify it under the terms of the 
 * GNU General Public License as published by the Free Software Foundation;
 * either version 2 of the License, or (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This file is distributed in the hope that it will be useful for ministry,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the file!
 ***************************************************************/
 
require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility :: extPath('cal').'view/class.tx_cal_base_view.php');
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');

/**
 *
 * @author Marco Huber
 */
class module_displayregistrationform extends tx_cal_eventview {

	var $pObj;
	var $moduleCaller;
	var $modelObj;
	var $modelValues;
	var $cObj;
	var $conf;
	var $template;
	var $fields;
	var $fieldsRequired;
	var $dbUser;
	var $wsUser;
	var $requiredError;
	var $extConf;
	var $eventWebservice;

	function start(&$moduleCaller){
		$GLOBALS['TSFE']->set_no_cache(); // disable cache for single view ... USER INT Should be enough! 
		
		$this->moduleCaller = $moduleCaller;
		$this->modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
		$this->cObj = &tx_cal_registry::Registry('basic','cobj'); 
		
		// $this->cObj->convertToUserIntObject(); // set plugin to user int. The form should not be cached
		
		$this->conf = &tx_cal_registry::Registry('basic','conf');
		$this->controller = &tx_cal_registry::Registry('basic','controller');
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['nem_calwebservices']); 
		$html = '';

		$this->modelValues = $this->moduleCaller->getValuesAsArray();

		if(intval($this->modelValues['l18n_parent']) > 0){ //overwrite some value with default language
			$origModelValues = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_cal_event', 'uid = '.intval($this->modelValues['l18n_parent']));
			$this->modelValues['tx_nemcalwebservices_registration'] = $origModelValues[0]['tx_nemcalwebservices_registration'];
			$this->modelValues['tx_nemcalwebservices_seats'] = $origModelValues[0]['tx_nemcalwebservices_seats'];
			$this->modelValues['tx_nemcalwebservices_formfields'] = $origModelValues[0]['tx_nemcalwebservices_formfields'];
			$this->modelValues['tx_nemcalwebservices_formfieldsrequired'] = $origModelValues[0]['tx_nemcalwebservices_formfieldsrequired'];
			$this->modelValues['tx_nemcalwebservices_notes_eventid'] = $origModelValues[0]['tx_nemcalwebservices_notes_eventid'];
			$this->modelValues['tx_nemcalwebservices_registeredusers'] = $origModelValues[0]['tx_nemcalwebservices_registeredusers'];
			$this->modelValues['tx_nemcalwebservices_waitingoptins'] = $origModelValues[0]['tx_nemcalwebservices_waitingoptins'];
			//var_dump($this->modelValues);
			//var_dump($origModelValues[0]);
		}
		//$GLOBALS['TSFE']->fe_user->user['uid'] = 9;

		// j.v. 16.11.2011 : do not show reg form if event is in the past .. 
		$startdate = mktime( 0,0,0, 
					substr( $this->modelValues['start_date'] , 4,2 ) , 
					substr( $this->modelValues['start_date'] , 6,2 ) ,  
					substr( $this->modelValues['start_date'] , 0,4 ) ) ; 
		$starttime = ( intval( $this->modelValues['start_time']) + $startdate ) ;
		$now = time() ;

		if ( $now > $starttime ) {
			return "<!-- Starttime: " . $this->modelValues['start_date'] . " - " . $this->modelValues['start_time'] . " ( " . $starttime . " ) > than now() -->";
		}			
		
		
		if($this->conf['view.']['event.']['module__displayregistrationform'] == 1
		&& $this->modelValues['tx_nemcalwebservices_registration'] == 1){
			$this->template = $GLOBALS['TSFE']->cObj->fileResource($this->conf['view.']['event.']['module__displayregistrationform.']['templateFile']);

			$this->fields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->modelValues['tx_nemcalwebservices_formfields'], true);
			//append default fields
			array_push($this->fields, 'eventid', 'userid');
			$this->fieldsRequired = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->modelValues['tx_nemcalwebservices_formfieldsrequired'], true);

			if(intval($GLOBALS['TSFE']->fe_user->user['uid'])>0){
				$this->dbUser = $this->getDbUser();
				if(false){ //if Webservice is configured
					//$this->wsUser = $this->getWsUser();
				}
			}
			//var_dump($this->modelValues['tx_nemcalwebservices_seats'], $this->modelValues['tx_nemcalwebservices_registeredusers']);
			$result = $this->submitForm();

			if(!$result  //no search submitted
			&& !$this->controller->piVars['confirmReg'] //no confirmation link clicked
			&& !$this->controller->piVars['deleteReg']==1){  //no cancel link clicked
			
				// before checking available seats, sync registered user count of webservice with count of typo3
				// $this->syncRegistrationCount();
			
				if($this->modelValues['tx_nemcalwebservices_seats'] - $this->modelValues['tx_nemcalwebservices_registeredusers'] > 0){ //seats available
					$markerArray['###HEADLINE###'] = '';
					$markerArray['###INFOTEXT###'] = '';
					$markerArray['###FORMACTION###'] = '';
					$markerArray['###PAGEID###'] = '';
					$markerArray['###FIELDS###'] = '';
					$markerArray['###SUBMIT###'] = '';

					$markerArray['###HEADLINE###'] = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['headline'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['headline.']);

					$markerArray['###INFOTEXT###'] = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['infoText'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['infoText.']);

					$origPiVars = $this->controller->piVars;
					unset($this->controller->piVars['calRegistration']);
					$markerArray['###FORMACTION###'] = $this->controller->pi_linkTP_keepPIvars_url(array(),0);
					$this->controller->piVars = $origPiVars;

					$markerArray['###PAGEID###'] = $GLOBALS['TSFE']->id;      
					
					
					for($i=0; $i<count($this->fields); $i++){
						if(in_array($this->fields[$i], $this->fieldsRequired)){
							$markerArray['###FIELDS###'] .= $this->renderField($this->fields[$i], true);
						} else {
							$markerArray['###FIELDS###'] .= $this->renderField($this->fields[$i]);
						}
					}

					$markerArray['###SUBMIT###'] = '<input type="submit" name="'.$this->moduleCaller->prefixId.'[calRegistration][submit]" value="'.$this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['submitButtonValue'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['submitButtonValue.']).'" />';     

					$subpart = $GLOBALS['TSFE']->cObj->getSubpart($this->template, '###MODULE__DISPLAYREGISTRATIONFORM###');
					$html = $GLOBALS['TSFE']->cObj->substituteMarkerArray($subpart, $markerArray);
				} else { //no seats available
					$markerArray['###HEADLINE###'] = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorNoSeatsAvailableHeadline'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorNoSeatsAvailableHeadline.']);
					$markerArray['###INFOTEXT###'] = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorNoSeatsAvailableInfotext'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorNoSeatsAvailableInfotext.']);
					$subpart = $GLOBALS['TSFE']->cObj->getSubpart($this->template, '###MODULE__DISPLAY_ERROR###');
					$html = $GLOBALS['TSFE']->cObj->substituteMarkerArray($subpart, $markerArray);
				}
			} elseif($this->controller->piVars['confirmReg']==1 //confirmation link clicked 
				|| $this->controller->piVars['deleteReg']==1){  //cancel link clicked

				$markerArray['###HEADLINE###'] = ''; 
				$markerArray['###RESULT###'] = '';

				$markerArray['###HEADLINE###'] = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['headline'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['headline.']);

				$markerArray['###RESULT###'] = $this->confirmReg();

				//$this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['registrationConfirmed'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['registrationConfirmed.']);  

				$subpart = $GLOBALS['TSFE']->cObj->getSubpart($this->template, '###MODULE__DISPLAYREGISTRATIONFORMRESULT###');
				$html = $GLOBALS['TSFE']->cObj->substituteMarkerArray($subpart, $markerArray);

			} else { //search submitted
				$markerArray['###HEADLINE###'] = ''; 
				$markerArray['###RESULT###'] = '';

				$markerArray['###HEADLINE###'] = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['headline'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['headline.']);

				$markerArray['###RESULT###'] = $result;  

				$subpart = $GLOBALS['TSFE']->cObj->getSubpart($this->template, '###MODULE__DISPLAYREGISTRATIONFORMRESULT###');
				$html = $GLOBALS['TSFE']->cObj->substituteMarkerArray($subpart, $markerArray);
			}
		} else {
			$html = '';
		}
		return $html;
	}

	function renderField($fieldName, $required=false){
		$fieldTS = $this->conf['view.']['event.']['module__displayregistrationform.']['fields.'][$fieldName.'.'];

		$out = '';    
		$markerArray['###LABEL###'] = '';
		$markerArray['###NAME###'] = '';
		$markerArray['###VALUE###'] = '';
		$markerArray['###ATTRIBUTES###'] = ''; 
		$markerArray['###OPTIONS###'] = '';
		$markerArray['###REQUIREDERROR###'] = '';

		$markerArray['###LABEL###'] = $this->cObj->cObjGetSingle($fieldTS['label'], $fieldTS['label.']);
		
		
		if($required){
			$markerArray['###LABEL###'] .= ' *';
			$cssClass[] = 'required';
		}

		$markerArray['###NAME###'] = $this->moduleCaller->prefixId.'[calRegistration]['.$fieldName.']';

		$markerArray['###VALUE###'] = $this->getValue($fieldName);

		if(is_array($fieldTS['options.'])){
			foreach($fieldTS['options.'] as $option){
				if(isset($option['value']) && isset($this->controller->piVars['calRegistration'][$fieldName]) && $this->controller->piVars['calRegistration'][$fieldName] == $option['value']){
					$selected = 'selected="selected"';
				} else {
					if(isset($option['value']) && $this->getValue($fieldName)!==false && $this->getValue($fieldName) == $option['value']){
						$selected = 'selected="selected"';
					} else {
						$selected = '';
					}
				}
				$markerArray['###OPTIONS###'] .= '<option value="'.$option['value'].'" '.$selected.'>'.$this->cObj->cObjGetSingle($option['label'], $option['label.']).'</option>';
			}
		}
		if(isset($fieldTS['special']) && $fieldTS['special'] === 'static_countries') {
			if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
				$markerArray['###OPTIONS###'] = $this->buildCountryOptions($fieldName, $fieldTS);
			}
		}

		if($this->requiredError[$fieldName]!=''){
			//+++03.06.2011, w.f. read errors from the array instead of hardcoded stuff
			//$markerArray['###REQUIREDERROR###'] = $this->cObj->cObjGetSingle($fieldTS['requiredError'], $fieldTS['requiredError.']);
			$markerArray['###REQUIREDERROR###'] = $this->requiredError[$fieldName];
			$cssClass[] = 'error';
		}

		if(is_array($cssClass)){
			$markerArray['###ATTRIBUTES###'] .= ' class="'.implode(' ', $cssClass).'" ';
		}

		if ($fieldName == 'student' && $this->controller->piVars['calRegistration']['student'] == 1) {
			$markerArray['###ATTRIBUTES###'] .= ' checked="checked" ';
		}

		$subpart = $GLOBALS['TSFE']->cObj->getSubpart($this->template, '###FIELD_'.strtoupper($fieldName).'###');
		$out = $GLOBALS['TSFE']->cObj->substituteMarkerArray($subpart, $markerArray);

		return $out;
	}

	function getValue($fieldName){
		$value = false;
		$fieldTS = $this->conf['view.']['event.']['module__displayregistrationform.']['fields.'][$fieldName.'.'];
		if(intval($GLOBALS['TSFE']->fe_user->user['uid'])>0){
			if($fieldTS['prefill.']['type'] == 'db' && $this->dbUser[$fieldName] != ''){
				$value = $this->dbUser[$fieldName];
			} elseif($fieldTS['prefill.']['type'] == 'ws' && $this->wsUser[$fieldName] != ''){
				$value = $this->wsUser[$fieldName];
			}
		}
		if($fieldTS['prefill.']['type'] == 'ts'){
			$value = $this->cObj->cObjGetSingle($fieldTS['prefill.']['value'], $fieldTS['prefill.']['value.']);
		}
		if(isset($this->controller->piVars['calRegistration'][$fieldName]) && $this->controller->piVars['calRegistration'][$fieldName] != '' && $this->controller->piVars['calRegistration'][$fieldName] != $this->cObj->cObjGetSingle($fieldTS['defaultValue'], $fieldTS['defaultValue.'])){
			$value = $this->controller->piVars['calRegistration'][$fieldName];
		}
		if($fieldTS['defaultValue']!='' && !$value && !isset($this->controller->piVars['calRegistration']['submit'])){
			$value = $this->cObj->cObjGetSingle($fieldTS['defaultValue'], $fieldTS['defaultValue.']);
		}
		return $value;
	}

	function getDbUser(){
		$dbUser = array();
		$user = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'fe_users', 'uid='.intval($GLOBALS['TSFE']->fe_user->user['uid']));
		if(count($user)>0){
			for($i=0; $i<count($this->fields); $i++){
				$fieldTS = $this->conf['view.']['event.']['module__displayregistrationform.']['fields.'][$this->fields[$i].'.'];
				if($fieldTS['prefill.']['field'] && $user[0][$fieldTS['prefill.']['field']]!=''){
					$dbUser[$this->fields[$i]] = $user[0][$fieldTS['prefill.']['field']];
				}
			}
		}
		return $dbUser;
	}

	//function getWsUser(){
		//$wsUser = ... //call Webservice to get the user...
	//	return $wsUser;
	//}

	function submitForm(){
		$out = false; //return false to show the form
		if(is_array($this->controller->piVars['calRegistration'])){ //if the form is submitted
			//var_dump($this->modelValues['tx_nemcalwebservices_seats'],$this->modelValues['tx_nemcalwebservices_registeredusers']);

			if($this->modelValues['tx_nemcalwebservices_seats'] - $this->modelValues['tx_nemcalwebservices_registeredusers'] > 0){ //seats available
			
			
				//cleanup values
				for($i=0; $i<count($this->fields); $i++){
					$fieldTS = $this->conf['view.']['event.']['module__displayregistrationform.']['fields.'][$this->fields[$i].'.'];
					$defaultValue = $this->cObj->cObjGetSingle($fieldTS['defaultValue'], $fieldTS['defaultValue.']);
					if($this->controller->piVars['calRegistration'][$this->fields[$i]] == $defaultValue){
						$cleanData[$this->fields[$i]] = '';
					} else {
						$cleanData[$this->fields[$i]] = $this->controller->piVars['calRegistration'][$this->fields[$i]];
					}
				}
				for($i=0; $i<count($this->fieldsRequired); $i++){ //check the required fields (this is the only validation we do in php. other validation will be done by javascript!)
					if($cleanData[$this->fieldsRequired[$i]] == '' 
					|| !isset($cleanData[$this->fieldsRequired[$i]])){
						$fieldTS = $this->conf['view.']['event.']['module__displayregistrationform.']['fields.'][$this->fieldsRequired[$i].'.'];  
						$this->requiredError[$this->fieldsRequired[$i]] = $this->cObj->cObjGetSingle($fieldTS['requiredError'], $fieldTS['requiredError.']);
					}
					//+++ w.f. 03.06.2011, checking email for right syntax
					if  ($this->fieldsRequired[$i] == "email"){
						// check email only if the field not empty
						if($cleanData["email"] != ""){
							$checkmail = t3lib_div::validEmail($cleanData["email"]);
							if ($checkmail == false){
								$fieldTS = $this->conf['view.']['event.']['module__displayregistrationform.']['fields.'][$this->fieldsRequired[$i].'.'];  
								$this->requiredError[$this->fieldsRequired[$i]] = $this->cObj->cObjGetSingle($fieldTS['syntaxError'], $fieldTS['syntaxError.']);
							}
						}
					}
				}
				if(!is_array($this->requiredError)){ //if all required fields are filled and the syntax of the email is ok
					
					// ++ ++++++++++  beginn   store in citrix goto webinar +++++++++++++
					if ( ( $this->modelValues['tx_nemcalwebservices_citrixuid'] <> '' OR $this->modelValues['tx_nemcalwebservices_salesforce_eventid'] <> '') && ( $this->modelValues['tx_nemcalwebservices_storeincitrix'] || $this->modelValues['tx_nemcalwebservices_storeinsite'] )) {
						$debugmail = "Debug info for store Event in citrix/site \n" ;	
						$debugmailjson = "" ;
						$debugmaildata = "" ;
						 
						$error = 0 ;  // if if error = 0 every thing is okay .. error =1 email is already registered .. error =2 Webservice does not respond correctly ... 
						$data = array() ;
						$jsonarr = array()  ;
						for($i=0; $i<count($this->fields); $i++){
							$fieldTS = $this->conf['view.']['event.']['module__displayregistrationform.']['fields.'][$this->fields[$i].'.'];
							
							if ($fieldTS['siteName'] <> '' ) {
								$data[$fieldTS['siteName']] = $this->controller->piVars['calRegistration'][$this->fields[$i]] ;
								$debugmaildata .= "\n" . $fieldTS['siteName'] . " = " . $this->controller->piVars['calRegistration'][$this->fields[$i]] ;
							}
							if ($fieldTS['citrixName'] <> '' ) {
								// remove some special Chars from input fields before sending it to citrix
								$badStrings = array("|" , "'" , "&" , "+" , "\\" , "/" , "?" , "!" , '"'  , "," , ';'  ) ;
								$goodStrings = array(" " , " " , " " , " " , " " , " " , " " , " " , ' ' , " " , ' '  ) ;
								$jsonarr[$fieldTS['citrixName']] = str_replace( $badStrings, $goodStrings , $this->controller->piVars['calRegistration'][$this->fields[$i]] ) ;

								$debugmailjson .= "\n" . $fieldTS['citrixName'] . " = " . $this->controller->piVars['calRegistration'][$this->fields[$i]]  . " corrected: " . $jsonarr[$fieldTS['citrixName']];
								
							}
							
						}

						$debugmail .= "\nField Count piVars: " . count($this->fields) ;
						$debugmail .= "\nField Count data: " . count($data) ;
						$debugmail .= "\nField Count jsonarr: " . count($jsonarr) ;
						
						if ( $this->modelValues['tx_nemcalwebservices_storeincitrix'] ) {
							$error = 2 ; // overwritten again if successfull ... 
							$httpresponseErr = "" ;
							$httpresponseErrText = "" ;
							
							$json = json_encode($jsonarr) ;
							$debugmail .= "\n+++++++++++ store in citrix is active ++++++++++++++++++\n\n"  ;
							$debugmail .= $debugmailjson ;
							// $citrixURL =  'https://api.citrixonline.com/G2W/rest/organizers/1465928619483499268/webinars/' . $this->modelValues['tx_nemcalwebservices_citrixuid'] . '/registrants?oauth_token=24d3492169e0b4920678e1e20c1db967' ;						
							$citrixURL =  'https://api.citrixonline.com/G2W/rest/organizers/' . $GLOBALS['TSFE']->config['config']['CitrixOrgID']
  										. '/webinars/' . $this->modelValues['tx_nemcalwebservices_citrixuid'] 
  										. '/registrants?oauth_token=' . $GLOBALS['TSFE']->config['config']['CitrixOrgAUTH'];
							
							$data['webinar'] = $this->modelValues['tx_nemcalwebservices_citrixuid'] ;
							if ( $_SERVER['SERVER_NAME'] == "relaunch" ||  $_SERVER['SERVER_NAME'] == "connect" ) {
	 							echo "<hr>No transport to salesForce / Citrix on testserver !!! <pre>" ; 
								var_dump( $jsonarr ) ;
								echo "</pre>" ;
								die;	
							} else {
								// $jsonheader = array ( "Accept: application/json" , "Content-type:application/json" , "Authorization: OAuth oauth_token=24d3492169e0b4920678e1e20c1db967" ) ;
                                $jsonheader = array ( "Accept: application/json" , "Content-type:application/json" , "Authorization: OAuth oauth_token=" . $GLOBALS['TSFE']->config['config']['CitrixOrgAUTH'] ) ;

                                $ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $citrixURL); // set the target url

								if($fp = tmpfile()){
									// with this option, CURL out put is not stored to Error Log
							  		curl_setopt ($ch, CURLOPT_STDERR, $fp);
								}
								curl_setopt($ch, CURLOPT_POST, 1 ); // 
								curl_setopt($ch, CURLOPT_POSTFIELDS, $json ); // the parameter 'username' with its value 'johndoe'
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true ); // don't give anything back (important in TYPO3!)
								curl_setopt($ch, CURLOPT_HEADER, TRUE );  // leider muss ich das komplett zurÃ¼ck holen, weil ich sonst nicht den HTTP response Code ( Fehler / OK ) zurÃ¼ck bekomme .. 
								curl_setopt($ch, CURLOPT_VERBOSE, true);
								curl_setopt($ch, CURLOPT_HTTPHEADER, $jsonheader );
								$debugmail .= "\n+++++++++++ store in citrix url: ++++++++++++++++++\n\n"  ;
								$debugmail .=  $citrixURL ;
								
								
								$result = curl_exec ($ch);
								curl_close ($ch);
								$resultarr = explode ( "\n" , $result ) ;
								$httpval = explode ( " " , $resultarr[0] ) ;
								
								for ( $i=1 ;  $i < count( $resultarr) ; $i++ ) {
									if ( is_array (json_decode( $resultarr[$i] , true)) ){
										$resultvals  = json_decode( $resultarr[$i] , true) ;
									}		
								}
								if ( $resultvals['registrantKey'] <> '' ) {
									if ( $httpval[1] == "201" )  {
										$error = 0 ; //  no error Overwrite error 2 
									} else {
										$error = 1 ;  // already registered for that event .. 
									}
								} else {
									$httpresponseErr = $httpval[1] ;
									$httpresponseErrText = substr($result , 0 ,1800)  ;
									$debugmail .= "\n+++++++++++ citrix error: ++++++++++++++++++\n"  ;
									$debugmail .= "\nhttpresponseErrText: " . $httpresponseErrText ;


								}
								$debugmail .= "\n+++++++++++ citrix resul: ++++++++++++++++++\n"  ;
								$debugmail .= "\nregistrantKey: " . $resultvals['registrantKey']  ;
								$debugmail .= "\nhttp header response: " . $httpval[1] ;
								$debugmail .= "\nError: " . $error;
								
							}
						}
						if ( $this->modelValues['tx_nemcalwebservices_storeinsite'] AND $error == 0  ) {
							if ( $_SERVER['SERVER_NAME'] == "relaunch" ||  $_SERVER['SERVER_NAME'] == "connect" ) {
	 							echo "<hr>No transport to salesForce / Citrix on testserver !!! <pre>" ; 
								var_dump( $data ) ;
								echo "</pre>" ;
								die;	
							} else {
								$debugmail .= "\n+++++++++++ store in Sales Force is active ++++++++++++++++++\n\n"  ;

                                $debugmail .= "\nStartdate:  " . $this->moduleCaller->getStart()->format(tx_cal_functions::getFormatStringFromConf($this->conf));
                                $debugmail .= "\nStartdate:  " . strftime($this->conf['view.']['event.']['event.']['timeFormat'], $this->modelValues['start_time']);
                                $debugmail .= "\n" .  $this->modelValues['title'];


                                $debugmail .= $debugmaildata ;

								$result = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
									'tx_nemcalwebservices_salesforce_customerid',
									'tt_address',
									'uid=' . intval($this->modelValues['organizer_id'])
								);
								if (is_array($result) && $result['tx_nemcalwebservices_salesforce_customerid'] !== '') {
									$salesForceContactId = $result['tx_nemcalwebservices_salesforce_customerid'];
									$debugmail .= "\nSalesForce Owner ID = " . $salesForceContactId;
									$data['OwnerId'] = $salesForceContactId;
								}


								$data['debug']  = "1" ;
								$debugmail .= "\ndebug: " . $data['debug'] ;
								$data['debugEmail']  = "pp.pospisil@googlemail.com" ;
								// $data['debugEmail']  = "jvelletti@allplan.com" ;
								
								$debugmail .= "\ndebugEmail: " . $data['debugEmail'] ;
								
								$data['retURL']  = "http://www.allplan.com" ;
								$debugmail .= "\nretURL: " . $data['retURL'] ;

								if ($this->controller->piVars['calRegistration']['student'] === '1') {
									$data['00N20000003aeMQ'] = 'education';
								} else {
									$data['00N20000003aeMQ'] = 'commercial';
								}

								if ( $this->modelValues['tx_nemcalwebservices_salesforce_eventid'] <> '' ) {
									// <!-- Feld fÃ¼r Webinar Key  VARIABLER WERT fÃ¼r jedes Webinar eindeutiger Wert-->
									$data['00N20000003acfo']  = $this->modelValues['tx_nemcalwebservices_salesforce_eventid'] ;
									$debugmail .= "\n00N20000003acfo: " . $data['00N20000003acfo'] ;
								
									// <!-- Datensatztyp fÃ¼r NON CITRIX Webinare = FIXER WERT-->

									// $data['recordType'] = "012W00000008aVq" ;
									// 27. Juni 2012 : nun doch der gleiche Record Type wie Citrix ....
									$data['recordType'] = "01220000000JRRJ" ;


								} else {
									// <!-- Feld fÃ¼r Webinar Key  VARIABLER WERT fÃ¼r jedes Webinar eindeutiger Wert-->
									$data['00N20000003acfo']  = $this->modelValues['tx_nemcalwebservices_citrixuid'] ;
									$debugmail .= "\n00N20000003acfo: " . $data['00N20000003acfo'] ;
								
									// <!-- Datensatztyp fÃ¼r CITRIX Webinare = FIXER WERT-->
									$data['recordType'] = "01220000000JRRJ" ;
								
									// Recordtypeid: 012W00000008aVq // wenn OHNE Citrix !!
								}
								if ( $this->modelValues['tx_nemcalwebservices_salesforce_recordType'] <> '' ) {
									$data['recordType'] =  $this->modelValues['tx_nemcalwebservices_salesforce_recordType'] ;
									$debugmail .= "\nRecordtype was loaded from Event Configuration!" ;
								} else {
									$debugmail .= "\nRecordtype was not changed by Event !" ;
								}
								
								$debugmail .= "\nrecordType: " . $data['recordType'] ;
								
							//	if ( $data['first_name'] == "testvelletti") {
															//	}
								// echo "<pre>" ;
								// var_dump($data) ;
								// echo "</pre>" ;
								// die( __FILE__ ."<br>" . __LINE__ . "<hr>" . $debugmail . "<hr>" ) ;
								
								$data['oid']  = "00D200000000ach" ;
								// auf Sandbox .. : "00DW00000075YnP" 
								// toDo : move these settings to a global place !!	  
								
								$debugmail .= "\nServer IP: " .  $_SERVER['SERVER_ADDR'];
								switch ($_SERVER['SERVER_ADDR']) {
									// old live
									//case '10.1.6.10':
									//case '10.1.6.35':
									//case '10.1.6.36':
									//case '10.1.6.37':
									//case '10.1.6.34':
									case '192.168.116.110':
									case '192.168.116.111':
									case '192.168.116.112':

								// point also from stage to LIVE Salesforce if needed
									// case '192.168.116.115':

										// $data['oid']  = "00D200000000ach" ;
										$debugmail .= "\nOid: " . $data['oid']  . " (live Server)";
										$POSTTARGET = 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8' ;
											
										break ;
									default:	
								//		$POSTTARGET = 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8' ;
										$data['oid']  = "00DW00000075YnP" ;
										$debugmail .= "\nOid: " . $data['oid']  . " (Sandbox)";
										// auf Sandbox .. : "00DW00000075YnP" 
										$POSTTARGET = 'https://cs86.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8' ;
										break ;
								}
								$data['00N20000001KIHf']  =      $GLOBALS['TSFE']->config['config']['constsite_lng'];
								$debugmail .= "\nLanguage (00N20000001KIHf): " . $data['00N20000001KIHf']  ;
								
								$data = http_build_query($data) ;
								$debugmail .= "\nData converted to string !!!!: " ;


								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $POSTTARGET ); // set the target url
								curl_setopt($ch, CURLOPT_POST, 1 ); // 
								if($fp = tmpfile()){
									// with this option, CURL out put is not stored to Error Log
							  		curl_setopt ($ch, CURLOPT_STDERR, $fp);
								}
								curl_setopt($ch, CURLOPT_POSTFIELDS, $data ); // the parameters of POST 
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true ); // don't give anything back (important in TYPO3!)
								curl_setopt($ch, CURLOPT_HEADER, TRUE );  // fÃ¼r den HTTP response Code (zurÃ¼ck bekomme )fehler / OK ) .. 
								// thenext line should prevend response Header 100 ....
								curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
								
								curl_setopt($ch, CURLOPT_VERBOSE, true);
								
								$result = curl_exec ($ch);
								$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
								curl_close ($ch);
								$debugmail .= "\n+++++++++++ store in Sales Force URL ++++++++++++++++++\n"  ;
								$debugmail .= "\n" . $POSTTARGET ;
								$debugmail .= "\n\nResponse: " . substr($result , 0 ,1800) ;
								$debugmail .= "\nStatus: " . $status ;
								
								$debugmail .= "\n+++++++++++++++++++++++++++++\n"  ;
							}	
						}							
						
						if ( $error == 1 && 1==2 ) { // actually response from Salesforce for doulbe registrations does not work
							$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorWhileRegistrationIsDouble'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorWhileRegistrationisDouble.']);
						} else if ($error == 2) {
							$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorWhileRegistration'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorWhileRegistration.']);
								
						} else {
//							$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullRegistration'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullRegistration.']);
							$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullConfirm'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullConfirm.']);        
						
							// register direct without waiting for confimation 
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_event','uid='.intval($this->controller->piVars['uid']).' OR l18n_parent = '.intval($this->controller->piVars['uid']),array('tx_nemcalwebservices_registeredusers' => 'tx_nemcalwebservices_registeredusers + 1' ), array('tx_nemcalwebservices_registeredusers'));
							
						}

						$Typo3_v6mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_mail_Message');
                        $Typo3_v6mail->setFrom( array( 'info.de@allplan.com' => $_SERVER['SERVER_NAME'] ) );
                        $Typo3_v6mail->setReturnPath( 'info.de@allplan.com' );
                        $Typo3_v6mail->setTo(
                        	array(
                        		'jvelletti@allplan.com' =>  '',
								'pbenke@allplan.com' => '',
							)
						);

                        $Typo3_v6mail->setSubject("Event Registration Debug ");




                        $Typo3_v6mail->setBody(nl2br( $debugmail . "\n\n" . $httpresponseErrText), 'text/html');
                        $Typo3_v6mail->addPart($this->removeATagKeepURL(strip_tags($debugmail . "\n\n" . $httpresponseErrText) , '<a>'));

                        if ( $this->modelValues['tx_nemcalwebservices_notify_organizer'] == 1 ) {
                           $this->sendInfoMailToOrganizer( $cleanData , substr($result , 0 ,1800) );
                        }
                        // var_dump(nl2br($debugmail)) ;
						// die( "<hr>Line: " - LINE__ . " in : " . __FILE__ ) ;
						if ( $error == 2 ) {
							$Typo3_v6mail->setTo( array(  "connect-admin@allplan.com" =>  '' ));

                            $Typo3_v6mail->setSubject("Event Registration ERROR " . $httpresponseErr);
							$Typo3_v6mail->send();

                            $Typo3_v6mail->setTo( array(  "pp.pospisil@googlemail.com" =>  '' ));

                            $Typo3_v6mail->send();

						}  

                        $Typo3_v6mail->addCc("pp.pospisil@googlemail.com", '');
                        $Typo3_v6mail->send();
							
					} else {
						$cleanData['regid'] = '';
						$cleanData['status'] = '0';
						$cleanData['numberperson'] = intval($cleanData['numberperson']);
	
	
						$this->eventWebservice = $this->initEventWebservice();
						// +++ j.v. New From 25.11.2011 
						$unencodedData = $cleanData ;
						foreach($cleanData as $key=>$value){
							$cleanData[$key] = utf8_encode($value);
						}
						$this->eventWebservice->soap_defencoding = 'UTF-8'; 
						$this->eventWebservice->decode_utf8 = false;
	
						$response = $this->eventWebservice->fncRegisterUpdateUser($cleanData); 
	
						if(!is_array($response)){ //no response at all
							$response['tx_nemcalwebservices_error'] = 1;
						} elseif($response['faultcode']!=''){ //response with SOAP error
							$response['tx_nemcalwebservices_error'] = 1;
						} elseif(intval($response['INTERROR']) > 0 || $response['STRERROR']!='') { //Lotus Notes error
							$response['tx_nemcalwebservices_error'] = 1;   
						} else { //success
							$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullRegistration'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullRegistration.']);
							$this->sendOptInMail($unencodedData, $response);

							//var_dump($GLOBALS['TYPO3_DB']->UPDATEquery('tx_cal_event','uid='.intval($this->controller->piVars['uid']).' OR l18n_parent = '.intval($this->controller->piVars['uid']),array('tx_nemcalwebservices_registeredusers' => 'tx_nemcalwebservices_registeredusers + 1', 'tx_nemcalwebservices_waitingoptins' => 'tx_nemcalwebservices_waitingoptins + 1'), array('tx_nemcalwebservices_registeredusers', 'tx_nemcalwebservices_waitingoptins')));
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_event','uid='.intval($this->controller->piVars['uid']).' OR l18n_parent = '.intval($this->controller->piVars['uid']),array('tx_nemcalwebservices_registeredusers' => 'tx_nemcalwebservices_registeredusers + 1', 'tx_nemcalwebservices_waitingoptins' => 'tx_nemcalwebservices_waitingoptins + 1'), array('tx_nemcalwebservices_registeredusers', 'tx_nemcalwebservices_waitingoptins'));
						}
						if($response['tx_nemcalwebservices_error']==1){
							$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorWhileRegistration'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorWhileRegistration.']);
	
							if (  $_SERVER['REMOTE_ADDR'] == "212.29.3.142"  OR $_SERVER['REMOTE_ADDR'] == "172.20.4.36" ) {
								echo "<pre>";
								echo "errormessages for debug (only visible for Nemetschek Proxy users IP: 212.29.3.142)<hr/>" ;
								var_dump($this->eventWebservice->getRequest());
								echo "<hr/>";
								var_dump($this->eventWebservice->getResponse());
								echo "</pre>";
							}
	
						}
					}
				}
			} else {
				$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['noMoreSeatsWhileRegistration'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['noMoreSeatsWhileRegistration.']);
			}
		}
		return $out;
	}


	function sendOptInMail($formData, $response){
		//var_dump($this->moduleCaller->cachedValueArray);
		//var_dump($response);
		$eventTitle = $this->modelValues['title'];

		$origPiVars = $this->controller->piVars;
		unset($this->controller->piVars['calRegistration']);

		$eventLink = '<a href="'.t3lib_div::locationHeaderUrl($this->controller->pi_linkTP_keepPIvars_url(array('tx_cal_controller[regId]' => ''), 0)).'">'.$eventTitle.'</a>';
		$confirmLink = '<a href="'.t3lib_div::locationHeaderUrl($this->controller->pi_linkTP_keepPIvars_url(array('confirmReg' => 1, 'regId' => $response['STRDOCID']), 0)).'">'.$this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['optInMail.']['confirmLinkText'], $this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['optInMail.']['confirmLinkText.']).'</a>';

		$this->controller->piVars = $origPiVars;

		$validTime = strftime($this->conf['view.']['event.']['event.']['dateFormat'].' '.$this->conf['view.']['event.']['event.']['timeFormat'], (time()+86400));

		$marker['###SALUTATION###'] = $this->getSalutation($formData['gender']);
		$marker['###EVENTTITLE###'] = $eventTitle;
		// 5.4.2012: do not link Event Title
		//$marker['###EVENTLINK###'] = $eventLink;
		$marker['###EVENTLINK###'] = $eventTitle;
		
		$marker['###CONFIRMLINK###'] = $confirmLink;
		$marker['###VALIDTIME###'] = $validTime;
		$marker['###FIRSTNAME###'] = '';
		$marker['###FIRSTNAME###'] .= $formData['firstname'];
		$marker['###LASTNAME###'] = '';
		$marker['###LASTNAME###'] .= $formData['lastname'];

		$email['body'] = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['optInMail.']['text'], $this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['optInMail.']['text.']);
		foreach($marker as $key => $singleMarker){
			$email['body'] = str_replace($key, $singleMarker, $email['body']);
		}
		$email['body'] = nl2br($email['body']);
		$email['body'] .= tx_nemsignature::getSignature($GLOBALS['TSFE']->sys_language_uid);

		$email['subject'] = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['optInMail.']['subject'], $this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['optInMail.']['subject.']);
		foreach($marker as $key => $singleMarker){
			$email['subject'] = str_replace($key, $singleMarker, $email['subject']);
		}

		$html_start = '<html><head><title>'.$email['subject'].'</title></head><body>';
		$html_end = '</body></html>';    

		$email['address'] = $formData['email'];

        $Typo3_v6mail = t3lib_div::makeInstance('t3lib_mail_Message');

        $optInMail = $this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['optInMail.'];

        $Typo3_v6mail->setFrom( array( $optInMail['fromEmail'] => $optInMail['fromName'] ) );
        $Typo3_v6mail->setReturnPath( $optInMail['fromEmail'] );

        $Typo3_v6mail->setTo( array(  $email['address'] =>  '' ));

        $Typo3_v6mail->setSubject($email['subject']);

        $Typo3_v6mail->setBody($html_start.$email['body'].$html_end, 'text/html');
        $Typo3_v6mail->addPart($this->removeATagKeepURL(strip_tags($email['body'], '<a>')), 'text/plain');

        $Typo3_v6mail->send();

	}

    /** NEW will replace all others : only used if option is activated and store in Saleforce is active
     * @param $formData
     * @param $response
     */

    function sendInfoMailToOrganizer($formData, $response){
        //var_dump($this->moduleCaller->cachedValueArray);
        //var_dump($response);

        // get data of organizer
        $organizerUid = intval($this->modelValues['organizer_id']);
        if($organizerUid) {
            $rowsOrganizer = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_address', 'uid='.$organizerUid);
        } else {
            // no Organizer , = no email to send to :...
            return ;
        }
        $organizer = $rowsOrganizer[0];
        if(! \TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($organizer['email'])) {
            // no valid email adresse no need to continue ...
           return ;
        }

        $eventTitle = $this->modelValues['title'];

        $origPiVars = $this->controller->piVars;
        unset($this->controller->piVars['calRegistration']);

        $eventLink = \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($this->controller->pi_linkTP_keepPIvars_url(array('tx_cal_controller[regId]' => ''), 0)) ;

        $this->controller->piVars = $origPiVars;



        $validTime = strftime($this->conf['view.']['event.']['event.']['dateFormat'].' '.$this->conf['view.']['event.']['event.']['timeFormat'], (time()+86400));
        $marker = array() ;
        $marker['###SALUTATION###'] = $this->getSalutation($formData['gender']);
        $marker['###EVENTTITLE###'] = $eventTitle;

        $marker['###EVENTLINK###'] = $eventLink;

        $marker['###VALIDTIME###'] = $validTime;
        $marker['###FIRSTNAME###'] = '';
        $marker['###FIRSTNAME###'] .= $formData['firstname'];
        $marker['###LASTNAME###'] = '';
        $marker['###LASTNAME###'] .= $formData['lastname'];
        $marker['###EVENTSTARTDATE###'] = $this->moduleCaller->getStart()->format(tx_cal_functions::getFormatStringFromConf($this->conf));
        $marker['###EVENTENDDATE###'] = $this->moduleCaller->getEnd()->format(tx_cal_functions::getFormatStringFromConf($this->conf));

// ############    sommer winterzeit #####################

		$timeZoneStart = ".000+01:00" ;
		$UnixStarttime = mktime( 10,10,0, substr( $this->modelValues['start_date'],4,2) , substr( $this->modelValues['start_date'],6,2) ,substr( $this->modelValues['start_date'],0,4) ) ;

		if ( date("I" , $UnixStarttime) == "1" ) {
			$timeZoneStart = ".000+02:00" ;
		}

		$timeZoneEnd = ".000+01:00" ;
		if ( date("I" , mktime( 10,10,0, substr( $this->modelValues['end_date'],4,2) , substr( $this->modelValues['end_date'],6,2) ,  substr( $this->modelValues['end_date'],0,4) )) == "1" ) {
			$timeZoneEnd = ".000+02:00" ;
		}

		$dateObj = new tx_cal_date($this->modelValues['start_date'].'000000');
		$dateObj->setTZbyId('UTC');
		$this->modelValues['start_date'] = $dateObj->format("%Y-%m-%d");

		$dateObj = new tx_cal_date($this->modelValues['end_date'].'000000');
		$dateObj->setTZbyId('UTC');
		$this->modelValues['end_date'] = $dateObj->format("%Y-%m-%d");

		date_default_timezone_set('UTC');
		$marker['###EVENTSTARTTIME###'] = date("H:i:s", $this->modelValues['start_time'] );
		$marker['###EVENTENDTIME###'] = date("H:i:s", $this->modelValues['end_time']);


        // (available fields: username, firstname, lastname, title, gender, department, companyname, companysize, streetaddress, zip, city, country, phonenumber, email,
        // additionalinformation, privacy, newsletter, customerid, profession, recall, (only Citrix / Salesforce: contactid, usedsoftware))</label>

        $marker['###USERNAME###'] = $formData['username'];
        $marker['###CUSTOMERID###'] = $formData['customerid'];
        $marker['###TITLE###'] = $formData['title'];
        $marker['###GENDER###'] = $formData['gender'];
        $marker['###DEPARTMENT###'] = $formData['department'];
        $marker['###COMPANYNAME###'] = $formData['company'];
        $marker['###COMPANYSIZE###'] = $formData['companysize'];
        $marker['###STREETADDRESS###'] = $formData['streetaddress'];
        $marker['###ZIP###'] = $formData['zip'];
        $marker['###CITY###'] = $formData['city'];
        $marker['###COUNTRY###'] = $formData['country'];
        $marker['###PHONENUMBER###'] = $formData['phonenumber'];
        $marker['###EMAIL###'] = $formData['email'];
        $marker['###ADDITIONALINFORMATION###'] = $formData['additionalinformation'];
        $marker['###PRIVACY###'] = $formData['privacy'];
        $marker['###PROFESSION###'] = $formData['profession'];
        $marker['###RECALL###'] = $formData['recall'];

        $marker['###CONTACTUID###'] = $formData['contactid'];
        $marker['###USEDSOFTWARE###'] = $formData['usedsoftware'];
        $marker['###NEWSLETTER###'] = $formData['newsletter'];


        $textMailSubject = "Registration in SalesForce for ###EVENTTITLE### by ###FIRSTNAME### ###LASTNAME### ( => " . $organizer['email'] . " )";
        foreach($marker as $key => $singleMarker){
            $textMailSubject = str_replace($key, $singleMarker, $textMailSubject);
        }

        switch ($GLOBALS['TSFE']->sys_language_uid) {
            case 18 :
                $textMailContent = "Informacion Interna - un nuevo registro en el siguiente evento
                        Evento: ###EVENTTITLE###
                        Evento - enlace: ###EVENTLINK###
                        Evento - dia de comienzo: ###EVENTSTARTDATE###
                        Evento - hora de comienzo: ###EVENTSTARTTIME###
                        Evento - dia de finalizacion: ###EVENTENDDATE###
                        Evento - hora de finalizacion: ###EVENTENDTIME###

                        Nombre de usuario: ###USERNAME###
                        Nombre: ###FIRSTNAME###
                        Apellidos: ###LASTNAME###
                        Titulo: ###TITLE###
                        Sr.D./Sra.DÃ±a: ###GENDER###
                        Departamento / Puesto: ###DEPARTMENT###
                        Nombre de la Company: ###COMPANYNAME###
                        Tamano de la Company: ###COMPANYSIZE###
                        Direccion: ###STREETADDRESS###
                        Codigo Postal: ###ZIP###
                        Ciudad: ###CITY###
                        Pais: ###COUNTRY###
                        Telefono: ###PHONENUMBER###
                        Email: ###EMAIL###
                        CAD usato: ###USEDSOFTWARE###
                        Contact UID: ###CONTACTUID###
                        Email Newsletter Optin:  ###NEWSLETTER###

                        Informacion adicional:

                        ================
                        ###ADDITIONALINFORMATION###
                        ================
                        Estoy de acuerdo con los tÃ©rminos y condiciones de Allplan!: ###PRIVACY###
                        Nomero de cliente: ###CUSTOMERID###
                        Ãrea de especializacion: ###PROFESSION###
                        Por favor, pongase en contacto conmigo por telefono si es necesario.: ###RECALL###
                        ";

                break;

            default:
                $textMailContent = "Internal Information about a new registration to the following event
					Event: ###EVENTTITLE### \n
					Eventlink: ###EVENTLINK### \n
					Event start date: ###EVENTSTARTDATE### \n
					Event start time: ###EVENTSTARTTIME### \n
					Event end date: ###EVENTENDDATE### \n
					Event end time: ###EVENTENDTIME### \n

					User Name: ###USERNAME### \n
					First name: ###FIRSTNAME### \n
					Last name: ###LASTNAME### \n
					Title: ###TITLE### \n
					Salutation: ###GENDER### \n
					Department / Function: ###DEPARTMENT### \n
					Company name: ###COMPANYNAME### \n
					Company size: ###COMPANYSIZE### \n
					Address: ###STREETADDRESS### \n
					Zip code: ###ZIP### \n
					City: ###CITY### \n
					Country: ###COUNTRY### \n
					Phone: ###PHONENUMBER### \n
					Email: ###EMAIL### \n
					User CAD Software: ###USEDSOFTWARE### \n
                    Contact UID: ###CONTACTUID### \n
                    Email Newsletter Optin:  ###NEWSLETTER### \n
					Further information: \n
					================ \n
					###ADDITIONALINFORMATION### \n
					================ \n
					I agree the Allplan Terms and condition!: ###PRIVACY### \n
					Customer ID: ###CUSTOMERID### \n
					Area of expertise: ###PROFESSION### \n
					Please contact me by phone if necessary.: ###RECALL### \n
					";
                break;
        }


        foreach($marker as $key => $singleMarker){
            $textMailContent = str_replace($key, $singleMarker, $textMailContent);
        }

        $html_start = '<html><head><title>'.$textMailSubject.'</title></head><body>';
        $html_end = '</body></html>';



        $Typo3_v6mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_mail_Message');
        $Typo3_v6mail->setSubject($textMailSubject);

        $Typo3_v6mail->setFrom(
            array(
                $this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['confirmMail.']['fromEmail']
                => $this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['confirmMail.']['fromName']
            )
        );
        $Typo3_v6mail->setReturnPath($this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['confirmMail.']['fromEmail']);

        $Typo3_v6mail->setTo(array($organizer['email'] => ''));
        // $Typo3_v6mail->setTo(array('jVelletti@nemetschek.com' => 'SF Eventregistration Test'));

        $Typo3_v6mail->setBody($html_start.  nl2br($textMailContent)  .$html_end, 'text/html');
        $Typo3_v6mail->addPart($this->removeATagKeepURL(strip_tags($textMailContent, '<a>')), 'text/plain');

        $Typo3_v6mail->send();

		$Typo3_v6mail->setTo(array('jVelletti@nemetschek.com' => 'SF Eventregistration Test Copy'));
		$Typo3_v6mail->send();

    }

	function confirmReg(){
		$out = '';

		$this->eventWebservice = $this->initEventWebservice();
		$user = $this->eventWebservice->fncShowParticipiantForEventByRegId($this->controller->piVars['regId']); 

		if($this->controller->piVars['confirmReg']==1){
			$status = '1';
		} elseif($this->controller->piVars['deleteReg']==1){
			$status = '99';
		}
		if ( ($user['REGENTRY']['STRSTATUS']==1 && $status == 1) ) {
			// do nothing ... the user is already confirmed, has got the email and we just show again the success message ... 
			$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullConfirm'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullConfirm.']);        
			return $out ;				
		}				
		if($user['REGENTRY']['STRSTATUS']==0 || ($user['REGENTRY']['STRSTATUS']==1 && $status == 99)   )
			$response = $this->eventWebservice->fncDeRegisterUser($this->controller->piVars['regId'], $status); 
		else {
			$response['tx_nemcalwebservices_error'] = 1;
		}
		/*
		print_r('<pre style="background: #fff;">');
		var_dump($user, $response);
		print_r('</pre>');
		*/
		if(!is_array($response)){ //no response at all
			$response['tx_nemcalwebservices_error'] = 1;
		} elseif($response['faultcode']!=''){ //response with SOAP error
			$response['tx_nemcalwebservices_error'] = 1;
		} elseif(intval($response['INTERROR']) > 0 || $response['STRERROR']!='') { //Lotus Notes error
			$response['tx_nemcalwebservices_error'] = 1;   
		} else { //success
			if(intval($user['REGENTRY']['STRSTATUS']) == 0){
				if($this->controller->piVars['confirmReg']==1){
					$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullConfirm'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullConfirm.']);        
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_event','uid='.intval($this->controller->piVars['uid']).' OR l18n_parent = '.intval($this->controller->piVars['uid']),array('tx_nemcalwebservices_waitingoptins' => 'tx_nemcalwebservices_waitingoptins - 1'), array('tx_nemcalwebservices_waitingoptins'));        

					$this->sendConfirmMail($user['REGENTRY'], $response);
				}
			} elseif(intval($user['REGENTRY']['STRSTATUS']) == 1 && $this->controller->piVars['deleteReg']==1) {
				$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullDelete'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['successfullDelete.']);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_event','uid='.intval($this->controller->piVars['uid']).' OR l18n_parent = '.intval($this->controller->piVars['uid']),array('tx_nemcalwebservices_registeredusers' => 'tx_nemcalwebservices_registeredusers - 1'), array('tx_nemcalwebservices_registeredusers'));
				$this->sendConfirmMail($user['REGENTRY'], $response, $status);
			} else {
				$response['tx_nemcalwebservices_error'] = 1;   
			}
		}
		if($response['tx_nemcalwebservices_error']==1){
			$out = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorWhileConfirmCancel'], $this->conf['view.']['event.']['module__displayregistrationform.']['frontendOutput.']['errorWhileConfirmCancel.']);
			//var_dump($this->eventWebservice->getRequest());
			//var_dump($this->eventWebservice->getResponse());
		}

		return $out;
	}

	function sendConfirmMail($user, $response, $status = 0){
		//var_dump($this->moduleCaller->cachedValueArray);
		//var_dump($response);

		// utf8 decode user data
		foreach($user as $key => $val) {
			$user[$key] = utf8_encode($val);
		}

		// get data of organizer
		$organizerUid = intval($this->modelValues['organizer_id']);
		if($organizerUid) {
			$rowsOrganizer = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_address', 'uid='.$organizerUid);
		}
		$organizer = $rowsOrganizer[0];

		// init static_info_tables for country
		if($organizer['country']) {
			$rowCountry = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid, cn_iso_3', 'static_countries', 'uid='.$organizer['country']);
			$countryIsoCode = $rowCountry[0]['cn_iso_3'];
			$staticInfoObj = &t3lib_div::getUserObj('&tx_staticinfotables_pi1');
			if ($staticInfoObj->needsInit()){
				$staticInfoObj->init();
			}
			$marker['###ORGANIZER_COUNTRY###'] = $staticInfoObj->getStaticInfoName('COUNTRIES', $countryIsoCode);
		} else {
			$marker['###ORGANIZER_COUNTRY###'] = '';
		}

		$marker['###ORGANIZER_COMPANY###'] = $organizer['company'];
		$marker['###ORGANIZER_ADDRESS###'] = $organizer['address'];
		$marker['###ORGANIZER_ZIP###'] = $organizer['zip'];
		$marker['###ORGANIZER_CITY###'] = $organizer['city'];
		$marker['###ORGANIZER_EMAIL###'] = $organizer['email'];
		$marker['###ORGANIZER_WWW###'] = $organizer['www'];
		$marker['###ORGANIZER_PHONE###'] = $organizer['phone'];
		$marker['###ORGANIZER_MOBILE###'] = $organizer['mobile'];
		$marker['###ORGANIZER_FAX###'] = $organizer['fax'];
		$marker['###ORGANIZER_BUILDING###'] = $organizer['building'];
		$marker['###ORGANIZER_ROOM###'] = $organizer['room'];
		$marker['###ORGANIZER_REGION###'] = $organizer['region'];

		$marker['###SALUTATION###'] = $this->getSalutation($user['STRGENDER']);
		$marker['###EVENTTITLE###'] = $this->modelValues['title'];
// 5.4.2012 : do not link Event Title ??? 
//		$marker['###EVENTLINK###'] = $this->modelValues['title'];
		$marker['###EVENTLINK###'] = '<a href="'.t3lib_div::locationHeaderUrl($this->controller->pi_linkTP_keepPIvars_url(array('confirmReg' => '', 'deleteReg' => ''), 0)).'">'.$marker['###EVENTTITLE###'].'</a>';
		$marker['###EVENTSTARTDATE###'] = $this->moduleCaller->getStart()->format(tx_cal_functions::getFormatStringFromConf($this->conf));
		$marker['###EVENTENDDATE###'] = $this->moduleCaller->getEnd()->format(tx_cal_functions::getFormatStringFromConf($this->conf));
		$marker['###DELETELINK###'] = '<a href="'.t3lib_div::locationHeaderUrl($this->controller->pi_linkTP_keepPIvars_url(array('confirmReg' => '', 'deleteReg' => 1), 0)).'">'.$this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['confirmMail.']['deleteLinkText'], $this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['confirmMail.']['deleteLinkText.']).'</a>';

		date_default_timezone_set('UTC');

		/*
		echo "<br>format: " . $this->conf['view.']['event.']['event.']['timeFormat'] ;
		echo "<br>eingabe " . $this->modelValues['start_time'] ;
		echo "<br>ergebnis 1 " . strftime($this->conf['view.']['event.']['event.']['timeFormat'], $this->modelValues['start_time']);
		echo "<br>ergebnis 2 " . date("H:i:s" , $this->modelValues['start_time']);
		die() ; 
		*/
		$marker['###EVENTSTARTTIME###'] = strftime($this->conf['view.']['event.']['event.']['timeFormat'], $this->modelValues['start_time']);
		$marker['###EVENTENDTIME###'] = strftime($this->conf['view.']['event.']['event.']['timeFormat'], $this->modelValues['end_time']);

		$marker['###USERNAME###'] = $user['STRUSERNAME'];
		$marker['###FIRSTNAME###'] = $user['STRFIRSTNAME'];
		$marker['###LASTNAME###'] = $user['STRLASTNAME'];
		$marker['###CUSTOMERID###'] = $user['STRCUSTOMERID'];
		$marker['###TITLE###'] = $user['STRTITLE'];
		$marker['###GENDER###'] = $user['STRGENDER'];
		$marker['###DEPARTMENT###'] = $user['STRDEPARTMENT'];
		$marker['###COMPANYNAME###'] = $user['STRCOMPANYNAME'];
		$marker['###COMPANYSIZE###'] = $user['STRCOMPANYSIZE'];
		$marker['###STREETADDRESS###'] = $user['STROFFICESTREETADDRESS'];
		$marker['###ZIP###'] = $user['STROFFICEZIP'];
		$marker['###CITY###'] = $user['STROFFICECITY'];
		$marker['###COUNTRY###'] = $user['STROFFICECOUNTRY'];
		$marker['###PHONENUMBER###'] = $user['STROFFICEPHONENUMBER'];
		$marker['###EMAIL###'] = $user['STRINTERNETADDRESS'];
		$marker['###ADDITIONALINFORMATION###'] = $user['STRBODY'];
		$marker['###PRIVACY###'] = $user['STRPRIVACY'];
		$marker['###PROFESSION###'] = $user['STRPROFESSION'];
		$marker['###RECALL###'] = $user['STRRECALL'];
		// j.v. 24.9.2012 : die 2 nachfolgenden gehen sicher so noch nicht .. 
		$marker['###CONTACTUID###'] = $user['CONTACTUID'];
		$marker['###USEDSOFTWARE###'] = $user['USEDSOFTWARE'];
		$marker['###NEWSLETTER###'] = $user['NEWSLETTER'];
		
		
		$email['body'] = $this->modelValues['tx_nemcalwebservices_email_text'];
		foreach($marker as $key => $singleMarker){
			$email['body'] = str_replace($key, $singleMarker, $email['body']);
		}
		$email['body'] = nl2br($email['body']);
		$email['body'] .= tx_nemsignature::getSignature($GLOBALS['TSFE']->sys_language_uid);

		$email['subject'] = $this->modelValues['tx_nemcalwebservices_email_subject'];
		foreach($marker as $key => $singleMarker){
			$email['subject'] = str_replace($key, $singleMarker, $email['subject']);
		}

		$html_start = '<html><head><title>'.$email['subject'] .'</title></head><body>';
		$html_end = '</body></html>';

		$email['address'] = $user['STRINTERNETADDRESS'];

        $Typo3_v6mail = t3lib_div::makeInstance('t3lib_mail_Message');

        $optInMail = $this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['optInMail.'];

        $Typo3_v6mail->setFrom( array( $optInMail['fromEmail'] => $optInMail['fromName'] ) );
        $Typo3_v6mail->setReturnPath( $optInMail['fromEmail'] );

        $Typo3_v6mail->setTo( array(  $email['address'] =>  '' ));

        $Typo3_v6mail->setSubject($email['subject']);

        $Typo3_v6mail->setBody($html_start.$email['body'].$html_end, 'text/html');
        $Typo3_v6mail->addPart($this->removeATagKeepURL(strip_tags($email['body'], '<a>')), 'text/plain');

		if($status != 99) {

            // mail to organizer and user
            $Typo3_v6mail->send();
			$this->sendMailToOrganizer($marker, $user, $response, $status, $organizer);
		} else {
			// if user deletes himself, mail only to organizer
			$this->sendMailToOrganizer($marker, $user, $response, $status, $organizer);
		}

	}
	/**
	* send mail to organizer
	* @param	array	$marker: filled marker
	* @param	array	$user: user data
	* @param	array	$response: webservice response
	* @param	int		$status: 0 unconfirmed 1 confirmed 99 deleted
	* @param	array	$organizer: data of organizer
	* $marker: filled marker array, $user: user data, , 
	*/
	function sendMailToOrganizer($marker, $user, $response, $status, $organizer) {
		$organizerEmail = $organizer['email'];

		if(t3lib_div::validEmail($organizerEmail)) {

			$textMailSubject = ($status == 99 ? "Deleted Registration" : "Registration")." for ###EVENTTITLE### by ###FIRSTNAME### ###LASTNAME###";
			foreach($marker as $key => $singleMarker){
				$textMailSubject = str_replace($key, $singleMarker, $textMailSubject);
			}
			
			switch ($GLOBALS['TSFE']->sys_language_uid) {
				case 18 :
						$textMailContent = "InformaciÃ³n Interna - un nuevo registro en el siguiente evento 
                        Evento: ###EVENTTITLE### 
                        Evento - enlace: ###EVENTLINK### 
                        Evento - dÃ­a de comienzo: ###EVENTSTARTDATE### 
                        Evento - hora de comienzo: ###EVENTSTARTTIME### 
                        Evento - dÃ­a de finalizaciÃ³n: ###EVENTENDDATE### 
                        Evento - hora de finalizaciÃ³n: ###EVENTENDTIME### 

                        Nombre de usuario: ###USERNAME### 
                        Nombre: ###FIRSTNAME### 
                        Apellidos: ###LASTNAME### 
                        Titulo: ###TITLE### 
                        Sr.D./Sra.DÃ±a: ###GENDER### 
                        Departamento / Puesto: ###DEPARTMENT### 
                        Nombre de la CompaÃ±Ã­a: ###COMPANYNAME### 
                        TamaÃ±o de la CompaÃ±Ã­a: ###COMPANYSIZE### 
                        DirecciÃ³n: ###STREETADDRESS### 
                        CÃ³digo Postal: ###ZIP### 
                        Ciudad: ###CITY### 
                        PaÃ­s: ###COUNTRY### 
                        TelÃ©fono: ###PHONENUMBER### 
                        Email: ###EMAIL### 
                        CAD usato: ###USEDSOFTWARE###
                        Contact UID: ###CONTACTUID###
                        InformaciÃ³n adicional: 
						Email Newsletter Optin:  ###NEWSLETTER###   
                        ================ 
                        ###ADDITIONALINFORMATION### 
                        ================ 
                        Estoy de acuerdo con los tÃ©rminos y condiciones de Allplan!: ###PRIVACY### 
                        NÃºmero de cliente: ###CUSTOMERID### 
                        Ã�rea de especializaciÃ³n: ###PROFESSION### 
                        Por favor, pÃ³ngase en contacto conmigo por telÃ©fono si es necesario.: ###RECALL### 
                        "; 
					
					break;
				
				default:
					$textMailContent = "Internal Information about a new registration to the following event 
					Event: ###EVENTTITLE###
					Eventlink: ###EVENTLINK###
					Event start date: ###EVENTSTARTDATE###
					Event start time: ###EVENTSTARTTIME###
					Event end date: ###EVENTENDDATE###
					Event end time: ###EVENTENDTIME###
		
					User Name: ###USERNAME###
					First name: ###FIRSTNAME###
					Last name: ###LASTNAME###
					Title: ###TITLE###
					Salutation: ###GENDER###
					Department / Function: ###DEPARTMENT###
					Company name: ###COMPANYNAME###
					Company size: ###COMPANYSIZE###
					Address: ###STREETADDRESS###
					Zip code: ###ZIP###
					City: ###CITY###
					Country: ###COUNTRY###
					Phone: ###PHONENUMBER###
					Email: ###EMAIL###
					User CAD Software: ###USEDSOFTWARE###
                    Contact UID: ###CONTACTUID###
                    Email Newsletter Optin:  ###NEWSLETTER###   
					Further information:
					================
					###ADDITIONALINFORMATION###
					================
					I agree the Allplan Terms and condition!: ###PRIVACY###
					Customer ID: ###CUSTOMERID###
					Area of expertise: ###PROFESSION###
					Please contact me by phone if necessary.: ###RECALL###
					";
					break;
			}
			

			foreach($marker as $key => $singleMarker){
				$textMailContent = str_replace($key, $singleMarker, $textMailContent);
			}

			/** @var t3lib_mail_Message $htmlMailOrganizer */
			$htmlMailOrganizer = t3lib_div::makeInstance('t3lib_mail_Message');
			$htmlMailOrganizer->setFrom(
				array(
					$this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['confirmMail.']['fromEmail']
					=> $this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['confirmMail.']['fromName']
				)
			);
			$htmlMailOrganizer->setReturnPath($this->conf['view.']['event.']['module__displayregistrationform.']['emailOutput.']['confirmMail.']['fromEmail']);
			$htmlMailOrganizer->setTo(array($organizerEmail => ''));
			$htmlMailOrganizer->setSubject($textMailSubject);
			$htmlMailOrganizer->setBody(nl2br($textMailContent), 'text/html');
			$htmlMailOrganizer->addPart(strip_tags( $textMailContent), 'text/plain');
			$htmlMailOrganizer->send();

		}
	}



	function initEventWebservice(){
		require_once(t3lib_extMgm::extPath('nem_calwebservices').'/Service/NotesWrapper.php');
		if($this->extConf['caInfoFile']!='' && $this->extConf['caInfoFile']!='' && $this->extConf['caInfoFile']!=''){
			$certRequest = array(
			'cainfofile' => $this->extConf['caInfoFile'],
			'sslcertfile' => $this->extConf['sslCertFile'],
			'sslkeyfile' => $this->extConf['sslKeyFile']
			);
		} else {
			$certRequest = false;
		}
		if(strpos($this->extConf['pathToWSDL'], 'http')===0){
			$wsdl = $this->extConf['pathToWSDL'];
		} else if(strpos($this->extConf['pathToWSDL'], 'file')===0){
      		$wsdl = $this->extConf['pathToWSDL'];
		} else {
			$wsdl = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST').'/'.$this->extConf['pathToWSDL'];
		}
		return t3lib_div::makeInstance('Tx_NemCalwebservices_Service_NotesWrapper', $wsdl, $this->extConf['pathToWebService'], $this->extConf['namespace'], $certRequest);
	}

	/*
	* @description	get localized salutation
	* @param			mixed	$gender: string: male or female or int: 0 or 1
	* @return			string	localized salutation
	*/
	function getSalutation($gender) {
		if($gender === 'female' || intval($gender) === 1) {
			$content = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['fields.']['salutation.']['1.']['label'], $this->conf['view.']['event.']['module__displayregistrationform.']['fields.']['salutation.']['1.']['label.']).' '.$this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['fields.']['gender.']['options.']['2.']['label'], $this->conf['view.']['event.']['module__displayregistrationform.']['fields.']['gender.']['options.']['2.']['label.']);
		} else {
			$content = $this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['fields.']['salutation.']['0.']['label'], $this->conf['view.']['event.']['module__displayregistrationform.']['fields.']['salutation.']['0.']['label.']).' '.$this->cObj->cObjGetSingle($this->conf['view.']['event.']['module__displayregistrationform.']['fields.']['gender.']['options.']['1.']['label'], $this->conf['view.']['event.']['module__displayregistrationform.']['fields.']['gender.']['options.']['1.']['label.']);
		}
		return $content;
	}

	/*
	* @description		removes links from given string, but keeps href and linktext
	* @param			string	$string: some text with html links
	* @return			string	cleaned string
	*/
	function removeATagKeepURL($string){
		$replacedString = preg_replace('#<a href=("|\')?([^ "\']*)("|\')>([^<]*)</a>#i', '$4 $2', $string);
		if($replacedString === NULL) {
			return $string; 
		} else {
			return $replacedString;
		}
	}
	
	/**
	* Sync registered users
	*
	* @description		sync register count of webservice to Notes with typo3 database
	*					this is done in case someone deletes registrations in the webservice .. Outdated!!!
	* @return			void
	*/
	function syncRegistrationCount() {
        return ;

	}

	/*
	 * generate list of country options for a select box. uses static_info_tables
	 */
	function buildCountryOptions($fieldName, $fieldTS) {
		$table = 'static_countries';
		$lang = '';
		if($fieldTS['curCountry']) {
			$lang = $fieldTS['curCountry'];
		}
		$titleFields = tx_staticinfotables_div::getTCAlabelField($table, TRUE, $lang);
		$prefixedTitleFields = array();
		$prefixedTitleFields[] = $table.'.cn_iso_2';
		foreach ($titleFields as $titleField) {
			$prefixedTitleFields[] = $table.'.'.$titleField;
		}

		array_unique($prefixedTitleFields);
		$labelFields = implode(',', $prefixedTitleFields);
		$where = '1';

		if(isset($fieldTS['allowedCountries']) && !empty($fieldTS['allowedCountries'])) {
			$allowedCountries = explode(',', $fieldTS['allowedCountries']);
			if(!empty($allowedCountries)) {
				$allowedCountries = $GLOBALS['TYPO3_DB']->fullQuoteArray($allowedCountries, $table);
				$allowedCountries = implode(',', $allowedCountries);
				$where .= ' AND cn_iso_2 IN(' . $allowedCountries . ')';
			}
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$labelFields,
			$table,
			$where . $GLOBALS['TSFE']->sys_page->enableFields($table),
			'',
			$titleFields[0] . ' ASC'
		);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			foreach ($titleFields as $titleField) {
				if ($row[$titleField]) {
					$countries[$row['cn_iso_2']] = $GLOBALS['TSFE']->csConv($row[$titleField], $GLOBALS['TSFE']->renderCharset);
					break;
				}
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		$content = '<option value="">' .
			$this->cObj->cObjGetSingle($fieldTS['labelChoose'], $fieldTS['labelChoose.'])
			. '</option>';

		foreach($countries as $key => $value) {
			$selected = '';
			if($key === $this->getValue($fieldName) || ( $key === $fieldTS['curCountry'] )) {   
//			if($key === $this->getValue($fieldName) ) {										// actually We Do  prefill of Country by domainname
				$selected = 'selected="selected"';
			}
			$content .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
		}

		return $content;
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nem_calwebservices/Service/class.module_displayregistrationform.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nem_calwebservices/Service/class.module_displayregistrationform.php']);
}
?>