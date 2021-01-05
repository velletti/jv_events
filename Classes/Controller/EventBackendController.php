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

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;

/**
 * EventBackendController
 */
class EventBackendController extends BaseController
{
    /**
     * @var \JVE\JvEvents\Signal\RegisterCitrixSignal
     */
    protected  $citrixSlot  ;

    /**
     * @var \JVE\JvEvents\Signal\RegisterHubspotSignal
     */
    protected  $hubspotSlot  ;



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
        $this->citrixSlot = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("JVE\\JvEvents\\Signal\\RegisterCitrixSignal");
        $this->hubspotSlot = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("JVE\\JvEvents\\Signal\\RegisterHubspotSignal");
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
        $recursive = false ;

        if( $this->request->hasArgument('recursive')) {
            $recursive = $this->request->getArgument('recursive') ;

        }
        $onlyActual = -999 ;
        if( $this->request->hasArgument('onlyActual')) {
            $onlyActual = $this->request->getArgument('onlyActual') ;

        }
        if ( $recursive ) {
            $queryGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\QueryGenerator');
            $this->settings['storagePids'] = $queryGenerator->getTreeList($pageId, 9999, 0, 1) ;
        }
         $email = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('email');
        $this->settings['filter']['startDate']  = $onlyActual ;
        $this->settings['storagePid'] = $pageId ;


        $this->settings['directmail'] = FALSE  ;

