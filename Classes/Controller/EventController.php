<?php
namespace JVE\JvEvents\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Jörg Velletti <jVelletti@allplan.com>, Allplan GmbH
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

use JVE\JvBanners\Utility\AssetUtility;
use JVE\JvEvents\Domain\Model\Event;
use JVE\JvEvents\Domain\Model\Location;
use JVE\JvEvents\Domain\Model\Organizer;
use JVE\JvEvents\Domain\Repository\BannerRepository;
use JVE\JvEvents\Utility\SlugUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * EventController
 */
class EventController extends BaseController
{


	public function initializeAction() {
        $this->timeStart = $this->microtime_float() ;

	    $this->debugArray[] = "Start:" . intval(1000 * $this->timeStart ) . " Line: " . __LINE__ ;
		if ($this->request->hasArgument('action')) {

			if ( in_array( $this->request->getArgument('action') , array("show" , "edit" , "update" , "create" , "delete" , "cancel") )) {
				// user is calling  one of the actions, that requires and event ID.

			    if ( !$this->request->hasArgument('event') && !$this->request->getArgument('action') == "show") {
			        // if no event with that ID found, redirect to show event. this will show the error message, that no event wit that id exists
					$this->forward("show") ;
				}
                if ( $this->request->hasArgument('event') && $this->request->getArgument('action') == "show"  && !ctype_digit( $this->request->getArgument('event') ) ) {
                    // if Real URl could not determine the event id and gave back the Name and Date of the Event instead of an ID
                    //  as this will crash we do the same as above ; this will show the error message, that no event wit that id exists
                    $this->forward("show" , null , null , array( 'action' => 'show' , 'event' => null )) ;
                }

			}

		} else {
		    // no action is set ? prevent
		    $this->request->setArgument('action', 'list') ;
            $this->forward("list") ;
        }
        if ( $this->request->hasArgument('event')) {
            if ( property_exists( $this->arguments , "event")) {
                $propertyMappingConfiguration = $this->arguments['event']->getPropertyMappingConfiguration();
                $propertyMappingConfiguration->allowProperties('changeFutureEvents') ;
            }

        }

        parent::initializeAction() ;
        $this->debugArray[] = "Init Done:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
	}

    /**
     * action list
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listAction()
    {
        $this->debugArray[] = "After Init :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;
        $this->settings['filter']['distance']['doNotOverrule'] = "false" ;

        if( $this->request->hasArgument('overruleFilter')) {

            $filter = $this->request->getArgument('overruleFilter') ;
            if( array_key_exists( "category" , $filter )) {
                if( $filter['category'] === 'true' ) {
                    $this->settings['filter']['categories'] = false ;
                }
            }
            if( array_key_exists( "startDate" , $filter )) {
                if( $filter['startDate']  ) {
                    $sdArr = explode("." , $filter['startDate'] ) ;
                    if( $sdArr[0] > 0 && $sdArr[0] < 32 && $sdArr[1] > 0 && $sdArr[1] < 13  && $sdArr[2] > 1970 ) {
                        $this->settings['filter']['startDate'] = mktime(0,0,0, $sdArr[1] , $sdArr[0] , $sdArr[2] ) ;
                        $this->settings['filter']['overruleStartDate'] = date( "d.m.Y" , $this->settings['filter']['startDate'] ) ;
                    } else {
                        $this->settings['filter']['startDate'] = intval( $filter['startDate'] ) ;
                    }
                }
            }
            if( array_key_exists( "maxDays" , $filter )) {
                if( $filter['maxDays']  ) {
                  $this->settings['filter']['maxDays'] = intval( $filter['maxDays'] ) ;
                    // if you have 1000s of events or even more, it may not be a good idear to allow to search too far in the future
                    if( $this->settings['security']['filter']['maxDays'] > 0 &&  $this->settings['filter']['maxDays'] >  $this->settings['security']['filter']['maxDays'] ) {
                        $this->settings['filter']['maxDays'] = $this->settings['security']['filter']['maxDays'] ;
                    }
                    $this->settings['filter']['distance']['default'] = 9999 ;
                    $this->settings['filter']['maxEvents'] = 999 ;
                    $this->settings['filter']['distance']['doNotOverrule'] = "true" ;

                }
            }
            if( array_key_exists( "organizer" , $filter )) {
                if( $filter['organizer']  ) {
                    $this->settings['filter']['organizer'] = intval( $filter['organizer'] ) ;
                }
            }
            if( array_key_exists( "location" , $filter )) {
                if( $filter['location']  ) {
                    $this->settings['filter']['location'] = intval( $filter['location'] ) ;
                }
            }

            // https://tango.ddev.local/index.php?id=9&L=0&&tx_jvevents_events[eventsFilter][organizers]=1&tx_jvevents_events[eventsFilter][tags]=5,6,8,7,10,4,1,20,11,14,&tx_jvevents_events[eventsFilter][citys]=undefined&tx_jvevents_events[eventsFilter][months]=undefined&tx_jvevents_events[overruleFilter][category]=true&no_cache=1
        }


        $this->debugArray[] = "Load Events:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
        /** @var QueryResultInterface $events */
        $events = $this->eventRepository->findByFilter(false, false,  $this->settings );
        $this->view->assign('events', $events);
        // read settings from Flexform .. if not set, take it from typoscript setup
        if( intval( $this->settings['detailPid'] ) < 1 ) {
            $this->settings['detailPid'] = intval( $this->settings['link']['detailPidDefault']) ;
        }

