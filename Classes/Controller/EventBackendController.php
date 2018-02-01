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
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;

/**
 * EventController
 */
class EventBackendController extends BaseController
{

    /**
     * eventRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository = NULL;


    /**
     * @var \JVE\JvEvents\Signal\RegisterCitrixSignal
     * @inject
     */
    protected  $citrixSlot  ;

    /**
	 * staticCountryRepository
	 *
	 * @var \JVE\JvEvents\Domain\Repository\StaticCountryRepository
	 * @inject
	 */
	protected $staticCountryRepository = NULL;

	public function initializeAction() {
		if ($this->request->hasArgument('action')) {

			if ($this->request->getArgument('action') == "show") {
				if (!$this->request->hasArgument('event')) {
					throw new \Exception('Missing Event Id in URL');
				}
			}
		}
		if (!$this->request->hasArgument('event')) {
			// ToDo redirect to error
		}

        parent::initializeAction() ;
	}
    /**
     * action list
     * http://master.dev/typo3/index.php?M=web_JvEventsEventmngt&moduleToken=0659e7b6ef0e5e1632d32734a03e2141563302d5
     *
     * @return void
     */
    public function listAction()
    {
        $itemsPerPage = 20 ;
        $pageId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id');
         $email = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('email');
        if( $this->request->hasArgument('event')) {
            $eventID = $this->request->getArgument('event') ;
            if( $eventID > 0 ) {
                $itemsPerPage = 200 ;
            }

        }
        $this->settings['filter']['startDate']  = -9999 ;
        $this->settings['storagePid'] = $pageId ;
        $this->settings['directmail'] = FALSE  ;
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('direct_mail')) {
            $this->settings['directmail'] = TRUE ;
        }
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events */
        $registrants = $this->registrantRepository->findByFilter($email, $eventID, $pageId ,  $this->settings , 9999 );
        $eventIds = $this->registrantRepository->findEventsByFilter($email , $pageId , $this->settings  ) ;
        if( count($eventIds) > 0 ) {
            // $events[] = array( "0" => "-" ) ;
            foreach ($eventIds as $key => $eventId ) {
                $event = $this->eventRepository->findByUidAllpages($eventId) ;
                if( is_array($event)) {
                    if(  $event[0] instanceof  \JVE\JvEvents\Domain\Model\Event )  {
                        $event[0]->setName( $event[0]->getStartDate()->format("d.m.Y - ") . substr( $event[0]->getName() , 0 , 50 )  . " (" . $event[0]->getUid() . ")" ) ;

                        $events[] = $event[0] ;
                        if ( $this->request->hasArgument("createDmailGroup")) {
                            if ($eventID == 0 || ($eventID == $event[0]->getUid())) {
                                $this->createDmailGroup($event[0]);
                            }
                        }
                    }
                }

            }
        }


