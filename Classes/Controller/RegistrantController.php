<?php
namespace JVE\JvEvents\Controller;

use JVE\JvEvents\Domain\Model\Registrant;
use JVE\JvEvents\Domain\Model\Subevent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @ignorevalidation $event
     * @param string $hash
     * @return void
     */
    public function listAction(\JVE\JvEvents\Domain\Model\Event $event, $hash  )
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

    private function getCsvHeader( $d , $eol , $t ) {
        $return = $t . "hidden" . $t  ;
        $return .= $d .  $t . "Firstname" . $t . $d  . $t . "Lastname" . $t  ;
        $return .= $d  . $t . "Gender" . $t . $d  . $t . "title" . $t  ;
        $return .= $d  . $t . "confirmed" . $t . $d  . $t . "email" . $t  ;
        $return .= $d  . $t . "company" . $t . $d  . $t . "department" . $t  ;
        $return .= $d  . $t . "address" . $t  ;
        $return .= $d  . $t . "zip" . $t . $d  . $t . "city" . $t  ;
        $return .= $d  . $t . "Country" . $t . $d  . $t . "Language" . $t  ;

        $return .= $d  . $t . "phone" . $t . $d  . $t . "profession" . $t  ;
        $return .= $d  . $t . "customer_id" . $t . $d  . $t . "contact_id" . $t  ;

        $return .= $d  . $t . "company2" . $t . $d  . $t . "department2" . $t  ;
        $return .= $d  . $t . "address2" . $t  ;
        $return .= $d  . $t . "zip2" . $t  . $d  . $t . "city2" . $t  ;
        $return .= $d  . $t . "Country2" . $t ;
        $return .= $d  . $t . "More" . $t ;
        $return .= $d  . $t . "More2" . $t ;
        $return .= $d  . $t . "More3" . $t ;
        $return .= $d  . $t . "Additional Info" . $t ;

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
        $return = $t . $this->cleanString($registrant->getHidden(), $t , $d ) . $t  ;
        $return .= $d . $t . $this->cleanString( $registrant->getFirstname() , $t , $d) . $t . $d  . $t . $this->cleanString($registrant->getLastName(), $t , $d ) . $t  ;

        $gender = $this->translate("register_gender_female" ) ;
        if( $registrant->getGender() < 2 ) {
            $gender = $this->translate("register_gender_male" ) ;
        }


        $return .= $d  . $t . $this->cleanString($gender , $t , $d ). $t . $d  . $t . $this->cleanString($registrant->getTitle() , $t , $d) . $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getConfirmed(), $t , $d) . $t . $d  . $t . $this->cleanString($registrant->getEmail() , $t , $d). $t  ;

        $return .= $d  . $t . $this->cleanString($registrant->getCompany(), $t , $d) . $t . $d  . $t . $this->cleanString($registrant->getDepartment() , $t , $d). $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getStreetAndNr(), $t , $d) . $t   ;
        $return .= $d  . $t . $this->cleanString(" " . $registrant->getZip(), $t , $d) . $t . $d  . $t . $this->cleanString($registrant->getCity() , $t , $d). $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getCountry(), $t , $d) . $t . $d  . $t . $this->cleanString($registrant->getLanguage(), $t , $d) . $t  ;
        $phone = " " . $registrant->getPhone() ;
        if ( str_replace( " " , "" , trim($phone )) == trim($phone) ) {
            $old = $phone ;
            $phone = " " . substr( $old , 0 , 2) . " " . substr( $old , 2 , 2 ) . " " . substr( $old , 4 , 2 ) . " " . substr( $old , 6 , 99 ) ;
        }
        $return .= $d  . $t . $this->cleanString($phone, $t , $d) . $t . $d  . $t . $this->cleanString($registrant->getProfession() , $t , $d). $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getCustomerId(), $t , $d) . $t . $d  . $t . $this->cleanString($registrant->getContactId(), $t , $d). $t  ;


        $return .= $d  . $t . $this->cleanString($registrant->getCompany2(), $t , $d) . $t . $d  . $t . $this->cleanString($registrant->getDepartment2(), $t , $d) . $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getStreetAndNr2(), $t , $d) . $t   ;
        $return .= $d  . $t . $this->cleanString(" " . $registrant->getZip2(), $t , $d) . $t . $d  . $t . $this->cleanString($registrant->getCity2(), $t , $d) . $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getCountry2(), $t , $d). $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getMore1(), $t , $d). $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getMore2(), $t , $d). $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getMore3(), $t , $d). $t  ;
        $return .= $d  . $t . $this->cleanString($registrant->getAdditionalInfo(), $t , $d). $t  ;

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
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @param Registrant $registrant
     * @ignorevalidation $event
     * @ignorevalidation $registrant
     * @return void
     */
    public function newAction(\JVE\JvEvents\Domain\Model\Event $event , Registrant $registrant=null)
    {
		 $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();
		// $this->addFlashMessage($this->translate('msg_error_cid'), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

		$this->settings['startReg'] = time() ;
		$tokenBase  = "P" . $this->settings['pageId'] . "-L" .$this->settings['sys_language_uid'] . "-E" . $event->getUid();

		$this->settings['formToken'] = md5($tokenBase);
		$this->settings['filter']['startDate'] = time() ;
		// $this->settings['filter']['maxDays'] = 99999 ;

		// $this->settings['debug'] = 2 ;
		$otherEvents = false ;
		if ( $event->getEventCategory() ) {
			$this->settings['filter']['skipEvent'] = $event->getUid() ;
			foreach ($event->getEventCategory() as $cat ) {
				if( $cat->getBlockRegistration() ) {
					$this->settings['filter']['categories'] = $cat->getUid() ;

					/** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events */
					$otherEvents = $this->eventRepository->findByFilter(false, false,  $this->settings );

				}
			}
		}


        $checkString =  $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate() ;
        $checkHash = hash("sha256" , $checkString ) ;

        $this->settings['fe_user']['user'] = $GLOBALS['TSFE']->fe_user->user ;
        $this->settings['fe_user']['organizer']['showTools'] = FALSE ;

        if ( $GLOBALS['TSFE']->fe_user->user ) {
            $userUid = $GLOBALS['TSFE']->fe_user->user['uid'] ;
            if( is_object($event->getOrganizer())) {
                $userAccessArr = \TYPO3\CMS\CORE\Utility\GeneralUtility::trimExplode( "," , $event->getOrganizer()->getAccessUsers() ) ;
                if( in_array( $userUid , $userAccessArr ))  {
                    $this->settings['fe_user']['organizer']['showTools'] = TRUE ;
                } else {
                    $usersGroups = \TYPO3\CMS\CORE\Utility\GeneralUtility::trimExplode( "," ,  $GLOBALS['TSFE']->fe_user->user['usergroup'] ) ;
                    $OrganizerAccessToolsGroups = \TYPO3\CMS\CORE\Utility\GeneralUtility::trimExplode( "," ,  $event->getOrganizer()->getAccessGroups() ) ;
                    foreach ($OrganizerAccessToolsGroups as $tempGroup ) {
                        if( in_array( $tempGroup , $usersGroups ))  {
                            $this->settings['fe_user']['organizer']['showTools'] = TRUE ;
                        }
                    }

                }
            }
        }


        if ( $registrant==null) {
            /** @var Registrant $registrant */
            $registrant = $this->objectManager->get("JVE\\JvEvents\\Domain\\Model\\Registrant");
            if($userUid) {
                $registrant->setGender(intval( $GLOBALS['TSFE']->fe_user->user['gender'] + 1 ));
                $registrant->setFirstName($GLOBALS['TSFE']->fe_user->user['first_name']);
                $registrant->setLastName($GLOBALS['TSFE']->fe_user->user['last_name']);
                $registrant->setEmail($GLOBALS['TSFE']->fe_user->user['email']);
                $registrant->setPhone($GLOBALS['TSFE']->fe_user->user['telephone']);
            }
        }

        $querysettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings ;
        $querysettings->setStoragePageIds(array( $event->getPid() )) ;

        $this->subeventRepository->setDefaultQuerySettings( $querysettings );
        $subevents = $this->subeventRepository->findByEventAllpages($event->getUid() , FALSE ) ;
        if ( ! is_object( $subevents ) ) {
            $this->view->assign('subevents', null );
            $this->view->assign('subeventcount', 0 );
        } else {
            $this->view->assign('subevents', $subevents);
            $this->view->assign('subeventcount', $subevents->count() + 1 );

        }

		$this->view->assign('registrant', $registrant);
		$this->view->assign('hash', $checkHash);
		$this->view->assign('otherEvents', $otherEvents);

		$this->view->assign('settings', $this->settings);
		$this->view->assign('event', $event);
    }


    /**
     * action createAction
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @ignorevalidation $event
     * @param Registrant $registrant
	 * @validate $registrant \JVE\JvEvents\Validation\Validator\RegistrantValidator
	 * @return void
     */
    public function createAction(\JVE\JvEvents\Domain\Model\Event $event, Registrant $registrant) {

        $latestEventDate = $event->getStartDate() ;

		$this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();
		$otherEvents = FALSE ;
		if ( is_array( $_POST['tx_jvevents_events']['jv_events_other_events'])) {
		    $temp = $_POST['tx_jvevents_events']['jv_events_other_events'] ;

			$registrant->setOtherEvents( serialize($_POST['tx_jvevents_events']['jv_events_other_events']) );

			foreach ($temp  as $key => $uid ) {
				/** @var \JVE\JvEvents\Domain\Model\Event $otherEvent */
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
            if( ! $this->settings['register']['doNotallowSameEmail'] ) {
                $this->registrantRepository->update($oldReg) ;
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
                            array( $event->getOrganizer()->getEmail() => '=?utf-8?B?'. base64_encode( $event->getOrganizer()->getName() ) .'?=' ) , $otherEvents , $registrantEmail);
                    }
                    $ccEmails = str_replace( array("," , ";" , " " ) , array("," , "," , ",") , $event->getOrganizer()->getEmailCc() ) ;
                    $ccEmailsArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $ccEmails , true ) ;
                    if ( count($ccEmailsArray) > 0 ) {
                        foreach ( $ccEmailsArray as $ccEmail ) {
                            if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail( $ccEmail ) ) {

                                $this->sendEmail($event, $registrant, "Organizer" ,
                                    array( $ccEmail => '=?utf-8?B?'. base64_encode( $event->getOrganizer()->getName() ) .'?=' ) , $otherEvents , $registrantEmail);
                            }
                        }
                    }
                }
            }
			if( $event->getNotifyRegistrant()  ) {
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($registrant->getEmail())) {
					$this->settings['successMsg'] = "register_email_with_infos" ;

					$this->sendEmail($event, $registrant, "Registrant" ,
                        $registrantEmail , $otherEvents , $replyto );
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
            /** @var \JVE\JvEvents\Domain\Model\Event $event */
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
     * action checkQrcodeAction
     *
     * @param Registrant|null $registrant
     * @param \JVE\JvEvents\Domain\Model\Event|null $eventUid
     * @param string $hash
     * @return void
     */
    public function checkQrcodeAction(Registrant $registrant=null , \JVE\JvEvents\Domain\Model\Event $event=null , string $hash='')
    {
        $args = $this->request->getArguments() ;
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
            /** @var \JVE\JvEvents\Domain\Model\Event $event */
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

}