        $this->debugArray[] = "Before generate Filter :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;
        switch ($this->settings['ShowFilter']) {
            case "3":
                $eventsFilter = $this->generateFilterAll( $this->settings['filter']) ;
                break;

            case "6":
            case "7":
              //  $eventsFilter = $this->generateFilterWithoutTagsCats( $events ,  $this->settings['filter']) ;
                $eventsFilter = $this->generateFilter( $events ,  $this->settings['filter']) ;
                // $eventsFilter = $this->generateFilterAll(  $this->settings['filter']) ;
                $this->debugArray[] = "After generate Filter others :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " | Line: " . __LINE__ ;
                $eventsFilter['box1'] = $this->generateFilterBox( $this->settings['filter']['tagbox1tags']  , intval($this->settings['filter']['tagShowAfterColon'] )) ;
                $this->debugArray[] = "After generate Filter Tags 1 : " . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " |  Line: " . __LINE__ ;
                $eventsFilter['box2'] = $this->generateFilterBox( $this->settings['filter']['tagbox2tags'] , intval($this->settings['filter']['tagShowAfterColon'] )) ;
                $this->debugArray[] = "After generate Filter Tags 2 : " . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " |  Line: " . __LINE__ ;
                $eventsFilter['box3'] = $this->generateFilterBox( $this->settings['filter']['tagbox3tags'] , intval($this->settings['filter']['tagShowAfterColon'] )) ;
                $this->debugArray[] = "After generate Filter Tags 3 : " . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " |  Line: " . __LINE__ ;
                $eventsFilter['box4'] = $this->generateFilterBox( $this->settings['filter']['tagbox4tags'] , intval($this->settings['filter']['tagShowAfterColon'] )) ;
                $this->debugArray[] = "After generate Filter Tags 4 : " . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " |  Line: " . __LINE__ ;
            break;

            default:
                $eventsFilter = $this->generateFilter( $events ,  $this->settings['filter']) ;
                break;

        }

        $this->debugArray[] = "After generate Filter :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;

        $dtz = $this->eventRepository->getDateTimeZone() ;
        $this->settings['navigationDates'] = $this->eventRepository->getDateArray($this->settings , $dtz ) ;

        $this->view->assign('eventsFilter', $eventsFilter);
       // $this->settings['checkInstallation'] = 2 ;
        $this->view->assign('settings', $this->settings );
        $this->debugArray[] = "before Render:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
        $this->view->assign('debugArray', $this->debugArray );