        $this->view->assign('event', $eventID );
        $this->view->assign('itemsPerPage', $itemsPerPage );
        $this->view->assign('events', $events);
        $this->view->assign('registrants', $registrants);
        $this->view->assign('settings', $this->settings );
    }
    
    /**
     * action show
     *  @param \JVE\JvEvents\Domain\Model\Event
     $event
     * @return void
     */
    public function showAction(\JVE\JvEvents\Domain\Model\Event $event)
    {
		
		$this->view->assign('event', $event);
    }
    
    /**
     * action new
     * @param \JVE\JvEvents\Domain\Model\Registrant
     * @return void
     */
    // public function confirmAction(\JVE\JvEvents\Domain\Model\Registrant $registrant )
    public function confirmAction()
    {
        $eventID = 0 ;
        if ( $this->request->hasArgument("eventID")) {
            $eventID = $this->request->getArgument("eventID");
        }
		if ( $this->request->hasArgument("registrant")) {
		    $regId = $this->request->getArgument("registrant") ;

		    if( $regId > 0 ) {
		        /** @var \JVE\JvEvents\Domain\Model\Registrant $registrant */
                $registrant = $this->registrantRepository->findByUid($regId) ;
                if( $registrant ) {

                    /** @var \JVE\JvEvents\Domain\Model\Event $event */
                    $event = $this->eventRepository->findByUidAllpages($registrant->getEvent() ) ;

                    if( $event ) {
                        $registrant->setConfirmed("1") ;
                        $name = trim( $registrant->getFirstName() . " " . $registrant->getLastName())  ;
                        if( strlen( $name ) < 3 ) {
                            $name = "RegistrantId: " . $registrant->getUid() ;
                        } else {
                            $name  = '=?utf-8?B?'. base64_encode( $name) .'?=' ;
                        }

                        $this->sendEmail($event[0] , $registrant ,"Registrant" , array( $registrant->getEmail() => $name ) )  ;

                        $this->registrantRepository->update($registrant) ;
                        $this->addFlashMessage($this->settings['register']['senderEmail'] . " -> Email send to " . $registrant->getEmail() . " - layout: " . $this->settings['LayoutRegister'] , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::INFO);
                    }
                }
            }

        }
        $this->redirect('list' , NULL , NULL , array( 'event' => $eventID )) ;
	}

    /**
     * action resendCitrix
     * @return void
     */
    // public function confirmAction(\JVE\JvEvents\Domain\Model\Registrant $registrant )
    public function resendCitrixAction()
    {
        if ( $this->request->hasArgument("registrant")) {
            $regId = $this->request->getArgument("registrant") ;

            if( $regId > 0 ) {
                /** @var \JVE\JvEvents\Domain\Model\Registrant $registrant */
                $registrant = $this->registrantRepository->findByUid($regId) ;
                if( $registrant ) {

                    /** @var \JVE\JvEvents\Domain\Model\Event $event */
                    $event = $this->eventRepository->findByUidAllpages($registrant->getEvent() , FALSE  ) ;

                    if( $event ) {

                        $ts = \Allplan\AllplanTools\Utility\TyposcriptUtility::loadTypoScriptFromScratch(
                            $event->getRegistrationFormPid() , "tx_jvevents_events" , array( "[globalVar = GP:L = 1]" ) ) ;
                        $this->settings['register']['citrix']['orgID'] = $ts['settings']['register']['citrix']['orgID'] ;
                        $this->settings['register']['citrix']['orgAUTH'] = $ts['settings']['register']['citrix']['orgAUTH'] ;
                        $response = $this->citrixSlot->createAction($registrant , $event , $this->settings ) ;
                        //   $response = "201" ;
                        // $registrant->setCitrixResponse($response) ;
                        // $this->registrantRepository->update($registrant) ;


                        if ( $response == 200 ||$response == 201 ) {
                            $colorCode = \TYPO3\CMS\Core\Messaging\AbstractMessage::INFO ;
                        } else {
                            $colorCode = \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING ;
                        }
                        $this->addFlashMessage( " Resent user " . $registrant->getEmail() . " to Citrix : " . var_export( $response , true )
                                , '', $colorCode );

                        $this->persistenceManager->persistAll() ;
                        $output = array( "uid" => $regId , "text" => $response)  ;

                        $jsonOutput = json_encode($output);
                        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                        header('Cache-Control: no-cache, must-revalidate');
                        header('Pragma: no-cache');
                        header('Content-Length: ' . strlen($jsonOutput));
                        header('Content-Type: application/json; charset=utf-8');
                        header('Content-Transfer-Encoding: 8bit');

                        $callbackId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("callback");
                        if ( $callbackId == '' ) {
                            echo $jsonOutput;
                        } else {
                            echo $callbackId . "(" . $jsonOutput . ")";
                        }

                        die();

                    }
                }
            }
        }
        die;
    }


    /**
     * action create
     *
     * @param \JVE\JvEvents\Domain\Model\Event $newEvent
     * @return void
     */
    public function createAction(\JVE\JvEvents\Domain\Model\Event $newEvent)
    {
        $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See http://wiki.typo3.org/T3Doc/Extension_Builder/Using_the_Extension_Builder#1._Model_the_domain', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
       // $this->eventRepository->add($newEvent);
        // $this->redirect('list');
    }
    
    /**
     * action edit
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @return void
     */
    public function editAction(\JVE\JvEvents\Domain\Model\Event $event)
    {
        $this->view->assign('event', $event);
    }
    
    /**
     * action update
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @return void
     */
    public function updateAction(\JVE\JvEvents\Domain\Model\Event $event)
    {
        $this->addFlashMessage('The object was updated. Please be aware that this action is publicly accessible unless you implement an access check. See http://wiki.typo3.org/T3Doc/Extension_Builder/Using_the_Extension_Builder#1._Model_the_domain', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
       // $this->eventRepository->update($event);
       // $this->redirect('list');
    }
    
    /**
     * action delete
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @return void
     */
    public function deleteAction(\JVE\JvEvents\Domain\Model\Event $event)
    {
        $this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See http://wiki.typo3.org/T3Doc/Extension_Builder/Using_the_Extension_Builder#1._Model_the_domain', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
       // $this->eventRepository->remove($event);
       //  $this->redirect('list');
    }
    



    /**
     * action search
     *
     * @return void
     */
    public function searchAction()
    {
        
    }


    // ########################################   functions ##################################
	/**
	 * helper for Formvalidation
	 * @param string $action
	 * @return string
	 */
	public function generateToken($action = "action")
	{
		/** @var \TYPO3\CMS\Core\FormProtection\FrontendFormProtection $formClass */
		$formClass =  $this->objectManager->get( "TYPO3\\CMS\\Core\\FormProtection\\FrontendFormProtection") ;

		return $formClass->generateToken(
			'event', $action ,   "P" . $this->settings['pageId'] . "-L" .$this->settings['sys_language_uid']
		);

	}

    /**
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @return bool
     */
	public function createDmailGroup( \JVE\JvEvents\Domain\Model\Event  $event ) {

        if (! \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('direct_mail')) {
            return ;
        }
        $dgroupRow =  \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordRaw("sys_dmail_group" , "deleted = 0 AND static_list = " . $event->getUid() , "*" ) ;
        if(is_array($dgroupRow )) {
            return ;
        }

        $query[] = array (
              'operator' => 'AND' ,
              'type' => 'FIELD_event' ,
              'comparison' => '32' ,
              'inputValue' => $event->getUid()
        ) ;
        $query[] = array (
            'operator' => 'AND' ,
            'type' => 'FIELD_email' ,
            'comparison' => '0' ,
            'inputValue' => "@"
        ) ;
        $query[] = array (
            'operator' => 'AND' ,
            'type' => 'FIELD_hidden' ,
            'comparison' => '129' ,
            'negate' => 'on' ,
            'inputValue' => "1"
        ) ;
        $query[] = array (
            'operator' => 'AND' ,
            'type' => 'FIELD_deleted' ,
            'comparison' => '129' ,
            'negate' => 'on' ,
            'inputValue' => "1"
        ) ;
        $query[] = array (
            'operator' => 'AND' ,
            'type' => 'FIELD_confirmed' ,
            'comparison' => '129' ,
            'inputValue' => "1"
        ) ;

	    $dgroup['title'] = "Event " . $event->getStartDate()->format("d.m.Y" ) . " (" . $event->getUid() . ") "  ;
	    //$dgroup['queryTable'] = "tx_jvevents_domain_model_registrant" ;
	    $dgroup['type'] = 3 ;

	    // toDo get PID from Config
	    $dgroup['pid'] = 141 ;
	    $dgroup['tstamp'] = time()  ;

	    $dgroup['static_list'] = $event->getUid()  ;
	    $dgroup['whichtables'] =  4 ;
	    $dgroup['description'] =  $event->getName() ;
	    $dgroup['list'] =  serialize( array() ) ;
	    $dgroup['csv'] =  0 ;
	    $dgroup['query'] = serialize($query) ;

        return $GLOBALS['TYPO3_DB']->exec_INSERTquery( "sys_dmail_group" ,  $dgroup) ;
    }

}