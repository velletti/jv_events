<?php
namespace JVE\JvEvents\Controller;

use JVE\JvEvents\Utility\SlugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use Velletti\Mailsignature\Service\SignatureService;

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
        $ordering = false ;

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
            if ( array_key_exists( "reverseSorting", $this->settings['filterorganizer']) ) {
                if(  $this->settings['filterorganizer']['reverseSorting'] > 0 ) {
                    $ordering = true;
                }
            }

        }
        $organizers = $this->organizerRepository->findByFilterAllpages($filter ,false , false , false , $ordering );
        $this->debugArray[] = "Before Generate Array:" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;


        /*  slow version ...
            $orgs = $organizers->toArray() ;
            $this->debugArray[] = "Before Generate Filter:" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;

            $orgFilter = $this->generateOrgFilter( $orgs ,  $this->settings['filter']) ;
           //     echo "<pre>";
            //    var_dump($orgFilter) ;
        // die;
        */
        if ( array_key_exists( "respectTagVisibility", $this->settings['filterorganizer']) )  {
            $filter['respectTagVisibility'] =  $this->settings['filterorganizer']['respectTagVisibility'] ;
        } else {
            $filter['respectTagVisibility'] = 1 ;
        }
        $orgFilter = $this->generateOrgFilterFast( $filter ) ;
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
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("organizer")
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
            $organizer = $this->objectManager->get(\JVE\JvEvents\Domain\Model\Organizer::class);
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
     * @TYPO3\CMS\Extbase\Annotation\Validate(param="organizer" , validator="JVE\JvEvents\Validation\Validator\OrganizerValidator")
     * @return void
     */
    public function createAction(\JVE\JvEvents\Domain\Model\Organizer $organizer)
    {
        if ( $GLOBALS['TSFE']->fe_user->user && $GLOBALS['TSFE']->fe_user->user['uid'] > 0 )  {
             $isOrganizer = $this->organizerRepository->findByUserAllpages( $GLOBALS['TSFE']->fe_user->user['uid'] , true , true ) ;
            if ( $isOrganizer ) {
                // reloaded paged ??
                $organizer->setUid( $isOrganizer['uid'] ) ;
            } else {
                $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

                $organizer = $this->cleanOrganizerArguments( $organizer ) ;

                // special needs for tango. maybe we make this configurabale via typoscript
                $organizer->setHidden(1) ;
                $organizer->setPid( 13 ) ;
                $organizer->setSorting( 99999999 ) ;
                $organizer->setSysLanguageUid(-1 ) ;

                $organizer->setAccessUsers(intval($GLOBALS['TSFE']->fe_user->user['uid'] ));
                $organizer->setAccessGroups( $this->settings['feEdit']['adminOrganizerGroudIds'] );

                $this->organizerRepository->add($organizer);
                $this->persistenceManager->persistAll() ;

                $this->cacheService->clearPageCache( array($this->settings['pageIds']['organizerAssist'])  );
                if( $organizer->getUid()) {
                    $this->addFlashMessage('The Organizer was created with ID:' . $organizer->getUid() , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

                } else {
                    $this->addFlashMessage('Error: Organizer did not get an ID:' , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
                }
            }



            // https://www.allplan.com.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[organizerUid]=111&tx_jvevents_ajax[action]=activate&tx_jvevents_ajax[userUid]=1&tx_jvevents_ajax[hmac]=hmac1234&&tx_jvevents_ajax[rnd]=11234
            // https://www.allplan.com.ddev.local/de/?uid=82&tx_jvevents_ajax[organizerUid]=111&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[action]=activate&tx_jvevents_ajax[userUid]=1&tx_jvevents_ajax[hmac]=hmac1234&&tx_jvevents_ajax[rnd]=11234

            $organizerUid = intval( $organizer->getUid()) ;
            $userUid = intval($GLOBALS['TSFE']->fe_user->user['uid']) ;
            $rnd = time() ;
            $tokenStr = "activateOrg" . "-" . $organizerUid . "-" . $GLOBALS['TSFE']->fe_user->user['crdate'] ."-". $userUid .  "-". $rnd ;
            $hmac = \TYPO3\CMS\Core\Utility\GeneralUtility::hmac( $tokenStr );

            $url  = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') ;
            /*
            $url .= "/de?tx_jvevents_ajax[action]=activate&tx_jvevents_ajax[controller]=ajax" ;

            $url .= "&tx_jvevents_ajax[organizerUid]=" . $organizerUid ;
            $url .= "&tx_jvevents_ajax[userUid]=" . $userUid ;
            $url .= "&tx_jvevents_ajax[rnd]=" . $rnd ;
            $url .= "&tx_jvevents_ajax[hmac]=" . $hmac ;
            */

            $url .= "/de/admin/assist.html?tx_jvevents_events[action]=activate&tx_jvevents_events[controller]=Organizer" ;

            $url .= "&tx_jvevents_events[organizerUid]=" . $organizerUid ;
            $url .= "&tx_jvevents_events[userUid]=" . $userUid ;
            $url .= "&tx_jvevents_events[rnd]=" . $rnd ;
            $url .= "&tx_jvevents_events[hmac]=" . $hmac ;

            $msg = "A new organizer has registered:" ;
            $msg .= "\n\n" . "Name: " . $organizer->getName() ;
            $msg .= "\n" . "Email: " . $organizer->getEmail() ;
            $msg .= "\n" . "Phone: " . $organizer->getPhone() ;
            $msg .= "\n" . "Link: " . $organizer->getLink() ;
            $msg .= "\n\n\n" . "Description: " . $organizer->getDescription() ;
            $msg .= "\n *********************************************** " ;
            $msg .= "\n Der  Veranstalter Account auf '" . $_SERVER['SERVER_NAME'] .  "' wurde freigeschaltet." ;
            $msg .= "\n" ;
            $msg .= "\n Nun können Veranstaltungsorte und Termine erstellt werden." ;
            $msg .= "\n <b>WICHTIG:</b> Bitte neu einloggen, damit die neuen Zugriffsrechte als Veranstalter wirksam sind." ;
            $msg .= "\n" ;
            $msg .= "\n Bei Fragen, ist ein Blick in den Hilfebereich sicher hilfreich. " ;
            $msg .= "\n" ;
            $msg .= "\n ***********************************************" ;

            $msg .= "\nFür den Webmaster: " ;
            $msg .= "\nToken String: " ;
            $msg .= "\n"  .  $tokenStr ;
            $msg .= "\n ***********************************************" ;

            $html = nl2br($msg) ;
            $html .= "<br>" . "<hr><br>" . '<a href="' . $url . '"> Klick To Enable  </a>' ."\n" . "\n"  ;

            $msg .= "\n\n" . "Klick to Enable Organizer Account: \n \n" . $url ;
            $msg .= "\n ***********************************************" ;

            $this->sendDebugEmail(  $this->settings['register']['senderEmail'] , $this->settings['register']['senderEmail'] , "[TANGO][NewOrganizer] - " . $organizer->getEmail() , $msg , $html) ;


            $this->showNoDomainMxError($organizer->getEmail() ) ;

            $this->redirect('assist' , NULL, Null , NULL , $this->settings['pageIds']['organizerAssist']);
        } else {
            $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush();

            $this->addFlashMessage('You do not have access rights to create own Organizer data.'  , '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        }

        $this->redirect('assist' , NULL, Null , NULL , $this->settings['pageIds']['organizerAssist'] );


    }

    /**
     * @param int $organizerUid
     * @param int $userUid
     * @param string $hmac
     * @param int $rnd
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function activateAction($organizerUid=0 , $userUid=0 , $hmac='invalid' , $rnd = 0 ) {

        $organizerUid = intval($organizerUid) ;
        $userUid = intval($userUid) ;
        $rnd = intval($rnd) ;
        if($rnd == 0 ||  $rnd < ( time() - (60*60*24*30)) ) {
            // if rnd is not set, take actual time so the value will be invalid  . same it rnd is older than 30 days
            $rnd = time() ;
        }
        $hmac = trim($hmac) ;
        if( $this->settings['pageIds']['organizerAssist'] < 1 ) {

            // fix this BUG : settings pageIds is not working anymore. Why ??
            $this->settings['pageIds']['organizerAssist'] = 24 ;
        }
        /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
        $organizer = $this->organizerRepository->findByUidAllpages($organizerUid, FALSE, TRUE);



        /** @var \JVE\JvEvents\Domain\Repository\FrontendUserRepository $userRepository */
        $userRepository = $this->objectManager->get(\JVE\JvEvents\Domain\Repository\FrontendUserRepository::class) ;
        /** @var \JVE\JvEvents\Domain\Model\FrontendUser $user */
        $user = $userRepository->findByUid($userUid) ;


        if( !$organizer  ) {
            $this->addFlashMessage("Organizer not found by ID : " . $organizerUid , "" , \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING) ;
            try {
                $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
            } catch(StopActionException $e) {

                foreach (  $e->getResponse()->getHeaders() as $header => $value ) {
                    header( "$header:" . $value[0]) ;
                }

                die;
            }
        }
        if( !$user ) {
            $this->addFlashMessage("User not found by ID : " . $userUid , "" , \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING) ;
            try {
                $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
            } catch(StopActionException $e) {
                foreach (  $e->getResponse()->getHeaders() as $header => $value ) {
                    header( "$header:" . $value[0]) ;
                }

                die;
            }
        }
        $tokenStr = "activateOrg" . "-" . $organizerUid . "-" . $user->getCrdate() ."-". $userUid .  "-". $rnd ;
        $tokenId = GeneralUtility::hmac( $tokenStr );


        if( $hmac != $tokenId ) {
            if ( 1==2 ) {
                echo "<pre> "  ;
                var_dump( $user->_getCleanProperties());
                echo "<hr> "  ;
                echo "<br>activateOrg-3097-1626975809-3353-1626976693 : "  ;
                echo "<br>Got hmac: " . $hmac ;
                echo "<br>Got token: " . $tokenStr ;
                echo "<br>Gives: " . $tokenId ;
                die;
            }
            $this->addFlashMessage("Hmac does not fit to: " . $tokenStr , "ERROR" , \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR) ;
            try {
                $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
            } catch(StopActionException $e) {
                foreach (  $this->response->getHeaders() as $header ) {
                    header( $header) ;
                }

                die;
            }

        }
        $groups =  $user->getUsergroup()->toArray() ;
        if ( isset($this->settings['organizer']) && isset($this->settings['organizer']['newOrgGetGroups'])
            &&  is_array( $this->settings['organizer']['newOrgGetGroups']) && count($this->settings['organizer']['newOrgGetGroups']) > 0
        ) {
            foreach ( $this->settings['organizer']['newOrgGetGroups'] as $group => $groupName ) {
                $groupsMissing[$group ] =  TRUE  ;
            }
        } else {
            $groupsMissing = array( 2 => TRUE , 7 => TRUE ) ;
        }

        if(is_array( $groups)) {
            /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $group */
            foreach ($groups as $group) {
                foreach ($groupsMissing as $key => $item) {
                    if ( $group->getUid() == $key ) {

                        $groupsMissing[$key] = FALSE ;
                    }
                }
            }
        }
        $msg = '' ;
        foreach ($groupsMissing as $key => $item) {
            if ( $item  ) {
                /** @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository $userGroupRepository */
                $userGroupRepository = $this->objectManager->get(\TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository::class) ;
                $newGroup = $userGroupRepository->findByUid($key) ;
                if( $newGroup ) {
                    if ( $msg == '' ) {
                        $msg .= " Added Group: " . $newGroup->getUId() ;
                    } else {
                        $msg .= ", " . $newGroup->getUId() ;
                    }

                    $user->addUsergroup($newGroup) ;
                }
            }

        }
        $user->setDisable(0) ;
        $userRepository->update( $user ) ;

        $organizer->setHidden(0) ;
        $this->organizerRepository->update( $organizer) ;
        $this->persistenceManager->persistAll() ;
        $this->addFlashMessage("User : " . $userUid . " (" . $user->getEmail() . ") enabled | " . $msg . "  " , "Success" , \TYPO3\CMS\Core\Messaging\AbstractMessage::OK) ;
        $this->addFlashMessage("Organizer : " . $organizerUid . " (" . $organizer->getName() . ")  enabled " , \TYPO3\CMS\Core\Messaging\AbstractMessage::OK) ;


        $msg = "\n *********************************************** " ;
        $msg .= "\n Der  Veranstalter Account auf '" . $_SERVER['SERVER_NAME'] .  "' wurde freigeschaltet." ;
        $msg .= "\n\n" . "Name: " . $organizer->getName() ;
        $msg .= "\n" . "Email: " . $organizer->getEmail() ;
        $msg .= "\n" . "Phone: " . $organizer->getPhone() ;
        $msg .= "\n" ;

        $msg .= "\n Nun können Veranstaltungsorte und Termine erstellt werden." ;
        $msg .= "\n <b>WICHTIG:</b> Bitte neu einloggen, damit die neuen Zugriffsrechte als Veranstalter wirksam sind." ;
        $msg .= "\n" ;
        $msg .= "\n Bei Fragen, ist ein Blick in den Hilfebereich sicher hilfreich. " ;
        $msg .= "\n" ;
        $msg .= "\n ***********************************************" ;

        $html = nl2br($msg) ;
        if (ExtensionManagementUtility::isLoaded('mailsignature')) {
            /** @var SignatureService $signatureService */
            $signatureService = GeneralUtility::makeInstance(SignatureService::class);
            $signature = $signatureService->getSignature($this->settings['signature']['uid']);
            $html .= "<br><br>" . $signature['html'] ;
            $msg .= "\n\n" . $signature['plain'] ;
        }




        $this->sendDebugEmail(  $this->settings['register']['senderEmail'] , $this->settings['register']['senderEmail'] , "[TANGO][NewOrganizer] - " . $organizer->getEmail() . " activated ", $msg , $html) ;
        $this->sendDebugEmail(  $organizer->getEmail() , $this->settings['register']['senderEmail'] , "[TANGO][NewOrganizer] - " . $organizer->getEmail() . " activated ", $msg , $html) ;

        try {
            $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
        } catch(StopActionException $e) {
            foreach (  $e->getResponse()->getHeaders() as $header => $value ) {
                header( "$header:" . $value[0]) ;
            }


            die;
        }

    }
    
    /**
     * action edit
     *
     * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("organizer")
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
     * @TYPO3\CMS\Extbase\Annotation\Validate(param="organizer" , validator="JVE\JvEvents\Validation\Validator\OrganizerValidator")

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
        $organizer->setLanguageUid( -1)  ;
        if ($organizer->getPid() < 1 ) {
            // ToDo find good way to handle ID Default .. maybe a pid per User, per location or other typoscript setting
            $organizer->setPid( 13 ) ;
        }
            $row['name'] =  $organizer->getName() ;
            $row['pid'] =  $organizer->getPid() ;
            $row['parentpid'] =  1 ;
            $row['uid'] =  $organizer->getUid() ;
            $row['sys_language_uid'] =  $organizer->getLanguageUid() ;
            $row['slug'] =  $organizer->getSlug() ;
            $slug = SlugUtility::getSlug("tx_jvevents_domain_model_organizer", "slug", $row  )  ;
            $organizer->setSlug( $slug ) ;
        return $organizer ;
    }


}