        // overruleFilterStartDate Nnext

    }
    
    /**
     * action show
     *
     * @param Event|null $event
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     * @return void
     */
    public function showAction(?Event $event=null)
    {
        if( $event ) {
            $checkString = $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate();
            $checkHash = hash("sha256", $checkString);


            //$querysettings = new Typo3QuerySettings;
            $querysettings =$this->subeventRepository->getTYPO3QuerySettings() ;
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

            $this->settings['fe_user']['user'] = $GLOBALS['TSFE']->fe_user->user;
            $this->settings['fe_user']['organizer']['showTools'] = FALSE;

            if ($GLOBALS['TSFE']->fe_user->user) {
                $userUid = $GLOBALS['TSFE']->fe_user->user['uid'];
                if (is_object($event->getOrganizer())) {
                    $userAccessArr = GeneralUtility::trimExplode(",", $event->getOrganizer()->getAccessUsers());
                    if (in_array($userUid, $userAccessArr)) {
                        $this->settings['fe_user']['organizer']['showTools'] = TRUE;
                    } else {
                        $usersGroups = GeneralUtility::trimExplode(",", $GLOBALS['TSFE']->fe_user->user['usergroup']);
                        $OrganizerAccessToolsGroups = GeneralUtility::trimExplode(",", $event->getOrganizer()->getAccessGroups());
                        foreach ($OrganizerAccessToolsGroups as $tempGroup) {
                            if (in_array($tempGroup, $usersGroups)) {
                                $this->settings['fe_user']['organizer']['showTools'] = TRUE;
                            }
                        }

                    }
                }


            }
            $this->view->assign('hash', $checkHash);

        } else {
            $this->addFlashMessage($this->translate("error.general.entry_not_found"), "Sorry!" , AbstractMessage::WARNING) ;

        }
        $this->view->assign('settings', $this->settings);
		$this->view->assign('event', $event);
    }
    
    /**
     * action new
     * @param Event|null $event
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     *
     * @return void
     */
    public function newAction(Event $event=null)
    {
        /** @var QueryResultInterface $categories */
        $categories = $this->categoryRepository->findAllonAllPages( '0' );

        /** @var QueryResultInterface $tags */
        $tags = $this->tagRepository->findAllonAllPages('0');

        if ( $event==null) {
            /** @var Event $event */
            $event = $this->objectManager->get(\JVE\JvEvents\Domain\Model\Event::class);
        }
        if ( $event->getUid() < 1 ) {
            $today = new \DateTime ;
            $today->setTime(0,0,0 , 0) ;
            $event->setStartDate( $today ) ;

            /** @var Organizer $organizer */
            $organizer = $this->getOrganizer() ;
            if ($organizer instanceof Organizer) {
                $event->setOrganizer($organizer);
                $this->view->assign('organizer', $organizer );
            } else {
                $pid = $this->settings['pageIds']['loginForm'] ;
                $this->addFlashMessage('You are not logged in as Organizer.'  , '', AbstractMessage::WARNING);
                $this->redirect(null , null , NULL , NULL , $pid );
            }

            if( $this->request->hasArgument('location')) {
                /** @var Location $location */
                $location= $this->locationRepository->findByUid( intval( $this->request->getArgument('location') )) ;
                if($location instanceof  Location ) {
                    $event->setLocation($location ) ;
                }
                $this->view->assign('location', $location );
            } else {
                $locations= $this->locationRepository->findByOrganizersAllpages( array(0 => $organizer->getUid()) , FALSE, FALSE ) ;
                $this->view->assign('locations', $locations );
            }

            $event->setEventType( 2 ) ;
            $event->setIntrotextRegistrant( $this->settings['Register']['introtext_registrant'] ) ;
            $event->setIntrotextRegistrantConfirmed( $this->settings['Register']['introtext_registrant_confirmed'] ) ;

            // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
            $event->setPid( 12 ) ;

        }
        if($this->isUserOrganizer() ) {
            $this->view->assign('user', intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) ;
            $this->view->assign('event', $event);
            $this->view->assign('categories', $categories);
            $this->view->assign('tags', $tags);
        }
	}
    
    /**
     * action create
     *
     * @param Event $event
     * @TYPO3\CMS\Extbase\Annotation\Validate(param="event" , validator="JVE\JvEvents\Validation\Validator\EventValidator")
     */
    public function createAction(Event $event)
    {
        $pid = 0 ;
        if( $this->request->hasArgument('event')) {
            $event = $this->cleanEventArguments( $event) ;
        }

        $action = "edit" ;
        if($this->isUserOrganizer() ) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();
            $event->setSysLanguageUid(-1) ;

            try {
                $this->eventRepository->add($event);
                $this->updateLatestEvent( $event , $event->getStartDate() ) ;
                $this->persistenceManager->persistAll() ;

                // got from EM Settings
                $clearCachePids = GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
                if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
                    $this->cacheService->clearPageCache( $clearCachePids );
                    $this->addFlashMessage('The object was created and Cache of following pages are cleared: ' . implode("," , $clearCachePids), '', AbstractMessage::OK);
                } else {
                    $this->addFlashMessage('The object was created.', '', AbstractMessage::OK);
                }
                $pid = $this->settings['pageIds']['showEventDetail'] ;
                $action = "show" ;
            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', AbstractMessage::WARNING);

            }

        } else {
            $action = null ;

            $pid = $this->settings['pageIds']['loginForm'] ;
            $this->addFlashMessage('The object was NOT created. You are not logged in as Organizer.' . $event->getUid() , '', AbstractMessage::WARNING);
            $this->redirect(null , null , NULL , array( 'event' => $event ) , $pid );
        }
        // if PID from TS settings is set: if User is not logged in-> Page with loginForm , on success -> showEventDetail  Page
        if( $pid < 1) {
            // else : stay on this page
            $pid = $GLOBALS['TSFE']->id ;

        }
        try {
            /** @var UriBuilder $uriBuilder */

            $uriBuilder = GeneralUtility::makeInstance( UriBuilder::class ) ;
            $uri = "https://" . GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY') .
                    $uriBuilder->reset()->setTargetPageUid($pid)
                    ->setArguments([ 'tx_jvevents_events' =>
                        ['controller' =>  'Event' , 'action' => $action , 'event' => $event->getUid() ] ])
                    ->build() ;
            // https://tangov10.ddev.site/de/tanzen/termin-details/event/event/show/qqqqqqq-20-08-2023.html

            return (new Response())
                ->withHeader('Location', $uri )
                ->withStatus("200");

        } catch  ( \Exception $e ) {
            $this->redirect(null, null , NULL, null , $pid);
        }

    }
    
    /**
     * action edit
     *
     * @param Event $event
     * @param integer $copy2Day if set, Copy the event to the given DateDiff in DAYS
     * @param integer $amount if set, the amount of Copys that shall be created
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     * @return void
     */
    public function editAction(Event $event , $copy2Day=0 , $amount=0 )
    {

        if($this->isUserOrganizer() ) {
            if( $this->hasUserAccess( $event->getOrganizer() )) {

                // ++++++++++ if ation is called with second parameter ( int val in Days ) jump to the function copy
                if( $this->request->hasArgument('copy2Day')) {
                    $copy2Day = $this->request->getArgument('copy2Day')  ;
                    if( $this->request->hasArgument('amount')) {
                        $amount = $this->request->getArgument('amount');
                    }
                    // ammount must be not more than max 8 / min 1
                    $amount = min ( max ( $amount , 1) , 8 ) ;
                    if( $amount != 0 ) {
                        $this->copyEvent($event , intval( $copy2Day ) , $amount ) ;
                   }
                }
                /** @var QueryResultInterface $categories */
                $categories = $this->categoryRepository->findAllonAllPages( '0' );

                /** @var QueryResultInterface $tags */
                $tags = $this->tagRepository->findAllonAllPages('0');

                $this->view->assign('user', intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) ;

                // remove RegistrationFormPid to be able to use Checkbox: maybe  will be set back to default on SAVE

                if ( $event->getRegistrationFormPid() == $this->settings['EmConfiguration']['RegistrationFormPid'] ) {
                    $event->setRegistrationFormPid( 0);
                }
                $this->view->assign('event', $event);
                $this->view->assign('categories', $categories);
                $this->view->assign('tags', $tags);


                $filter['startDate'] = $event->getStartDate()->getTimestamp() ;
                $filter['maxDays'] = 999 ;
                $filter['skipEvent'] = $event->getUid() ;
                $filter['masterId'] = $event->getMasterId() ;

                $relatedEvents = $this->eventRepository->findByFilter($filter ) ;
                $this->view->assign('relatedEvents', $relatedEvents );

                $locations= $this->locationRepository->findByOrganizersAllpages( array(0 => $event->getOrganizer() ) , FALSE, FALSE ) ;
                $this->view->assign('locations', $locations );

            } else {
                $this->view->assign('event', FALSE );
            }
        } else {
            $this->view->assign('event', FALSE );
        }
    }

    /**
     * @param Event $event
     * @param int $copy2Day
     * @param int $amount
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    private function copyEvent(Event $event, $copy2Day= 0, $amount= 0 ){
        // Does the Copy Master Event already have a masterId? if not, use its uid as new  Master
        // with this master ID we are able to update Changes from one event to all events with the same master Id
        // if we just do ONE Copy, do not set master ID

        if ( ( intval( $event->getMasterId() ) < 1 || $event->getMasterId() === null ) && $copy2Day > 0 ) {
            $event->setMasterId( $event->getUid() ) ;
            $this->eventRepository->update($event) ;
        }


        $newDate = new \DateTime(  ) ;
        $newDate->setTimestamp($event->getStartDate()->getTimestamp()) ;
        $newDate->setTime(0,0,0);

        $addDays = intval( $copy2Day  ) ;
        $diff= date_interval_create_from_date_string( $addDays  . " days") ;

        for ( $i=1 ;$i<= $amount ; $i++) {
            /** @var Event $newEvent */

            $newEvent = $this->objectManager->get( \JVE\JvEvents\Domain\Model\Event::class)  ;

            $properties = $event->_getProperties() ;
            unset($properties['uid']) ;

            // copy  most properties
            foreach ($properties as $key => $value ) {
                $newEvent->_setProperty( $key , $value ) ;
            }


            // unset all registion Infos Minimal this
            $newEvent->setRegisteredSeats(0) ;
            $newEvent->setUnconfirmedSeats(0) ;
            $newEvent->setViewed(0) ;
            $newEvent->setTopEvent(0) ;

            // then have a look at Ext Conf
            $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class) ->get('jv_events');

            $fields =  $extConf['resetFieldListAfterCopy']   ;

            // default: setUnconfirmedSeats:0;setRegisteredSeats:0;setSalesForceEventId:"";setSalesForceSessionId:""
            $fieldsArray = explode(";" , trim($fields)  ) ;
            if( is_array($fieldsArray)) {
                foreach ($fieldsArray as $value ) {
                    $fieldsArraySub = explode(":" , trim($value)  ) ;
                    if( is_array($fieldsArraySub)) {
                        $func = $fieldsArraySub[0] ;

                        if(method_exists($newEvent , $func )) {
                            if(strlen($fieldsArraySub[1]) == 0 ) {
                                $newEvent->$func( "" ) ;
                            } else {
                                $newEvent->$func( $fieldsArraySub[1] ) ;
                            }


                            // echo "<hr>event->" . $func . "(" . $fieldsArraySub[1] . ") ;" ;
                        }
                    }
                }
            }

            // if we just do ONE Copy, do not set master ID
            if ( $copy2Day == 0 ) {
                $newEvent->setMasterId( 0 ) ;
            }
            $newDate->add( $diff) ;
            $newEvent->setStartDate($newDate ) ;
            /** @var \DateTime $newRegDate */
            $newRegDate = new \DateTime(  ) ;
            $newRegTimestamp= $newEvent->getStartDate()->getTimestamp() ;
            $newRegDate->setTimestamp($newRegTimestamp) ;
            $addHours = 23 ;
            if (  $newRegDate->format("I")  == "1" ) {
                $addHours = 22 ;
            }
            $newRegDate->setTime($addHours,0,0);

            $newEvent->setRegistrationUntil( $newRegDate );
            $newEvent->setSysLanguageUid(-1 ) ;

            // ++++ now copy the Categories and tags  ++++
            if( $event->getEventCategory() ) {
                /** @var \JVE\JvEvents\Domain\Model\Category $category */
                foreach ($event->getEventCategory() as $category ) {
                    $newEvent->addEventCategory($category) ;
                }
            }

            if( $event->getTags() ) {
                /** @var \JVE\JvEvents\Domain\Model\Tag $tag */
                foreach ($event->getTags() as $tag ) {
                    if ( $tag->getNocopy() != 1 ) {
                        $newEvent->addTag($tag) ;
                    } else {
                        $newEvent->removeTag($tag);
                    }
                }
            }




            // ----- end copy the Categories and tags and now  Teaser image  -----

            $this->eventRepository->add($newEvent) ;
            try {
                $this->persistenceManager->persistAll() ;
                $this->addFlashMessage('The Event was copied to: ' . $newEvent->getStartDate( )->format("d.m.Y"), '', AbstractMessage::OK);

            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error in action ' . ' - copyEvent -', AbstractMessage::WARNING);
            }
            $eventUid = $newEvent->getUid() ;

            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

            // --------- now fix Slug Name
            $row['name'] =  $newEvent->getName() ;
            $row['pid'] =  $newEvent->getPid() ;
            $row['parentpid'] =  1 ;
            $row['uid'] =  $eventUid;
            $row['sys_language_uid'] =  $newEvent->getSysLanguageUid() ;
            $row['slug'] =  $newEvent->getSlug() ;
            $row['start_date'] =  $newEvent->getStartDate()->format("d-m-Y") ;
            $slug = SlugUtility::getSlug("tx_jvevents_domain_model_event", "slug", $row  )  ;
            // $newEvent->setSlug( $slug ) ;
            // $this->eventRepository->update($newEvent) ;
            /** @var \TYPO3\CMS\Core\Database\Connection $dbConnectionForSysRef */
            $dbConnectionForSlug = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_event');

            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderEvent */
            $queryBuilderEvent = $dbConnectionForSlug->createQueryBuilder();
            $queryBuilderEvent->update('tx_jvevents_domain_model_event')->set('slug' , $slug )
                ->where( $queryBuilderEvent->expr()->eq('uid' , $queryBuilderEvent->createNamedParameter( $eventUid , \PDO::PARAM_INT )) )
                ->execute() ;


            /** @var \TYPO3\CMS\Core\Database\Connection $dbConnectionForSysRef */
            $dbConnectionForSysRef = $connectionPool->getConnectionForTable('sys_file_reference');

            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
            $queryBuilder = $dbConnectionForSysRef->createQueryBuilder();

            $affectedRows = $queryBuilder
                ->select( "uid","uid_local" )
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter( $event->getUid() )),
                    $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter( 'tx_jvevents_domain_model_event' )),
                    $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter( 'teaser_image' ))

                )
                ->execute();


            $mediaRow = $affectedRows->fetchAssociative() ;
            if ( is_array( $mediaRow)) {
                $affectedRows = $queryBuilder
                    ->insert('sys_file_reference')
                    ->values([
                        'uid_local' => $mediaRow['uid_local'] ,
                        'table_local' => 'sys_file'  ,
                        'uid_foreign' => intval( $eventUid ) ,
                        'tablenames' => 'tx_jvevents_domain_model_event'  ,
                        'fieldname' => 'teaser_image'  ,

                    ])
                    ->execute();
            }

            if (  $i == $amount ) {
                $this->updateLatestEvent($newEvent , $newEvent->getStartDate()) ;
            }
            unset( $newEvent ) ;
        }
        // got from EM Settings
        $clearCachePids = GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
        if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
            $clearCachePids[] = $GLOBALS['TSFE']->id ;
            $this->cacheService->clearPageCache( $clearCachePids );
            $this->addFlashMessage('The object was updated and Cache of following pages are cleared: ' . implode("," , $clearCachePids), '', AbstractMessage::OK);
        }
        $action = "show" ;
        if ( $copy2Day == 0 &&  $amount == 1 ) {
            $action = "edit" ;
        }
       $this->redirect($action , null , null , array( "event" => $eventUid  )) ;

    }
    /**
     * action cancel - will toogle the canceled status
     *
     * @param Event $event
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     * @return void
     */
    public function cancelAction(Event $event)
    {
        if($this->isUserOrganizer() ) {
            if( $this->hasUserAccess( $event->getOrganizer() )) {
                if( $event->getCanceled() ) {
                    $event->setCanceled( '0' ) ;
                } else {
                    $event->setCanceled( '1' ) ;
                }
                $event->setLastUpdated(time());
                if ( intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) {
                    $event->setLastUpdatedBy(intval($GLOBALS['TSFE']->fe_user->user['uid'] ) );
                }


                try {
                    $this->eventRepository->update($event) ;

                    if( ExtensionManagementUtility::isLoaded("jv_banners")) {
                        $bannerRepository = GeneralUtility::makeInstance(BannerRepository::class);
                        $row = $bannerRepository->getBannerByEventId( intval( $event->getUid()) );
                        if ( $row) {

                            $update['uid'] = $row['uid'];
                            $update['hidden'] = 1;
                            $bannerRepository->updateBanner( $update );
                            $this->addFlashMessage('The Banner ' .  $row['uid'] . ' will be stopped also soon. (<4 h) ', '', AbstractMessage::OK);
                        }
                    }

                    // got from EM Settings
                    $clearCachePids = GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
                    if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
                        $clearCachePids[] = $GLOBALS['TSFE']->id ;
                        $this->cacheService->clearPageCache( $clearCachePids );
                        $this->addFlashMessage('The object was updated and Cache of following pages are cleared: ' . implode("," , $clearCachePids), '', AbstractMessage::OK);
                    } else {
                        $this->addFlashMessage('The object was updated.', '', AbstractMessage::OK);
                    }

                } catch ( \Exception $e ) {
                    $this->addFlashMessage($e->getMessage() , 'Error', AbstractMessage::WARNING);

                }
                $this->persistenceManager->persistAll() ;
            }


        }

        $this->redirect('show' ,null , null , array("event" => $event )) ;
    }
    /**
     * action update
     *
     * @param Event $event
     *  @TYPO3\CMS\Extbase\Annotation\Validate(param="event" , validator="JVE\JvEvents\Validation\Validator\EventValidator")
     * @return void
     */
    public function updateAction(Event $event)
    {

        if( $this->request->hasArgument('event')) {
            $event = $this->cleanEventArguments( $event) ;
        }

        if ( $this->hasUserAccess($event->getOrganizer() )) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            try {
                /** @var ConnectionPool $connection */
                /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
                $connection = GeneralUtility::makeInstance(ConnectionPool::class);
                $queryBuilder = $connection->getQueryBuilderForTable('tx_jvevents_domain_model_event');
                $oldEventRows = $queryBuilder->select('*' )
                    ->from('tx_jvevents_domain_model_event')
                    ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($event->getUid(), \PDO::PARAM_INT)) )
                    ->execute()
                    ->fetchAllAssociative();

                if ( count($oldEventRows ) > 0 ) {
                    $oldEventRow = $oldEventRows[0] ;
                }
                if( ExtensionManagementUtility::isLoaded("jv_banners")) {

                    // update Banner Text etc
                    $bannerRepository = GeneralUtility::makeInstance(BannerRepository::class);
                    $row = $bannerRepository->getBannerByEventId( intval( $event->getUid()) );
                    if ( $row) {

                        $row['title'] = $event->getName();
                        $row['description'] = $event->getTeaser();
                        $html = $event->getStartDate()->format("d.m.Y") . "<br>\n" ;
                        $html .= date( "H:i" , $event->getStartTime() ) . "-" ;
                        $html .= date( "H:i" , $event->getEndTime() ) . "<br>\n" ;
                        if( $event->getLocation() ) {
                            $html .= $event->getLocation()->getCity() ;
                        }
                        $row['html'] = $html;
                        $bannerRepository->updateBanner( $row );

                        // update image
                        $assetData = AssetUtility::loadSysFileReference($event->getUid() , "tx_jvevents_domain_model_event" , "teaser_image") ;
                        $imageFrom = "Event" ;
                        if( !is_array($assetData )) {
                            $imageFrom = "Location" ;

                            $assetData = AssetUtility::loadSysFileReference($event->getLocation()->getUid() , "tx_jvevents_domain_model_location" , "teaser_image") ;
                        }
                        if( !is_array($assetData )) {
                            $imageFrom = "Organizer" ;
                            $assetData = AssetUtility::loadSysFileReference($event->getOrganizer()->getUid() , "tx_jvevents_domain_model_organizer" , "teaser_image") ;
                        }
                        if( is_array($assetData )) {
                            $assetDataLink = AssetUtility::loadSysFileReference( $row['uid'] , "tx_sfbanners_domain_model_banner" , "assets") ;

                            if( $assetDataLink ) {
                                AssetUtility::updateUidLocal($assetDataLink['uid'] , ['uid_local' => $assetData['uid_local'] ] ) ;
                            }

                        }


                        $this->addFlashMessage('The Banner ' .  $row['uid'] . ' will be updated soon. (<4 h). Changed Images may take more time.', '', AbstractMessage::OK);
                    }
                }

                $event->setLastUpdated(time());
                if ( intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) {
                    $event->setLastUpdatedBy(intval($GLOBALS['TSFE']->fe_user->user['uid'] ) );
                }
                $this->eventRepository->update($event) ;
                if( $event->getChangeFutureEvents() && $event->getMasterId() > 0 ) {
                    $filter['startDate'] = $event->getStartDate()->getTimestamp() ;
                    $filter['maxDays'] = 999 ;
                    $filter['skipEvent'] = $event->getUid() ;
                    $filter['masterId'] = $event->getMasterId() ;

                    $otherEvents = $this->eventRepository->findByFilter($filter ) ;
                    /** @var Event $otherEvent */
                    if ( count($otherEvents) > 0 ) {
                        $otherDaysText = " " ;
                        foreach ( $otherEvents as $otherEvent ) {
                            $otherEvent->setLastUpdated(time());
                            if ( intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) {
                                $otherEvent->setLastUpdatedBy(intval($GLOBALS['TSFE']->fe_user->user['uid'] ) );
                            }

                            $otherDaysText .= $otherEvent->getStartDate()->format("d.M-Y") .  " (Id:" . $otherEvent->getUid() ."), " ;
                            if( $oldEventRow['name'] != $event->getName() ) {
                                $otherEvent->setName( $event->getName() ) ;
                            }
                            if( $oldEventRow['teaser'] != $event->getTeaser() ) {
                                $otherEvent->setTeaser( $event->getTeaser() ) ;
                            }
                            if( $oldEventRow['description'] != $event->getDescription() ) {
                                $otherEvent->setDescription( $event->getDescription() ) ;
                            }
                            if( $oldEventRow['price'] != $event->getPrice() ) {
                                $otherEvent->setPrice( $event->getPrice() ) ;
                            }
                            if( $oldEventRow['start_time'] != $event->getStartTime() ) {
                                $otherEvent->setStartTime( $event->getStartTime() ) ;
                            }
                            if( $oldEventRow['end_time'] != $event->getEndTime() ) {
                                $otherEvent->setEndTime( $event->getEndTime() ) ;
                            }
                            if( $oldEventRow['entry_time'] != $event->getEntryTime() ) {
                                $otherEvent->setEntryTime( $event->getEntryTime() ) ;
                            }
                            if( $oldEventRow['price_reduced'] != $event->getPriceReduced() ) {
                                $otherEvent->setPriceReduced( $event->getPriceReduced() ) ;
                            }
                            if( $oldEventRow['price_reduced_text'] != $event->getPriceReducedText() ) {
                                $otherEvent->setPriceReducedText( $event->getPriceReducedText() ) ;
                            }

                            // --------- now fix Slug Name
                                $row['name'] = $otherEvent->getName();
                                $row['pid'] = $otherEvent->getPid();
                                $row['parentpid'] = 1;
                                $row['uid'] = $otherEvent->getUid();
                                $row['sys_language_uid'] = $otherEvent->getSysLanguageUid();
                                $row['slug'] = $otherEvent->getSlug();
                                $row['start_date'] = $otherEvent->getStartDate()->format("d-m-Y");
                                $slug = SlugUtility::getSlug("tx_jvevents_domain_model_event", "slug", $row);
                                $otherEvent->setSlug($slug);

                            $this->eventRepository->update($otherEvent) ;

                        }
                        $this->addFlashMessage('The following Events : ' . $otherDaysText . ' were also updated.', '', AbstractMessage::OK);

                    }
                }

                $this->updateLatestEvent($event , $event->getStartDate()) ;

                // got from EM Settings
                $clearCachePids = GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
                if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
                    $this->cacheService->clearPageCache( $clearCachePids );
                    $this->addFlashMessage('The object was updated and Cache of following pages are cleared: ' . implode("," , $clearCachePids), '', AbstractMessage::OK);
                } else {
                    $this->addFlashMessage('The object was updated.', '', AbstractMessage::OK);
                }

            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', AbstractMessage::WARNING);

            }

        } else {
            $this->addFlashMessage('You do not have access rights to change this data.' . $event->getUid() , '', AbstractMessage::WARNING);
        }
        $this->persistenceManager->persistAll() ;
        $pid = $this->settings['pageIds']['showEventDetail'] ;
        // if PID from TS settings is set: if User is not logged in-> Page with loginForm , on success -> showEventDetail  Page
        if( $pid < 1) {
            // else : stay on this page
            $pid = $GLOBALS['TSFE']->id ;

        }
        $this->redirect('show' , NULL, Null , array( "event" => $event) ,$pid  );

    }
    
    /**
     * action delete
     *
     * @param Event $event
     * @param integer $deleteFutureEvents
     * @return void
     */
    public function deleteAction(Event $event , $deleteFutureEvents = 0)
    {

        if($this->request->hasArgument('deleteFutureEvents') ) {
            $deleteFutureEvents = $this->request->getArgument('deleteFutureEvents') ;
        }
        $orgId = $event->getOrganizer() ;
        if ( $this->hasUserAccess($event->getOrganizer() )) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            try {
                $masterId = $event->getMasterId() ;
                $event->setLastUpdated(time());
                if ( intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) {
                    $event->setLastUpdatedBy(intval($GLOBALS['TSFE']->fe_user->user['uid'] ) );
                }

                if( ExtensionManagementUtility::isLoaded("jv_banners")) {
                    $bannerRepository = GeneralUtility::makeInstance(BannerRepository::class);
                    $row = $bannerRepository->getBannerByEventId( intval( $event->getUid()) );
                    if ( $row) {

                        $update['uid'] = $row['uid'];
                        $update['hidden'] = 1;
                        $update['deleted'] = 1;
                        $bannerRepository->updateBanner( $update );
                        $this->addFlashMessage('The Banner ' .  $row['uid'] . ' will be deleted also soon. (<4 h) ', '', AbstractMessage::OK);
                    }
                }

                $this->eventRepository->remove($event) ;

                if( $masterId && $deleteFutureEvents) {
                    $querysettings =$this->subeventRepository->getTYPO3QuerySettings() ;
                    $querysettings->setStoragePageIds(array( $event->getPid() )) ;

                    $this->eventRepository->setDefaultQuerySettings( $querysettings );
                    $filter = array() ;
                    $filter['startDate'] = $event->getStartDate()->getTimestamp() ;
                    $filter['maxDays'] = 999 ;
                    $filter['skipEvent'] = $event->getUid() ;
                    $filter['masterId']  = $event->getMasterId() ;

                    $otherEvents = $this->eventRepository->findByFilter($filter ) ;
                    /** @var Event $otherEvent */
                    if ( count($otherEvents) > 0 ) {
                        $otherDaysText = count($otherEvents) . " Copies: "  ;
                        foreach ( $otherEvents as $otherEvent ) {
                            if( $otherEvent) {
                                $otherDaysText .= $otherEvent->getStartDate()->format("d.M-Y") .  " (Id:" . $otherEvent->getUid() ."), " ;
                                $otherEvent->setLastUpdated(time());
                                if ( intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) {
                                    $otherEvent->setLastUpdatedBy(intval($GLOBALS['TSFE']->fe_user->user['uid'] ) );
                                }
                                $this->eventRepository->remove($otherEvent) ;
                            }
                        }
                        $otherDaysText .= " also deleted." ;
                    }
                }

                // got from EM Settings
                $clearCachePids = GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
                if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
                    $this->cacheService->clearPageCache( $clearCachePids );
                    $this->addFlashMessage('The object was successfully deleted and Cache of following pages are cleared: ' . implode("," , $clearCachePids), '', AbstractMessage::OK);
                } else {
                    $this->addFlashMessage('The Event was deleted.', '', AbstractMessage::OK);
                }
                if( $otherDaysText ) {
                    $this->addFlashMessage($otherDaysText, '', AbstractMessage::OK);
                }

            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', AbstractMessage::WARNING);

            }

        } else {
            $this->addFlashMessage('You do not have access rights to delete this event.' . $event->getUid() , '', AbstractMessage::WARNING);
        }
        $this->persistenceManager->persistAll() ;

        $arguments = array ( 'overruleFilter' => array( 'organizer' => $orgId , 'category' => 'true' , 'maxDays' => 90 )) ;

        $this->redirect('list' , null , null , $arguments, $this->settings['pageIds']['eventList']);
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
	public function generateToken($action = "action"): string
    {
		/** @var \TYPO3\CMS\Core\FormProtection\FrontendFormProtection $formClass */
		$formClass =  $this->objectManager->get( \TYPO3\CMS\Core\FormProtection\FrontendFormProtection::class) ;

		return $formClass->generateToken(
			'event', $action ,   "P" . $this->settings['pageId'] . "-L" .$this->settings['sys_language_uid']
		);

	}

    /**
     * @param Event $event
     * @return mixed
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
	public function cleanEventArguments(Event  $event)
    {
        // Type validation should be done in validator class so we can ignore issue with wrong format
        $eventArray = $this->request->getArgument('event');



        /*   +******  Update the Category  ************* */
        $eventCatUid = intval($eventArray['eventCategory']) ;
        /** @var \JVE\JvEvents\Domain\Model\Category $eventCat */
        $eventCat = $this->categoryRepository->findByUid($eventCatUid) ;

        if($eventCat) {
            if( $event->getEventCategory() ){
                $event->getEventCategory()->removeAll($event->getEventCategory()) ;
            }
            $event->addEventCategory($eventCat) ;
        }


        // Update the Tags
        $eventTagUids = GeneralUtility::trimExplode("," , $eventArray['tagsFE'] , true ) ;
        if( is_array($eventTagUids) && count($eventTagUids) > 0  ) {
            $existingTags = $event->getTags() ;
            $existingTagsArray = array() ;
            $notexistingTagsArray = array() ;
            if ( $existingTags ) {
                /** @var  \JVE\JvEvents\Domain\Model\Tag $existingTag */
                foreach ( $existingTags as $existingTag ) {
                    if( !in_array( $existingTag->getUid()  , $eventTagUids , false)) {
                        $notexistingTagsArray[] = $existingTag ;
                    } else {
                        $existingTagsArray[] = $existingTag->getUid() ;
                    }
                }
                if ( count( $notexistingTagsArray) > 0 ) {
                    foreach ( $notexistingTagsArray as $notexistingTag ) {
                        $event->removeTag( $notexistingTag) ;
                    }
                }
            }
            foreach ($eventTagUids as $eventTagUid) {
                if( intval( $eventTagUid ) > 0 && !in_array( $eventTagUid , $existingTagsArray , false )) {
                    /** @var  \JVE\JvEvents\Domain\Model\Tag $eventTag */
                    $eventTag = $this->tagRepository->findByUid($eventTagUid) ;
                    if($eventTag) {
                        $event->addTag($eventTag) ;
                    }
                }
            }
        }
        $stD = \DateTime::createFromFormat('d.m.Y', $eventArray['startDateFE']  );
        $stD->setTime(0,0,0,0 ) ;
        $event->setStartDate( $stD ) ;

        $startT =  ( intval( substr( $eventArray['startTimeFE']  , 0,2 ) ) * 3600 )
                    + ( intval( substr( $eventArray['startTimeFE']  , 3,2 ) ) * 60 ) ;
        $event->setStartTime( $startT ) ;
        if ( trim($eventArray['endTimeFE'] == "00:00")) {
            $endT = ((3600 * 23)  + (59*60)) ;
        } else {
            $endT =  ( intval( substr( $eventArray['endTimeFE']  , 0,2 ) ) * 3600 )
                + ( intval( substr( $eventArray['endTimeFE']  , 3,2 ) ) * 60  ) ;
        }

        $event->setEndTime( $endT ) ;

        $desc = str_replace( array( "\n" , "\r" , "\t" ), array(" " , "" , " " ), $eventArray['description'] ) ;
        $desc = strip_tags($desc , "<p><br><a><i><strong><h2><h3>") ;

        $event->setDescription( $desc ) ;
        $desc = str_replace( array( "\r" , "\t" ), array( "" , " " ), $eventArray['introtextRegistrant'] ) ;
        $desc = strip_tags($desc , "<b>") ;
        $event->setIntrotextRegistrant( $desc ) ;

        $desc = str_replace( array(  "\r" , "\t" ), array( "" , " " ), $eventArray['introtextRegistrantConfirmed'] ) ;
        $desc = strip_tags($desc , "<b>") ;
        $event->setIntrotextRegistrantConfirmed( $desc ) ;

        $event->setChangeFutureEvents( $eventArray['changeFutureEvents'] ) ;
        $event->setAvailableSeats(intval($event->getAvailableSeats()));
        $event->setAvailableWaitingSeats(intval($event->getAvailableWaitingSeats()));

        if(!array_key_exists( 'registrationShowStatus' , $eventArray ) || is_null($eventArray['registrationShowStatus']))  {
            $event->setRegistrationShowStatus( 0) ;
        } else {
            $event->setRegistrationShowStatus( intval($event->getRegistrationShowStatus())) ;
        }

        $event->setRegistrationGender( intval($event->getRegistrationGender())) ;
        $event->setWithRegistration(intval($event->getWithRegistration()));
        if( intval($event->getWithRegistration()) == 1 ) {
            $regD = \DateTime::createFromFormat('d.m.Y', $eventArray['startDateFE']  );
            $addHours = 23 ;
            if (  $regD->format("I")  == "1" ) {
                $addHours = 22 ;
            }
            $regD->setTime($addHours,0,0,0 ) ;

            $event->setRegistrationUntil( $regD );
            $event->setNotifyOrganizer($this->settings['EmConfiguration']['notifyOrganizer']);
            $event->setNotifyRegistrant($this->settings['EmConfiguration']['notifyRegistrant']);
            if ( $eventArray['registrationFormPid'] > $this->settings['EmConfiguration']['RegistrationFormPid'] ) {
                $event->setRegistrationFormPid( intval( $eventArray['registrationFormPid']) ) ;
            } else {
                $event->setRegistrationFormPid($this->settings['EmConfiguration']['RegistrationFormPid']);
            }
            $event->setRegistrationPid($this->settings['EmConfiguration']['RegistrationPid']);

            // var_dump($eventArray['registrationFormPid'] );
            // die;
        }

        if ( $event->getPid() < 1 ) {
            // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
            $event->setPid( 12 ) ;
        }

            $row['name'] =  $event->getName() ;
            $row['pid'] =  $event->getPid() ;
            $row['parentpid'] =  1 ;
            $row['uid'] =  $event->getUid() ? $event->getUid() : 0  ;
            $row['sys_language_uid'] = -1 ;
            $row['start_date'] =  $event->getStartDate()->format("d-m-Y") ;
            $row['slug'] =  $event->getSlug() ? $event->getSlug() : $event->getName() . "-" . $row['start_date'] ;
            $slug = SlugUtility::getSlug("tx_jvevents_domain_model_event", "slug", $row  )  ;
            $event->setSlug( $slug ) ;

        return $event ;
    }

    /**
     * @param Event $event   The Event nneded to get Location and Organizer
     * @param \DateTime|null $date  Will used to set The Latest Events Date
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function updateLatestEvent( Event $event , \DateTime $date = NULL ) {
        if( $date == NULL ) {
            $date = new \DateTime('now' ) ;
            // Idea : : get latest Date from any other event of this organizer ?
        }
        $organizer = $event->getOrganizer() ;
        if( is_object( $organizer )) {
            if ( $organizer->getUid() > 0 ) {
                if ( $date > $organizer->getLatestEvent() ) {
                    $organizer->setLatestEvent( $date ) ;
                }

                $organizer->setTstamp( time() ) ;
                $this->organizerRepository->update($organizer ) ;
            }
        }
        $location = $event->getLocation() ;
        if( is_object( $location )) {
            if ( $location->getUid() > 0 ) {
                if ( $date > $location->getLatestEvent() ) {
                    $location->setLatestEvent($date);
                }
                $this->locationRepository->update($location ) ;
            }
        }
    }

}