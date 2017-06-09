<?php
namespace JVE\jv_events\Tests\Unit\Service;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/*
* This class is a testcase for the Nem_Calwebservices Hook to Cal Extension : write to Sales Force
*/
// for developement : stop during any test with this line to get some debug infos ... 
//		$this->markTestSkipped(var_dump($test));		


class StoreToSalesforceRestAPI0Test extends \Tx_Phpunit_TestCase {

    /**
     * $settings
     * @var array $settings
      */
    public $sfSettings;

    /** @var  \JVE\JvEvents\Classes\Utility\SalesforceWrapper */
	public $sfConnect ;




    /**
	 * @setUp
	 * @author jVelletti
	 */
	public function setUp() {

        /** @var  \JVE\JvEvents\Classes\Utility\SalesforceWrapper */
		$this->sfConnect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JVE\\JvEvents\\Classes\\Utility\\SalesforceWrapper');

	}

	/**
	 * @testIfcanConnectToSalesForceRest
	 * @author jVelletti
	 */
	public function testIfcanConnectToSalesForceRest() {
        $test = $this->sfConnect->contactSF() ;

        $this->assertTrue($test['faultstring'] == "", 'Result should not contain Error but was : ' . var_export(  $test , true ));
        $this->markTestSkipped("test: " . var_export(  $test , true ) );
    }

    /**
     * @testIfcanCreateTWtrainingObject
     * @author jVelletti
     */
    public function testIfcanCreateTWtrainingObject() {
        $settings = $this->sfConnect->contactSF() ;
        $this->assertTrue($settings['faultstring'] == "", 'Result should not contain Error but was : ' . var_export(  $settings , true ));
        $data = array(
            'TW_TrainingWebinarName__c' => "test2Velletti-" .date( "d.m.Y H:i" , time()) ,

            // API Connect am alten Test system  ...
            'OwnerId' => "00520000002mxlK" ,

            // RecordTypeId has to be set, otherwise TW_WebinarKeyText will not be set in salesforce
            'RecordTypeId' => '01220000000cj8K' ,

            // Store Typo3 Event UID to SF
            'TW_UID__c' => 12345 ,
            'TW_Start_Time__c' =>  $this->sfConnect->convertTStampToSFdate( 1505347200 +  32400 ) ,
            'TW_End_Time__c' =>  $this->sfConnect->convertTStampToSFdate(  1505347200 +  36000 ) ,

            'TW_Description__c' =>  html_entity_decode( strip_tags( "das wäre die Beschreibung mit allem ") , ENT_COMPAT , 'UTF-8') ,
            // Schulungsanmeldungen wien
            'Marketing_Process__c' => "a1Tw0000001QUot" ,
        ) ;

        $url = $settings['SFREST']['instance_url'] . "/services/data/v20.0/sobjects/TW_TrainingWebinar__c/" ;
        $test = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "201" ,  $data , false , True )  ;
        $test = json_decode($test) ;

        $this->assertTrue($test->success , 'Result should have Success : ' . var_export(  $test , true ));
        $this->assertTrue($test->id != '' , 'Result should have ID : ' . var_export(  $test , true ));
        $this->assertTrue( count( $test->error ) == 0  , 'Result should have  no Error : ' . var_export(  $test , true ));

        $sessionData = array(
            'TW_TrainingWebinar__c' => $test->id ,
            'TW_Start_Time__c' => $data['TW_Start_Time__c'] ,
            'TW_End_Time__c' => $data['TW_End_Time__c'] ,
        ) ;

        $url = $settings['SFREST']['instance_url'] . "/services/data/v20.0/sobjects/TW_Session__c/" ;
        $test2 = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "201" ,  $sessionData , false , True )  ;

        $this->markTestSkipped("test2: " . var_export(  $test2 , true ) );
    }

    /**
     * @testIfcanCreateTWtrainingObject
     * @author jVelletti
     */
    public function testIfcanUpdateTWtrainingObject() {
        $settings = $this->sfConnect->contactSF() ;
        $this->assertTrue($settings['faultstring'] == "", 'Result should not contain Error but was : ' . var_export(  $settings , true ));
        $data = array(
            'TW_TrainingWebinarName__c' => "test 3 Velletti-" .date( "d.m.Y H:i" , time()) ,

            'OwnerId' => "005w0000003ewWz" ,

            // RecordTypeId has to be set, otherwise TW_WebinarKeyText will not be set in salesforce
            'RecordTypeId' => '01220000000cj8K' ,

            // Store Typo3 Event UID to SF
            'TW_UID__c' => 12345 ,
            'TW_Start_Time__c' =>  $this->sfConnect->convertTStampToSFdate( 1505347200 +  32400 ) ,
            'TW_End_Time__c' =>  $this->sfConnect->convertTStampToSFdate(  1505347200 +  36000 ) ,

            'TW_Description__c' =>  html_entity_decode( strip_tags( "das wäre die Beschreibung mit allem ") , ENT_COMPAT , 'UTF-8') ,
            // Schulungsanmeldungen wien
            'Marketing_Process__c' => "a1Tw0000001QUot" ,
        ) ;

        $url = $settings['SFREST']['instance_url'] . "/services/data/v20.0/sobjects/TW_TrainingWebinar__c/" ;
        $test = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "201" ,  $data , false , True )  ;
        $test = json_decode($test) ;

        $this->assertTrue($test->success , 'Result should have Success : ' . var_export(  $test , true ));
        $this->assertTrue($test->id != '' , 'Result should have ID : ' . var_export(  $test , true ));
        $this->assertTrue( count( $test->error ) == 0  , 'Result should have  no Error : ' . var_export(  $test , true ));

        $data['TW_TrainingWebinarName__c'] =  "testUpdate Velletti-" .date( "d.m.Y H:i" , time()) ;
        $data['TW_Description__c']          =  "testUpdate Description Velletti-" .date( "d.m.Y H:i" , time()) ;

        $url .=  $test->id ;
        $test2 = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "204" ,  $data , true , True )  ;
        $this->assertTrue($test2  == '204' , 'Result should be status 204  but was: ' . var_export(  $test , true ));




      //  $this->markTestSkipped("test 2: " . var_export(  $test2 , true ) );
    }


    /**
	 * @tearDown
	 * @author jVelletti
	 */
	protected function tearDown() {
		//  actually nothing  ???


	}


}

?>