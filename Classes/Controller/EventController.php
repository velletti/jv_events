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
		} else {
		    /** @var \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration $propertyMappingConfiguration */
            //  $propertyMappingConfiguration = $this->arguments['event']->getPropertyMappingConfiguration();
            //  $propertyMappingConfiguration->allowProperties('tags') ;
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
    	if( $this->request->hasArgument('startDate')) {
    		$startDate = $this->request->getArgument('startDate') ;
			$sdArr = explode("." , $startDate ) ;
			if( $sdArr[0] > 0 && $sdArr[0] < 32 && $sdArr[1] > 0 && $sdArr[1] < 13  && $sdArr[2] > 1970 ) {
				$this->settings['filter']['startDate'] = mktime(0,0,0, $sdArr[1] , $sdArr[0] , $sdArr[2] ) ;
			} else {
				$this->settings['filter']['startDate'] = intval( $startDate ) ;
			}

			// http://nemetschek.local/index.php?id=118&tx_jvevents_events[startDate]=25.11.2016
		}

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events */
        $events = $this->eventRepository->findByFilter(false, false,  $this->settings );


		$this->view->assign('events', $events);
        // read settings from Flexform .. if not set, take it from typoscript setup
        if( intval( $this->settings['detailPid'] ) < 1 ) {
            $this->settings['detailPid'] = intval( $this->settings['link']['detailPidDefault']) ;
        }

        $eventsFilter = $this->generateFilter( $events->toArray() ) ;
        $this->view->assign('eventsFilter', $eventsFilter);
        $this->view->assign('settings', $this->settings );
    }
    
    /**
     * action show
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @ignorevalidation $event
     * @return void
     */
    public function showAction(\JVE\JvEvents\Domain\Model\Event $event)
    {
        $checkString =  $_SERVER["SERVER_NAME"] . "-" . $event->getUid() . "-" . $event->getCrdate() ;
        $checkHash = hash("sha256" , $checkString ) ;

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

        $this->view->assign('settings', $this->settings);
        $this->view->assign('hash', $checkHash);
		$this->view->assign('event', $event);
    }
    
    /**
     * action new
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @ignorevalidation $event
     *
     * @return void
     */
    public function newAction(\JVE\JvEvents\Domain\Model\Event $event=null)
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $categories */
        $categories = $this->categoryRepository->findAllonAllPages( '0' );

        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $tags */
        $tags = $this->tagRepository->findAllonAllPages();

        if ( $event==null) {


            /** @var \JVE\JvEvents\Domain\Model\Event $event */
            $event = $this->objectManager->get("JVE\\JvEvents\\Domain\\Model\\Event");
        }
        if ( $event->getUid() < 1 ) {
            $event->setStartDate( new \DateTime ) ;

            if( $this->request->hasArgument('organizer')) {
                /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
                $organizer= $this->organizerRepository->findByUid( intval( $this->request->getArgument('organizer') )) ;
                if($organizer instanceof  \JVE\JvEvents\Domain\Model\Organizer ) {
                    $event->setOrganizer($organizer ) ;
                }
                $event->setEventType( 2 ) ;


                // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
                $event->setPid( 12 ) ;
            }

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

        if($this->isUserOrganizer() ) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

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
            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);

            }

        } else {
            $pid = $this->settings['pageIds']['loginForm'] ;
            $this->addFlashMessage('The object was NOT created. You are not logged in as Organizer.' . $event->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }
        // if PID from TS settings is set: if User is not logged in-> Page with loginForm , on success -> showEventDetail  Page
        if( $pid < 1) {
            // else : stay on this page
            $pid = $GLOBALS['TSFE']->id ;
        }
        $this->redirect('edit' , 'Event' , NULL , array( 'event' => $event ) , $pid );
    }
    
    /**
     * action edit
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @ignorevalidation $event
     * @return void
     */
    public function editAction(\JVE\JvEvents\Domain\Model\Event $event)
    {

        if($this->isUserOrganizer() ) {
            if( $this->hasUserAccess( $event->getOrganizer() )) {

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $categories */
                $categories = $this->categoryRepository->findAllonAllPages( '0' );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $tags */
                $tags = $this->tagRepository->findAllonAllPages();

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
        $this->addFlashMessage('The object was NOT deleted.  this action is not  implemented yet', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
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