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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;

/**
 * EventController
 */
class EventController extends BaseController
{

    /**
     * eventRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository = NULL;

    /**
     * @var array
     */
    public $debugArray ;


    /**
     * @var float
     */
    public $timeStart ;


	/**
	 * staticCountryRepository
	 *
	 * @var \JVE\JvEvents\Domain\Repository\StaticCountryRepository
	 * @inject
	 */
	protected $staticCountryRepository = NULL;

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
        /** @var \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration $propertyMappingConfiguration */
        //  $propertyMappingConfiguration = $this->arguments['event']->getPropertyMappingConfiguration();
        //  $propertyMappingConfiguration->allowProperties('tags') ;

        parent::initializeAction() ;
        $this->debugArray[] = "Init Done:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
	}
    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $this->debugArray[] = "After Init :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;

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
                }
            }
            if( array_key_exists( "organizer" , $filter )) {
                if( $filter['organizer']  ) {
                    $this->settings['filter']['organizer'] = intval( $filter['organizer'] ) ;
                }
            }

            // https://tango.ddev.local/index.php?id=9&L=0&&tx_jvevents_events[eventsFilter][organizers]=1&tx_jvevents_events[eventsFilter][tags]=5,6,8,7,10,4,1,20,11,14,&tx_jvevents_events[eventsFilter][citys]=undefined&tx_jvevents_events[eventsFilter][months]=undefined&tx_jvevents_events[overruleFilter][category]=true&no_cache=1
        }
        $this->debugArray[] = "Load Events:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events */
        $events = $this->eventRepository->findByFilter(false, false,  $this->settings );
        $this->view->assign('events', $events);
        // read settings from Flexform .. if not set, take it from typoscript setup
        if( intval( $this->settings['detailPid'] ) < 1 ) {
            $this->settings['detailPid'] = intval( $this->settings['link']['detailPidDefault']) ;
        }
        $this->debugArray[] = "Before generate Filter :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;
        $eventsFilter = $this->generateFilter( $events ,  $this->settings['filter']) ;
        $this->debugArray[] = "After generate Filter :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;

        $this->view->assign('eventsFilter', $eventsFilter);
        $this->view->assign('settings', $this->settings );
        $this->debugArray[] = "before Render:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
        $this->view->assign('debugArray', $this->debugArray );
    }
    
    /**
     * action show
     *
     * @param \JVE\JvEvents\Domain\Model\Event|null $event
     * @ignorevalidation $event
     * @return void
     */
    public function showAction(\JVE\JvEvents\Domain\Model\Event $event=null)
    {
        if( $event ) {
            $checkString = $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate();
            $checkHash = hash("sha256", $checkString);

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
            $this->view->assign('hash', $checkHash);
        }
        $this->view->assign('settings', $this->settings);
		$this->view->assign('event', $event);
    }
    
    /**
     * action new
     * @param \JVE\JvEvents\Domain\Model\Event|null $event
     * @ignorevalidation $event
     *
     * @return void
     */
    public function newAction(\JVE\JvEvents\Domain\Model\Event $event=null)
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $categories */
        $categories = $this->categoryRepository->findAllonAllPages( '0' );

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $tags */
        $tags = $this->tagRepository->findAllonAllPages('0');

        if ( $event==null) {
            /** @var \JVE\JvEvents\Domain\Model\Event $event */
            $event = $this->objectManager->get("JVE\\JvEvents\\Domain\\Model\\Event");
        }
        if ( $event->getUid() < 1 ) {
            $event->setStartDate( new \DateTime ) ;

            /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
            $organizer = $this->getOrganizer() ;
            if ($organizer instanceof \JVE\JvEvents\Domain\Model\Organizer) {
                $event->setOrganizer($organizer);
                $this->view->assign('organizer', $organizer );
            } else {
                $pid = $this->settings['pageIds']['loginForm'] ;
                $this->addFlashMessage('You are not logged in as Organizer.'  , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
                $this->redirect(null , null , NULL , NULL , $pid );
            }

            if( $this->request->hasArgument('location')) {
                /** @var \JVE\JvEvents\Domain\Model\Location $location */
                $location= $this->locationRepository->findByUid( intval( $this->request->getArgument('location') )) ;
                if($location instanceof  \JVE\JvEvents\Domain\Model\Location ) {
                    $event->setLocation($location ) ;
                }
                $this->view->assign('location', $location );
            } else {
                $locations= $this->locationRepository->findByOrganizersAllpages( array(0 => $organizer->getUid()) , FALSE, FALSE ) ;
                $this->view->assign('locations', $locations );
            }

            $event->setEventType( 2 ) ;

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
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @validate $event \JVE\JvEvents\Validation\Validator\EventValidator
     * @return void
     */
    public function createAction(\JVE\JvEvents\Domain\Model\Event $event)
    {

        if( $this->request->hasArgument('event')) {
            $event = $this->cleanEventArguments( $event) ;
        }
        $action = "edit" ;
        if($this->isUserOrganizer() ) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();
            $event->setSysLanguageUid(-1) ;

            try {
                $this->eventRepository->add($event);
                $this->persistenceManager->persistAll() ;

                // got from EM Settings
                $clearCachePids = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
                if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
                    $this->cacheService->clearPageCache( $clearCachePids );
                    $this->addFlashMessage('The object was created and Cache of following pages are cleared: ' . implode("," , $clearCachePids), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                } else {
                    $this->addFlashMessage('The object was created.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                }
                $pid = $this->settings['pageIds']['showEventDetail'] ;
                $action = "show" ;
            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);

            }

        } else {
            $action = false ;

            $pid = $this->settings['pageIds']['loginForm'] ;
            $this->addFlashMessage('The object was NOT created. You are not logged in as Organizer.' . $event->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
            $this->redirect(null , null , NULL , array( 'event' => $event ) , $pid );
        }
        // if PID from TS settings is set: if User is not logged in-> Page with loginForm , on success -> showEventDetail  Page
        if( $pid < 1) {
            // else : stay on this page
            $pid = $GLOBALS['TSFE']->id ;

        }
        $this->redirect($action , 'Event' , NULL , array( 'event' => $event ) , $pid );
    }
    
    /**
     * action edit
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @param integer $copy2Date if set, Copy the event to the given DateDiff in DAYS
     * @param integer $amount if set, the amount of Copys that shall be created
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @ignorevalidation $event
     * @return void
     */
    public function editAction(\JVE\JvEvents\Domain\Model\Event $event , $copy2Day=0 , $amount=0 )
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
                    if( $copy2Day != 0 ) {
                        $this->copyEvent($event , intval( $copy2Day ) , $amount ) ;
                   }
                }
                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $categories */
                $categories = $this->categoryRepository->findAllonAllPages( '0' );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $tags */
                $tags = $this->tagRepository->findAllonAllPages('0');

                $this->view->assign('user', intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) ;
                $this->view->assign('event', $event);
                $this->view->assign('categories', $categories);
                $this->view->assign('tags', $tags);
            } else {
                $this->view->assign('event', FALSE );
            }
        } else {
            $this->view->assign('event', FALSE );
        }
    }

    /**
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @param int $copy2Day
     * @param int $amount
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    private function copyEvent(\JVE\JvEvents\Domain\Model\Event $event, $copy2Day= 0, $amount= 1){

        for ( $i=1 ;$i<= $amount ; $i++) {
            /** @var \JVE\JvEvents\Domain\Model\Event $newEvent */

            $newEvent = $this->objectManager->get( "JVE\\JvEvents\\Domain\\Model\\Event")  ;

            $properties = $event->_getProperties() ;
            unset($properties['uid']) ;

            // copy  most properties
            foreach ($properties as $key => $value ) {
                $newEvent->_setProperty( $key , $value ) ;
            }
            // now set the new Date
            $newDate = $event->getStartDate() ;
            $addDays = intval( $copy2Day  ) ;
            $diff= date_interval_create_from_date_string( $addDays  . " days") ;
            $newDate->add( $diff) ;

            $newEvent->setStartDate($newDate ) ;

            // Does the Copy Master Event already have a masterId? if not, use its uid as new  Master
            // wthi this master ID we are able to update Changes from one event to all events with the same master Id
            if( intval( $event->getMasterId() ) < 1 || $event->getMasterId() === null ) {
                $newEvent->setMasterId( $event->getUid() ) ;
            }

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
                    $newEvent->addTag($tag) ;
                }
            }

            // ----- end copy the Categories and tags and now  Teaser image  -----

            $this->eventRepository->add($newEvent) ;
            try {
                $this->persistenceManager->persistAll() ;
                $this->addFlashMessage('The Event was copied to: ' . $newEvent->getStartDate( )->format("d.m.Y"), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error in action ' . ' - copyEvent -', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
            }
            $eventUid = $newEvent->getUid() ;

            /** @var \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);

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

            $mediaRow = $affectedRows->fetch() ;

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

            unset( $newEvent ) ;
        }



       $this->redirect("edit" , null , null , array( "event" => $eventUid  )) ;

    }
    /**
     * action cancel - will toogle the canceled status
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @ignorevalidation $event
     * @return void
     */
    public function cancelAction(\JVE\JvEvents\Domain\Model\Event $event)
    {
        if($this->isUserOrganizer() ) {
            if( $this->hasUserAccess( $event->getOrganizer() )) {
                if( $event->getCanceled() ) {
                    $event->setCanceled( '0' ) ;
                } else {
                    $event->setCanceled( '1' ) ;
                }

                try {
                    $this->eventRepository->update($event) ;

                    // got from EM Settings
                    $clearCachePids = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
                    if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
                        $clearCachePids[] = $GLOBALS['TSFE']->id ;
                        $this->cacheService->clearPageCache( $clearCachePids );
                        $this->addFlashMessage('The object was updated and Cache of following pages are cleared: ' . implode("," , $clearCachePids), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                    } else {
                        $this->addFlashMessage('The object was updated.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                    }

                } catch ( \Exception $e ) {
                    $this->addFlashMessage($e->getMessage() , 'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);

                }
                $this->persistenceManager->persistAll() ;
            }


        }

        $this->redirect('show' ,null , null , array("event" => $event )) ;
    }
    /**
     * action update
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @validate $event \JVE\JvEvents\Validation\Validator\EventValidator
     * @return void
     */
    public function updateAction(\JVE\JvEvents\Domain\Model\Event $event)
    {
        if( $this->request->hasArgument('event')) {
            $event = $this->cleanEventArguments( $event) ;

        }

        if ( $this->hasUserAccess($event->getOrganizer() )) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            try {
                $this->eventRepository->update($event) ;


                // got from EM Settings
                $clearCachePids = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
                if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
                    $this->cacheService->clearPageCache( $clearCachePids );
                    $this->addFlashMessage('The object was updated and Cache of following pages are cleared: ' . implode("," , $clearCachePids), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                } else {
                    $this->addFlashMessage('The object was updated.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                }

            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);

            }

        } else {
            $this->addFlashMessage('You do not have access rights to change this data.' . $event->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }
        $this->persistenceManager->persistAll() ;

        $this->redirect('edit' , NULL, Null , array( "event" => $event));

    }
    
    /**
     * action delete
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @return void
     */
    public function deleteAction(\JVE\JvEvents\Domain\Model\Event $event)
    {
        $orgId = $event->getOrganizer() ;
        if ( $this->hasUserAccess($event->getOrganizer() )) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            try {
                $this->eventRepository->remove($event) ;

                // got from EM Settings
                $clearCachePids = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
                if( is_array($clearCachePids) && count( $clearCachePids) > 0 ) {
                    $this->cacheService->clearPageCache( $clearCachePids );
                    $this->addFlashMessage('The object was successfully deleted and Cache of following pages are cleared: ' . implode("," , $clearCachePids), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                } else {
                    $this->addFlashMessage('The Event was deleted.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                }

            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);

            }

        } else {
            $this->addFlashMessage('You do not have access rights to delete this event.' . $event->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }
        $this->persistenceManager->persistAll() ;

        $arguments = array ( 'overruleFilter' => array( 'organizer' => $orgId , 'category' => true , 'maxDays' => 90 )) ;

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
     * @return mixed
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
	public function cleanEventArguments(\JVE\JvEvents\Domain\Model\Event  $event)
    {
        // validation should be done in validatar class so we can ignore issue with wrong format

        $eventArray = $this->request->getArgument('event');
        // Update the Categories
        $eventCatUid = intval($eventArray['eventCategory']) ;
        /** @var \JVE\JvEvents\Domain\Model\Category $eventCat */
        $eventCat = $this->categoryRepository->findByUid($eventCatUid) ;

        if($eventCat) {
            if( $event->getEventCategory() ){
                $event->getEventCategory()->removeAll($event->getEventCategory()) ;
            }
            $event->addEventCategory($eventCat) ;
        }
        // Update the Categories
        $eventTagUids =  $tagArray= \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $eventArray['tagsFE']) ;
        if( is_array($eventTagUids) && count($eventTagUids) > 0  ) {
            $existingTags = $event->getTags() ;

            if ( $existingTags ) {
                /** @var  \JVE\JvEvents\Domain\Model\Tag $existingTag */
                foreach ( $existingTags as $existingTag ) {
                    if( !in_array( $existingTag->getUid()  , $eventTagUids)) {
                        $event->getTags()->detach($existingTag) ;
                        unset($eventTagUids[$existingTag->getUid()] ) ;
                    }

                }
            }
            if( is_array($eventTagUids) && count($eventTagUids) > 0  ) {
                foreach ($eventTagUids as $eventTagUid) {
                    if( intval( $eventTagUid ) > 0 ) {
                        /** @var  \JVE\JvEvents\Domain\Model\Tag $eventTag */
                        $eventTag = $this->tagRepository->findByUid($eventTagUid) ;

                        if($eventTag) {
                            $event->addTag($eventTag) ;
                        }
                    }
                }
            }
        }

        $stD = \DateTime::createFromFormat('d.m.Y', $eventArray['startDateFE']  );
        $event->setStartDate( $stD ) ;

        $startT =  ( intval( substr( $eventArray['startTimeFE']  , 0,2 ) ) * 3600 )
                    + ( intval( substr( $eventArray['startTimeFE']  , 3,2 ) ) * 60 ) ;
        $event->setStartTime( $startT ) ;

        $endT =  ( intval( substr( $eventArray['endTimeFE']  , 0,2 ) ) * 3600 )
            + ( intval( substr( $eventArray['endTimeFE']  , 3,2 ) ) * 60  ) ;
        $event->setEndTime( $endT ) ;

        $desc = str_replace( array( "\n" , "\r" , "\t" ), array(" " , "" , " " ), $eventArray['description'] ) ;
        $desc = strip_tags($desc , "<p><br><a><i><strong><h2><h3>") ;

        $event->setDescription( $desc ) ;

        return $event ;
    }

}