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

use JVE\JvEvents\Utility\SlugUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProcessDatamap {

	protected $webserviceErrors;
	protected $pObj;
	protected $table;
	protected $status;
	protected $id;
	protected $deleted;
	protected $fieldArray;

	/** @var  array */
	protected $flashMessage = array() ;

    /** @var  \JVE\JvEvents\Utility\SalesforceWrapperUtility
     */
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
        $this->extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class) ->get('jv_events');

        $this->main();
	}
 //   public function processDatamap_beforeStart($pObj ) {
 //
 //   }

	public function main() {

		if ($this->table == 'tx_jvevents_domain_model_event') { //do only things when we are in the  event table

			/** @var  \JVE\JvEvents\Domain\Repository\EventRepository $objectManager */
			$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Object\\ObjectManager") ;

			/** @var  \JVE\JvEvents\Domain\Repository\EventRepository $eventRepository */
			$this->eventRepository = $this->objectManager->get('JVE\\JvEvents\\Domain\\Repository\\EventRepository');


			/** @var  \JVE\JvEvents\Domain\Model\Event $event */
			$this->event = $this->eventRepository->findByUidAllpages(intval($this->id), FALSE ) ;

			$allowedError = 0 ;
            $configuration = \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();
           // $this->flashMessage['INFO'][] = 'config:  !' . var_export( $configuration , true ) ;

			if( is_object( $this->event ) ) {



			    // remove unwanted Chars from Text Initial we start with removign ETX = end of text
			    $search[] = chr(3) ;
			    $replace[] = '' ;
                $this->event->setDescription( str_replace($search , $replace , $this->event->getDescription())) ;

                    $row['name'] =  $this->event->getName() ;
                    $row['pid'] =  $this->event->getPid() ;
                    $row['parentpid'] =  1 ;
                    $row['uid'] =  $this->event->getUid() ;
                    $row['sys_language_uid'] =  $this->event->getSysLanguageUid() ;
                    $row['slug'] =  $this->event->getSlug() ;

                    $slugGenerationDateFormat = "d-m-Y" ;
                    if( is_array( $this->extConf) and array_key_exists( "slugGenerationDateFormat" , $this->extConf )) {
                        $slugGenerationDateFormat =  $this->extConf['slugGenerationDateFormat'] ;
                    }
                    $row['start_date'] =  $this->event->getStartDate()->format($slugGenerationDateFormat) ;
                    $slug = SlugUtility::getSlug("tx_jvevents_domain_model_event", "slug", $row  )  ;
                    $this->event->setSlug( $slug ) ;


                if ($this->event->getEventType() == 0 ) {
                    // this uses an external page for details, not the internal Registration , detail View etc. so no checks possible
                    // Also we will remove possible false settings
                    if(
                        $this->event->getWithRegistration() ||
                        $this->event->getRegistrationPid() ||
                        $this->event->getRegistrationFormPid() ||
                        $this->event->getNeedToConfirm() ||
                        $this->event->getNotifyOrganizer() ||
                        $this->event->getNotifyRegistrant() )
                    {
                        $this->flashMessage['WARNING'][] = 'We disable all fields related to internal registration! !' ;

                    }

                    $this->event->setWithRegistration(0) ;
                    $this->event->setRegistrationPid(0);
                    $this->event->setRegistrationFormPid(0);
                    $this->event->setNeedToConfirm( 0 ) ;
                    $this->event->setNotifyOrganizer( 0 ) ;
                    $this->event->setNotifyRegistrant( 0 ) ;

                } else {
                    if( $this->event->getUrl() != '' ) {
                        $this->event->setUrl('') ;
                        $this->flashMessage['WARNING'][] = 'You have changed the EventType from "Link" to "internal Event". To correct this, the info in field URL was removed!' ;
                    }

                    if ($this->event->getNotifyRegistrant() ==  0 && $this->event->getNeedToConfirm() == 1 ) {
                        $this->event->setNeedToConfirm( 0 ) ;
                        $this->flashMessage['WARNING'][] = 'You can not set Need to confirm without sending Email to the participant !' ;
                        $allowedError ++ ;
                    }

                    $ST = $this->event->getStartDate() ;
                    $ST = $ST->modify( "+" . intval($this->event->getStartTime() ) . " second" ) ;

                    if ( is_integer($this->event->getAccessStarttime())) {
                        /** @var \DateTime $accessStart */
                        $accessStart = new \DateTime(  ) ;
                        $accessStart->setTimestamp($this->event->getAccessStarttime()) ;
                    } else {
                        $accessStart = $this->event->getAccessStarttime() ;
                    }

                    if ($accessStart  >  ( $ST )) {
                        $this->flashMessage['WARNING'][] = 'Event visibility Start date was after Start Date: ' .  $ST->format("d.m.Y - H:i") ;
                        $this->event->setAccessStarttime( $ST) ;
                    }
                    if ( is_integer($this->event->getAccessEndtime())) {
                        /** @var \DateTime $accessStart */
                        $accessEnd = new \DateTime(  ) ;
                        $accessEnd->setTimestamp($this->event->getAccessEndtime()) ;
                    } else {
                        $accessEnd = $this->event->getAccessEndtime() ;
                    }
                    if( $accessEnd ) {
                        if ( $accessEnd->getTimestamp() < time() && $accessEnd->getTimestamp() > 0 ) {
                            $this->flashMessage['WARNING'][] = 'Event visibility END date is in the past: ' .  $accessEnd->format("d.m.Y - H:i") ;
                        }
                    }


                    if ($this->event->getWithRegistration()   ) {

                        /* ++++++++++++++++++    Part One : Test Registration Dates / Time +++++++++++++++++++++++++++++  */

                        if (! $this->event->getRegistrationUntil() || is_integer( $this->event->getRegistrationUntil()) ) {
                            $this->flashMessage['ERROR'][] = 'Registration Until date is not set! ' ;
                            $this->event->setWithRegistration(0) ;
                            $allowedError ++ ;
                        } else {
                            if( $this->event->getRegistrationUntil() instanceof \DateTime ) {
                                if ($this->event->getRegistrationUntil()->getTimestamp()  < time()  ) {
                                    $this->flashMessage['WARNING'][] = 'Registration Until date is in the Past! Registration is not possible' ;
                                }
                            }

                        }

                        if ($this->event->getRegistrationUntil()  >  ( $ST )) {
                            if( $this->event->getRegistrationUntil() && $this->event->getStartDate() ) {
                                $this->flashMessage['WARNING'][] = 'Registration Until date ' . $this->event->getRegistrationUntil()->format("d.m.Y H:i") .  ' is after Event Start Date! ' . $ST->format("d.m.Y H:i:s") ;
                            } else {
                                $this->flashMessage['WARNING'][] = 'Registration Until date is after Event Start Date! StartDate is not set now!' ;
                            }
                        }

                        if( intval( $this->event->getRegistrationFormPid() ) < 1 ) {
                            $this->flashMessage['WARNING'][] = 'You must select the Page with the Registration Form!' ;

                        }
                        if( intval( $this->event->getRegistrationPid() ) < 1 ) {
                            $this->flashMessage['WARNING'][] = 'You must select the Page where the Registrations should be stored! (actual Pid was set)' ;
                            $this->event->setRegistrationPid( $this->event->getPid()) ;
                        }

                    } else {
                        if (trim($this->event->getRegistrationUrl()) == '' || !$this->event->getRegistrationUrl() ) {
                            $this->flashMessage['INFO'][] = 'Registration URL is not set "' .  $this->event->getRegistrationUrl() . '". Registration is not Possible.' ;
                        } else {
                            if (! $this->event->getRegistrationUntil() ) {
                                $this->flashMessage['ERROR'][] = 'Registration Until date is not set!' ;
                                $allowedError ++ ;
                            } else {
                                if( $this->event->getRegistrationUntil() instanceof \DateTime ) {
                                    if ($this->event->getRegistrationUntil()->getTimestamp()  < time()  ) {
                                        $this->flashMessage['ERROR'][] = 'Registration Until date is in the Past!' ;
                                    }
                                }
                            }
                        }
                        /* ++++++++++++++++++    Part One V2 : Test Registration Dates / Time +++++++++++++++++++++++++++++  */

                        if ($this->event->getRegistrationUntil()  >  ( $ST )) {
                            if( $this->event->getRegistrationUntil() && $this->event->getStartDate() ) {
                                $this->flashMessage['WARNING'][] = 'Registration Until date ' . $this->event->getRegistrationUntil()->format("d.m.Y H:i") .  ' is after Event Start Date! ' . $ST->format("d.m.Y - H:i") ;
                            } else {
                                $this->flashMessage['WARNING'][] = 'Registration Until date is after Event Start Date!' ;
                            }
                        }
                        // echo "this->event->getAccessStarttime(): " . $this->event->getAccessStarttime() ;

                    }

                    // IMPORTANT: without this modification startDate  will have still added the start time !
                    $ST = $ST->modify( "-" . intval($this->event->getStartTime() ) . " second" ) ;
                    unset($ST) ;



                    /* ++++++++++++++++++    Part TWO : Test if important fields are filled  +++++++++++++++++++++++++++++  */

                    if( !$this->event->getOrganizer()) {
                        $this->flashMessage['ERROR'][] = 'No organizer selected in Tab Relations!' ;
                        $allowedError ++ ;
                    }
                    if( !$this->event->getLocation()) {
                        $this->flashMessage['ERROR'][] = 'No Location selected in Tab Relations!' ;
                        $allowedError ++ ;
                    }
                    if( count( $this->event->getEventCategory()) < 1 ) {
                        $this->flashMessage['WARNING'][] = 'No Event Category selected in Tab Relations. This event may not be found in lists!' ;
                    }
                    if( count(  $this->event->getTags() ) < 1 ) {
                        $this->flashMessage['WARNING'][] = 'No Event Tags selected in Tab Relations!' ;
                    }

                    if ($allowedError >  0 ) {
                        // $this->event->setWithRegistration(FALSE ) ;
                        $this->flashMessage['NOTICE'][] = $allowedError .' AllowedErrors found! We do not store to external Systems like Salesforce etc,!' ;

                    } else {
                        // j.v. : Kopiert von altem Plugin: wenn Store in Citrix "An" Ist, das Event NICHT als "TW Webinar /Event " nach Salesforce Schreiben !
                        if( $this->event->getStoreInHubspot() ) {
                            if(  $this->event->getStoreInSalesForce() ) {
                                $allowedError ++ ;
                                $this->flashMessage['WARNING'][] = 'You can not set "Store in Salesforce" together with "Store in Hubspot"! (store in salesforce is disabled)!' ;

                                $this->event->setStoreInSalesForce(0) ;
                            }
                            $this->flashMessage['NOTICE'][] = '"Store in Hubspot" should run now!' ;

                            $this->createUpdateEventForSF2019()  ;
                        } else {
                            if( $this->event->getStoreInSalesForce() ) {
                                if( $this->event->getStoreInCitrix() ) {
                                    $this->flashMessage['WARNING'][] = 'You can not set "Store in Salesforce" together with "Store in Citrix"! (store in salesforce is disabled)!' ;
                                    $this->event->setStoreInSalesForce(0) ;
                                    $allowedError ++ ;
                                } else {


                                    if( $configuration['enableSalesForceLightning'] == 1 ) {

                                        if( $this->event->getStartDate()->format("Ymd") > date("Ymd") && ! $this->event->getHidden() ) {

                                            $this->flashMessage['WARNING'][] = 'You can not set "Store in Salesforce" anymore! "Store in Hubspot" is now enabled' ;
                                            $this->event->setStoreInSalesForce(0) ;
                                            $this->event->setStoreInHubspot(1) ;
                                            $this->createUpdateEventForSF2019()  ;


                                            // Automatic Migration 2019 : Update registrants that they have to be moved to hubspot

                                            /** @var  \JVE\JvEvents\Domain\Repository\RegistrantRepository $registrantRepository */
                                            $registrantRepository = $this->objectManager->get('JVE\\JvEvents\\Domain\\Repository\\RegistrantRepository');

                                            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $registrants */
                                            $registrants = $registrantRepository->findByFilter('' , $this->event->getUid(), 0 , array() , 999 ) ;
                                            $repairCount = 0 ;
                                            if ( $registrants->count() > 0 ) {
                                                /** @var  \JVE\JvEvents\Domain\Model\Registrant $registrant */
                                                $registrant = $registrants->getFirst() ;
                                                $pid = $registrant->getPid() ;
                                                while ( $repairCount < $registrants->count()  ){
                                                    if ( !is_object($registrant)) {
                                                        break ;
                                                    }
                                                    $repairCount++ ;
                                                    $registrant->setHubspotResponse("100") ;
                                                    $registrantRepository->update($registrant ) ;
                                                    $registrant = $registrants->next() ;
                                                }
                                                if( $repairCount ) {
                                                    $this->flashMessage['WARNING'][] = 'We Found ' .  $repairCount . " exisiting Registration(s). Please go to PageID : " . $pid . ", use Module: Event Mngt, filter by Event and send Registration to Hubspot!";
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
                if ( $configuration['enableHubspot'] && ! $this->event->getStoreInHubspot() && $this->event->getWithRegistration() ) {
                    $this->flashMessage['WARNING'][] = 'You should activate:  ' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:jv_events/Resources/Private/Language/locallang_db.xlf:tx_jvevents_domain_model_event.store_in_hubspot', 'JvEvents', NULL);;
                }




                $this->eventRepository->update($this->event) ;

                /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
                $persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
                $persistenceManager->persistAll() ;

                if ( !key_exists( 'WARNING' , $this->flashMessage ) && !key_exists( 'ERROR' , $this->flashMessage ) ) {
                    $this->flashMessage['OK'][] = 'All syntax checks (V1.2) run successfull and the event was stored!' ;
                }
			} else {
                $this->flashMessage['WARNING'][] = 'Hook ProcessDatamap is active but found no Event Object! Maybe this is a disabled event ??' ;
            }

			$this->showFlashMessage();
		}
	}



    /*  Create or Update a campaign and needed campaign memberStatus +++++++++++++++++  Salesforce ++++++++++++++++++
    *   New since May 2019 needs enableHubspot in extension Configuration (emConf) reads also enableSalesForceLightning
    */

    private function createUpdateEventForSF2019() {
        $configuration = \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();

        /** @var  \JVE\JvEvents\Utility\SalesforceWrapperUtility */
        $this->sfConnect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JVE\\JvEvents\\Utility\\SalesforceWrapperUtility');

        $settings = $this->sfConnect->contactSF() ;
    //    $this->flashMessage['NOTICE'][] = 'Salesforce: Connect result ! : ' . var_export( $settings , true ) ;
        if( $settings['faultstring'] ) {
            $settings['SFREST']['SF_PASSWORD'] = substr( $settings['SFREST']['SF_PASSWORD'] , 0 , 3 ) . "...." . substr( $settings['SFREST']['SF_PASSWORD'] , 8 , 99 ) ;
            $this->flashMessage['ERROR'][] = 'Create Update Campaign in Salesforce: could not Connect ! : ' . var_export( $settings , true ) ;
            return ;
        }

        /// ++++++++++++  first we genereate the data Array
        $start = new \DateTime( $this->event->getStartDate()->format("c")) ;
        $end = new \DateTime( $this->event->getStartDate()->format("c")) ;
        $end = $end->modify("+30 day") ;
        // OR $date->add(new DateInterval('P30D'));
        $startD =  $this->sfConnect->convertDate2SFdate( $start ,  $this->event->getStartTime() ) ;

        $endD =  $this->sfConnect->convertDate2SFdate( $end ,  $this->event->getEndTime() ) ;
        // $this->flashMessage['NOTICE'][] = 'StartDate Converted for SalesForce to ! : ' . $startD ;


        $SummerTime = new \DateTime( $start->format("Y-M-d" ) . ' Europe/Berlin');
       // $this->flashMessage['NOTICE'][] = "Date Formated : " . $start->format("d-M-Y" ) . ' as NEW Date for SummerTime ! : ' . $SummerTime->format( "d.m.Y H:i:s (I) ") ;



        $data = array(
            'IsActive' => true,
            'Description' =>  "TYPO3 Event: " . $this->event->getUid() . " on PID: " . $this->event->getPid() . " \n\n"
                . $this->event->getStartDate()->format("d.m.Y") . " - " . date( "H:i" ,  $this->event->getStartTime() )  . " \n"

                . html_entity_decode( strip_tags( $this->event->getDescription() ) , ENT_COMPAT , 'UTF-8') ,

            'EndDate' =>  $endD ,
            'StartDate' =>  $startD   ,
            'Typo3Id__c' =>  $this->event->getUid()    ,

            'AllplanOrganization__c' =>  '300'   , // will be overwritten later

        ) ;

        if ( $this->event->getPrice() > 0 ) {
            $data['Type']  = 'Training' ;
        }


        if(  is_object( $this->event->getOrganizer() ) ) {
            $owner = str_replace( " " , "_" , substr( trim( strip_tags ( $this->event->getOrganizer()->getName())) , 0, 10 ) ) ;
            if( $this->event->getOrganizer()->getSalesForceUserOrg() ) {
                $data['AllplanOrganization__c']  =   $this->event->getOrganizer()->getSalesForceUserOrg() ;  ;
            }
            $data['OwnerId']  =    trim( $this->event->getOrganizer()->getSalesForceUserId2())  ;
        } else {
            $this->flashMessage['ERROR'][] = 'Store in Salesforce: No Organizer set in Relations ! : '  ;
        }



        // +++ generate the name of the Campaign should be like DE-2019-05 Organizer_name city_name And Now Event Titel
        // Organizer_name and City is cut to 10 chars
        if ( !$owner ) {
            $owner = "NoOrgName" ;
        }


        if(  is_object( $this->event->getLocation() ) ) {
            $cntry = $this->event->getLocation()->getCountry() ;
            $locCity = str_replace( " " , "_" , substr( trim( strip_tags ($this->event->getLocation()->getCity())) , 0, 10 ) ) ;
        }
        if ( !$cntry ) {
            $cntry = "XX" ;
        }
        if ( !$locCity ) {
            $locCity = "NonameCity" ;
        }
        $campaignName = $cntry . "-" . $this->event->getStartDate()->format("Y-m-") . $owner. "-" . $locCity . "-" .  strip_tags ( $this->event->getName() ) ;
        $data['Name'] = substr( html_entity_decode(  $campaignName  , ENT_COMPAT , 'UTF-8') , 0 , 80 ) ;





        /*
        * May 2019: as we need this new feature BEFORE we need to know if we talk to NEW or  OLD Salesforce
        */
        if ( $_SERVER['SERVER_NAME'] == "www.allplan.com.ddev.local") {
            $data['OwnerId']  =   $settings['SFREST']['id'] ;
            $this->flashMessage['NOTICE'][] = 'On www.allplan.com.ddev.local  OwernId is always set to: ' . $data['OwnerId'] ;

        }
        if( !$configuration['enableSalesForceLightning']  ) {
            unset( $data['AllplanOrganization__c']) ;
            unset( $data['Typo3Id__c']) ;
            unset( $data['ListPricePerCampaignMember__c']) ;
            $data['Type'] = "Event" ;
          //  $data['OwnerId']  =    trim( $this->event->getOrganizer()->getSalesForceUserId())  ;
            // During Pre Sunrise period: we need to put here hardcoded the User Id of Linda Graf
            $data['OwnerId']  =    "005w0000007HsMd"  ;


            $this->flashMessage['NOTICE'][] = 'enableSalesForceLightning is not activated! we unset AllplanOrganization__c and Type and OwernId is set to: ' .
             $data['OwnerId'] ;

        }
        $data['ExtendMemberAccess__c'] = true ;

        // a possible fallback during tests :
        //    $data['OwnerId']  = "0051w000000rsfAAAQ" ;  // that is the ID (on DEV !) of the user: allplan-dev-api@allplan.com

        if( $data['OwnerId']  == "" ) {
            $this->flashMessage['ERROR'][] = 'Store in Salesforce: No Salesforce User ID set in Organizer Data ! : '  ;
            return ;
        }

        $this->flashMessage['NOTICE'][] = 'Data  : ' . var_export( $data , true ) ;


        if( $this->event->getSalesForceCampaignId() ) {
            // Update
            $url = $settings['SFREST']['instance_url'] . "/services/data/v48.0/sobjects/Campaign/" . $this->event->getSalesForceCampaignId() ;
            $sfResponse = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "204" ,  $data , true  , false )  ;
            if( is_int( $sfResponse ) && $sfResponse == 204 ) {
                $this->flashMessage['OK'][] = "Campaign was updated in Salesforce: ! : " . $settings['SFREST']['instance_url'] . "/" . $this->event->getSalesForceCampaignId()   ;
            } else {
                $this->flashMessage['WARNING'][] = 'Could not update Response  : ' . var_export( $sfResponse , true ) ;
                $sfResponse = json_decode($sfResponse) ;
            }

        } else {
            // Insert

            $data['Status']  =  'ongoing'   ;  // see https://doc.allplan.com/display/SFDOC/Event+Registration+Mapping+Tables
            // and https://doc.allplan.com/display/SFDOC/Event+Campaign+Definition maybe  replaced by "ongoing"

            $data['Type'] =  'Outbound - Event'   ;
            $data['ListPricePerCampaignMember__c'] =  $this->event->getPrice() ;


            $url = $settings['SFREST']['instance_url'] . "/services/data/v48.0/sobjects/Campaign/" ;
            $sfResponse = $this->sfConnect->getCurl($url , $settings['SFREST']['access_token'] , "201" ,  $data , false , false )  ;
            $this->flashMessage['NOTICE'][] = 'Store Campaign in Salesforce: ' .var_export( $sfResponse , true )  ;
            $sfResponse = json_decode($sfResponse) ;

            if( is_object($sfResponse)) {
                if ($sfResponse->success && $sfResponse->id) {
                    $this->flashMessage['OK'][] = "Campaign created in Salesforce: ! : " . $settings['SFREST']['instance_url'] . "/" . $sfResponse->id   ;

                    $this->event->setSalesForceCampaignId($sfResponse->id);


                    // every campaign needs a set of campaignmemberStati
                    // see : https://doc.allplan.com/display/SFDOC/Event+Campaign+Definition
                    // first remove automatically created stati then add our set of stati ..
                    $this->renameCampaignMemberStati( $settings['SFREST']['instance_url'] , $settings['SFREST']['access_token'] ,  $sfResponse->id ) ;

                    $url = $settings['SFREST']['instance_url'] . "/services/data/v48.0/sobjects/CampaignMemberStatus/" ;
                    $this->createCampaignMemberStati( $url , $settings['SFREST']['access_token'] ,  $sfResponse->id ) ;

                    /** @var \TYPO3\CMS\Core\Mail\MailMessage $Typo3_v6mail */
                    $Typo3_v6mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
                    $Typo3_v6mail->setFrom( array( 'www@systems.allplan.com' => $_SERVER['SERVER_NAME'] ) );
                    $Typo3_v6mail->setReturnPath( 'www@systems.allplan.com' );

                    $Typo3_v6mail->setTo(
                        array(
                            'jvelletti@allplan.com' =>  'Joerg V',
                            'lgraf@allplan.com' => 'Linda G',
                        )
                    );
                    /** @var Typo3Version $tt */
                    $tt = GeneralUtility::makeInstance( \TYPO3\CMS\Core\Information\Typo3Version::class ) ;

                    if( $tt->getMajorVersion()  < 10 ) {
                        $Typo3_v6mail->setBody(nl2br( $settings['SFREST']['instance_url'] . "/" . $sfResponse->id . "\n\n\n" . var_export($data , true  )  ) , 'text/html'  );
                    } else {
                        $Typo3_v6mail->html(nl2br( $settings['SFREST']['instance_url'] . "/" . $sfResponse->id . "\n\n\n" . var_export($data , true  )  ) , 'utf-8'  );
                    }

                    $Typo3_v6mail->setSubject( "[JV Events] Campaign created  - " .  $data['Name']  );
                      $Typo3_v6mail->send();
                    $this->flashMessage['NOTICE'][] = 'send Info Email to : ' .var_export( $Typo3_v6mail->getTo()  , true )  ;
                } else {
                    if ( substr( $sfResponse , 0 , 6 ) == "Error" ) {
                        $this->flashMessage['ERROR'][] = "Could not create Campaign in Salesforce: ! : " . var_export( $sfResponse , true )  ;
                    }
                }
            } else {
                if ( substr( $sfResponse , 0 , 6 ) == "Error" ) {
                    $this->flashMessage['ERROR'][] = "Could not create Campaign in Salesforce: ! : " . var_export( $sfResponse , true )  ;
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

    private function renameCampaignMemberStati( $url , $access_token , $sfCampaignId ) {
        $data['what'] = ", Label,HasResponded,IsDefault ";
        $data['from'] = "CampaignMemberStatus" ;
        $data['where']= " CampaignId = '" . $sfCampaignId . "'" ;

        $sfResponse = $this->sfConnect->getData($url , $access_token ,   $data  )  ;
        if ( is_array($sfResponse) ) {
            if  ( array_key_exists( "records" , $sfResponse['result'] )) {
                if ( count ( $sfResponse['result']['records']) > 0 ) {
                    foreach ($sfResponse['result']['records'] as $record ) {
                        $this->flashMessage['NOTICE'][] = 'Campaign Member Stati found : ' . var_export($record, true)  ;
                        if ( $record['IsDefault']) {
                            $record['Label'] = 'Registered' ;
                            $record['SortOrder'] = 1 ;
                        }
                        if ( $record['HasResponded']) {
                            $record['Label'] = 'Attended' ;
                            $record['SortOrder'] = 2 ;
                        }
                        $id = $record['Id'] ;
                        unset ( $record['Id'] ) ;

                        $upDate = $this->sfConnect->getCurl($url . "/services/data/v48.0/sobjects/CampaignMemberStatus/" . $id ,
                            $access_token , "204" ,  $record , true  , false )  ;
                        $this->flashMessage['NOTICE'][] = 'Campaign Member Stati rename : ' . $id . " to " . $record['Label'] . " => " . var_export($upDate , true );

                    }
                }
            }
        }

    }

    /**
     * @param $url
     * @param $access_token
     * @param $sfCampaignId
     */
    private function createCampaignMemberStati( $url , $access_token , $sfCampaignId ) {

        $dataMaster = array( 'Wait listed' , 'Confirmed' , 'No Show' , 'Cancelled' , 'Blocked from Email' ,  'Blocked by Sales' ) ;

        $status = "" ;
        foreach ( $dataMaster as $key => $datalabel ) {
            $data['Label'] = $datalabel  ;
            $data['CampaignId'] = $sfCampaignId  ;
            $data['HasResponded'] = false  ;
            $data['IsDefault'] = false  ;
            $data['SortOrder'] = $key + 3 ;
            $sfResponse = $this->sfConnect->getCurl($url , $access_token , "201" ,  $data , false , false )  ;
          //  $this->flashMessage['NOTICE'][] = 'Campaign Member Status "' . $data['Label'] . '" created : ' . var_export($sfResponse , true ) ;
            $sfResponse = json_decode($sfResponse) ;
            if( is_object($sfResponse)) {
                if ($sfResponse->success && $sfResponse->id) {
                    $status .= $data['Label'] . ", " ;
                }
            }
        }
        if ( $status ) {
            $this->flashMessage['OK'][] = 'Campaign Member Stati created : ' . $status ;
        } else {
            $this->flashMessage['WARNING'][] = 'NO Campaign Member Stati created ! ' .  var_export($sfResponse , true ) ;
        }

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
				// echo "admin: " . $this->pObj->admin . " Type : " . $typeInt ;
				// die;
				if(( $this->pObj->admin) || $typeInt > -1)
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


}

?>