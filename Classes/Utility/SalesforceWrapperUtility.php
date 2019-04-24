<?php
/**
 * Created by PhpStorm.
 * User: velletti
 * Date: 08.06.2017
 * Time: 11:12
 */

namespace JVE\JvEvents\Utility;


class SalesforceWrapperUtility
{
    /** @var  array */
    public $sfSettings ;

    /* @var boolean */
    public $debug=false  ;

    /** @var string */
    public $forceEnv ;

    public function _construct() {

    }


    /*  ############################    Helper Functions ###############################  */


    /**
     * @access   public
     */
    public function contactSF() {

        // ToDo read $env from TYPOScript constants

        switch ($_SERVER['SERVER_NAME']) {
            case "www.allplan.com" :
            case "www-typo3.allplan.com" :
                $env = "PROD" ;
                break ;
            case "www-stage.allplan.com" :
                $env = "STAGE" ;
                break ;
            default:
                $env = "DEV" ;
                break ;
        }

        // For some cases, we need to enforce the environment to f.e. "LOCAL" or "LIVE"
        if($this->forceEnv){
            $env =  $this->forceEnv ;
        }
        $this->sfSettings['ENV'] = $env ;

        if( $_SERVER['NEM_SALESFORCE'][$env]['uri'] == '' ) {
            return array( "faultstring" => 'Could not load SalesForce Settings from Env : ' . $env  ) ;
        }

        $this->sfSettings['SFREST']["LOGIN_URI"] = $_SERVER['NEM_SALESFORCE'][$env]['uri'];
        $this->sfSettings['SFREST']["fallbackAccountId"] = $_SERVER['NEM_SALESFORCE'][$env]['fallbackAccountId'];
        $this->sfSettings['SFREST']["FinalFallbackAccountId"] = $_SERVER['NEM_SALESFORCE'][$env]['FinalFallbackAccountId'];
        $this->sfSettings['SFREST']["CLIENT_ID"] = $_SERVER['NEM_SALESFORCE'][$env]['clientid'];
        $this->sfSettings['SFREST']["CLIENT_SECRET"] = $_SERVER['NEM_SALESFORCE'][$env]['clientsecret'];
        $this->sfSettings['SFREST']["SF_USERNAME"] = $_SERVER['NEM_SALESFORCE'][$env]['bn'];
        $this->sfSettings['SFREST']["SF_PASSWORD"] = $_SERVER['NEM_SALESFORCE'][$env]['pw'] . $_SERVER['NEM_SALESFORCE'][$env]['hash'] ;

        $this->sfSettings['SFREST']['instance_url'] = "";
        $this->sfSettings['SFREST']['access_token'] = "";
        $trys = 0;
        try {
            $token_url = $this->sfSettings['SFREST']["LOGIN_URI"] . "/services/oauth2/token";
            $params = "&grant_type=password"
                . "&client_id=" . $this->sfSettings['SFREST']["CLIENT_ID"]
                . "&client_secret=" . $this->sfSettings['SFREST']["CLIENT_SECRET"]
                . "&username=" . $this->sfSettings['SFREST']["SF_USERNAME"]
                . "&password=" . $this->sfSettings['SFREST']["SF_PASSWORD"];

            $curl = curl_init($token_url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            $curl = $this->setAdditionalCurlOptions($curl);

            $json_response = curl_exec($curl);

            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl) ;
            $curlErrorNo = curl_errno($curl) ;
            $error = "";
            if ($status != 200) {
                $error .= "Line " . __LINE__ . ":Error: call to token URL $token_url failed with status $status, response $json_response, curl_error " . $curlError . ", curl_errno " . $curlErrorNo ;
            }

            curl_close($curl);

            $response = json_decode($json_response , true);

            $this->sfSettings['SFREST']['access_token'] = $response['access_token'];
            $this->sfSettings['SFREST']['instance_url'] = $response['instance_url'];

            if (!isset($this->sfSettings['SFREST']['access_token']) || $this->sfSettings['SFREST']['access_token'] == "") {
                $error = "Line " . __LINE__ . ": Error - access token missing from response! ";
            }

            if (!isset($this->sfSettings['SFREST']['instance_url']) || $this->sfSettings['SFREST']['instance_url'] == "") {
                $error .= "Line " . __LINE__ . ":Error - instance URL missing from response!";
            }
            if ($error <> "") {
                if( array_key_exists( "error" , $response) && $response['error'] == "unknown_error" &&  $response['error_description'] == "retry your request" ) {

                    $this->sfSettings['SFREST']['faultstring'] = $error . "\n The Redirection to the correct Salesforce instance ( f.e. login.salesforce.com to eu3.salesforce.com did not work !! " ;
                    $this->sfSettings['SFREST']['faultstring'] = $error . "\n See: https://developer.salesforce.com/forums/?id=906F00000008tiFIAQ " ;
                }

                $this->sfSettings['SFREST']['faultstring'] = $error . " json_response: " .  var_export($json_response , true ) .  " * sfSettings: " . var_export($this->sfSettings , true );


            } else {
                return $this->sfSettings;
            }
        } catch (exception $err) {
            $trys++;
            if ($trys > 2) {
                return FALSE;
            }
        }
        return $this->sfSettings;
    }

