<?php
namespace JVE\JvEvents\Signal;
/***************************************************************
 * Copyright notice
 *
 * (c) 2017 -  jörg Velletti <jVelletti@allplan.com>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Class RegisterHubspotSignal
 *
 * @package JVE\Jvevents\Signal
 */
class RegisterHubspotSignal {


    /** @var \Allplan\Library\Hubspot\Service\Hubspot */
    public $hubspotApi ;

    /**
     * Initialize action settings
     * @return void
     */
    public function __construct()
    {
        $env = $_SERVER['NEM_HUBSPOT']['env'] ;
        $config = $_SERVER['NEM_HUBSPOT'][$env] ;
        if( !array_key_exists( "portalID" , $config) || !array_key_exists( "formID" , $config) || !array_key_exists( "hapikey" , $config) || !array_key_exists( "uri" , $config)  ) {
            $this->logToFile( " \n no hapikey,  portalID, FirmID or URI set ! See : ../conf/AllplanHubspotConfiguration.php OR Typoscript Settings -> register -> hubspot: " . var_export( $config , true ))  ;
            return ;
        }
        $this->hubspotApi = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("Allplan\\Library\\Hubspot\\Service\\Hubspot" , $config);
    }

    /**
     * create action settings
     *
     * @param \JVE\Jvevents\Domain\Model\Registrant $registrant
     * @param \JVE\Jvevents\Domain\Model\Event $event
     * @param array $settings
     *
     * @return void
     */
    public function createAction($registrant, $event ,  $settings)
    {

        if ( !is_object( $event ))  {
            $this->logToFile( "\n\n ### ERROR ### In RegisterHubspotSignal - event is not an Object!: " . var_export($event , true )  );
            return;
        }
        $debugmail = "\n+++++++++++ got this data from Controller ++++++++++++++++++\n"  ;
        $debugmail .= "\nRunning on Server: " .  $_SERVER['HOSTNAME'] .  " - "  . php_uname() ;
        $debugmail .= "\nRegistrants Email " .  $registrant->getEmail() .  ""  ;
        $debugmail .= "\nEvent Id: " .  $event->getUid() . " Date: " . $event->getStartDate()->format( "d.m.Y" )   . " | Salesforce Campaign ID: " . $event->getSalesForceCampaignId()   ;
        $debugmail .= "\nExtension Manager: enableHubspot: " .   $settings['EmConfiguration']['enableHubspot'] . " Stroe In Hubspot: " . $event->getStoreInHubspot()  ;
        $debugmail .= "\nTitle: " .  $event->getName()  ;
       // echo $debugmail ;
       // die;

        if ( $settings['EmConfiguration']['enableHubspot'] < 1  || !is_object( $event->getOrganizer() ) || $event->getStoreInHubspot() < 1 )  {
            $this->logToFile( "\n\n ### ERROR ### In RegisterHubspotSignal - Registrant : " . $registrant->getEmail()
                . "\n EmConf Enable enableHubspot: " . $settings['EmConfiguration']['enableHubspot']
                . "\n Event: " . $event->getUid() . " - Store in Hubspot: " . $event->getStoreInHubspot() );

            return;

        }


        $httpresponseErr = "" ;
        $httpresponseErrText = "" ;
        unset( $data) ;
        $data =  $this->convertToArray($registrant , $event->getSysLanguageUid() ) ;


        // Subject
        $data['typo3_event_id']  =   $event->getUid()  ;
        $data['event_date']  =   $event->getStartDate()->format( "d.m.Y" )  ;

        $data['comment']  .=   "\n" . " ********************** " . $event->getStartDate()->format("D d.m.Y") . " - " ;




        $debugmail .= "\n+++++++++++ store in Hubspot as LEAD is active ++++++++++++++++++\n\n"  ;




        if(  is_object( $event->getOrganizer() )  ) {
            if (  strlen( $event->getOrganizer()->getSalesForceUserId2())  > 10 ) {
                // overwrite it with value from organizer if it is defined and long enough to be a nearly valid SF ID (should be 16 or 19 digits ..
                $data['comment']  .=  "\n SF Owner: " . $event->getOrganizer()->getSalesForceUserId2() . " " .  $event->getOrganizer()->getName() ;
                $debugmail .= "\nField : SF OwnerId is taken from  getOrganizer()getSalesForceUserId2 =" . $data['OwnerId'] . " | Org: " . $event->getOrganizer()->getSalesForceUserOrg() ;
            }
            $data['comment']  .=  "\n" . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( "tx_jvevents_domain_model_event.organizer" , 'JvEvents' ) . ": "
                . $event->getOrganizer()->getName() ;
            $data['comment']  .= " \n(" . $event->getOrganizer()->getEmail() . ") " ;

        }
        if( is_object(  $event->getLocation() )  ) {
            $data['comment']  .= " \n\n" . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( "tx_jvevents_domain_model_location" , 'JvEvents' ) .  ": "
                . $event->getLocation()->getZip() . " " . $event->getLocation()->getCity() . " \n" . $event->getLocation()->getStreetAndNr() ;
        }
        $data['comment'] .= " \n\nTYPO3 Event " . $event->getUid() . " on Page: " . $event->getPid() ;
        $data['comment'] .= "\n**********************************\n" . $registrant->getAdditionalInfo() ;
        // remove unwanted  Characters from Array ..
        $data =  $this->cleanArray($data) ;

        // problem from 12.6.:
        if( strlen($data['comment'] )  > 800 ) {
            $debugmail .= "\n \n ###### ERROR !!! ##### Field Additional Info is more than 800 chars !!" ;
            $data['comment']  = substr( $data['comment']  , 0 , 900 ) ;
            $error = true ;
        }

        // $data['00N20000003aeMQ'] = 'commercial';

        if ( $event->getSalesForceCampaignId()  <> '' ) {
            // <!-- Feld fÃ¼r Webinar Key  VARIABLER WERT fÃ¼r jedes Webinar eindeutiger Wert-->
           // $data['sf_campaign_id']  = $event->getSalesForceCampaignId()  ;
            $data['sfdc_campaign_id']  =   $event->getSalesForceCampaignId()  ;
            $debugmail .= "\nField : sfdc_campaign_id  = " . $data['sfdc_campaign_id'] ;

        }

        $data['language']  =   $settings['lang'] ;
        $data['is_hidden']  =   $registrant->getHidden() ;
        $data['is_confirmed']  =   $registrant->getConfirmed() ;

        $hubspotutk      = $_COOKIE['hubspotutk']; //grab the cookie from the visitors browser.
        $ip_addr         = $_SERVER['REMOTE_ADDR']; //IP address too.
        $hs_context      = array(
            'hutk' => $hubspotutk,
            'ipAddress' => $ip_addr,
            'pageUrl' => 'https://www.allplan.com/index.php?id=' . $event->getRegistrationFormPid() . "&tx_jvevents_events[event]=" . $event->getUid() . "&L=" . $event->getSysLanguageUid() . "&tx_jvevents_events[action]=new&tx_jvevents_events[controller]=Registrant",
            'pageName' =>  $event->getName()
        );
        $data['hs_context'] = $hs_context ;

        $debugmail .= "\ndata Array : \n\n"  ;
        $debugmail .= var_export( $data , true ) ;

        $formId = $this->hubspotApi->getConfigConnectionFormID() ;
        if( $settings['register']['hubspot']['formId']) {
            $formId = $settings['register']['hubspot']['formId'] ;
        }
        /// ************************ +++++++++++++++++++++++ -------------- #####################
        $form = $this->hubspotApi->getForm($formId) ;
        $formFields = $this->hubspotApi->getFieldnamesFromForm( $form['data']['formFieldGroups']) ;

        $debugmail .= "\n+++++++++++ store in Hubspot url: ++++++++++++++++++\n\n"  ;
        $debugmail .=  $this->hubspotApi->getConfigConnectionUri() ;
        $debugmail .=  $formId ;


        // $settings['debug'] = 1 ;

        if (   $settings['debug'] == 1 ) {
            echo "<hr>No transport to Hubspot if Debug is  set a value > 0 .. if you want to test curl and see response also local, set debug to 2  !!! <pre>" ;
            echo $debugmail  ;
            echo "<hr>" ;
            var_dump($data) ;
            echo "</pre>" ;
            die;
        } else {
            $response = $this->hubspotApi->submitForm( $formId , $data ) ;
            $debugmail .= "\n+++++++++++  Hubspot response: ++++++++++++++++++\n\n"  ;
            $debugmail .= var_export($response , true ) ;
        }
        if ( $settings['debug'] > 1 ) {
            echo " Settings debug > 2 .. so we Die() in Line " . __LINE__ . " in File: " . __FILE__ . "<hr>";
            echo nl2br( $debugmail ) ;
            die ;
        }
        /** @var \TYPO3\CMS\Core\Mail\MailMessage $Typo3_v6mail */
        $Typo3_v6mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
        $Typo3_v6mail->setFrom( array( 'www@systems.allplan.com' => $_SERVER['SERVER_NAME'] ) );
        $Typo3_v6mail->setReturnPath( 'www@systems.allplan.com' );

        $Typo3_v6mail->setTo(
            array(
                'jvelletti@allplan.com' =>  '',
                'pbenke@allplan.com' => '',
            )
        );
        if( $response['error'] ) {
            $Typo3_v6mail->setSubject( "[ERROR] JV Events Registration - " . $event->getStartDate()->format("d.m.Y") . " - " . $event->getName()  );
        } else {
            $Typo3_v6mail->setSubject( "JV Events Registration Debug - " . $event->getStartDate()->format("d.m.Y") . " - " . $event->getName()  );
        }

        $Typo3_v6mail->setBody(nl2br( $debugmail ) , 'text/html'  );
        $Typo3_v6mail->send();


        $this->logToFile( $debugmail , $event->getPid() , $error )  ;
        return $response  ;
    }
    private function logToFile( $text , $pid = 0 , $error = 0 ) {

        $insertFields = array(
            "action"  => 1 ,
            "tablename" => "tx_jvevents_domain_model_registrant" ,
            "error" => $error ,
            "event_pid" => $pid ,
            "details" => "Event registration sent to Hubspot " ,
            "tstamp" => time() ,
            "type" => 1 ,
            "message" => $text ,

        ) ;

        $GLOBALS['TYPO3_DB']->exec_INSERTquery("sys_log" , $insertFields ) ;

    }
    /** convertToString
     *
     * Create a string response from registrant Model
     * @param \JVE\Jvevents\Domain\Model\Registrant $registrant
     * @return array
     */
    public function convertToArray(  $registrant , $lng = 0) {
        $jsonArray = array() ;
        $jsonArray['firstname'] = trim($registrant->getFirstName()) ;
        $jsonArray['lastname'] = trim($registrant->getLastName()) ;

        $jsonArray['gender'] = $registrant->getGender() ;
        // ToDo Maybe need to create a kind fo mapping including translation ..



        $jsonArray['salutation'] = "Mrs." ;
        if( $registrant->getGender() == 1) {
            $jsonArray['salutation'] = "Mr." ;
        }

        $jsonArray['company'] = trim($registrant->getCompany()) ;
        $jsonArray['address'] = trim($registrant->getStreetAndNr() ) ;
        $jsonArray['zip'] = trim($registrant->getZip() ) ;
        $jsonArray['city'] = trim($registrant->getCity() ) ;
        $jsonArray['laenderkennzeichen__c'] = trim($registrant->getCountry() ) ;
        $jsonArray['phone'] = trim($registrant->getPhone() ) ;
        $jsonArray['email'] = trim($registrant->getEmail() ) ;
        $jsonArray['invoice_company_name'] = trim($registrant->getCompany2() ) ;
        $jsonArray['invoice_street_address'] = trim($registrant->getStreetAndNr2()) ;
        $jsonArray['invoice_postal_code'] = trim($registrant->getZip2()) ;
        $jsonArray['invoice_city'] = trim($registrant->getCity2() ) ;
        $jsonArray['customerno__c'] = trim($registrant->getCustomerId() ) ;

      //  $jsonArray['invoice_email'] = ''  ;
      //  $jsonArray['industry'] = ''  ;
      //  $jsonArray['title'] = ''  ;


        $jsonArray['profession'] = trim($registrant->getProfession()  ) ;

        $jsonArray['comment'] = trim($registrant->getAdditionalInfo() ) ;



        return $jsonArray  ;
    }

    /** convertToString
     *
     * Create a string response from registrant Model
     * @param array $data
     * @return array
     */
    public function cleanArray(  $data ) {
        // remove some special Chars from input fields before sending it to Hubspot
        $badStrings = array("|" , "'" , "&" , "+" ,  "/" , "?" , "!" , '"'  , "," , ';'  ) ;
        $goodStrings = array(" " , " " , " " , " " ,  " " , " " , " " , ' ' , " " , ' '  ) ;

        foreach ( $data as $key => $value ) {
            $data[$key ]  =  str_replace( $badStrings, $goodStrings , $value ) ;
            if ( trim( $data[$key ]  ) == "") {
                unset($data[$key ] ) ;
            }
        }

        return $data ;
    }

}