<?php
namespace JVE\JvEvents\Controller;

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

            $this->view->assign('registrants', $registrants);
            $this->view->assign('hash', $hash);

        } else {
            $this->addFlashMessage("Error - wrong hash : " . $hash ) ;
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
        $return .= $d  . $t . "Additional Info" . $t ;

        return $return . $eol ;
    }

    /**
     * @param \JVE\JvEvents\Domain\Model\Registrant $registrant
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
     * @return void
     */
    public function newAction(\JVE\JvEvents\Domain\Model\Event $event)
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

		$this->view->assign('hash', $checkHash);
		$this->view->assign('otherEvents', $otherEvents);

		$this->view->assign('settings', $this->settings);
		$this->view->assign('event', $event);
    }


    /**
     * action createAction
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @param \JVE\JvEvents\Domain\Model\Registrant $registrant
	 * @validate $registrant \JVE\JvEvents\Validation\Validator\RegistrantValidator
     * @return void
     */
    public function createAction(\JVE\JvEvents\Domain\Model\Event $event, \JVE\JvEvents\Domain\Model\Registrant $registrant) {
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
						}
					}
				} else {
				    unset( $temp[$key]) ;
                }

			}

            $registrant->setOtherEvents( serialize($temp ) );
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
			}
		}
		// set Status 0 unconfirmed || 1 Confirmed by Partizipant || 2 Confirmed by Organizer

		if( $this->settings['alreadyRegistered'] ) {
			$this->registrantRepository->update($oldReg) ;
			// ToDO : decide what we do
		} else {
			// ToDo Availble Seats or Waintig seats dann ?
			if (intval($event->getAvailableSeats()) > (intval($event->getRegisteredSeats()) + intval($event->getUnconfirmedSeats()))) {
				// there are enough free Seats so no confirmation by organizer is needed
				$registrant->setConfirmed(1);
			} else {
				// 2 possible situations: registration is using waitingLists
				$registrant->setConfirmed(0);
			}



			if ($event->getNeedToConfirm() == 1) {
				$registrant->setHidden(1);
				$this->settings['success'] = TRUE ;
				$this->settings['successMsg'] = "register_need_to_confirm" ;
				$event->setUnconfirmedSeats($event->getUnconfirmedSeats() + 1);
				// TODo : send Email to partizipant with LINK
			} else {
				$event->setRegisteredSeats($event->getRegisteredSeats() + 1);
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
					/** @var \JVE\JvEvents\Domain\Model\Registrant $newregistrant */
					$newregistrant = $this->objectManager->get( "JVE\\JvEvents\\Domain\\Model\\Registrant")  ;
					$properties = $registrant->_getProperties() ;
					unset($properties['uid']) ;

					foreach ($properties as $key => $value ) {
						$newregistrant->_setProperty( $key , $value ) ;
					}

					if ($otherEvent->getNeedToConfirm() == 1) {
						$otherEvent->setUnconfirmedSeats($otherEvent->getUnconfirmedSeats() + 1);

					} else {
						$otherEvent->setRegisteredSeats($otherEvent->getRegisteredSeats() + 1);
					}

					if (intval($otherEvent->getAvailableSeats()) > (intval($otherEvent->getRegisteredSeats()) + intval($otherEvent->getUnconfirmedSeats()))) {
						// there are enough free Seats so no confirmation by organizer is needed
						$newregistrant->setConfirmed(1);
					} else {
						// 2 possible situations: registration is using waitingLists
						$newregistrant->setConfirmed(0);
					}

					if ($otherEvent->getNeedToConfirm() == 1) {
						$newregistrant->setHidden(1);
						$this->settings['success'] = TRUE ;
						$this->settings['successMsg'] = "register_need_to_confirm" ;
						$otherEvent->setUnconfirmedSeats($otherEvent->getUnconfirmedSeats() + 1);
						// TODo : send Email to partizipant with LINK
					} else {
						$event->setRegisteredSeats($otherEvent->getRegisteredSeats() + 1);
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

			$this->persistenceManager->persistAll();

		}
		
		if( $registrant->getHidden() == 0  ) {
			$this->settings['success'] = TRUE ;

			if( $event->getNotifyRegistrant()  ) {
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($registrant->getEmail())) {
					$this->settings['successMsg'] = "register_email_with_infos" ;

					$name = trim( $registrant->getFirstName() . " " . $registrant->getLastName())  ;
					if( strlen( $name ) < 3 ) {
						$name = "RegistrantId: " . $registrant->getUid() ;
					} else {
						$name  = '=?utf-8?B?'. base64_encode( $name) .'?=' ;
					}
					$this->sendEmail($event, $registrant, "Registrant" ,
						array( $registrant->getEmail() => $name ) , $otherEvents);
				}
			}
			if( $event->getNotifyOrganizer() ) {

				if (is_object($event->getOrganizer())) {
					if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($event->getOrganizer()->getEmail())) {

						$this->sendEmail($event, $registrant, "Organizer" ,
							array( $event->getOrganizer()->getEmail() => '=?utf-8?B?'. base64_encode( $event->getOrganizer()->getName() ) .'?=' ) , $otherEvents);
					}
				}
			}
		}
		

		// TODo Store Data to SF
		// TODo Store Data to Citrix
		// TODo Send Debug Mails to admin or Developer



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
     * @param \JVE\JvEvents\Domain\Model\Registrant $registrant
     * @return void
     */
    public function confirmAction(\JVE\JvEvents\Domain\Model\Registrant $registrant)
    {
        $this->view->assign('registrant', $registrant);
    }

    /**
     * action deleteAction
     *
     * @param \JVE\JvEvents\Domain\Model\Registrant $registrant
     * @return void
     */
    public function deleteAction(\JVE\JvEvents\Domain\Model\Registrant $registrant)
    {
        $this->view->assign('registrant', $registrant);
    }


    /**
     * action show
     *
     * @param \JVE\JvEvents\Domain\Model\Registrant $registrant
     * @return void
     */
    public function showAction(\JVE\JvEvents\Domain\Model\Registrant $registrant)
    {
        $this->view->assign('registrant', $registrant);
    }


}