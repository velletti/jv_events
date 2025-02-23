<?php
namespace JVelletti\JvEvents\Controller;

use JVelletti\JvEvents\Utility\MigrationUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Annotation\Validate;
use JVelletti\JvEvents\Validation\Validator\RegistrantValidator;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException;
use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Registrant;
use JVelletti\JvEvents\Domain\Model\Subevent;
use JVelletti\JvEvents\Utility\RegisterHubspotUtility;
use JVelletti\JvEvents\Utility\RegisterMarketoUtility;
use Psr\Http\Message\ResponseInterface;
use SJBR\SrFreecap\Utility\EncryptionUtility;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
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
     * @param string $hash
     * @return void
     */
    #[IgnoreValidation(['value' => 'event'])]
    public function listAction(Event $event, ?string $hash = ''  ): ResponseInterface
    {
        // toDo add restrictions to listing ... only for admins or the organizer himself ..

        $doExport = 0 ;
        if( $this->request->hasArgument('export')) {
            $doExport = $this->request->getArgument('export') ;
        }
        $pid = 0 ;
        if( $this->request->hasArgument('pid')) {
            $pid = $this->request->getArgument('pid') ;
        } else {
            $pid = $event->getRegistrationPid() ;
        }
        $registrants = [] ;

        $checkString =  $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate() ;
        $checkHash = GeneralUtility::hmac ( $checkString ) ;


        if( $checkHash ==  $hash ) {
            //overwrite any wrong setting start Date ... to get all registrations
            $this->settings['filter']['startDate'] = 0 ;
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
                    , '', AbstractMessage::WARNING);
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
        return $this->htmlResponse();

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
        $gender = match ($registrant->getGender()) {
            1 => $this->translate("register_gender_male" ),
            2 => $this->translate("register_gender_female" ),
            default => $this->translate("register_gender_diverse" ),
        };

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

        $string =   str_replace("\n" , "   " , (string) $string ) ;
        $string =   str_replace("\r" , "   " , $string ) ;
        return  str_replace($delim , $replace , $string ) ;
    }
    /**
     * action new
     *
     * @return void
     */
    #[IgnoreValidation(['value' => 'event'])]
    #[IgnoreValidation(['value' => 'registrant'])]
    public function newAction(Event $event=null, Registrant $registrant=null): ResponseInterface
    {
        $this->getFlashMessageQueue()->getAllMessagesAndFlush();
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
                        $this->settings['filter']['maxEvents'] = 99 ;
                        /** @var QueryResultInterface $events */
                        $otherEvents = $this->eventRepository->findByFilter(false, false, $this->settings);
                    }
                }
            }
            $checkString = $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate();
            $checkHash = GeneralUtility::hmac ( $checkString );
            $this->settings['fe_user']['user'] = $this->frontendUser->user;
            $this->settings['fe_user']['organizer']['showTools'] = FALSE;
            $userUid = 0 ;
            if ($this->frontendUser->user) {
                $userUid = $this->frontendUser->user['uid'];
                if (is_object($event->getOrganizer())) {
                    $userAccessArr = \TYPO3\CMS\CORE\Utility\GeneralUtility::trimExplode(",", $event->getOrganizer()->getAccessUsers());
                    if (in_array($userUid, $userAccessArr)) {
                        $this->settings['fe_user']['organizer']['showTools'] = TRUE;
                    } else {
                        $usersGroups = \TYPO3\CMS\CORE\Utility\GeneralUtility::trimExplode(",", $this->frontendUser->user['usergroup']);
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
                $registrant = GeneralUtility::makeInstance(Registrant::class);
                if ($userUid > 0 ) {
                    $registrant->setGender(intval($this->frontendUser->user['gender'] + 1));
                    $registrant->setFirstName($this->frontendUser->user['first_name']);
                    $registrant->setLastName($this->frontendUser->user['last_name']);
                    $registrant->setEmail($this->frontendUser->user['email']);
                    $registrant->setPhone($this->frontendUser->user['telephone']);
                    $registrant->setTitle($this->frontendUser->user['title']);
                    $registrant->setCity($this->frontendUser->user['city']);
                    $registrant->setZip($this->frontendUser->user['zip']);
                    $registrant->setCountry($this->frontendUser->user['country']);
                    if (array_key_exists('tx_nem_firstname', $this->frontendUser->user)) {
                        $registrant->setFirstName($this->frontendUser->user['tx_nem_firstname']);
                        $registrant->setLastName($this->frontendUser->user['tx_nem_lastname']);

                        $registrant->setProfession($this->frontendUser->user['tx_nem_profession']);
                        $registrant->setCompany($this->frontendUser->user['tx_nem_company']);
                        //  $registrant->setDepartment($this->frontendUser->user['tx_nem_department']);
                        $registrant->setCustomerId($this->frontendUser->user['tx_nem_cnum']);
                        $registrant->setStreetAndNr($this->frontendUser->user['tx_nem_street_and_nr']);
                    }
                }
            }

            $addFields = $this->settings['Register']['add_mandatory_fields'] ? trim((string) $this->settings['Register']['add_mandatory_fields']) : '';
            $registrant->setAddMandatoryFields($addFields);


            $querysettings =$this->subeventRepository->getTYPO3QuerySettings() ;
            $querysettings->setStoragePageIds([$event->getPid()]);

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
        return $this->htmlResponse();
    }


    /**
     * action createAction
     *
     * @return void
     */
    #[IgnoreValidation(['value' => 'event'])]
    #[Validate(['param' => 'registrant', 'validator' => RegistrantValidator::class])]
    public function createAction(Event $event, Registrant $registrant): ResponseInterface {



        $latestEventDate = $event->getStartDate() ;

		$this->getFlashMessageQueue()->getAllMessagesAndFlush();
		$otherEvents = FALSE ;
		if ( isset( $_POST['tx_jvevents_registrant']['jv_events_other_events']) && is_array( $_POST['tx_jvevents_registrant']['jv_events_other_events'])) {
		    $temp = $_POST['tx_jvevents_registrant']['jv_events_other_events'] ;

			$registrant->setOtherEvents( serialize( $_POST['tx_jvevents_registrant']['jv_events_other_events']) );

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
        $querysettings =$this->subeventRepository->getTYPO3QuerySettings() ;
        $querysettings->setStoragePageIds([$event->getPid()]) ;

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


        $forward['success'] = FALSE ;
        $forward['successMsg'] = FALSE ;

        $checkString =  $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate() ;
        $this->settings['hash'] = GeneralUtility::hmac ( $checkString ) ;

		$registrant->setEvent($event->getUid() );
		if( $latestEventDate instanceof \DateTime ) {
            $latestEventDate = $latestEventDate->getTimestamp() ;
        }
        $latestEventDate = $latestEventDate + (3540 * 24  ) ; // add 23:59  to  Enddate. This  is used to calculate depending on GDPR settings, when registration will be deleted.
		$registrant->setEndtime( $latestEventDate) ;

		if ($event->getRegistrationPid() > 0) {
			$registrant->setPid($event->getRegistrationPid());
		} else {
			$registrant->setPid( $detailPid = MigrationUtility::getPageId() );
		}

		$registrant->setFingerprint( );



		// test if user is already registered ..
        $forward['alreadyRegistered'] = FALSE ;
		// $this->settings['debug']  = 2 ;

		$this->settings['debug']  = 0 ;



		/** @var QueryResultInterface $existingRegistration */
        $existingRegistration = $this->registrantRepository->findByFilter($registrant->getEmail() , $event->getUid() , 0 , $this->settings );


		if( is_object( $existingRegistration ) ) {
		    /** @var Registrant $oldReg */
			$oldReg = $existingRegistration->getFirst() ;
			if( is_object( $oldReg ) ) {
				// solve it by setting .. : allow Double registrations ...
				// $this->settings['alreadyRegistered'] = TRUE ;
                if( isset($this->settings['register']['doNotallowSameEmail']) && $this->settings['register']['doNotallowSameEmail'] ) {
                    // this case should be blocked in validator .. !
                } else {
                    $forward['alreadyRegistered'] = TRUE ;

                }
			}
		}

		// noraml each registration is only one Person but we can configure a second Person. if fields like more1, more2 are set
        // the registration is for 2 Persons (or maybe more)

		$totalPersonCount = $this->getTotalPersonCount($registrant)['total'] ?? 0 ;

        // set Status 0 unconfirmed || 1 Confirmed by Partizipant || 2 Confirmed by Organizer
        if(isset($this->settings['alreadyRegistered']) && $this->settings['alreadyRegistered'] ) {
            if( is_object( $oldReg ) ) {
                if(  ! $this->settings['register']['doNotallowSameEmail'] ) {
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
                $forward['success'] = TRUE ;
                $forward['successMsg'] = "register_need_to_confirm" ;
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

            $oldReg = $registrant ;
            $this->registrantRepository->add($registrant);
            $this->persistenceManager->persistAll();

            if ( $this->settings['EmConfiguration']['enableMarketo'] > 0 ) {
                /** @var RegisterMarketoUtility $marketo */
                $marketo = GeneralUtility::makeInstance(RegisterMarketoUtility::class) ;
                $marketoResponse = $marketo->createAction( $registrant , $event ,  $this->settings ) ;
            }

            if ( $this->settings['EmConfiguration']['enableHubspot'] > 0 ) {
                /** @var RegisterHubspotUtility $hubspot */
                $hubspot = GeneralUtility::makeInstance(RegisterHubspotUtility::class) ;
                $hubspot->createAction( $registrant , $event ,  $this->settings ) ;
            }



			if( is_array($otherEvents)) {

				foreach ($otherEvents as $key => $otherEvent) {
					/** @var Registrant $newregistrant */
					$newregistrant = GeneralUtility::makeInstance( Registrant::class)  ;
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
                        $forward['success'] = TRUE ;
                        $forward['successMsg'] = "register_need_to_confirm" ;

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
						$newregistrant->setPid( $detailPid = MigrationUtility::getPageId() );
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
            $forward['success'] = TRUE ;
			$replyto = false ;
			$registrantEmail = $this->getRegistrantEmail($registrant) ;


            if (is_object($event->getOrganizer())) {
                if (GeneralUtility::validEmail($event->getOrganizer()->getEmail())) {
                    $replyto = [$event->getOrganizer()->getEmail() => '=?utf-8?B?'. base64_encode( (string) $event->getOrganizer()->getName() ) .'?='] ;
                }
                if( $event->getNotifyOrganizer() && $replyto ) {

                    if (GeneralUtility::validEmail($event->getOrganizer()->getEmail())) {
                        $this->sendEmail($event, $registrant, "Organizer" ,
                            [$event->getOrganizer()->getEmail() => '=?utf-8?B?'. base64_encode( (string) $event->getOrganizer()->getName() ) .'?='] , $otherEvents , $registrantEmail , $oldReg );
                    }
                    $ccEmails = str_replace( [",", ";", " "] , [",", ",", ","] , (string) $event->getOrganizer()->getEmailCc() ) ;
                    $ccEmailsArray = GeneralUtility::trimExplode("," , $ccEmails , true ) ;
                    if ( (is_countable($ccEmailsArray) ? count($ccEmailsArray) : 0) > 0 ) {
                        foreach ( $ccEmailsArray as $ccEmail ) {
                            if (GeneralUtility::validEmail( $ccEmail ) ) {

                                $this->sendEmail($event, $registrant, "Organizer" ,
                                    [$ccEmail => '=?utf-8?B?'. base64_encode( (string) $event->getOrganizer()->getName() ) .'?='] , $otherEvents , $registrantEmail , $oldReg );
                            }
                        }
                    }
                }
            }
			if( $event->getNotifyRegistrant()  ) {
                if (is_object($event->getLocation())) {
                    if (GeneralUtility::validEmail($event->getLocation()->getEmail())) {
                        $replyto = [$event->getLocation()->getEmail() => '=?utf-8?B?'. base64_encode( (string) $event->getLocation()->getName() ) .'?='] ;
                    }
                }
				if (GeneralUtility::validEmail($registrant->getEmail())) {
					$forward['successMsg'] = "register_email_with_infos" ;

					$this->sendEmail($event, $registrant, "Registrant" ,
                        $registrantEmail , $otherEvents , $replyto , $oldReg );
				}
			}

		} else {
		    // ToDo: create Workflow that this user get a confirmation Email with link before he is REALLY registered actually not in use !!!
        }

        $this->persistenceManager->persistAll();

		return $this->redirect("created", "Registrant", "JvEvents", [ "event" => $event->getUid(), "registrant" => $registrant->getUid() , "settings" => $forward ] ) ;

    }

    public function createdAction(array $settings = [] ): ResponseInterface
    {

        if( $this->request->hasArgument('registrant')) {
            $registrantUid = (int) trim(strip_tags( (string) $this->request->getArgument('registrant') ));
            $registrant = $this->registrantRepository->getOneById($registrantUid , true ) ;
        }
        if( $this->request->hasArgument('event')) {
            $eventUid = (int) trim(strip_tags( (string) $this->request->getArgument('event') ));
            $event = $this->eventRepository->findByUid($eventUid) ;
        }
        $this->view->assign('event', $event);
        if( $settings['alreadyRegistered'] ) {
            $this->view->assign('registrant', $oldReg);
        } else {
            $this->view->assign('registrant', $registrant);
        }
        $settings =  array_merge($this->settings, $settings) ;
        $this->view->assign('settings', $settings);
        return $this->htmlResponse();
    }

    /**
     * action confirmAction
     *
     * @return void
     */
    public function confirmAction(Registrant $registrant)
    {
        $eventUid = 0 ;
        $hash = '' ;
        if( $this->request->hasArgument('hash')) {
            $hash = trim(strip_tags( (string) $this->request->getArgument('hash') ));
        }
        if( $this->request->hasArgument('registrant')) {
            $registrantUid = trim(strip_tags( (string) $this->request->getArgument('registrant') ));
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
                                    if (GeneralUtility::validEmail($event->getOrganizer()->getEmail())) {
                                        $replyto = [$event->getOrganizer()->getEmail() => '=?utf-8?B?'. base64_encode( $event->getOrganizer()->getName() ) .'?='] ;
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
                                    $this->addFlashMessage('Confirmation Mail is sent registration ', '', AbstractMessage::OK);
                                }
                            }
                        } else {
                            $this->addFlashMessage('registration was already confirmed!', '', AbstractMessage::OK);
                        }

                    } else {
                        $this->addFlashMessage('No Access ! leasee login ?? ', '', AbstractMessage::ERROR);
                    }
                } else {
                    $this->addFlashMessage('No Access ! leasee login ?? ', '', AbstractMessage::ERROR);
                }
            } else {
                $this->addFlashMessage('Could not find event (id: "' . $registrant->getEvent() . '") related to this registration ', '', AbstractMessage::ERROR);

            }
        }
        $this->persistenceManager->persistAll() ;
        $this->redirect("list" , null , null, ["event" => $eventUid, "hash" => $hash] ) ;

    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
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
        } catch(Exception) {
        }
    }
    /**
     * action checkQrcodeAction
     *
     * @param Event|null $eventUid
     * @param string $hash
     * @return void
     */
    public function checkQrcodeAction(Event $event=null , $hash=''): ResponseInterface
    {
        $registrant = false ;

        if ( $this->request->hasArgument("registrant") ) {
            $registrantId = $this->request->getArgument("registrant") ;
            $registrant= $this->registrantRepository->getOneById($registrantId , true ) ;
        }
        $this->view->assign('registrant', $registrant);
        $this->view->assign('event', $event);
        $this->view->assign('hash', $hash);
        return $this->htmlResponse();

    }


    /**
     * action deleteAction
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function deleteAction()
    {
        $registrant = null;
        $eventUid = 0 ;
        $hash = '' ;
        if( $this->request->hasArgument('hash')) {
            $hash = trim(strip_tags( (string) $this->request->getArgument('hash') ));
        }
        if( $this->request->hasArgument('registrant')) {
            $registrantUid = trim(strip_tags( (string) $this->request->getArgument('registrant') ));
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


                        $this->addFlashMessage('registration sucessful canceled', '', AbstractMessage::OK);
                    } else {
                        $this->addFlashMessage('You do not have access to this event registrations', '', AbstractMessage::ERROR);
                    }
                } else {
                    $this->addFlashMessage('You do not have access as organizer. maybe not logged in ?', '', AbstractMessage::ERROR);
                }
            } else {
                $this->addFlashMessage('Could not find event (id: "' . $registrant->getEvent() . '") related to this registration ', '', AbstractMessage::ERROR);

            }
        } else {
            $this->addFlashMessage('Could not find this registration ', '', AbstractMessage::ERROR);
        }
        $this->persistenceManager->persistAll() ;
        $this->redirect("list" , null , null, ["event" => $eventUid, "hash" => $hash] ) ;


    }


    /**
     * action show
     *
     * @return void
     */
    public function showAction(Registrant $registrant): ResponseInterface
    {
        $this->view->assign('registrant', $registrant);
        return $this->htmlResponse();
    }

    /**
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
                        $func = "get" . ucfirst((string) $field) ;
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
                $func = "get" . ucfirst((string) $field) ;
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
        return ["total" => $totalPersonCount] ;
    }

    /**
     * @return array|bool
     */
    public function getRegistrantEmail(Registrant $registrant) {
        $registrantEmail = false ;
        if (GeneralUtility::validEmail($registrant->getEmail())) {

            $name = trim( $registrant->getFirstName() . " " . $registrant->getLastName())  ;
            if( strlen( $name ) < 3 ) {
                $name = "RegistrantId: " . $registrant->getUid() ;
            } else {
                $name  = '=?utf-8?B?'. base64_encode( $name) .'?=' ;
            }
            $registrantEmail = [$registrant->getEmail() => $name] ;
        }
        return $registrantEmail ;
    }

    /**
     * @return ResponseInterface
     */
    public function processRequest(RequestInterface $request ):ResponseInterface
    {
        try {
            return parent::processRequest($request);
        }
        catch(TargetNotFoundException) {

        }
    }


}