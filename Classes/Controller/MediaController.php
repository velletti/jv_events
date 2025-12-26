<?php
namespace JVelletti\JvEvents\Controller;

use JVelletti\JvEvents\Utility\MigrationUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\CMS\Core\Utility\HttpUtility;
use Psr\Http\Message\ResponseInterface;
use JVelletti\JvEvents\Domain\Model\Media;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Annotation\Validate;
use JVelletti\JvEvents\Validation\Validator\MediaValidator;
use JVelletti\JvEvents\Domain\Model\Organizer;
use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Category;
use JVelletti\JvEvents\Utility\SlugUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use DateTime;

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
 * MediaController
 */
class MediaController extends BaseController
{


    /**
     * action init
     *
     * @return void
     */
    public function initializeAction()
    {
        if( !$this->request->hasArgument('media')) {
            // ToDo redirect to error
        }
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
        if( array_key_exists( 'filtermedia' , $this->settings)) {

            if ( array_key_exists( "categories", $this->settings['filtermedia']) && strlen(trim((string) $this->settings['filtermedia']['categories'] )) > 0 )  {
                $filter["mediaCategory.uid"] =  GeneralUtility::trimExplode( "," , $this->settings['filtermedia']['categories'] )   ;
            }
        }

        // ($filter=FALSE , $toArray=FALSE , $ignoreEnableFields = FALSE , $limit=FALSE, $lastModified = '-1 YEAR')
        $medias = $this->mediaRepository->findByFilterAllpages($filter , false , true , false  );
        $this->view->assign('medias', $medias);
        return $this->htmlResponse();
    }
    
    /**
     * action show
     *
     * @return void
     */
    public function showAction(?Media $media): ResponseInterface
    {
        if ( $media ) {
            $this->view->assign('media', $media);
        } else {
            $this->addFlashMessage($this->translate("error.general.entry_not_found"), "Sorry!" , ContextualFeedbackSeverity::WARNING) ;
        }
        return $this->htmlResponse();
    }
    
    /**
     * action new
     * @param Media|Null $media
     * @return void
     */
    #[IgnoreValidation(['value' => 'media'])]
    public function newAction(Media $media=Null): ResponseInterface
    {
        /** @var QueryResultInterface $categories */
        $categories = $this->categoryRepository->findAllonAllPages( '3' );


        if ( $media==null) {
            /** @var Media $media */
            $media = GeneralUtility::makeInstance(Media::class);
        }
        $organizer= null ;
        if ( $media->getUid() < 1 ) {
            $organizer = $this->getOrganizer() ;
            if( $organizer ) {
                $media->setOrganizer($organizer);
            }

            // ToDo find good way to handle ID Default .. maybe a pid per User, per media or other typoscript setting
            $media->setPid( 45 ) ;
        }

        if($this->isUserOrganizer() ) {
            $this->view->assign('user', intval($this->frontendUser->user['uid'] ) ) ;
            $this->view->assign('media', $media);
            $this->view->assign('organizer', $organizer );
            $this->view->assign('categories', $categories);
        }
        return $this->htmlResponse();
    }
    
    /**
     * action create
     *
     *
     * @return void
     */
    #[Validate(['param' => 'media', 'validator' => MediaValidator::class])]
    public function createAction(Media $media)
    {
        if( $this->request->hasArgument('media')) {
            $media = $this->cleanMediaArguments( $media) ;
        }
        $action = "edit" ;
        if($this->isUserOrganizer() ) {
            $this->getFlashMessageQueue()->getAllMessagesAndFlush();


            try {
                // ToDo Storage PID for media via typoscript
                $media->setPid(45) ;
                $organizer = $this->getOrganizer() ;
                if( $organizer ) {
                    $orgId = $organizer->getUid() ;
                    $media->setOrganizer($organizer);
                }

                $this->mediaRepository->add($media);
                $this->persistenceManager->persistAll() ;


                $this->addFlashMessage('The Media was created.  It may take up some hours before it is visible', '', ContextualFeedbackSeverity::OK);
            } catch ( \Exception $e ) {
                $this->addFlashMessage($e->getMessage() , 'Error', ContextualFeedbackSeverity::WARNING);

            }

        } else {

            $pid = $this->settings['pageIds']['loginForm'] ;
            $this->addFlashMessage('The object was NOT created. You are not logged in as Organizer.' . $media->getUid() , '', ContextualFeedbackSeverity::WARNING);
            return $this->redirect(null , null , NULL , NULL , $pid );
        }

        $pid = $this->settings['pageIds']['editMedia'] ;


        if( $pid < 1) {
            $pid = MigrationUtility::getPageId($this) ;
            $controller = NULL ;
            $action = NULL ;
        } else {
            $controller = "Media" ;
            $action = "show" ;
        }
        return $this->redirect($action , $controller , null , [ 'media' => $media->getUid()]  , $pid );

    }

