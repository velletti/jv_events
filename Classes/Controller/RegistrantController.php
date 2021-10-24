<?php
namespace JVE\JvEvents\Controller;

use JVE\JvEvents\Domain\Model\Event;
use JVE\JvEvents\Domain\Model\Registrant;
use JVE\JvEvents\Domain\Model\Subevent;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Jörg velletti <jVelletti@allplan.com>, Allplan GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 ***************************************************************/


/**
 * RegistrantController
 */
class RegistrantController extends BaseController
{
	
	
	/**
	 * action init
	 *
	 * @return void
	 */
	public function initializeAction()
	{
		if( !$this->request->hasArgument('event')) {
			// ToDo redirect to error
		}
		if( !$this->request->hasArgument('registrant')) {
			// ToDo redirect to error
		}
		parent::initializeAction() ;

	}
	
	
    /**
     * action list
     * @param Event $event
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     * @param string $hash
     * @return void
     */
    public function listAction(Event $event, $hash  )
    {
        // toDo add restrictions to listing ... only for admins or the organizer himself ..

        $doExport = 0 ;
        if( $this->request->hasArgument('export')) {
            $doExport = $this->request->getArgument('export') ;
        }
        $pid = 0 ;
        if( $this->request->hasArgument('pid')) {
            $pid = $this->request->getArgument('pid') ;
        }
        if ( $pid == 0 ) {
            $pid = $event->getRegistrationPid() ;
        }
        $registrants = array() ;

        $checkString =  $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate() ;
        $checkHash = hash("sha256" , $checkString ) ;


        if( $checkHash ==  $hash ) {
            $registrants = $this->registrantRepository->findByFilter('', $event->getUid(), $pid , $this->settings, 9999) ;


            if( $doExport == 1 ) {
                // csv Export
                $d = ";" ;
                // $d = chr(9);
                $eol = "\r\n" ;
                 $t = "" ;
                // $t = '"' ;
                $csvdata = $this->getCsvHeader( $d , $eol ,  $t ) ;
                foreach ($registrants as $registrant) {
                    $csvdata .= $this->getCsvValues($registrant ,$d , $eol ,  $t) ;
                }

                $csvdata =  pack("CCC", 0xef, 0xbb, 0xbf) . $csvdata ;
                header("content-type: application/csv-comma-delimited-table; Charset=utf-8");
                // header("content-type: application/csv-tab-delimited-table; Charset=utf-8");

                header("content-length: ".strlen($csvdata));
                header("content-disposition: attachment; filename=\"csv_export.csv\"");

                print $csvdata;
                die;
                // $this->redirect("list" , null , array( "event" => $event->getUid() , "hash" => $hash )) ;


                $this->addFlashMessage("Export done ") ;

            }
            $registered = 0 ;
            $waiting = 0 ;
            /** @var Registrant $registrant */
            foreach ($registrants as $registrant) {
                if(!$registrant->getHidden() ) {
                    $single = $this->getTotalPersonCount($registrant) ;
                    if( $registrant->getConfirmed() ) {
                        $registered = $registered + $single['total'] ;
                    }   else {
                        $waiting = $waiting +  $single['total'] ;
                    }

                }
            }
            if( $event->getRegisteredSeats() != $registered || $event->getUnconfirmedSeats() != $waiting ) {

                $this->addFlashMessage('Number of registrations was corrected from '
                    . $event->getRegisteredSeats() . " (+" . $event->getUnconfirmedSeats() . ") to : " . $registered . " (+" .  $waiting . ") registrations."
                    , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
                $event->setRegisteredSeats($registered) ;
                $event->setUnconfirmedSeats($waiting)  ;
                $this->eventRepository->update($event) ;
            }

            $this->view->assign('registrants', $registrants);
            $this->view->assign('hash', $hash);
            $error = false ;
        } else {
            $error = true ;
            $this->addFlashMessage("Error - Got wrong hash Code: " . $hash ) ;
        }
        $this->view->assign('error', $error);
        $this->view->assign('event', $event);
        $this->view->assign('registrants', $registrants);

    }

    private function getCsvLine( $d ,  $t , $value , $fields , $field , $all ) {
        if( $all || array_key_exists($field , $fields )) {
            return $d .  $t . '"' . $value . '"' ;
        }
        return '' ;
    }
    private function getCsvHeader( $d , $eol , $t ) {
        $all = true ;
        $fields = [] ;
        if( array_key_exists( 'allformFields' ,$this->settings['register'] )
            && is_array ( $this->settings['register']['allformFields'] )
            && count($this->settings['register']['allformFields'] )  > 0
        ) {
            $all = false ;
            $fields = $this->settings['register']['allformFields'] ;
        }

        // gender,streetAndNr,firstName,lastName,zip,city,phone,email,privacy,hotprice,more1,more2,more3,hidden,confirmed

        $return = $this->getCsvLine(  "",  $t , "hidden" , $fields , "hidden" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "Firstname" , $fields , "firstName" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "Lastname" , $fields , "lastName" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "Gender" , $fields , "gender" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "title" , $fields , "title" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "confirmed" , $fields , "confirmed" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "email" , $fields , "email" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "company" , $fields , "company" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "department" , $fields , "department" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "address" , $fields , "streetAndNr" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "zip" , $fields , "zip" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "city" , $fields , "city" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "Country" , $fields , "country" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "Language" , $fields , "language" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "phone" , $fields , "phone" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "profession" , $fields , "profession" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "customer_id" , $fields , "customerId" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "contact_id" , $fields , "contactId" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "company2" , $fields , "company2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "department2" , $fields , "department2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "address2" , $fields , "streetAndNr2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "zip2" , $fields , "zip2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "city2" , $fields , "city2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "Country2" , $fields , "country2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "More" , $fields , "more" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "More2" , $fields , "more2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "More3" , $fields , "more3" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , "Additional Info" , $fields , "additionalInfo" , $all ) ;
        return $return . $eol ;
    }

    /**
     * @param Registrant $registrant
     * @param $d
     * @param $eol
     * @param $t
     * @return string
     */
    private function getCsvValues( $registrant , $d , $eol , $t ) {

        $all = true ;
        $fields = [] ;
        if( array_key_exists( 'allformFields' ,$this->settings['register'] ) && is_array ( $this->settings['register']['allformFields'] )) {
            $all = false ;
            $fields = $this->settings['register']['allformFields'] ;
        }

        $gender = $this->translate("register_gender_female" ) ;
        if( $registrant->getGender() < 2 ) {
            $gender = $this->translate("register_gender_male" ) ;
        }
        $phone = " " . $registrant->getPhone() ;
        if ( str_replace( " " , "" , trim($phone )) == trim($phone) ) {
            $old = $phone ;
            $phone = " " . substr( $old , 0 , 2) . " " . substr( $old , 2 , 2 ) . " " . substr( $old , 4 , 2 ) . " " . substr( $old , 6 , 99 ) ;
        }

        $return = $this->getCsvLine(  "",  $t , $this->cleanString($registrant->getHidden() , $t , $d), $fields , "hidden" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString( $registrant->getFirstname() , $t , $d), $fields , "firstName" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , $this->cleanString($registrant->getLastName() , $t , $d), $fields , "lastName" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , $this->cleanString($gender , $t , $d ) , $fields , "gender" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , $this->cleanString($registrant->getTitle() , $t , $d) , $fields , "title" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , $this->cleanString($registrant->getConfirmed()  , $t , $d) , $fields , "confirmed" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getEmail()  , $t , $d) , $fields , "email" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , $this->cleanString($registrant->getCompany(), $t , $d) , $fields , "company" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t , $this->cleanString($registrant->getDepartment() , $t , $d) , $fields , "department" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getStreetAndNr(), $t , $d) , $fields , "streetAndNr" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString(" " . $registrant->getZip(), $t , $d) , $fields , "zip" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getCity() , $t , $d), $fields , "city" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getCountry(), $t , $d), $fields , "country" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getLanguage(), $t , $d) , $fields , "language" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($phone, $t , $d) , $fields , "phone" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getProfession() , $t , $d) , $fields , "profession" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getCustomerId(), $t , $d), $fields , "customerId" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getContactId(), $t , $d), $fields , "contactId" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getCompany2(), $t , $d) , $fields , "company2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getDepartment2(), $t , $d), $fields , "department2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getStreetAndNr2()  , $t , $d) , $fields , "streetAndNr2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString(" " . $registrant->getZip2(), $t , $d)  , $fields , "zip2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getCity2(), $t , $d)  , $fields , "city2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getCountry2(), $t , $d), $fields , "country2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getMore1(), $t , $d) , $fields , "more" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getMore2(), $t , $d) , $fields , "more2" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getMore3(), $t , $d) , $fields , "more3" , $all ) ;
        $return .= $this->getCsvLine(  $d ,  $t ,  $this->cleanString($registrant->getAdditionalInfo(), $t , $d) , $fields , "additionalInfo" , $all ) ;

        return $return . $eol ;
    }
    private function cleanString($string , $delim , $split ) {
        // $delim = substr($delim , 1, 1) ;
        if ( $delim == '"' ) {
            $replace = "'" ;
        } else {
            $replace = "`" ;
            if ( $delim == '' ) {
                if ( $split == ";") {
                    $delim = ';' ;
                    $replace = "," ;
                }
            }

        }

        $string =   str_replace("\n" , "   " , $string ) ;
        $string =   str_replace("\r" , "   " , $string ) ;
        return  str_replace($delim , $replace , $string ) ;
    }
    /**
     * action new
     *
     * @param Event $event
     * @param Registrant $registrant
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("registrant")
     * @return void
     */
    public function newAction(Event $event=null, Registrant $registrant=null)
    {
        $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();
        if (is_object($event)) {

            $this->settings['startReg'] = time();
            $tokenBase = "P" . $this->settings['pageId'] . "-L" . $this->settings['sys_language_uid'] . "-E" . $event->getUid();

            $this->settings['formToken'] = md5($tokenBase);
            $this->settings['filter']['startDate'] = time();
            // $this->settings['filter']['maxDays'] = 99999 ;

            // $this->settings['debug'] = 2 ;
            $otherEvents = false;
            if ($event->getEventCategory()) {
                $this->settings['filter']['skipEvent'] = $event->getUid();
                foreach ($event->getEventCategory() as $cat) {
                    if ($cat->getBlockRegistration()) {
                        $this->settings['filter']['categories'] = $cat->getUid();

                        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events */
                        $otherEvents = $this->eventRepository->findByFilter(false, false, $this->settings);

                    }
                }
            }


            $checkString = $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate();
            $checkHash = hash("sha256", $checkString);

            $this->settings['fe_user']['user'] = $GLOBALS['TSFE']->fe_user->user;
            $this->settings['fe_user']['organizer']['showTools'] = FALSE;

            if ($GLOBALS['TSFE']->fe_user->user) {
                $userUid = $GLOBALS['TSFE']->fe_user->user['uid'];
                if (is_object($event->getOrganizer())) {
                    $userAccessArr = \TYPO3\CMS\CORE\Utility\GeneralUtility::trimExplode(",", $event->getOrganizer()->getAccessUsers());
                    if (in_array($userUid, $userAccessArr)) {
                        $this->settings['fe_user']['organizer']['showTools'] = TRUE;
                    } else {
                        $usersGroups = \TYPO3\CMS\CORE\Utility\GeneralUtility::trimExplode(",", $GLOBALS['TSFE']->fe_user->user['usergroup']);
                        $OrganizerAccessToolsGroups = \TYPO3\CMS\CORE\Utility\GeneralUtility::trimExplode(",", $event->getOrganizer()->getAccessGroups());
                        foreach ($OrganizerAccessToolsGroups as $tempGroup) {
                            if (in_array($tempGroup, $usersGroups)) {
                                $this->settings['fe_user']['organizer']['showTools'] = TRUE;
                            }
                        }

                    }
                }
            }
            $mayEdit = false;
            if (is_object($registrant) && $registrant->getUid() > 0) {
                if (is_object($event) && is_object($event->getOrganizer())) {
                    if ($registrant->getEvent() == $event->getUid()) {
                        if ($this->isUserOrganizer()) {
                            if ($this->hasUserAccess($event->getOrganizer())) {
                                if ($this->request->hasArgument("hash")) {
                                    if ($this->request->getArgument("hash") == $checkHash) {
                                        $mayEdit = true;
                                        $registrant->setPrivacy("1");
                                    }

                                }
                            }
                        }
                    }
                }

                if (!$mayEdit) {
                    $registrant = null;
                }
            }


            if ($registrant == null) {
                /** @var Registrant $registrant */
                $registrant = $this->objectManager->get("JVE\\JvEvents\\Domain\\Model\\Registrant");
                if ($userUid) {
                    $registrant->setGender(intval($GLOBALS['TSFE']->fe_user->user['gender'] + 1));
                    $registrant->setFirstName($GLOBALS['TSFE']->fe_user->user['first_name']);
                    $registrant->setLastName($GLOBALS['TSFE']->fe_user->user['last_name']);
                    $registrant->setEmail($GLOBALS['TSFE']->fe_user->user['email']);
                    $registrant->setPhone($GLOBALS['TSFE']->fe_user->user['telephone']);
                    $registrant->setTitle($GLOBALS['TSFE']->fe_user->user['title']);
                    $registrant->setCity($GLOBALS['TSFE']->fe_user->user['city']);
                    $registrant->setZip($GLOBALS['TSFE']->fe_user->user['zip']);
                    $registrant->setCountry($GLOBALS['TSFE']->fe_user->user['country']);
                    if (array_key_exists('tx_nem_firstname', $GLOBALS['TSFE']->fe_user->user)) {
                        $registrant->setFirstName($GLOBALS['TSFE']->fe_user->user['tx_nem_firstname']);
                        $registrant->setLastName($GLOBALS['TSFE']->fe_user->user['tx_nem_lastname']);

                        $registrant->setProfession($GLOBALS['TSFE']->fe_user->user['tx_nem_profession']);
                        $registrant->setCompany($GLOBALS['TSFE']->fe_user->user['tx_nem_company']);
                        //  $registrant->setDepartment($GLOBALS['TSFE']->fe_user->user['tx_nem_department']);
                        $registrant->setCustomerId($GLOBALS['TSFE']->fe_user->user['tx_nem_cnum']);
                        $registrant->setStreetAndNr($GLOBALS['TSFE']->fe_user->user['tx_nem_street_and_nr']);
                    }
                }
            }

            $addFields = $this->settings['Register']['add_mandatory_fields'] ? trim($this->settings['Register']['add_mandatory_fields']) : '';
            $registrant->setAddMandatoryFields($addFields);


            $querysettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
            $querysettings->setStoragePageIds(array($event->getPid()));

            $this->subeventRepository->setDefaultQuerySettings($querysettings);
            $subevents = $this->subeventRepository->findByEventAllpages($event->getUid(), FALSE);
            if (!is_object($subevents)) {
                $this->view->assign('subevents', null);
                $this->view->assign('subeventcount', 0);
            } else {
                $this->view->assign('subevents', $subevents);
                $this->view->assign('subeventcount', $subevents->count() + 1);

            }

            $this->view->assign('registrant', $registrant);
            $this->view->assign('hash', $checkHash);
            $this->view->assign('otherEvents', $otherEvents);


            $this->view->assign('event', $event);
        }
        $this->view->assign('settings', $this->settings);
    }


    /**
     * action createAction
     *
     * @param Event $event
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     * @param Registrant $registrant
     * @TYPO3\CMS\Extbase\Annotation\Validate(param="registrant" , validator="JVE\JvEvents\Validation\Validator\RegistrantValidator")
	 * @return void
     */
    public function createAction(Event $event, Registrant $registrant) {

        $latestEventDate = $event->getStartDate() ;

		$this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();
		$otherEvents = FALSE ;
		if ( is_array( $_POST['tx_jvevents_events']['jv_events_other_events'])) {
		    $temp = $_POST['tx_jvevents_events']['jv_events_other_events'] ;

			$registrant->setOtherEvents( serialize($_POST['tx_jvevents_events']['jv_events_other_events']) );

			foreach ($temp  as $key => $uid ) {
				/** @var Event $otherEvent */
				if( intval($uid) > 0 ) {
					$otherEvent = $this->eventRepository->findByUidAllpages( intval($uid )) ;
					// var_dump($otherEvent[0]) ;
					if( is_object($otherEvent[0])) {
						if($otherEvent[0]->isIsRegistrationPossible() ) {
							$otherEvents[] = $otherEvent[0] ;
							if( $otherEvent[0]->getStartDate() > $latestEventDate ) {
                                $latestEventDate = $otherEvent[0]->getStartDate() ;
                            }

						}
					}
				} else {
				    unset( $temp[$key]) ;
                }

			}

            $registrant->setOtherEvents( serialize($temp ) );
		}
        $querysettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings ;
        $querysettings->setStoragePageIds(array( $event->getPid() )) ;

        $this->subeventRepository->setDefaultQuerySettings( $querysettings );
        $subevents = $this->subeventRepository->findByEventAllpages($event->getUid() , true ) ;
        if ( is_array( $subevents ) && count( $subevents)  > 0 ) {
            /** @var Subevent $subevent */
            foreach ($subevents as $subevent) {
                if( is_object($subevent)) {
                    if( $subevent->getStartDate() > $latestEventDate ) {
                        $latestEventDate = $subevent->getStartDate() ;
                    }
                }
            }

        }

		if( $registrant->getCompany2() == '' ) {
			if( $registrant->getDepartment2() <> '' ) {
				$registrant->setCompany2(" - ") ;
			}
			if( $registrant->getZip2() <> '' ) {
				$registrant->setCompany2(" - ") ;
			}
			if( $registrant->getCity2() <> '' ) {
				$registrant->setCompany2(" - ") ;
			}
			if( $registrant->getStreetAndNr2() <> '' ) {
				$registrant->setCompany2(" - ") ;
			}
			if( $registrant->getCountry2() <> '' ) {
				$registrant->setCompany2(" - ") ;
			}
		}
        // $registrant->setLanguage( $this->settings['sys_language_uid'] ) ;


		$this->settings['success'] = FALSE ;
		$this->settings['successMsg'] = FALSE ;

        $checkString =  $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate() ;
        $this->settings['hash'] = hash("sha256" , $checkString ) ;

		$registrant->setEvent($event->getUid() );
		if( $latestEventDate instanceof \DateTime ) {
            $latestEventDate = $latestEventDate->getTimestamp() ;
        }
        $latestEventDate = $latestEventDate + (3540 * 24  ) ; // add 23:59  to  Enddate. This  is used to calculate depending on GDPR settings, when registration will be deleted.
		$registrant->setEndtime( $latestEventDate) ;

		if ($event->getRegistrationPid() > 0) {
			$registrant->setPid($event->getRegistrationPid());
		} else {
			$registrant->setPid( $GLOBALS['TSFE']->id );
		}

		$registrant->setFingerprint( );



		// test if user is already registered ..
		$this->settings['alreadyRegistered'] = FALSE ;
		// $this->settings['debug']  = 2 ;

		$this->settings['debug']  = 0 ;



		/** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $existingRegistration */
        $existingRegistration = $this->registrantRepository->findByFilter($registrant->getEmail() , $event->getUid() , 0 , $this->settings );


		if( is_object( $existingRegistration ) ) {
		    /** @var Registrant $oldReg */
			$oldReg = $existingRegistration->getFirst() ;
			if( is_object( $oldReg ) ) {
				// solve it by setting .. : allow Double registrations ...
				// $this->settings['alreadyRegistered'] = TRUE ;
                if( $this->settings['register']['doNotallowSameEmail'] ) {
                    // this case should be blocked in validator .. !
                } else {
                    $this->settings['alreadyRegistered'] = TRUE ;

                }
			}
		}

		// noraml each registration is only one Person but we can configure a second Person. if fields like more1, more2 are set
        // the registration is for 2 Persons (or maybe more)

		$totalPersonCount = $this->getTotalPersonCount($registrant)['total'] ;

        // set Status 0 unconfirmed || 1 Confirmed by Partizipant || 2 Confirmed by Organizer
        if( $this->settings['alreadyRegistered'] ) {
            if( is_object( $oldReg ) ) {
                if( ! $this->settings['register']['doNotallowSameEmail'] ) {
                    // 1. we need to Check if Number registrered Persons have changed
                    $oldPersonCount = $this->getTotalPersonCount($oldReg)['total'] ;

                    $diffCount  = $totalPersonCount - $oldPersonCount ;
                    if($diffCount != 0 ) {
                        if($oldReg->getConfirmed() ) {
                            $registrant->setConfirmed(1) ;
                            // maybe one More or one Less. if OLD registration have 2 Persons, new only 1 , diffcount will be -1
                            $event->setRegisteredSeats($event->getRegisteredSeats() + $diffCount );
                        } else {
                            $event->setUnconfirmedSeats($event->getUnconfirmedSeats() + $diffCount);
                            $registrant->setConfirmed(0);
                        }

                        if ($event->getNeedToConfirm() == 1 && $oldReg->getHidden() == 1 ) {
                            $registrant->setHidden(1);
                        }
                    }
                    $oldReg->setPid( $registrant->getPid() ) ;
                    // 1. we need to load Data from OLD New Registration to OLD Registration
                    $this->updateOldReg( $oldReg , $registrant ) ;
                }
            } else {
                $oldReg = $registrant ;
            }

		} else {

            if ($event->getNeedToConfirm() == 1) {
                $registrant->setHidden(1);
                $this->settings['success'] = TRUE ;
                $this->settings['successMsg'] = "register_need_to_confirm" ;
                $event->setUnconfirmedSeats($event->getUnconfirmedSeats() + $totalPersonCount);
                // TODo : send Email to partizipant with LINK
                // in this Process  we need to remove him from UnconfirmedSeats and add him to   RegisteredSeats

            } else {
                if (intval($event->getAvailableSeats()) > (intval($event->getRegisteredSeats()) + intval($event->getUnconfirmedSeats()))) {
                    // there are enough free Seats so no confirmation by organizer is needed
                    $registrant->setConfirmed(1);
                    $event->setRegisteredSeats($event->getRegisteredSeats() + $totalPersonCount);
                } else {
                    // 2 possible situations: registration is using waitingLists
                    $event->setUnconfirmedSeats($event->getUnconfirmedSeats() + $totalPersonCount);
                    $registrant->setConfirmed(0);
                }
            }


			$registrant->setCrdate(time() ) ;

			$this->signalSlotDispatcher->dispatch(
                __CLASS__,
                __FUNCTION__,
                array(
                    'registrant' => &$registrant,
                    'event' => &$event,
                    'settings' => $this->settings,
                )
            );
            $oldReg = $registrant ;
			$this->registrantRepository->add($registrant);



			$this->persistenceManager->persistAll();
			if( is_array($otherEvents)) {

				foreach ($otherEvents as $key => $otherEvent) {
					/** @var Registrant $newregistrant */
					$newregistrant = $this->objectManager->get( "JVE\\JvEvents\\Domain\\Model\\Registrant")  ;
					$properties = $registrant->_getProperties() ;
					unset($properties['uid']) ;

					foreach ($properties as $key => $value ) {
						$newregistrant->_setProperty( $key , $value ) ;
					}

					if ($otherEvent->getNeedToConfirm() == 1) {
						$otherEvent->setUnconfirmedSeats($otherEvent->getUnconfirmedSeats() + $totalPersonCount);

					} else {
						$otherEvent->setRegisteredSeats($otherEvent->getRegisteredSeats() + $totalPersonCount);
					}



					if ($otherEvent->getNeedToConfirm() == 1) {
						$newregistrant->setHidden(1);
						$this->settings['success'] = TRUE ;
						$this->settings['successMsg'] = "register_need_to_confirm" ;

						// TODo : send Email to partizipant with LINK
                        // User needs to confirm  is not in use in the moment.. !!
                        $otherEvent->setUnconfirmedSeats($otherEvent->getUnconfirmedSeats() + $totalPersonCount);

					} else {
                        if (intval($otherEvent->getAvailableSeats()) > (intval($otherEvent->getRegisteredSeats()) + intval($otherEvent->getUnconfirmedSeats()))) {
                            // there are enough free Seats so no confirmation by organizer is needed
                            $newregistrant->setConfirmed(1);
                            $otherEvent->setUnconfirmedSeats($otherEvent->getUnconfirmedSeats() + $totalPersonCount);
                        } else {
                            // 2 possible situations: registration is using waitingLists
                            $newregistrant->setConfirmed(0);
                            $otherEvent->setRegisteredSeats($otherEvent->getRegisteredSeats() + $totalPersonCount);
                        }

					}

					if ($otherEvent->getRegistrationPid() > 0) {
						$newregistrant->setPid($otherEvent->getRegistrationPid());
					} else {
						$newregistrant->setPid( $GLOBALS['TSFE']->id );
					}
                    $newregistrant->setEvent( $otherEvent->getUid() ) ;
					$this->registrantRepository->add($newregistrant);

					$this->eventRepository->update($otherEvent);
					$this->persistenceManager->persistAll();

					unset( $newregistrant ) ;
				}
			}



			$this->eventRepository->update($event);
		}
        $this->persistenceManager->persistAll();



		if( $registrant->getHidden() == 0  ) {
			$this->settings['success'] = TRUE ;
			$replyto = false ;
			$registrantEmail = $this->getRegistrantEmail($registrant) ;


            if (is_object($event->getOrganizer())) {
                if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($event->getOrganizer()->getEmail())) {
                    $replyto = array( $event->getOrganizer()->getEmail() => '=?utf-8?B?'. base64_encode( $event->getOrganizer()->getName() ) .'?=' ) ;
                }
                if( $event->getNotifyOrganizer() && $replyto ) {

                    if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($event->getOrganizer()->getEmail())) {
                        $this->sendEmail($event, $registrant, "Organizer" ,
                            array( $event->getOrganizer()->getEmail() => '=?utf-8?B?'. base64_encode( $event->getOrganizer()->getName() ) .'?=' ) , $otherEvents , $registrantEmail , $oldReg );
                    }
                    $ccEmails = str_replace( array("," , ";" , " " ) , array("," , "," , ",") , $event->getOrganizer()->getEmailCc() ) ;
                    $ccEmailsArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $ccEmails , true ) ;
                    if ( count($ccEmailsArray) > 0 ) {
                        foreach ( $ccEmailsArray as $ccEmail ) {
                            if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail( $ccEmail ) ) {

                                $this->sendEmail($event, $registrant, "Organizer" ,
                                    array( $ccEmail => '=?utf-8?B?'. base64_encode( $event->getOrganizer()->getName() ) .'?=' ) , $otherEvents , $registrantEmail , $oldReg );
                            }
                        }
                    }
                }
            }
			if( $event->getNotifyRegistrant()  ) {
                if (is_object($event->getLocation())) {
                    if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($event->getLocation()->getEmail())) {
                        $replyto = array( $event->getLocation()->getEmail() => '=?utf-8?B?'. base64_encode( $event->getLocation()->getName() ) .'?=' ) ;
                    }
                }
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($registrant->getEmail())) {
					$this->settings['successMsg'] = "register_email_with_infos" ;

					$this->sendEmail($event, $registrant, "Registrant" ,
                        $registrantEmail , $otherEvents , $replyto , $oldReg );
				}
			}

		} else {
		    // ToDo: create Workflow that this user get a confirmation Email with link before he is REALLY registered actually not in use !!!
        }
		

        $this->view->assign('event', $event);
		if( $this->settings['alreadyRegistered'] ) {
			$this->view->assign('registrant', $oldReg);
		} else {
			$this->view->assign('registrant', $registrant);
		}

        $this->view->assign('settings', $this->settings);
    }

    /**
     * action confirmAction
     *
     * @param Registrant $registrant
     * @return void
     */
    public function confirmAction(Registrant $registrant)
    {
        $eventUid = 0 ;
        $hash = '' ;
        if( $this->request->hasArgument('hash')) {
            $hash = trim(strip_tags( $this->request->getArgument('hash') ));
        }
        if( $this->request->hasArgument('registrant')) {
            $registrantUid = trim(strip_tags( $this->request->getArgument('registrant') ));
            $registrant = $this->registrantRepository->getOneById($registrantUid , true ) ;
        }

        if(is_object($registrant) && $registrant->getEvent()) {
            $error = false ;
            /** @var Event $event */
            $event= $this->eventRepository->findByUid($registrant->getEvent()) ;
            if ( $event && is_object($event->getOrganizer())) {
                $eventUid = $event->getUid() ;
                if($this->isUserOrganizer() ) {
                    if( $this->hasUserAccess( $event->getOrganizer() )) {

                        if( $registrant->getConfirmed() == 0 ) {
                            $registrant->setConfirmed(1) ;
                            $this->registrantRepository->update($registrant);
                            if( $event->getNotifyRegistrant()  ) {
                                $replyto = false ;
                                if (is_object($event->getOrganizer())) {
                                    if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($event->getOrganizer()->getEmail())) {
                                        $replyto = array( $event->getOrganizer()->getEmail() => '=?utf-8?B?'. base64_encode( $event->getOrganizer()->getName() ) .'?=' ) ;
                                    }
                                }
                                $registrantEmail = $this->getRegistrantEmail($registrant)   ;
                                $totalNumberOfPersons = $this->getTotalPersonCount($registrant)['total']  ;
                                $event->setUnconfirmedSeats( $event->getUnconfirmedSeats() - $totalNumberOfPersons );
                                $event->setRegisteredSeats( $event->getRegisteredSeats() + $totalNumberOfPersons );

                                $this->eventRepository->update($event);
                                $this->persistenceManager->persistAll();

                                if ($registrantEmail) {
                                    $this->settings['successMsg'] = "register_email_with_infos" ;

                                    $this->sendEmail($event, $registrant, "Registrant" ,
                                        $registrantEmail , false , $replyto );
                                    $this->addFlashMessage('Confirmation Mail is sent registration ', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                                }
                            }
                        } else {
                            $this->addFlashMessage('registration was already confirmed!', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                        }

                    } else {
                        $this->addFlashMessage('No Access ! leasee login ?? ', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
                    }
                } else {
                    $this->addFlashMessage('No Access ! leasee login ?? ', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
                }
            } else {
                $this->addFlashMessage('Could not find event (id: "' . $registrant->getEvent() . '") related to this registration ', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

            }
        }
        $this->persistenceManager->persistAll() ;
        $this->redirect("list" , null , null, array("event" => $eventUid , "hash" => $hash ) ) ;

    }

    /**
     * @param Registrant $oldReg
     * @param Registrant $registrant
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function updateOldReg(Registrant $oldReg , Registrant $registrant){
        try {
            if( array_key_exists( 'allformFields' ,$this->settings['register'] ) && is_array ( $this->settings['register']['allformFields'] )) {
                foreach ( $this->settings['register']['allformFields'] as $fieldname => $value ) {

                    if( $registrant->_hasProperty($fieldname)) {
                        $value = $registrant->_getProperty($fieldname) ;
                        $oldReg->_setProperty( $fieldname , $value ) ;
                    }
                }
            }
            $this->registrantRepository->update($oldReg) ;
        } catch(Exception $e) {
        }
    }
    /**
     * action checkQrcodeAction
     *
     * @param Event|null $eventUid
     * @param string $hash
     * @return void
     */
    public function checkQrcodeAction(Event $event=null , $hash='')
    {
        $registrant = false ;

        if ( $this->request->hasArgument("registrant") ) {
            $registrantId = $this->request->getArgument("registrant") ;
            $registrant= $this->registrantRepository->getOneById($registrantId , true ) ;
        }
        $this->view->assign('registrant', $registrant);
        $this->view->assign('event', $event);
        $this->view->assign('hash', $hash);

    }


    /**
     * action deleteAction
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function deleteAction()
    {
        $eventUid = 0 ;
        $hash = '' ;
        if( $this->request->hasArgument('hash')) {
            $hash = trim(strip_tags( $this->request->getArgument('hash') ));
        }
        if( $this->request->hasArgument('registrant')) {
            $registrantUid = trim(strip_tags( $this->request->getArgument('registrant') ));
            $registrant = $this->registrantRepository->getOneById($registrantUid , true ) ;
        }

        if(is_object($registrant) && $registrant->getEvent()) {
            $error = false ;
            /** @var Event $event */
            $event= $this->eventRepository->findByUid($registrant->getEvent()) ;
            if ( $event && is_object($event->getOrganizer())) {
                $eventUid = $event->getUid() ;
                if($this->isUserOrganizer() ) {
                    if( $this->hasUserAccess( $event->getOrganizer() )) {

                        if( $registrant->getHidden() ) {
                            $this->registrantRepository->remove($registrant) ;
                        } else {
                            $newValue = $event->getRegisteredSeats() - $this->getTotalPersonCount( $registrant)['total']  ;
                            $event->setRegisteredSeats($newValue);
                            $this->eventRepository->update($event) ;
                            $registrant->setHidden(1) ;
                            $this->registrantRepository->update($registrant) ;
                        }


                        $this->addFlashMessage('registration sucessful canceled', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                    } else {
                        $this->addFlashMessage('You do not have access to this event registrations', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
                    }
                } else {
                    $this->addFlashMessage('You do not have access as organizer. maybe not logged in ?', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
                }
            } else {
                $this->addFlashMessage('Could not find event (id: "' . $registrant->getEvent() . '") related to this registration ', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

            }
        } else {
            $this->addFlashMessage('Could not find this registration ', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
        }
        $this->persistenceManager->persistAll() ;
        $this->redirect("list" , null , null, array("event" => $eventUid , "hash" => $hash ) ) ;


    }


    /**
     * action show
     *
     * @param Registrant $registrant
     * @return void
     */
    public function showAction(Registrant $registrant)
    {
        $this->view->assign('registrant', $registrant);
    }

    /**
     * @param Registrant $registrant
     * @return int|mixed
     */
    private function getTotalPersonCount(Registrant $registrant) {
        $totalPersonCount = 1 ;
        if( array_key_exists('secondPerson' , $this->settings['register'] )) {
            // variant 1 : used for tango münchen: we allow only registration of a second person and gender / first/ lastname
            // fields should be filled. So $totalPersonCount may rise t o2

            if ( array_key_exists( 'fieldNames', $this->settings['register']['secondPerson']) )  {
                $secondPersonFields = GeneralUtility::trimExplode( "," ,$this->settings['register']['secondPerson']['fieldNames'] ) ;
                if (is_array( $secondPersonFields) && count($secondPersonFields) > 0 ) {
                    $secondPerson = [] ;
                    foreach ($secondPersonFields as $field) {
                        $func = "get" . ucfirst($field) ;
                        if(method_exists ($registrant , $func)) {
                            if( $registrant->$func() ) {
                                $secondPerson[] = $registrant->$func() ;
                            }
                        }
                    }
                    if( count($secondPerson) > 1) {
                        // at least first and Lastname , or gender and lastname is set ..
                        $totalPersonCount = 2 ;
                    }
                }
            }
            // variant 2 :: we have A FIELD that contains the total number of persons in this registaion.
            if( array_key_exists( 'fieldNameAmount', $this->settings['register']['secondPerson']) )  {
                $field = $this->settings['register']['secondPerson']['fieldNameAmount'] ;
                $func = "get" . ucfirst($field) ;
                if(method_exists ($registrant , $func)) {
                    $totalPersonCount = intval( $registrant->$func()) ;
                }
                if( array_key_exists( ['maxAmount'], $this->settings['register']['secondPerson']) )  {
                    $totalPersonCount = min ( $totalPersonCount , $this->settings['register']['secondPerson']['maxAmount']) ;
                }

            }
            // Finally: minium 1 registration but maybe 2 or more up to maxAmount
            $totalPersonCount = max( 1 , $totalPersonCount ) ;
        }
        return array( "total" => $totalPersonCount ) ;
    }

    /**
     * @param Registrant $registrant
     * @return array|bool
     */
    public function getRegistrantEmail(Registrant $registrant) {
        $registrantEmail = false ;
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($registrant->getEmail())) {

            $name = trim( $registrant->getFirstName() . " " . $registrant->getLastName())  ;
            if( strlen( $name ) < 3 ) {
                $name = "RegistrantId: " . $registrant->getUid() ;
            } else {
                $name  = '=?utf-8?B?'. base64_encode( $name) .'?=' ;
            }
            $registrantEmail = array( $registrant->getEmail() => $name ) ;
        }
        return $registrantEmail ;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request
     * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response
     * @throws \Exception|\TYPO3\CMS\Extbase\Property\Exception
     */
    public function processRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request, \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response)
    {
        try {
            parent::processRequest($request, $response);
        }
        catch(\TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException $e) {

        }
        catch(\Exception $e) {
            throw $e;
        }
    }


}