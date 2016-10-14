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
		if (!$this->request->hasArgument('event')) {
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
     * @return void
     */
    public function showAction(\JVE\JvEvents\Domain\Model\Event $event)
    {
        $this->view->assign('event', $event);
    }
    
    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
		$this->addFlashMessage('Please be aware that this action is publicly accessible unless you implement an access check.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);

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
        $this->eventRepository->add($newEvent);
        $this->redirect('list');
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
        $this->eventRepository->update($event);
        $this->redirect('list');
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
        $this->eventRepository->remove($event);
        $this->redirect('list');
    }
    
    /**
     * action register
	 * @param \JVE\JvEvents\Domain\Model\Registrant $newRegistrant
     * @return void
     */
    public function registerAction(\JVE\JvEvents\Domain\Model\Registrant $newRegistrant)
    {
        // attach registration to event
		// sent Out Email Notifications to Organizer and or registrant
		// store to Citrix or SalesForce
		// reduce available Seats and maybe inform Organizer if < 1

    }
    
    /**
     * action confirm
     * @param \JVE\JvEvents\Domain\Model\Registrant $registrant
     * @return void
     */
    public function confirmAction(\JVE\JvEvents\Domain\Model\Registrant $registrant)
    {
        // activate registration if event is setup taht confirmation is needed.

    }
    
    /**
     * action search
     *
     * @return void
     */
    public function searchAction()
    {
        
    }

	/**
	 * helper for Formvalidation
	 * @param string $action
	 * @return string
	 */
	public function generateToken($action = "action")
	{
		/** @var \TYPO3\CMS\Core\FormProtection\FrontendFormProtection $formClass */
		$formClass =  $this->objectManager->get( "TYPO3\\CMS\Core\\FormProtection\\FrontendFormProtection") ;

		return $formClass->generateToken(
			'event', $action ,   "P" . $this->settings['pageId'] . "-L" .$this->settings['sys_language_uid']
		);

	}

}