    /**
     * action edit
     *
     * @return void
     */
    #[IgnoreValidation(['value' => 'media'])]
    public function editAction(Media $media): ResponseInterface
    {
        /** @var QueryResultInterface $categories */
        $categories = $this->categoryRepository->findAllonAllPages( '3' );


        if( ! $hasAccess = $this->isAdminOrganizer()  ) {
           if( $organizer = $media->getOrganizer() ) {
               $hasAccess = $this->hasUserAccess( $organizer ) ;
           } else {
               $media->setOrganizer(null) ;
           }

        } else {
            $this->view->assign('isAdmin', true );
            $organizers  = $this->organizerRepository->findByFilterAllpages( false , false , true , false , false ) ;
            $this->view->assign('organizers', $organizers );
        }
        if ( $hasAccess ) {
            $this->view->assign('user', intval( $this->frontendUser->user['uid'] ) );

            $this->view->assign('media', $media);
            $this->view->assign('categories', $categories);
        } else {
            $this->addFlashMessage('You do not have access rights to change this data.' , 'Error', ContextualFeedbackSeverity::WARNING);
            $this->addFlashMessage('ID: ' . $media->getUid(), '', AbstractMessageContextualFeedbackSeverity::WARNING);
        }
        return $this->htmlResponse();
    }
    
    /**
     * action update
     *
     * @return void
     */
    #[Validate(['param' => 'media', 'validator' => MediaValidator::class])]
    public function updateAction(Media $media)
    {
        if( ! $hasAccess = $this->isAdminOrganizer()  ) {
            if( $organizer = $media->getOrganizer() ) {
                $hasAccess = $this->hasUserAccess( $organizer ) ;
            }
        }

        if ($hasAccess ) {
            $media = $this->cleanMediaArguments( $media) ;
            $this->getFlashMessageQueue()->getAllMessagesAndFlush();
            $this->addFlashMessage('The object was updated. It may take some hours before it is visible', '', ContextualFeedbackSeverity::OK);
            $this->mediaRepository->update($media);
        } else {
            $this->addFlashMessage('You do not have access rights to change this data.' . $media->getUid() , '', ContextualFeedbackSeverity::WARNING);
        }
        return $this->redirect('edit' , NULL, Null , ["media" => $media]);
    }
    
    /**
     * action delete
     *
     * @return ResponseInterface
     */
    public function deleteAction(Media $media): ResponseInterface
    {
        $delete = false ;
        if( $this->request->hasArgument('delete')) {
            $delete = $this->request->getArgument('delete');

            if ( $delete ) {

                if ( $this->hasUserAccess($media->getOrganizer() )) {
                    $this->getFlashMessageQueue()->getAllMessagesAndFlush();
                    $this->mediaRepository->remove($media);
                    $this->addFlashMessage('This Media entry was deleted and will disappear in th next 24h', '', ContextualFeedbackSeverity::OK);
                } else {
                    $this->addFlashMessage('You do not have access rights to change this data.' . $location->getUid() , '', ContextualFeedbackSeverity::WARNING);
                }
            }
        }

        $this->view->assign('delete', $delete);
        $this->view->assign('media', $media);
        $this->view->assign('settings', $this->settings );
        return $this->htmlResponse() ;
    }

    /**
     * @return Media
     */
    public function cleanMediaArguments(Media $media) {

        $row = [];
        $desc = str_replace( ["\n", "\r", "\t"], [" ", "", " "], (string) $media->getDescription() ) ;
        $desc = strip_tags($desc , "<p><br><a><i><strong><h2><h3>") ;
        $media->setDescription( $desc ) ;
        $media->setLink( trim((string) $media->getLink())) ;
        $media->setTeaserText( trim((string) strip_tags($media->getTeaserText()))) ;
        $media->setTstamp( time() ) ;



        // Type validation should be done in validator class so we can ignore issue with wrong format
        $mediaArray = $this->request->getArgument('media');

        $stD = DateTime::createFromFormat('d.m.Y', $mediaArray['releaseDateFE']  );
        $stD->setTime(0,0,0,0 ) ;
        $media->setReleaseDate( $stD ) ;

        /*   +******  Update the Category  ************* */
        $mediaCatUid = intval( $mediaArray['mediaCategory'] ) ;
        /** @var Category $mediaCat */
        $mediaCat = $this->categoryRepository->findByUid($mediaCatUid) ;


        if( $mediaCat ) {
            if( $media->getMediaCategory() ){
                $media->getMediaCategory()->removeAll($media->getMediaCategory()) ;
            }
            $media->addMediaCategory($mediaCat) ;
        }



        if ( $this->isAdminOrganizer()) {
            if( is_array( $mediaArray['organizer'] ) && isset($mediaArray['organizer']["__identity"] ) ) {
                $orgUid = $mediaArray['organizer']["__identity"]  ;
            } else {
                $orgUid = $mediaArray['organizer'];
            }
            $organizer = $this->organizerRepository->findByUidAllpages(  intval( $orgUid ) , false, false  ) ;

            if (  $organizer  ) {
                $media->setOrganizer($organizer);
            } else {
                $media->setOrganizer(null);
            }
        }



        if( $media->getPid() < 1) {
            // ToDo find good way to handle ID Default .. maybe a pid per User, per media or other typoscript setting
            $media->setPid( 45 ) ;
        }


        $media->setLanguageUid(-1) ;

        $row['name'] =  $media->getName() ;
        $row['pid'] =  $media->getPid() ;
        $row['parentpid'] =  1 ;
        $row['uid'] =  $media->getUid() ;
        $row['sys_language_uid'] =  $media->getLanguageUid() ;
        $row['slug'] =  $media->getSlug() ;
        $slug = SlugUtility::getSlug("tx_jvevents_domain_model_media", "slug", $row )  ;
        $media->setSlug( $slug ) ;


        return $media ;
    }

}