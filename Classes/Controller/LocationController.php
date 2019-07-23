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
 * LocationController
 */
class LocationController extends BaseController
{




    /**
     * action init
     *
     * @return void
     */
    public function initializeAction()
    {
        if( !$this->request->hasArgument('location')) {
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
        $locations = $this->locationRepository->findAll();
        $this->view->assign('locations', $locations);
    }
    
    /**
     * action show
     *
     * @param \JVE\JvEvents\Domain\Model\Location $location
     * @return void
     */
    public function showAction(\JVE\JvEvents\Domain\Model\Location $location)
    {
        $this->view->assign('location', $location);
    }
    
    /**
     * action new
     * @param \JVE\JvEvents\Domain\Model\Location|Null $location
     * @ignorevalidation $location
     * @return void
     */
    public function newAction(\JVE\JvEvents\Domain\Model\Location $location=Null)
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $categories */
        $categories = $this->categoryRepository->findAllonAllPages( '1' );


        if ( $location==null) {


            /** @var \JVE\JvEvents\Domain\Model\Location $location */
            $location = $this->objectManager->get("JVE\\JvEvents\\Domain\\Model\\Location");
        }
        if ( $location->getUid() < 1 ) {
            $organizer = $this->getOrganizer() ;
            if( $organizer ) {
                $location->setOrganizer($organizer);
            }

            // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
            $location->setPid( 14 ) ;

        }
        if($this->isUserOrganizer() ) {
            $this->view->assign('user', intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) ;
            $this->view->assign('location', $location);
            $this->view->assign('organizer', $organizer );
            $this->view->assign('categories', $categories);
        }
    }
    
    /**
     * action create
     *
     * @param \JVE\JvEvents\Domain\Model\Location $location
     * @validate $location \JVE\JvEvents\Validation\Validator\LocationValidator
     * @return void
     */

    public function createAction(\JVE\JvEvents\Domain\Model\Location $location)
    {
        if( $this->request->hasArgument('location')) {
            $location = $this->cleanLocationArguments( $location) ;
        }
        $action = "edit" ;
        if($this->isUserOrganizer() ) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();


            try {
                // ToDo Storage PID for location via typoscript
                $location->setPid(14) ;
                $organizer = $this->getOrganizer() ;
                if( $organizer ) {
                    $orgId = $organizer->getUid() ;
                    $location->setOrganizer($organizer);
                }

                $this->locationRepository->add($location);
                $this->persistenceManager->persistAll() ;


                $this->addFlashMessage('The object was created.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
                $pid = $this->settings['pageIds']['editLocationSuccess'] ;

                $action = "list" ;
            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);

            }

        } else {

            $pid = $this->settings['pageIds']['loginForm'] ;
            $this->addFlashMessage('The object was NOT created. You are not logged in as Organizer.' . $location->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
            $this->redirect(null , null , NULL , NULL , $pid );
        }
        // if PID from TS settings is set: if User is not logged in-> Page with loginForm , on success -> showEventDetail  Page
        if( $pid < 1) {
            // else : stay on this page
            $pid = $GLOBALS['TSFE']->id ;

        }
        $this->redirect($action , "Event" , NULL , array( 'eventsFilter' => array( 'organizers' => $orgId) ) , $pid );

    }
    
    /**
     * action edit
     *
     * @param \JVE\JvEvents\Domain\Model\Location $location
     * @ignorevalidation $location
     * @return void
     */
    public function editAction(\JVE\JvEvents\Domain\Model\Location $location)
    {

        if ( $this->hasUserAccess($location->getOrganizer() )) {
            $this->view->assign('user', intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) );
            $this->view->assign('location', $location);
        } else {
            $this->addFlashMessage('You do not have access rights to change this data.' , 'Error', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
            $this->addFlashMessage('ID: ' . $location->getUid(), '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }
    }
    
    /**
     * action update
     *
     * @param \JVE\JvEvents\Domain\Model\Location $location
     * @validate $location \JVE\JvEvents\Validation\Validator\LocationValidator
     * @return void
     */
    public function updateAction(\JVE\JvEvents\Domain\Model\Location $location)
    {
        if ( $this->hasUserAccess($location->getOrganizer() )) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();
            $this->addFlashMessage('The object was updated.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
            $this->locationRepository->update($location);
        } else {
            $this->addFlashMessage('You do not have access rights to change this data.' . $location->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }
        $this->showNoDomainMxError($location->getEmail() ) ;
        $this->redirect('edit' , NULL, Null , array( "location" => $location));
    }
    
    /**
     * action delete
     *
     * @param \JVE\JvEvents\Domain\Model\Location $location
     * @return void
     */
    public function deleteAction(\JVE\JvEvents\Domain\Model\Location $location)
    {
        $this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See http://wiki.typo3.org/T3Doc/Extension_Builder/Using_the_Extension_Builder#1._Model_the_domain', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
		//   $this->locationRepository->remove($location);
		//   $this->redirect('list');
    }

    /**
     * @param \JVE\JvEvents\Domain\Model\Location $location
     * @return \JVE\JvEvents\Domain\Model\Location
     */
    public function cleanLocationArguments(\JVE\JvEvents\Domain\Model\Location $location) {

        $desc = str_replace( array( "\n" , "\r" , "\t" ), array(" " , "" , " " ), $location->getDescription() ) ;
        $desc = strip_tags($desc , "<p><br><a><i><strong><h2><h3>") ;

        $location->setDescription( $desc ) ;

        $location->setLanguageUid(-1) ;
        return $location ;
    }

}