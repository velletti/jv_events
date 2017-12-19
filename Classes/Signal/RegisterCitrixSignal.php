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
 * Class RegisterCitrixSignal
 *
 * @package JVE\Jvevents\Signal
 */
class RegisterCitrixSignal {

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
        if ( $event->getCitrixUid() == "" || $settings['EmConfiguration']['enableCitrix'] < 1 ) {
            return ;
        }

        if( $settings['register']['citrix']['username'] == '' ) {
            // ToDo: try to get orgID / orgAuth

        }
        if( $settings['register']['citrix']['orgID'] == '' ) {
            return ;
        }
        $debugmail = "\n+++++++++++ got this data from Controller ++++++++++++++++++\n"  ;
        $debugmail .= "\nRegistrants Email " .  $registrant->getEmail() .  ""  ;
        $debugmail .= "\nEvent Id: " .  $event->getUid() . " Date: " . $event->getStartDate()->format( "d.m.Y" )   . " | Citrix ID: " . $event->getCitrixUid()   ;
        $debugmailTitle = "Event Id: " .  $event->getUid() . " Date: " . $event->getStartDate()->format( "d.m.Y" )   . " | Citrix ID: " . $event->getCitrixUid()   ;
        $debugmail .= "\nTitle: " .  $event->getName()  ;
        $debugmailTitle .= "\ - Title: " .  $event->getName()  ;

        $error = 2 ; // overwritten again if successfull ...
        $httpresponseErr = "" ;
        $httpresponseErrText = "" ;
        $json =  $this->convertToJson($registrant) ;

        $debugmail .= "\n+++++++++++ store in citrix is active ++++++++++++++++++\n\n"  ;
        $debugmail .= var_export( $json , true ) ;

        // $citrixURL =  'https://api.citrixonline.com/G2W/rest/organizers/1465928619483499268/webinars/' . $event->getCitrixUid() . '/registrants?oauth_token=24d3492169e0b4920678e1e20c1db967' ;
        $citrixURL =  'https://api.getgo.com/G2W/rest/organizers/' . $settings['register']['citrix']['orgID']
            . '/webinars/' . $event->getCitrixUid()
            . '/registrants?oauth_token=' . $settings['register']['citrix']['orgAUTH'];
        $debugmail .= "\n+++++++++++ store in citrix url: ++++++++++++++++++\n\n"  ;
        $debugmail .=  $citrixURL ;

        $data['webinar'] = $event->getCitrixUid() ;
        $tag = "[CITRIX]" ;
        if ( substr($_SERVER['SERVER_NAME'], -6 , 6 )  == ".local"  || $settings['debug'] > 0 ) {
            echo "<hr>No transport to salesForce / Citrix on a local testserver or if Debug is  set a value > 0 .. if you want to test curl and see response also local, set debug to 2  !!! <pre>" ;
            echo $debugmail  ;
            // var_dump($settings) ;
            echo "</pre>" ;
            die;
        } else {
            // $jsonheader = array ( "Accept: application/json" , "Content-type:application/json" , "Authorization: OAuth oauth_token=24d3492169e0b4920678e1e20c1db967" ) ;
            $jsonheader = array ( "Accept: application/json" , "Content-type:application/json" , "Authorization: OAuth oauth_token=" . $settings['register']['citrix']['orgAUTH'] ) ;

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
                $tag = "[CITRIX-ERROR]" ;
                $httpresponseErr = $httpval[1] ;
                $httpresponseErrText = substr($result , 0 ,1800)  ;
                $debugmail .= "\n+++++++++++ citrix error: ++++++++++++++++++\n"  ;
                $debugmail .= "\nhttpresponseErrText: " . $httpresponseErrText ;


            }
            $debugmail .= "\n+++++++++++ citrix result: ++++++++++++++++++\n"  ;
            $debugmail .= "\nregistrantKey: " . $resultvals['registrantKey']  ;
            $debugmail .= "\nhttp header response: " . $httpval[1] ;
            $debugmail .= "\nErrorstatus: " . $error;

        }
        if ( $settings['debug'] > 1 ) {
            echo nl2br( $debugmail ) ;
            echo " Die in Line " . __LINE__ . " in File: " . __FILE__ ;
            Die ;
        }

        mail("jvelletti@allplan.com" , $tag  . $debugmailTitle , $debugmail ) ;

    }

     /** convertToJson
     *
      * Create a json response from registrant Model
     * @param \JVE\Jvevents\Domain\Model\Registrant $registrant
     * @return string
     */
    public function convertToJson(  $registrant ) {
        $jsonArray = array() ;
        $jsonArray['firstName'] = trim($registrant->getFirstName()) ;
        $jsonArray['lastName'] = trim($registrant->getLastName()) ;
        $jsonArray['organization'] = trim($registrant->getCompany()) ;
        $jsonArray['address'] = trim($registrant->getStreetAndNr() ) ;
        $jsonArray['zipCode'] = trim($registrant->getZip() ) ;
        $jsonArray['city'] = trim($registrant->getCity() ) ;
        $jsonArray['country'] = trim($registrant->getCountry() ) ;
        $jsonArray['phone'] = trim($registrant->getPhone() ) ;
        $jsonArray['email'] = trim($registrant->getEmail() ) ;
        $jsonArray['questionsAndComments'] = trim($registrant->getAdditionalInfo() ) ;

        // remove some special Chars from input fields before sending it to citrix
        $badStrings = array("|" , "'" , "&" , "+" , "\\" , "/" , "?" , "!" , '"'  , "," , ';'  ) ;
        $goodStrings = array(" " , " " , " " , " " , " " , " " , " " , " " , ' ' , " " , ' '  ) ;
        // ToDo: add this field to Registrant modell
        // $jsonArray['numberOfEmployees'] = trim($registrant->getCompanySize()) ;

        foreach ( $jsonArray as $key => $value ) {
            $jsonArray[$key ]  =  str_replace( $badStrings, $goodStrings , $value ) ;
            if ( trim( $jsonArray[$key ]  ) == "") {
                unset($jsonArray[$key ] ) ;
            }
        }

        return json_encode( $jsonArray ) ;
    }

}