    /**
     * Sets additional values for a curl resource
     * @author Peter Benke <pbenke@allplan.com>
     * @param resource $curl
     * @return resource
     */
    private function setAdditionalCurlOptions($curl){


        if(!empty($_SERVER['NEM_SALESFORCE'][$this->sfSettings['ENV']]['addtional_curl_settings'])){
            foreach($_SERVER['NEM_SALESFORCE'][$this->sfSettings['ENV']]['addtional_curl_settings'] as $option => $value){
                curl_setopt($curl, $option, $value);
            }
        }

        return $curl;

    }


    /**
     * @access   public
     * @param string $url Path to Salesforce
     * @param string $access_token from SF
     * @param string $test200  test if Status 200 or 204 or something else must be checked
     * @param array $valueArray false if only GET Request, As post request contains pairs: SF_Fieldnames => values
     * @param bool $update if TRUE update, if FALSE insert
     * @param bool $debug if true response debugstring ..
     * string $response   SalesForceDateTime
     */
    public function getCurl($url, $access_token, $test200 = '', $valueArray = FALSE, $update = TRUE, $debug = FALSE) {

        $curlDebug = "";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $curl = $this->setAdditionalCurlOptions($curl);

        if (is_array($valueArray)) {
            $curlDebug .= "valueArray = " . var_export($valueArray, true);

            if ($update == TRUE) {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
                $curlDebug .= " ***  is Update *** ";
            }
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($valueArray));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token",
                "Content-type: application/json"));

        $json_response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlDebug .= " *** Status: " . $status;
        $curlError = curl_error($curl) ;
        $curlErrorNo = curl_errno($curl) ;

        curl_close($curl);

        if ($test200 <> '') {
            if ($status != $test200) {
                return "Error: call to token URL $url failed with status $status, response $json_response, curl_error " . $curlError . ", curl_errno " . $curlErrorNo;
            } else {
                // success Full Insert row to database will give status 201 so wie need to return the created ID
                if ($test200 == "201") {
                    return $json_response;
                }
                return $status;
            }
        }
        if ($debug == TRUE) {
            return "Debug: call to token URL $url ends with status $status, response $json_response, curl_error " . $curlError . ", curl_errno " . $curlErrorNo . " curlDebug" . $curlDebug;
        }

        return $json_response;
    }


    /**
     * @param \DateTime $Date
     * @param integer $time
     * @return null|string
     */
    public function convertDate2SFdate( $start , $time ) {
        if( ! $start instanceof \DateTime ) {
            return NULL  ;
        }
        $timeZone = ".000+01:00" ;
        $timeString = date( "H:i:s" , $time ) ;

        // we need to create our Own DateTime Object from startDate with European TimeZone to have correct object to calculate daylight saving ..
        $SummerTime = new \DateTime( $start->format("Y-M-d" ) . ' Europe/Berlin');

        if ( $SummerTime->format( "I")  == "1" ) {
           $timeZone = ".000+02:00" ;
        }
        return  $start->format("Y-m-d" )   . "T" . $timeString  . $timeZone  ;

    }


    /**
     * @access   public
     * params integer $timestamp  unix time Stamp
     * return string
     */
    public function convertTStampToSFdate($timestamp) {
        if ($timestamp == 0 || $timestamp == '') {
            return "";
        }
        $date = new \DateTime();
        try {
            if (date("H:i:s", $timestamp) == "00:00:00") {
                $timestamp = $timestamp + 2 * 60 * 60;
            }
            $date->setTimestamp($timestamp);
            $timeZone = ".000+01:00" ;
            if ( date("I" , $timestamp ) == "1" ) {
                $timeZone = ".000+02:00" ;
            }
            $string = $date->format('Y-m-d') . "T" . $date->format('H:i:s') . $timeZone ;

        } catch (exception $err) {
            $string = '';
        }

        return $string;
    }
}