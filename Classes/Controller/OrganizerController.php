<?php
namespace JVE\JvEvents\Controller;

use JVE\JvEvents\Utility\SlugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $this->timeStart = $this->microtime_float() ;
        $this->debugArray[] = "Start:" . intval(1000 * $this->timeStart ) . " Line: " . __LINE__ ;
        parent::initializeAction() ;
        if ($this->request->hasArgument('action')) {
        // Todo some checks if all params exists ..

        } else {
            $this->forward( $this->settings['defaultOrganizerAction'],null,null, array('action' => $this->settings['defaultOrganizerAction'] )  ) ;
        }

        $this->debugArray[] = "after Init:" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;


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
        $this->view->assign('userData', $GLOBALS['TSFE']->fe_user->user ) ;
        $organizer = $this->organizerRepository->findByUserAllpages(intval($GLOBALS['TSFE']->fe_user->user['uid']), true, TRUE);
        if( is_array( $organizer)  && is_object( $organizer[0]) ) {
            $locations= $this->locationRepository->findByOrganizersAllpages( array(0 => $organizer[0]->getUid()) , FALSE, FALSE ) ;
            $this->view->assign('locations', $locations );

            $oldDefaultLocation = $this->locationRepository->findByOrganizersAllpages( array(0 => $organizer[0]->getUid()) , FALSE, FALSE , TRUE )->getFirst() ;
            if($oldDefaultLocation) {
                $this->view->assign('defaultLocation', $oldDefaultLocation->getUid() );
            }
        }


        $this->view->assign('count', count($organizer));
        $this->view->assign('organizer', $organizer);
        $this->view->assign('isOrganizer', $this->isUserOrganizer());

    }
    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {


        $this->settings['filter']['sorttags'] = "sorting" ;
        $this->debugArray[] = "Before DB Load:" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;


        $filter = false ;
        if( array_key_exists( 'filterorganizer' , $this->settings)) {
            if ( array_key_exists( "tags", $this->settings['filterorganizer']))  {
                if( count(GeneralUtility::trimExplode( "," , $this->settings['filterorganizer']['tags'] , true)  ) > 0 ) {
                    $filter["tags.uid"]  = GeneralUtility::trimExplode( "," , $this->settings['filterorganizer']['tags'] , true )   ;
                }
            }
            if ( array_key_exists( "hideInactives", $this->settings['filterorganizer']) )  {
               if(  $this->settings['filterorganizer']['hideInactives'] ) {
                   $filter['latest_event'] = time() ;
               }
            }
            if ( array_key_exists( "latestUpdate", $this->settings['filterorganizer']) )  {
                if(  $this->settings['filterorganizer']['latestUpdate'] > 0 ) {
                    $filter['tstamp'] = time() - ( intval( $this->settings['filterorganizer']['latestUpdate'] ) * 3600 * 24 ) ;
                }
            }

        }
        $organizers = $this->organizerRepository->findByFilterAllpages($filter);
        $this->debugArray[] = "Before Generate Array:" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;


        /*  slow version ...
            $orgs = $organizers->toArray() ;
            $this->debugArray[] = "Before Generate Filter:" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;

            $orgFilter = $this->generateOrgFilter( $orgs ,  $this->settings['filter']) ;
           //     echo "<pre>";
            //    var_dump($orgFilter) ;
        // die;
        */
        $orgFilter = $this->generateOrgFilterFast(  $this->settings['filter']) ;
        $this->view->assign('organizers', $organizers);
        $this->view->assign('orgFilter', $orgFilter);
        $this->debugArray[] = "Finished:". intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;

       // echo "<pre>" ;
       // var_dump( $this->debugArray) ;
       // die;
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
        $tags = $this->tagRepository->findAllonAllPages( '2' );


        $organizers = $this->organizerRepository->findByUserAllpages( intval($GLOBALS['TSFE']->fe_user->user['uid'] )  , FALSE , TRUE  );
        $this->view->assign('count', count( $organizers )) ;

        if ( $organizer) {
            if( $organizer->getEmail() == '' ) {
                $organizer->setEmail( $GLOBALS['TSFE']->fe_user->user['email'] ) ;
            }
        } else{
            /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
            $organizer = $this->objectManager->get("JVE\\JvEvents\\Domain\\Model\\Organizer");
            // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
            $organizer->setPid( 13 ) ;
            $organizer->setEmail( $GLOBALS['TSFE']->fe_user->user['email'] ) ;
            $organizer->setName( $GLOBALS['TSFE']->fe_user->user['first_name'] . " " . $GLOBALS['TSFE']->fe_user->user['last_name'] ) ;
            $organizer->setPhone( $GLOBALS['TSFE']->fe_user->user['phone'] ) ;

            // We want to confirm each new Organizer
            $organizer->sethidden( 1 ) ;

        }
        $this->view->assign('user', intval($GLOBALS['TSFE']->fe_user->user['uid'] ) ) ;

        $this->view->assign('organizer', $organizer );
        $this->view->assign('tags', $tags);
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
        if ( $GLOBALS['TSFE']->fe_user->user && $GLOBALS['TSFE']->fe_user->user['uid'] > 0 )  {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            $organizer = $this->cleanOrganizerArguments( $organizer ) ;

            // special needs for tango. maybe we make this configurabale via typoscript
            $organizer->setHidden(1) ;
            $organizer->setPid( 13 ) ;
            $organizer->setSorting( 99999999 ) ;
            $organizer->setSysLanguageUid(-1 ) ;

            $organizer->setAccessUsers(intval($GLOBALS['TSFE']->fe_user->user['uid'] ));
            $organizer->setAccessGroups( $this->settings['feEdit']['adminOrganizerGroudIds'] );
            // special needs for tango. maybe we make this configurabale via typoscript

            $this->organizerRepository->add($organizer);
            $this->persistenceManager->persistAll() ;

            $this->cacheService->clearPageCache( array($this->settings['pageIds']['organizerAssist'])  );
            if( $organizer->getUid()) {
                $this->addFlashMessage('The Organizer was created with ID:' . $organizer->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

            } else {
                $this->addFlashMessage('Error: Organizer did not get an ID:' , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
            }




            // https://www.allplan.com.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[organizerUid]=111&tx_jvevents_ajax[action]=activate&tx_jvevents_ajax[userUid]=1&tx_jvevents_ajax[hmac]=hmac1234&&tx_jvevents_ajax[rnd]=11234

            $organizerUid = intval( $organizer->getUid()) ;
            $userUid = intval($GLOBALS['TSFE']->fe_user->user['uid']) ;
            $rnd = time() ;
            $tokenStr = "activateOrg" . "-" . $organizerUid . "-" . $GLOBALS['TSFE']->fe_user->user['crdate'] ."-". $userUid .  "-". $rnd ;
            $hmac = \TYPO3\CMS\Core\Utility\GeneralUtility::hmac( $tokenStr );

            $url  = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') ;
            $url .= "/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[action]=activate" ;

            $url .= "&tx_jvevents_ajax[organizerUid]=" . $organizerUid ;
            $url .= "&tx_jvevents_ajax[userUid]=" . $userUid ;
            $url .= "&tx_jvevents_ajax[rnd]=" . $rnd ;
            $url .= "&tx_jvevents_ajax[hmac]=" . $hmac ;

            $msg = "A new organizer has registered:" ;
            $msg .= "\n\n" . "Name: " . $organizer->getName() ;
            $msg .= "\n" . "Email: " . $organizer->getEmail() ;
            $msg .= "\n" . "Phone: " . $organizer->getPhone() ;
            $msg .= "\n" . "Link: " . $organizer->getLink() ;
            $msg .= "\n\n\n" . "Description: " . $organizer->getDescription() ;
            $msg .= "\n *********************************************** " ;
            $msg .= "\n Der  Veranstalter Account auf tangomumchen.de wurde freigeschaltet." ;
            $msg .= "\n" ;
            $msg .= "\n Nun können Veranstaltungsorte und Termine erstellt werden." ;
            $msg .= "\n WICHTIG: Bitte neu einloggen, damit die neuen Zugriffsrechte als Veranstalter wirksam sind." ;
            $msg .= "\n" ;
            $msg .= "\n Bei Fragen, ist ein Blick in den Hilfebereich sicher hilfreich. " ;
            $msg .= "\n" ;
            $msg .= "\n ***********************************************" ;

            $html = nl2br($msg) ;
            $html .= "<br>" . "<hr><br>" . '<a href="' . $url . '"> Klick To Enable  </a>' ."\n" . "\n"  ;

            $msg .= "\n\n" . "Klick to Enable: \n \n" . $url ;
            $this->sendDebugEmail( "tango@velletti.de" ,"info@tangomuenchen.de", "[TANGO][NewOrganizer] - " . $organizer->getEmail() , $msg , $html) ;


            $this->showNoDomainMxError($organizer->getEmail() ) ;

            $this->redirect('assist' , NULL, Null , NULL , $this->settings['pageIds']['organizerAssist']);
        } else {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            $this->addFlashMessage('You do not have access rights to create own Organizer data.'  , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }

        $this->redirect('assist' , NULL, Null , NULL , $this->settings['pageIds']['organizerAssist'] );


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
        } else {
            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $categories */
            $tags = $this->tagRepository->findAllonAllPages( '2' );
            $this->view->assign('tags', $tags);
            $this->view->assign('organizer', $organizer);
        }

        $this->view->assign('user', intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) );

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
            $organizer = $this->cleanOrganizerArguments( $organizer ) ;
            $this->organizerRepository->update($organizer);
            $this->addFlashMessage('The object was updated.', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);
        } else {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            $this->addFlashMessage('You do not have access rights to change this data.' . $organizer->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }

        $this->showNoDomainMxError($organizer->getEmail() ) ;

        $this->redirect('assist' , NULL, Null , NULL , $this->settings['pageIds']['organizerAssist']);
    }
    
    /**
     * action delete
     *
     * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
     * @return void
     */
    public function deleteAction(\JVE\JvEvents\Domain\Model\Organizer $organizer)
    {
        $this->addFlashMessage('The object was NOT deleted. this feature is not implemented yet', 'Unfinished Feature', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
     //   $this->organizerRepository->remove($organizer);
        $this->redirect('assist' , NULL, Null , NULL , $this->settings['pageIds']['organizerAssist']);
    }




    /**
     * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
     * @return \JVE\JvEvents\Domain\Model\Organizer
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function cleanOrganizerArguments(\JVE\JvEvents\Domain\Model\Organizer  $organizer)
    {
        // validation should be done in validatar class so we can ignore issue with wrong format

        $organizerArray = $this->request->getArgument('organizer');

        // Update the Tags
        if( is_array( $organizerArray) && array_key_exists( 'tagsFE' , $organizerArray )) {
            $organizerTagUids =  \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $organizerArray['tagsFE']) ;
            if( is_array($organizerTagUids) && count($organizerTagUids) > 0  ) {
                $existingTags = $organizer->getTags() ;

                if ( $existingTags ) {
                    /** @var  \JVE\JvEvents\Domain\Model\Tag $existingTag */
                    foreach ( $existingTags as $existingTag ) {
                        if( !in_array( $existingTag->getUid()  , $organizerTagUids)) {
                            $organizer->getTags()->detach($existingTag) ;
                            unset($organizerTagUids[$existingTag->getUid()] ) ;
                        }

                    }
                }
                if( is_array($organizerTagUids) && count($organizerTagUids) > 0  ) {
                    foreach ($organizerTagUids as $organizerTagUid) {
                        if( intval( $organizerTagUid ) > 0 ) {
                            /** @var  \JVE\JvEvents\Domain\Model\Tag $organizerTag */
                            $organizerTag = $this->tagRepository->findByUid($organizerTagUid) ;

                            if($organizerTag) {
                                $organizer->addTag($organizerTag) ;
                            }
                        }
                    }
                }
            }
        }


        if( is_array( $organizerArray) && array_key_exists( 'description' , $organizerArray )) {
            $desc = str_replace(array("\n", "\r", "\t"), array(" ", "", " "), $organizerArray['description']);
            $desc = strip_tags($desc, "<p><br><a><i><strong><h2><h3>");

            $organizer->setDescription($desc);
        }
        $organizer->setLink( trim($organizer->getLink())) ;
        $organizer->setCharityLink( trim($organizer->getCharityLink())) ;
        $organizer->setEmail( trim($organizer->getEmail())) ;

        if ($organizer->getPid() < 1 ) {
            // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
            $organizer->setPid( 13 ) ;
        }
        if( intval( TYPO3_branch ) > 8 ) {
            $row['name'] =  $organizer->getName() ;
            $row['pid'] =  $organizer->getPid() ;
            $row['parentpid'] =  1 ;
            $row['uid'] =  $organizer->getUid() ;
            $row['sys_language_uid'] =  $organizer->getLanguageUid() ;
            $row['slug'] =  $organizer->getSlug() ;
            $slug = SlugUtility::getSlug("tx_jvevents_domain_model_organizer", "slug", $row  )  ;
            $organizer->setSlug( $slug ) ;
        }
        return $organizer ;
    }


}