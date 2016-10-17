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
     *
     * @return void
     */
    public function listAction()
    {
        // toDo add restrictions to listing ... only for admins or the organizer himself ..  
        $registrants = null ;
        // $registrants = $this->registrantRepository->findAll();
        $this->view->assign('registrants', $registrants);
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

		$this->settings['success'] = FALSE ;
		$this->settings['successMsg'] = FALSE ;

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

		$this->settings['debug']  = 1 ;
		/** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $existingRegistration */
		$existingRegistration = $this->registrantRepository->findByFilter($registrant->getEmail() , $event->getUid() , 0 , $this->settings );


		if( is_object( $existingRegistration ) ) {
			$oldReg = $existingRegistration->getFirst() ;
			if( is_object( $oldReg ) ) {
				$this->settings['alreadyRegistered'] = TRUE ;
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
			$this->registrantRepository->add($registrant);

			$this->persistenceManager->persistAll();

			$this->eventRepository->update($event);
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
					$this->sendEmail($event, $registrant, "Organizer" ,
						array( $registrant->getEmail() => $name ));
				}
			}
			if( $event->getNotifyOrganizer() ) {

				if (is_object($event->getOrganizer())) {
					if (\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($event->getOrganizer()->getEmail())) {

						$this->sendEmail($event, $registrant, "Organizer" ,
							array( $event->getOrganizer()->getEmail() => '=?utf-8?B?'. base64_encode( $event->getOrganizer()->getName() ) .'?=' ));
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