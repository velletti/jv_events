<?php
namespace JVelletti\JvEvents\Controller;

use Psr\Http\Message\ResponseInterface;
use JVelletti\JvEvents\Domain\Model\Location;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Annotation\Validate;
use JVelletti\JvEvents\Validation\Validator\LocationValidator;
use JVelletti\JvEvents\Domain\Model\Organizer;
use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Category;
use JVelletti\JvEvents\Utility\SlugUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

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

use TYPO3\CMS\Extbase\Annotation as Extbase;

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
/*
        if ( $this->request->hasArgument('location')) {
            if ( property_exists( $this->arguments , "location")) {
                $propertyMappingConfiguration = $this->arguments['location']->getPropertyMappingConfiguration();
                $propertyMappingConfiguration->allowProperties('organizerUid') ;
            }

        }
*/
        parent::initializeAction() ;

    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction(): ResponseInterface
    {
        $filter = []     ;
        if( array_key_exists( 'filterlocation' , $this->settings)) {

            if ( array_key_exists( "dist", $this->settings['filterlocation']))  {
                if( intval($this->settings['filterlocation']['dist']) > 0 ) {
                    $filter = $this->locationRepository->getBoundingBox(  $this->settings['filterlocation']['lat'], $this->settings['filterlocation']['lng'] ,  $this->settings['filterlocation']['dist']) ;
                }
            }
            if ( array_key_exists( "categories", $this->settings['filterlocation']) && strlen(trim($this->settings['filterlocation']['categories'] )) > 0 )  {
                $filter["locationCategory.uid"] =  GeneralUtility::trimExplode( "," , $this->settings['filterlocation']['categories'] )   ;
            }
        }

        $lastModified = '-1 YEAR' ;
        if( array_key_exists( 'filterorganizer' , $this->settings) && array_key_exists( "latestUpdate", $this->settings['filterorganizer'])) {
           if( intval( $this->settings['filterorganizer']['latestUpdate']) == 0 ) {
               $lastModified = null ;
           } else {
               $lastModified = "-" . intval( $this->settings['filterorganizer']['latestUpdate']) . " DAY" ;
           }
        }

        // ($filter=FALSE , $toArray=FALSE , $ignoreEnableFields = FALSE , $limit=FALSE, $lastModified = '-1 YEAR')
        $locations = $this->locationRepository->findByFilterAllpages($filter , false , false , false  , $lastModified);
        $this->view->assign('locations', $locations);
        return $this->htmlResponse();
    }
    
    /**
     * action show
     *
     * @param Location|null $location
     * @return void
     */
    public function showAction(?Location $location): ResponseInterface
    {
        if ( $location ) {
            $nextEventOrganizer = null;
            $nextEventLocation = null;
            $this->settings['filter']['maxEvent'] =  1  ;

            if ( $location->getOrganizer() ) {
                $this->settings['filter']['organizer'] =  $location->getOrganizer()->getUid()  ;
                $nextEventOrganizer = $this->eventRepository->findByFilter(false, 1,  $this->settings )->getFirst() ;
            }
            $this->settings['filter']['location'] =  $location->getUid()  ;
            $nextEventLocation = $this->eventRepository->findByFilter(false, 1,  $this->settings )->getFirst() ;

            $this->view->assign('location', $location);
            $this->view->assign('nextEventLocation', ($nextEventLocation ? $nextEventLocation->getStartdate()->format("d.m.Y") : date("d.m.Y") ));
            $this->view->assign('nextEventOrganizer', ( $nextEventOrganizer ? $nextEventOrganizer->getStartdate()->format("d.m.Y") : date("d.m.Y") ));
        } else {
            $this->addFlashMessage($this->translate("error.general.entry_not_found"), "Sorry!" , ContextualFeedbackSeverity::WARNING) ;
        }
        return $this->htmlResponse();
    }
    
    /**
     * action new
     * @param Location|Null $location
     * @return void
     */
    #[IgnoreValidation(['value' => 'location'])]
    public function newAction(Location $location=Null): ResponseInterface
    {
        /** @var QueryResultInterface $categories */
        $categories = $this->categoryRepository->findAllonAllPages( '1' );


        if ( $location==null) {
            /** @var Location $location */
            $location = GeneralUtility::makeInstance(Location::class);
            $location->setLng( ($this->settings['filterlocation']['lng'] ?? '-34.5877631' ));
            $location->setLat( ($this->settings['filterlocation']['lat'] ?? '-58.4589249' ));

        }
        $organizer= null ;
        if ( $location->getUid() < 1 ) {
            $organizer = $this->getOrganizer() ;
            if( $organizer ) {
                $location->setOrganizer($organizer);
            }

            // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
            $location->setPid( 14 ) ;
            $city = $this->settings["location"]["new"]["defaultCity"] ?? "München";
            $location->setCity( $city ) ;
            $location->setCountry( "DE" ) ;

        }

        if($this->isUserOrganizer() ) {
            $this->view->assign('user', intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) ;
            $this->view->assign('location', $location);
            $this->view->assign('organizer', $organizer );
            $this->view->assign('categories', $categories);
        }
        return $this->htmlResponse();
    }
    
    /**
     * action create
     *
     * @param Location $location
     *
     * @return void
     */
    #[Validate(['param' => 'location', 'validator' => LocationValidator::class])]
    public function createAction(Location $location)
    {
        if( $this->request->hasArgument('location')) {
            $location = $this->cleanLocationArguments( $location) ;
        }
        $action = "edit" ;
        if($this->isUserOrganizer() ) {
            $this->getFlashMessageQueue()->getAllMessagesAndFlush();


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


                $this->addFlashMessage('The Location was created.  It may take up some hours before it is visible', '', ContextualFeedbackSeverity::OK);
            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', ContextualFeedbackSeverity::WARNING);

            }

        } else {

            $pid = $this->settings['pageIds']['loginForm'] ;
            $this->addFlashMessage('The object was NOT created. You are not logged in as Organizer.' . $location->getUid() , '', ContextualFeedbackSeverity::WARNING);
            $this->redirect(null , null , NULL , NULL , $pid );
        }

        $pid = $this->settings['pageIds']['editEvent'] ;


        if( $pid < 1) {
            $pid = $GLOBALS['TSFE']->id ;
            $controller = NULL ;
            $action = NULL ;
        } else {
            $controller = "Event" ;
            $action = "new" ;
        }
        $this->redirect($action , $controller , NULL , array( 'organizer' => $orgId , 'location' => $location->getUid() )  , $pid );

    }

    /**
     * action new
     * @param Location $location
     * @param Organizer $organizer
     * @param integer $oldDefault
     * @return void
     */
    #[IgnoreValidation(['value' => 'location'])]
    public function setDefaultAction(Location $location , Organizer $organizer ,  $oldDefault= 0) {

        $locations = $this->locationRepository->findByOrganizersAllpages([$organizer->getUid()] , true , TRUE , true ) ;
        if ( $locations && count($locations) > 0) {
            foreach ( $locations as $location) {
                if( is_object($location)) {
                    if ( $this->hasUserAccess($location->getOrganizer() )) {
                        $location->setDefaultLocation( FALSE ) ;
                        $this->locationRepository->update($location) ;
                    }
                }
            }
        }
        if ( $this->hasUserAccess($location->getOrganizer() )) {
            $location->setDefaultLocation( TRUE ) ;
            $this->locationRepository->update($location) ;
            $organizer->setLat( $location->getLat() ) ;
            $organizer->setLng( $location->getLng() ) ;
            $this->organizerRepository->update($organizer) ;
        }
        $this->addFlashMessage("Default Location: " . $location->getName() , null , ContextualFeedbackSeverity::OK);


        return $this->redirect('assist' , 'Organizer', Null , NULL , $this->settings['pageIds']['organizerAssist'] );

    }

    /**
     * action edit
     *
     * @param Location $location
     * @return void
     */
    #[IgnoreValidation(['value' => 'location'])]
    public function editAction(Location $location): ResponseInterface
    {
        /** @var QueryResultInterface $categories */
        $categories = $this->categoryRepository->findAllonAllPages( '1' );


        if( ! $hasAccess = $this->isAdminOrganizer()  ) {
           if( $organizer = $location->getOrganizer() ) {
               $hasAccess = $this->hasUserAccess( $organizer ) ;
           } else {
               $location->setOrganizer(null) ;
           }

        } else {
            $this->view->assign('isAdmin', true );
            $organizers  = $this->organizerRepository->findByFilterAllpages( false , false , true , false , false ) ;
            $this->view->assign('organizers', $organizers );
        }
        if ( $hasAccess ) {
            $this->view->assign('user', intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) );

            $this->view->assign('location', $location);
            $this->view->assign('categories', $categories);
        } else {
            $this->addFlashMessage('You do not have access rights to change this data.' , 'Error', ContextualFeedbackSeverity::WARNING);
            $this->addFlashMessage('ID: ' . $location->getUid(), '', AbstractMessageContextualFeedbackSeverity::WARNING);
        }
        return $this->htmlResponse();
    }
    
    /**
     * action update
     *
     * @param Location $location
     * @return void
     */
    #[Validate(['param' => 'location', 'validator' => LocationValidator::class])]
    public function updateAction(Location $location)
    {
        if( ! $hasAccess = $this->isAdminOrganizer()  ) {
            if( $organizer = $location->getOrganizer() ) {
                $hasAccess = $this->hasUserAccess( $organizer ) ;
            }
        }

        if ($hasAccess ) {
            $location = $this->cleanLocationArguments( $location) ;
            $this->getFlashMessageQueue()->getAllMessagesAndFlush();
            $this->addFlashMessage('The object was updated. It may take some hours before it is visible', '', ContextualFeedbackSeverity::OK);
            $this->locationRepository->update($location);
        } else {
            $this->addFlashMessage('You do not have access rights to change this data.' . $location->getUid() , '', ContextualFeedbackSeverity::WARNING);
        }
        $this->showNoDomainMxError($location->getEmail() ) ;
        return $this->redirect('edit' , NULL, Null , array( "location" => $location));
    }
    
    /**
     * action delete
     *
     * @param Location $location
     * @return void
     */
    public function deleteAction(Location $location)
    {
        $delete = false ;
        $events = $this->eventRepository->findByLocation( $location->getUid() ) ;
        if( $this->request->hasArgument('delete')) {
            $delete = $this->request->getArgument('delete');
            if( $this->request->hasArgument('eventCount')) {
                $eventCount = $this->request->getArgument('eventCount');
            }

            if ( $delete &&  $events->count() == $eventCount) {

                if ( $this->hasUserAccess($location->getOrganizer() )) {
                    $this->getFlashMessageQueue()->getAllMessagesAndFlush();
                    if ( $events->count() > 0 ) {
                        $eventIds = "Ids: " ;
                        /** @var Event $event */
                        foreach ( $events as $event) {
                            $eventIds .= $event->getUid() . ", " ;
                            $this->eventRepository->remove($event) ;
                        }
                        $this->addFlashMessage('The following Events are deleted: ' . $eventIds, '', ContextualFeedbackSeverity::OK);
                    }

                    $this->locationRepository->remove($location);
                    $this->addFlashMessage('The Location was deleted.', '', ContextualFeedbackSeverity::OK);


                } else {
                    $this->addFlashMessage('You do not have access rights to change this data.' . $location->getUid() , '', ContextualFeedbackSeverity::WARNING);
                }


                $this->redirect('assist' , 'Organizer', Null , NULL , $this->settings['pageIds']['organizerAssist'] );
            }
        }
        $this->view->assign('location', $location);
        $this->view->assign('eventCount', $events->count() );
        $this->view->assign('settings', $this->settings );

    }

    /**
     * @param Location $location
     * @return Location
     */
    public function cleanLocationArguments(Location $location) {

        $desc = str_replace( array( "\n" , "\r" , "\t" ), array(" " , "" , " " ), $location->getDescription() ) ;
        $desc = strip_tags($desc , "<p><br><a><i><strong><h2><h3>") ;

        $location->setDescription( $desc ) ;
        $location->setLink( trim($location->getLink())) ;
        $location->setEmail( trim($location->getEmail())) ;
        $location->setTstamp( time() ) ;

        // Type validation should be done in validator class so we can ignore issue with wrong format
        $locationArray = $this->request->getArgument('location');
        /*   +******  Update the Category  ************* */
        $locationCatUid = intval( $locationArray['locationCategory'] ) ;
        /** @var Category $locationCat */
        $locationCat = $this->categoryRepository->findByUid($locationCatUid) ;


        if( $locationCat ) {
            if( $location->getLocationCategory() ){
                $location->getLocationCategory()->removeAll($location->getLocationCategory()) ;
            }
            $location->addLocationCategory($locationCat) ;
        }



        if ( $this->isAdminOrganizer()) {
            if( is_array( $locationArray['organizer'] ) && isset($locationArray['organizer']["__identity"] ) ) {
                $orgUid = $locationArray['organizer']["__identity"]  ;
            } else {
                $orgUid = $locationArray['organizer'];
            }
            $organizer = $this->organizerRepository->findByUidAllpages(  intval( $orgUid ) , false, false  ) ;

            if (  $organizer  ) {
                $location->setOrganizer($organizer);
            } else {
                $location->setOrganizer(null);
            }
        }



        if( $location->getPid() < 1) {
            // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
            $location->setPid( 14 ) ;
        }


        $location->setLanguageUid(-1) ;

            $row['name'] =  $location->getName() ;
            $row['pid'] =  $location->getPid() ;
            $row['parentpid'] =  1 ;
            $row['uid'] =  $location->getUid() ;
            $row['sys_language_uid'] =  $location->getLanguageUid() ;
            $row['slug'] =  $location->getSlug() ;
            $slug = SlugUtility::getSlug("tx_jvevents_domain_model_location", "slug", $row )  ;
            $location->setSlug( $slug ) ;


        return $location ;
    }

}