<?php
namespace JVelletti\JvEvents\Utility;
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
use Allplan\Library\Marketo\Service\Marketo;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository;
use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Registrant;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RegisterMarketoEvent
 *
 * @package JVelletti\JvEvents\EventListener
 */
class RegistermarketoUtility {


    /** @var \Allplan\Library\Marketo\Service\Marketo|null  */
    public $marketoApi ;

    /**
     * Initialize action settings
     * @return void
     */
    public function __construct()
    {
        if( !is_array($_SERVER) || !array_key_exists("NEM_MARKETO" , $_SERVER ) || !array_key_exists("env" , $_SERVER["NEM_MARKETO"] )) {
            return ;
        }
        $env = $_SERVER['NEM_MARKETO']['env'] ;
        $config = $_SERVER['NEM_MARKETO'][$env] ;
        if( !is_array($config) || !array_key_exists( "client_id" , $config) || !array_key_exists( "formID" , $config) || !array_key_exists( "client_secret" , $config) || !array_key_exists( "uri" , $config)  ) {
            $this->logToFile( " \n no client_id,  client_secret , FormID or URI set ! See : ../conf/AllplanMarketoConfiguration.php OR Typoscript Settings -> register -> hubspot: " . var_export( $config , true ))  ;
            return ;
        }
        if( class_exists('Allplan\Library\Marketo\Service\Marketo')) {
            $this->marketoApi = GeneralUtility::makeInstance(Marketo::class , $config);
        }
    }

