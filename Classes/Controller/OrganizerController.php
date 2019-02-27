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
        // maybe we need this as Overview -> Select type of Organizer -> jump to list -> Filtered by type
    }
    /**
     * action list
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
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



        $organizers = $this->organizerRepository->findByFilterAllpages();
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
     * @param \JVE\JvEvents\Domain\Model\Organizer|Null $organizer
     * @ignorevalidation $organizer
     * @return void
     */
    public function newAction(\JVE\JvEvents\Domain\Model\Organizer $organizer = null )
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $categories */
        $categories = $this->categoryRepository->findAllonAllPages( '3' );


        if ( $organizer==null) {


            /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
            $organizer = $this->objectManager->get("JVE\\JvEvents\\Domain\\Model\\Organizer");
            // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
            $organizer->setPid( 13 ) ;
            $organizer->setEmail( $GLOBALS['TSFE']->fe_user->user['username'] ) ;

            // We want to confirm each new Organizer
            $organizer->sethidden( 1 ) ;

        }
        if($this->isUserOrganizer() ) {
            $this->view->assign('user', intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) ;
            $this->view->assign('organizer', $organizer );
            $this->view->assign('categories', $categories);
        }
    }
    
    /**
     * action create
     *
     * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
     * @validate $organizer \JVE\JvEvents\Validation\Validator\OrganizerValidator
     * @return void
     */
    public function createAction(\JVE\JvEvents\Domain\Model\Organizer $organizer)
    {
        $this->addFlashMessage('The object NewOrganizer was NOT created, because the "create" Action not finished Yet!', 'Uncomplete Function', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
		// Todo: add needed checks like in updateAction
        // adding some default Fields
        // Look via Email into Old Organizer Table and get "tango_veranstler -> created Date  ??
        // send Email to Admin with Link to add user to Organizer FE User Gorup

        // create a validator( Name, Email, phone, link tags, and Description

        $this->organizerRepository->add($organizer);
		$this->redirect('new' , null, null );
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
        $this->addFlashMessage('The object was NOT deleted. this feature is not implemented yet', 'Unfinised Feature', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
     //   $this->organizerRepository->remove($organizer);
        $this->redirect('list');
    }

}