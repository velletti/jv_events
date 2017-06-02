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
 * Class RegisterSalesforceSignal
 *
 * @package JVE\Jvevents\Signal
 */
class RegisterSalesforceSignal {

    /**
     * Initialize action settings
     * @return void
     */
    public function initializeAction()
    {
        // do we need this ??

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
        if ( $settings['EmConfiguration']['enableSalesForce'] < 1  || !is_object( $event->getOrganizer() ) ) {
            return ;
        }


        $debugmail = "\n+++++++++++ got this data from Controller ++++++++++++++++++\n"  ;
        $debugmail .= "\nRegistrants Email " .  $registrant->getEmail() .  ""  ;
        $debugmail .= "\nEvent Id: " .  $event->getUid() . " Date: " . $event->getStartDate()->format( "d.m.Y" )   . " | Citrix ID: " . $event->getCitrixUid()   ;
        $debugmail .= "\nTitle: " .  $event->getName()  ;

        $error = 2 ; // overwritten again if successfull ...
        $httpresponseErr = "" ;
        $httpresponseErrText = "" ;
        $data =  $this->convertToArray($registrant) ;


        // Subject
        $data['00N20000003aHBg']  =   $event->getName()  ;
        $data['00N20000003aeN4']  .=   "\n" . " ********************** " . $event->getStartDate()->format("D d.m.Y") . " - " ;




        $debugmail .= "\n+++++++++++ store in Salesforce as LEAD is active ++++++++++++++++++\n\n"  ;
        $debugmail .= var_export( $data , true ) ;
        $data['OwnerId'] =  $settings['register']['salesForce']['ownerId'] ;

        // read generic SaelsForce Owner ID (who is allowed to see the lead Data
        $data['oid']  = $settings['register']['salesForce']['oid'] ;

        if( $data['oid'] == '' ) {
            return ;
        }

        if(  is_object( $event->getOrganizer() )  ) {
            if (  strlen( $event->getOrganizer()->getSalesForceUserId())  > 12 ) {
                // overwrite it with value from organizer if it is defined and long enough to be a nearly valid SF ID (should be 16 or 19 digits ..
                $data['OwnerId']  = $event->getOrganizer()->getSalesForceUserId() ;

            }
            $data['00N20000003aeN4']  .=  "\n" . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( "tx_jvevents_domain_model_event.organizer" , 'JvEvents' ) . ": "
                                            . $event->getOrganizer()->getName() ;
            $data['00N20000003aeN4']  .= " \n(" . $event->getOrganizer()->getEmail() . ") " ;

        }
        if( is_object(  $event->getLocation() )  ) {
            $data['00N20000003aeN4']  .= " \n\n" . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( "tx_jvevents_domain_model_location" , 'JvEvents' ) .  ": "
                                            . $event->getLocation()->getZip() . " " . $event->getLocation()->getCity() . " \n" . $event->getLocation()->getStreetAndNr() ;
        }
        $data['00N20000003aeN4'] .= " \n\nTYPO3 Event " . $event->getUid() . " on Page: " . $event->getPid() ;

        // remove unwanted  Characters from Array ..
        $data =  $this->cleanArray($data) ;


        $data['debug']  = "1" ;
        $data['debugEmail']  = "web-admin@allplan.com" ;

        $data['retURL']  = $_SERVER['SERVER_NAME'];

        $data['00N20000003aeMQ'] = 'commercial';

        /*
        // ToDo if ($this->registrant->getIsStudent() === '1') {
        // ToDo     $data['00N20000003aeMQ'] = 'education';
        // ToDo } else {
           $data['00N20000003aeMQ'] = 'commercial';
        / / ToDo  }
        */

        if ( $event->getSalesForceEventId()  <> '' ) {
            // <!-- Feld fÃ¼r Webinar Key  VARIABLER WERT fÃ¼r jedes Webinar eindeutiger Wert-->
            $data['00N20000003acfo']  = $event->getSalesForceEventId()  ;
            $debugmail .= "\n00N20000003acfo: " . $data['00N20000003acfo'] ;

            // $data['recordType'] = "012W00000008aVq" ;
            // 27. Juni 2012 : nun doch der gleiche Record Type wie Citrix ....
            $data['recordType'] = "01220000000JRRJ" ;

        } else {
            // <!-- Feld fÃ¼r Webinar Key  VARIABLER WERT fÃ¼r jedes Webinar eindeutiger Wert-->
            if(  $event->getCitrixUid()  ) {
                $data['00N20000003acfo']  = $event->getCitrixUid()  ;
            }

            $debugmail .= "\n00N20000003acfo: " . $data['00N20000003acfo'] ;

            // <!-- Datensatztyp fÃ¼r CITRIX Webinare = FIXER WERT-->
            $data['recordType'] = "01220000000JRRJ" ;

        }
        if ( trim($event->getSalesForceRecordType())  <> '' ) {
            $data['recordType'] =  trim( $event->getSalesForceRecordType()) ;
            $debugmail .= "\nrecordType overwritten by Event: " . $data['recordType'] ;
        }

        $data['00N20000001KIHf']  =   $settings['register']['salesForce']['lang'] ;

        if( trim ( $event->getMarketingProcessId() ) != '' ) {
            $data['00N200000035nbU']  =   $event->getMarketingProcessId() ;
        }



        $URL =  $settings['register']['salesForce']['url'] ;

        //// ************************ TEST TO LIVE Salesforce ... BE CAREFULL *********************
       //  $URL = "https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8" ;
       //  $data['recordType'] = '01220000000JRRJ' ;
       //  $data['oid'] = '00D200000000ach' ;



        /// ************************ +++++++++++++++++++++++ -------------- #####################

        $debugmail .= "\n+++++++++++ store in Salesforce url: ++++++++++++++++++\n\n"  ;
        $debugmail .=  $URL ;
        if( $event->getCitrixUid()  ) {
            $data['webinar'] = $event->getCitrixUid() ;
        }


        if ( ( substr($_SERVER['SERVER_NAME'], -6 , 6 )  == ".local" && 1==1 )  || $settings['debug'] > 0 ) {
            echo "<hr>No transport to salesForce / Citrix on a local testserver or if Debug is  set a value > 0 .. if you want to test curl and see response also local, set debug to 2  !!! <pre>" ;
            echo $debugmail  ;
            var_dump($data) ;
            echo "</pre>" ;
            die;
        } else {
            $data = http_build_query($data) ;
            // $jsonheader = array ( "Accept: application/json" , "Content-type:application/json" , "Authorization: OAuth oauth_token=24d3492169e0b4920678e1e20c1db967" ) ;
            $jsonheader = array ( "Accept: application/json" , "Content-type:application/json" , "Authorization: OAuth oauth_token=" . $settings['register']['citrix']['orgAUTH'] ) ;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $URL ); // set the target url
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
            curl_close ($ch);

            $debugmail .= "\n+++++++++++ salesforce result: ++++++++++++++++++\n"  ;
            $debugmail .= "\nresponse: " . var_export( $result , true );

        }
        if ( $settings['debug'] > 1 || 2==1 ) {
            echo nl2br( $debugmail ) ;
            echo " Die in Line " . __LINE__ . " in File: " . __FILE__ ;
            Die ;
        }


    }

     /** convertToString
     *
      * Create a string response from registrant Model
     * @param \JVE\Jvevents\Domain\Model\Registrant $registrant
     * @return array
     */
    public function convertToArray(  $registrant ) {
        $jsonArray = array() ;
        $jsonArray['first_name'] = trim($registrant->getFirstName()) ;
        $jsonArray['last_name'] = trim($registrant->getLastName()) ;

        // ToDo Maybe need to create a kind fo mapping including translation ..
        $jsonArray['salutation'] = "Mrs." ;
        if( $registrant->getGender() == 1) {
            $jsonArray['salutation'] = "Mr." ;
        }

        $jsonArray['company'] = trim($registrant->getCompany()) ;
        $jsonArray['street'] = trim($registrant->getStreetAndNr() ) ;
        $jsonArray['zip'] = trim($registrant->getZip() ) ;
        $jsonArray['city'] = trim($registrant->getCity() ) ;
        $jsonArray['00N20000001JKql'] = trim($registrant->getCountry() ) ;
        $jsonArray['phone'] = trim($registrant->getPhone() ) ;
        $jsonArray['email'] = trim($registrant->getEmail() ) ;
        $jsonArray['00N20000003aHBb'] = trim($registrant->getCustomerId() ) ;
        $jsonArray['00N20000003aceq'] = trim($registrant->getContactId()  ) ;

        $jsonArray['00N20000003aeM'] = trim($registrant->getDepartment()  ) ;
        $jsonArray['00N20000001Jy59'] = trim($registrant->getProfession()  ) ;

        $jsonArray['00N20000003aeN4'] = trim($registrant->getAdditionalInfo() ) ;

        // used Software ...
        // $jsonArray['00N20000001Jy8z'] = trim($registrant->getAdditionalInfo() ) ;

        // Registration for Newsletter
        // $jsonArray['00N20000003aHCA'] = trim($registrant->get .. ) ;
        // Nuber of registerd Persons
        // $jsonArray['00N200000011kGJ'] = trim($registrant->get ... ) ;

        //


        return $jsonArray  ;
    }

    /** convertToString
     *
     * Create a string response from registrant Model
     * @param array $data
     * @return array
     */
    public function cleanArray(  $data ) {
        // remove some special Chars from input fields before sending it to salesforce
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