    /**
     * create action settings
     *
     * @param Registrant $registrant
     * @param Event $event
     * @param array $settings
     *
     * @return mixed
     */
    public function createAction($registrant, $event ,  $settings)
    {
        if( !class_exists('Allplan\Library\Marketo\Service\Marketo') || ! $this->marketoApi ) {
            return "Missing Marketo API Class" ;
        }
        $error = 0 ;
        if ( !is_object( $event ))  {
            $this->logToFile( "\n\n ### ERROR ### In RegisterMarketoSignal - event is not an Object!: " . var_export($event , true )  );
            return "No Event Object" ;
        }
        $debugmail = "\n+++++++++++ got this data from Controller ++++++++++++++++++\n"  ;
        $debugmail .= "\nRunning on Server: " .  $_SERVER['HOSTNAME'] .  " - "  . php_uname() ;
        $debugmail .= "\nRegistrants Email " .  $registrant->getEmail() .  ""  ;
        $debugmail .= "\nEvent Id: " .  $event->getUid() . " Date: " . $event->getStartDate()->format( "d.m.Y" )   . " | Salesforce Campaign ID: " . $event->getSalesForceCampaignId()   ;
        $debugmail .= "\nExtension Manager: enableMarketo: " .   $settings['EmConfiguration']['enableMarketo'] . " Stroe In Marketo: " . $event->getStoreInMarketo()  ;
        $debugmail .= "\nTitle: " .  $event->getName()  ;

        if ( $settings['EmConfiguration']['enableMarketo'] < 1  || !is_object( $event->getOrganizer() )
            || $event->getStoreInMarketo() < 1 || strlen( $event->getSalesForceCampaignId() < 2) )  {
            $this->logToFile( "\n\n ### ERROR ### In RegisterMarketoSignal - Registrant : " . $registrant->getEmail()
                . "\n EmConf Enable enableMarketo: " . $settings['EmConfiguration']['enableMarketo']
                . "\n Event: " . $event->getUid() . " - Store in Marketo: " . $event->getStoreInMarketo() );

            return "No ORGANIZER or no Marketo enabled" ;
        }

        $httpresponseErr = "" ;
        $httpresponseErrText = "" ;

        unset( $data) ;
        $data =  $this->convertToArray($registrant , $event) ;

        $data["message"] .= "\n\n" . $this->addEventInfoToMessage( $event ) ;
        if( strlen($data['message'] )  > 800 ) {
            $data['message']  = substr( $data['message']  , 0 , 800 ) ;
        }


        $data['LanguageCode__c']  =   $settings['lang'] ?? "DEU";
        // if AUT, set it to DEU as Marketo does not know AUT or DEA 
        $data['LanguageCode__c']  =   $settings['lang'] != "AUT" ? $settings['lang']  :  "DEU";

        if ( $event->getPrice() > 0 ) {
            $data['category']  =   'Training' ;
        } else {
            $data['category']  =   'Event' ;
        }


        $debugmail .= "\n+++++++++++ store in Marketo as LEAD is active ++++++++++++++++++\n\n"  ;

        $formId = $this->marketoApi->getConfigConnectionFormID() ;
        if( $settings['register']['marketo']['formId']) {
            $formId = $settings['register']['marketo']['formId'] ;
        }

        $workFlowId = 1018 ;
        if( $settings['register']['marketo']['workFlowId']) {
            $workFlowId = (int)$settings['register']['marketo']['workFlowId'] ;
        }
        // $settings['debug'] = 2 ;

        $visitorData = [
            'pageURL' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
            'queryString' => GeneralUtility::getIndpEnv('QUERY_STRING'),
            'userAgentString' => GeneralUtility::getIndpEnv('HTTP_USER_AGENT')
        ] ;

        if (isset($_COOKIE['_mkto_trk'])) {
            $visitorData['cookie'] =  $_COOKIE['_mkto_trk'];
        }

        $debugmail .= "\ndata Array : \n\n"  ;
        $debugmail .= var_export( $data , true ) ;
        $debugmail .= "\nVisitorData Array : \n\n"  ;
        $debugmail .= var_export( $visitorData , true ) ;


        if (   $settings['debug'] == 1 ) {
            echo "<hr>No transport to Marketo if Debug is  set a value > 0 .. if you want to test curl and see response also local, set debug to 2  !!! <pre>" ;
            echo $debugmail  ;
            echo "<hr>" ;
            var_dump($data) ;
            echo "</pre>" ;
            die;
        } else {
            try {
                $debugmail .= "\n+++++++++++  Send to Marketo " .   $this->marketoApi->getConfigConnectionUri() .  " | Form  $formId and Workflow : $workFlowId ++++++++++++++++++\n\n"  ;
                $response = $this->marketoApi->submitForm(   $data , $visitorData , $formId , $workFlowId) ;
                $debugmail .= "\n+++++++++++  Marketo response is stored in hubspot response : ++++++++++++++++++\n\n"  ;
                $debugmail .= var_export($response , true ) ;
                $registrant->setMarketoResponse($response['status']) ;
            } catch ( \Exception $e) {
                $debugmail .= "\n+++++++++++  Exception in Marketo !! " .$e->getMessage() . " ++++++++++++++++++\n\n"  ;
                $response['error']  = true ;
            }
        }
        if ( $settings['debug'] > 1 ) {
            echo " Settings debug > 2 .. so we Die() in Line " . __LINE__ . " in File: " . __FILE__ . "<hr>";
            echo nl2br( $debugmail ) ;
            die ;
        }
        /** @var MailMessage $Typo3_v6mail */
        $Typo3_v6mail = GeneralUtility::makeInstance(MailMessage::class);
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
        $Typo3_v6mail->html( nl2br( $debugmail )  , 'utf-8'  );
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
            "details" => "Event registration sent to Marketo " ,
            "tstamp" => time() ,
            "type" => 1 ,
            "message" => $text ,

        ) ;
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('sys_log') ;

