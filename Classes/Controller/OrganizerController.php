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
 * OrganizerController
 */
class OrganizerController extends BaseController
{




    /**
     * action init
     *
     * @return void
     */
    public function initializeAction()
    {
        parent::initializeAction() ;
        if ($this->request->hasArgument('action')) {
        // Todo some checks if all params exists ..

        } else {
            $this->forward( $this->settings['defaultOrganizerAction'],null,null, array('action' => $this->settings['defaultOrganizerAction'] )  ) ;
        }



    }
    /**
     * action list
     *
     * @return void
     */
    public function indexAction()
    {
        $organizers = $this->organizerRepository->findAll();
        $this->view->assign('organizers', $organizers);
    }
    /**
     * action list
     *
     * @return void
     */
    public function assistAction()
    {
        $this->view->assign('user', intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) ;
        if($this->isUserOrganizer() ) {
            $organizer = $this->organizerRepository->findByUserAllpages( intval($GLOBALS['TSFE']->fe_user->user['uid'] )  , FALSE , TRUE  );
            $this->view->assign('count', count( $organizer )) ;
            $this->view->assign('organizer', $organizer ) ;

        }
    }
    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $organizers = $this->organizerRepository->findAll();
        $this->view->assign('organizers', $organizers);
    }
    
    /**
     * action show
     *
     * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
     * @return void
     */
    public function showAction(\JVE\JvEvents\Domain\Model\Organizer $organizer)
    {
        $this->view->assign('organizer', $organizer);
    }
    
    /**
     * action new
     *
     * @return void
     */
    public function newAction()
    {
        
    }
    
    /**
     * action create
     *
     * @param \JVE\JvEvents\Domain\Model\Organizer $newOrganizer
     * @return void
     */
    public function createAction(\JVE\JvEvents\Domain\Model\Organizer $newOrganizer)
    {
        $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See http://wiki.typo3.org/T3Doc/Extension_Builder/Using_the_Extension_Builder#1._Model_the_domain', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
		//    $this->organizerRepository->add($newOrganizer);
		//    $this->redirect('list');
    }
    
    /**
     * action edit
     *
     * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
     * @ignorevalidation $organizer
     * @return void
     */
    public function editAction(\JVE\JvEvents\Domain\Model\Organizer $organizer)
    {


        if ( ! $this->hasUserAccess($organizer )) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            $this->addFlashMessage('No access.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
            $this->view->assign('noAccess', TRUE );
        }

        $this->view->assign('user', intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) );
        $this->view->assign('organizer', $organizer);
    }
    
    /**
     * action update
     *
     * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
     * @validate $organizer \JVE\JvEvents\Validation\Validator\OrganizerValidator

     * @return void
     */
    public function updateAction(\JVE\JvEvents\Domain\Model\Organizer $organizer)
    {
        if ( $this->hasUserAccess($organizer )) {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            $this->addFlashMessage('The object was updated.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
            $this->organizerRepository->update($organizer);
        } else {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            $this->addFlashMessage('You do not have access rights to change this data.' . $organizer->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }

        $this->showNoDomainMxError($organizer->getEmail() ) ;

		$this->redirect('edit' , NULL, Null , array( "organizer" => $organizer));
    }
    
    /**
     * action delete
     *
     * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
     * @return void
     */
    public function deleteAction(\JVE\JvEvents\Domain\Model\Organizer $organizer)
    {
        $this->addFlashMessage('The object was deleted. Please be aware that this action is publicly accessible unless you implement an access check. See http://wiki.typo3.org/T3Doc/Extension_Builder/Using_the_Extension_Builder#1._Model_the_domain', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
     //   $this->organizerRepository->remove($organizer);
     //   $this->redirect('list');
    }

}