        if( $this->request->hasArgument('event')) {
            $eventID = $this->request->getArgument('event') ;
            if( $eventID > 0 ) {
                $itemsPerPage = 100 ;
            }

        }

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('direct_mail')) {
            $this->settings['directmail'] = TRUE ;
        }


        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events */
        $registrants = $this->registrantRepository->findByFilter($email, $eventID, $pageId ,  $this->settings , 999 );


        $pageRow = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord(
            'pages',
            $pageId,
            '*'
        );

        if( $GLOBALS['BE_USER']->doesUserHaveAccess($pageRow , 1 ) ) {

            /**
             * @var $queryGenerator \TYPO3\CMS\Core\Database\QueryGenerator
             */

            $eventIds = $this->registrantRepository->findEventsByFilter($email , $pageId , $this->settings  ) ;
            if( count($eventIds) > 0 ) {
                // $events[] = array( "0" => "-" ) ;
                foreach ($eventIds as $key => $eventId ) {
                    $event = $this->eventRepository->findByUidAllpages($eventId) ;
                    if( is_array($event)) {
                        if(  $event[0] instanceof  \JVE\JvEvents\Domain\Model\Event )  {
                            $location = '' ;
                            if ($event[0]->getLocation() instanceof  \JVE\JvEvents\Domain\Model\Location )  {
                                $location = $event[0]->getLocation()->getCity() ;
                            }
                            $event[0]->setName( $event[0]->getStartDate()->format("Y-m-d - ") . substr( $event[0]->getName() , 0 , 50 ) . " | " . $location . " (ID: " . $event[0]->getUid() . ")" ) ;
                            if( $eventID > 0 ) {
                                $this->view->assign('eventName', $event[0]->getName() );

                            }
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
            $this->view->assign('onlyActual', $onlyActual  );
            $this->view->assign('recursive', $recursive );
            $this->view->assign('pageId', $pageId );
        } else {
            die("You do not have Access to page " .$pageId ) ;
        }


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

                        $pid = $event[0]->getRegistrationFormPid() ;
                        $lng = $event[0]->getSysLanguageUid() ;
                        $typoScript = \JVE\JvEvents\Utility\TyposcriptUtility::loadTypoScriptFromScratch( $pid , "tx_jvevents_events" , array( "[globalVar = GP:L = " . intval($lng ) . "]" )) ;


                        $this->settings = array_merge( $this->settings ,  $typoScript['settings'] ) ;
                        $this->settings['pageId'] = $pid ;
                        $this->settings['sys_language_uid'] = $lng ;

                        // ***** NEEDS a FIX !!! ********************
                        // ToDo : J.v. 4.2.2019 Keine Ahnung wie das früher jemals funktioniert hat ?
                        // das Layout steht nur in dem Plug, an das komme ich aber vom Backend so nicht dran ..


                        if( !$this->settings['LayoutRegister'] ) {
                            $this->settings['LayoutRegister'] = "2Allplan" ;
                        }
                        // echo "<pre>" ;
                        // var_dump($this->settings) ;
                        // die;
                        // ***** NEEDS a FIX !!! ********************

                        $registrant->setConfirmed("1") ;
                        $name = trim( $registrant->getFirstName() . " " . $registrant->getLastName())  ;
                        if( strlen( $name ) < 3 ) {
                            $name = "RegistrantId: " . $registrant->getUid() ;
                        } else {
                            $name  = '=?utf-8?B?'. base64_encode( $name) .'?=' ;
                        }

                        $this->sendEmail($event[0] , $registrant ,"Registrant" , array( $registrant->getEmail() => $name ) , FALSE )  ;

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
     *
     * will output json Resonse
     */
    public function resendCitrixAction()
    {
        if( $this->settings['EmConfiguration']['enableCitrix'] < 1 ) {
            $output = array( "enableCitrix" => false ) ;
        } elseif (!$this->request->hasArgument("registrant")) {
            $output = array( "error" => true , "msg" => "No registrant Id" ) ;
        } else {
            $regId = intval( $this->request->getArgument("registrant")) ;
            $registrant = false ;
            $event = false ;
            if ($regId < 1) {
                $output = array( "error" => true , "msg" => "Registrant Id < 1" ) ;
            } else {
                /** @var \JVE\JvEvents\Domain\Model\Registrant $registrant */
                $registrant = $this->registrantRepository->findByUid($regId);
            }
            if (! $registrant || !is_object($registrant)) {
                $output = array( "error" => true , "msg" => "Registrant not Found" ) ;
            } else {
                /** @var \JVE\JvEvents\Domain\Model\Event $event */
                $event = $this->eventRepository->findByUidAllpages($registrant->getEvent(), FALSE);
            }
            if (! $event || !is_object($event)) {
                $output = array( "error" => true , "msg" => "Event not Found" ) ;
            } else {
                // Special Solution For allplan. So this Extension exists
                // Condition is special for DE but should work also in all langauges
                // if anybody also needs this, we should make it more flexibel ..

                $lng = intval( $event->getLanguageUid()) ;
                $ts = \Allplan\AllplanTools\Utility\TyposcriptUtility::loadTypoScriptFromScratch(
                    $event->getRegistrationFormPid(), "tx_jvevents_events", array("[globalVar = GP:L = " . $lng . "]")
                );

                $this->settings['register']['citrix']['orgID'] = $ts['settings']['register']['citrix']['orgID'];
                $this->settings['register']['citrix']['orgAUTH'] = $ts['settings']['register']['citrix']['orgAUTH'];
                $response = $this->citrixSlot->createAction($registrant, $event, $this->settings);
                //   $response = "201" ;
                $registrant->setCitrixResponse($response['CitrixResponse']) ;
                $this->registrantRepository->update($registrant) ;


                if ($response == 200 || $response == 201) {
                    $colorCode = \TYPO3\CMS\Core\Messaging\AbstractMessage::INFO;
                } else {
                    $colorCode = \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING;
                }
                // $this->addFlashMessage(" Resent user " . $registrant->getEmail() . " to Citrix : " . var_export($response, true)
                //    , '', $colorCode);

                $this->persistenceManager->persistAll();
                $output = array("uid" => $regId, "text" => $response);
            }
        }
        $jsonOutput = json_encode($output);
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($jsonOutput));
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Transfer-Encoding: 8bit');

        $callbackId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("callback");
        if ($callbackId == '') {
            echo $jsonOutput;
        } else {
            echo $callbackId . "(" . $jsonOutput . ")";
        }

        die();
    }


    /**
     * action resendHubspotAction
     *
     * will output json Resonse
     */
    public function resendHubspotAction()
    {
        if( $this->settings['EmConfiguration']['enableHubspot'] < 1 ) {
            $output = array( "enableHubspot" => false ) ;
        } elseif (!$this->request->hasArgument("registrant")) {
            $output = array( "error" => true , "msg" => "No registrant Id" ) ;
        } else {
            $regId = intval( $this->request->getArgument("registrant")) ;
            $registrant = false ;
            $event = false ;
            if ($regId < 1) {
                $output = array( "error" => true , "msg" => "Registrant Id < 1" ) ;
            } else {
                /** @var \JVE\JvEvents\Domain\Model\Registrant $registrant */
                $registrant = $this->registrantRepository->findByUid($regId);
            }
            if (! $registrant || !is_object($registrant)) {
                $output = array( "error" => true , "msg" => "Registrant not Found" ) ;
            } else {
                /** @var \JVE\JvEvents\Domain\Model\Event $event */
                $event = $this->eventRepository->findByUidAllpages($registrant->getEvent(), FALSE);
            }
            if (! $event || !is_object($event)) {
                $output = array( "error" => true , "msg" => "Event not Found" ) ;
            } else {
                // Special Solution For allplan. So this Extension exists
                // Condition is special for DE but should work also in all langauges
                // if anybody also needs this, we should make it more flexibel ..

                $lng = intval( $event->getLanguageUid()) ;

                $ts = \Allplan\AllplanTools\Utility\TyposcriptUtility::loadTypoScriptFromScratch(
                    $event->getRegistrationFormPid(), "tx_jvevents_events", array("[globalVar = GP:L = " . $lng . "]")
                );

                $response = $this->hubspotSlot->createAction($registrant, $event, $this->settings);


                if ($response['status'] == 201 || $response['status']  == 204) {
                    $colorCode = \TYPO3\CMS\Core\Messaging\AbstractMessage::INFO;
                    $registrant->setHubspotResponse($response['status']) ;
                } else {
                    $colorCode = \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING;
                    $registrant->setHubspotResponse($response['status'] . " - " . $response['error']) ;
                }
                $this->registrantRepository->update($registrant) ;
                // $this->addFlashMessage(" Resent user " . $registrant->getEmail() . " to Citrix : " . var_export($response, true)
                //    , '', $colorCode);

                $this->persistenceManager->persistAll();
                $output = array("uid" => $regId, "text" => $response);
            }
        }

        $jsonOutput = json_encode($output);
        $callbackId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("callback");
        if ($callbackId ) {
            $jsonOutput = $callbackId . "(" . $jsonOutput . ")";
        }
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($jsonOutput));
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Transfer-Encoding: 8bit');

         echo $jsonOutput;

        die();
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
            return true ;
        }
        /** @var \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('sys_dmail_group') ;
        $dgroupRow = $queryBuilder->select("*")->from("sys_dmail_group")->where($queryBuilder->expr()->eq("static_list" ,$event->getUid() ))->execute()->fetchAll() ;

        // $dgroupRow =  \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordRaw("sys_dmail_group" , "deleted = 0 AND static_list = " . $event->getUid() , "*" ) ;
        if($dgroupRow ) {
            return true ;
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



        return $queryBuilder->insert('sys_dmail_group')->values($dgroup)->execute() ;

    }

}