        $queryBuilder->insert('sys_log')->values($insertFields)->executeStatement()  ;

    }

    public function addEventInfoToMessage( $event ) {
        $addMessage .= " \n\nTYPO3 Event " . $event->getUid() . " on Page: " . $event->getPid() ;
        if(  is_object( $event->getOrganizer() )  ) {
            if (  strlen( $event->getOrganizer()->getSalesForceUserId2())  > 10 ) {
                // overwrite it with value from organizer if it is defined and long enough to be a nearly valid SF ID (should be 16 or 19 digits ..
                $addMessage  .=  "\n SF Owner: " . $event->getOrganizer()->getSalesForceUserId2() . " " .  $event->getOrganizer()->getName() ;
            }
            $addMessage  .=  "\n" . LocalizationUtility::translate( "tx_jvevents_domain_model_event.organizer" , 'JvEvents' ) . ": "
               . $event->getOrganizer()->getName() ;
            $addMessage  .= " \n(" . $event->getOrganizer()->getEmail() . ") " ;

        }
        if( is_object(  $event->getLocation() )  ) {
            $addMessage  .= " \n\n" . LocalizationUtility::translate( "tx_jvevents_domain_model_location" , 'JvEvents' ) .  ": "
               . $event->getLocation()->getZip() . " " . $event->getLocation()->getCity() . " \n" . $event->getLocation()->getStreetAndNr() ;
        }

        return $addMessage ;
    }
    /** convertToString
     *
     * Create a string response from registrant Model
     * @param Registrant $registrant
     * @return array
     */
    public function convertToArray(  $registrant , $event) {

        /* +++++++++++++++++++++ Step 1 map object to array +++++++++++++++++++++ */
        $data =[];
        if ( $event->getSalesForceCampaignId()  <> '' ) {
            $data['campaign']  =   $event->getSalesForceCampaignId()  ;
        }
        $data['Typo3EventID_mkto']  =   $event->getUid()  ;
        $data['InboundActionName_mkto'] = substr( $event->getName() , 0 , 80 ) ;
        $data['IsWaitListed_mkto']  =   $registrant->getConfirmed() ? '' : 'yes' ;


        $data['firstName'] = trim($registrant->getFirstName()) ;
        $data['lastName'] = trim($registrant->getLastName()) ;

        $jsonArray['salutation'] = "" ;
        $data['CountryCode'] =strtoupper( $registrant->getCountry() ) ;

        // Currently we skip this CustomerSegmentWebsiteL__c
        // $data['CustomerSegmentWebsiteL__c'] = trim($registrant->getProfession()  ) ;
        /*
        if( $registrant->getGender() == 1) {
            $data['salutation'] = "Mr." ;
        }
        if( $registrant->getGender() == 2) {
            $data['salutation'] = "Mrs." ;
        }
        */

        $data['company'] = trim($registrant->getCompany()) ;
        $data['address'] = trim($registrant->getStreetAndNr() ) ;
        $data['postalCode'] = trim($registrant->getZip() ) ;
        $data['city'] = trim($registrant->getCity() ) ;


        /** @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository $countries */
        $countries =  GeneralUtility::makeInstance(CountryRepository::class);
        /** @var \SJBR\StaticInfoTables\Domain\Model\Country $cn_short_en */
        $cn_short_en = $countries->findOneByIsoCodeA2( trim($registrant->getCountry() ) ) ;
        if( is_object($cn_short_en) ) {
            $data['country']  = $cn_short_en->getShortNameEn() ;
        }
        $data['phone'] = trim($registrant->getPhone() ) ;

        /* +++++++++++++++++++++ Step 2  clean up the array +++++++++++++++++++++ */

        $data =  $this->cleanArray($data) ;


        $data['email'] = trim($registrant->getEmail() ) ;

        // now replace a "+" in email to html entity as "test+1234@domain.com"  is url decoded to "test 1234@domain.com"
        $data['email'] = str_replace( "+" , "%2B" , $data['email'] ) ;




        /* +++++++++++++++++++++ Step 2 add on info as Message +++++++++++++++++++++ */
        $data['message'] = trim($registrant->getAdditionalInfo() ) .  "\n\n"  ;

        // add a second address if it is set. as there are no own Fields, use message field
        if( $registrant->getCompany2() ) {
            $data['message'] .=  "\n" . trim($registrant->getCompany2() ) . "\n";
        }
        if( $registrant->getStreetAndNr2() ) {
            $data['message'] .=  "\n" . trim($registrant->getStreetAndNr2() ) . "\n";
        }
        if ( $registrant->getZip2() ) {
            $data['message'] .= "\n" . trim($registrant->getZip2()) . " " .  trim($registrant->getCity2() )  ;
        } elseif ( $registrant->getCity2() ) {
            $data['message'] .= "\n" . trim($registrant->getCity2() ) ;
        }
        if( $registrant->getCustomerId() ) {
            $data['message'] .= "\nCustomer Id: " . trim($registrant->getCustomerId() ) ;
        }

        // maybe not needed but was for hubspot
        str_replace("&" , "+" , $data['message'] ) ;


        return $data  ;
    }

    /** convertToString
     *
     * Create a string response from registrant Model
     * @param array $data
     * @return array
     */
    public function cleanArray(  $data ) {
        // remove some special Chars from input fields before sending it to Marketo
        $badStrings = array("|" , "'" , "&" , "+" ,  "/" , "?" , "!" , '"'  , "," , ';'  ) ;
        $goodStrings = array(" " , " " , " " , "%2B" ,  " " , " " , " " , ' ' , " " , ' '  ) ;

        foreach ( $data as $key => $value ) {
            $data[$key ]  =  str_replace( $badStrings, $goodStrings , $value ) ;
            if ( trim( $data[$key ]  ) == "") {
                unset($data[$key ] ) ;
            }
        }

        return $data ;
    }

}