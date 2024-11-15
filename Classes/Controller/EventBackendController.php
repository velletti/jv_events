<?php
namespace JVelletti\JvEvents\Controller;

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

use JVelletti\JvEvents\Domain\Repository\RegistrantRepository;
use JVelletti\JvEvents\Utility\EmConfigurationUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Location;
use JVelletti\JvEvents\Domain\Model\Registrant;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\FormProtection\FrontendFormProtection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use JVelletti\JvEvents\Utility\RegisterHubspotUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;

/**
 * EventBackendController
 */
class EventBackendController extends BaseController
{

    /**
     * registrantRepository
     *
     * @var RegistrantRepository
     */
    protected $registrantRepository = NULL;

    /**
     * @var RegisterHubspotUtility
     */
    protected RegisterHubspotUtility $hubspot  ;

    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected PageRenderer $pageRenderer;
    protected BackendUserAuthentication $backendUser;

    public function __construct(
       RegistrantRepository $registrantRepository,
       ModuleTemplateFactory $moduleTemplateFactory,
       PageRenderer $pageRenderer,
       BackendUserAuthentication $backendUser
    ) {
        $this->registrantRepository = $registrantRepository;
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->pageRenderer = $pageRenderer;
        $this->backendUser = $backendUser;
    }


	public function initializeAction() {
       // This works only, if moduleTemplateFactory is used for view => see action function(s)
       $this->pageRenderer->addCssFile('EXT:jv_events/Resources/Public/Css/backendModule.css', 'stylesheet', 'all', '', false);
       // $this->pageRenderer->loadJavaScriptModule('@peterBenke/pbNotifications/Notifications.js');


        // $this->hubspot = GeneralUtility::makeInstance(RegisterHubspotUtility::class);
        // parent::initializeAction() ;
	}
    /**
     * action list
     * http://master.dev/typo3/index.php?M=web_JvEventsEventmngt&moduleToken=0659e7b6ef0e5e1632d32734a03e2141563302d5
     *
     * @return void
     */
    public function listAction(): ResponseInterface
    {
        $view = $this->moduleTemplateFactory->create($this->request);

        $pageId = 0 ;
        if ( $this->request->hasArgument('id') ) {
            $pageId = $this->request->getArgument("id") ;
            $pageRow = BackendUtility::getRecord(
               'pages',
               $pageId,
               '*'
            );
/*
            if (!$this->backendUser->doesUserHaveAccess($pageRow, 1)) {
                $view->assign('error', 'No access to this page');
                return $view->renderResponse('/EventBackend/List.html');
            }
*/
        }


        $view->setTitle('Event Management');
        $view->setModuleName("web_JvEventsEventmngt");
        $view->setModuleId("jvevents_eventmngt");

        $eventID = null;
        $events = [];
        $itemsPerPage = 20 ;
        $pageId = GeneralUtility::_GP('id');

        $pageRow = BackendUtility::getRecord(
           'pages',
           $pageId,
           '*'
        );

        if( ! $GLOBALS['BE_USER']->doesUserHaveAccess($pageRow , 1 ) ) {
            die( "No access to this page: " . $pageId) ;
        }
        $recursive = false ;

        if( $this->request->hasArgument('recursive')) {
            $recursive = $this->request->getArgument('recursive') ;
        }
        $currentPage = 1;
        if( $this->request->hasArgument('currentPage')) {
            $currentPage = $this->request->getArgument('currentPage') ;

        }
        $onlyActual = -999 ;
        if( $this->request->hasArgument('onlyActual') && $this->request->getArgument('onlyActual') ) {
            $onlyActual = $this->request->getArgument('onlyActual') ;

        }
        if ( $recursive ) {
            $this->settings['storagePids'] = $this->registrantRepository->getTreeList($pageId, 9999, 0, 1) ;
        }

        $this->settings['filter']['startDate']  = $onlyActual ;
        $this->settings['storagePid'] = $pageId ;


        $this->settings['directmail'] = FALSE  ;
        $eventID = 0 ;
        if( $this->request->hasArgument('event')) {
            $eventID = $this->request->getArgument('event') ;
            if( $eventID > 0 ) {
                $itemsPerPage = 100 ;
                $selectedEvent= $this->eventRepository->findByUidAllpages($eventID , false) ;
                if ( $selectedEvent instanceof  Event  ){
                    $location = '' ;
                    if ($selectedEvent->getLocation() instanceof  Location )  {
                        $location = $selectedEvent->getLocation()->getCity() ;
                    }
                    $eventName = $selectedEvent->getStartDate()->format("Y-m-d - ")
                       . substr( (string) $selectedEvent->getName() , 0 , 50 ) . " | " . $location . " (ID: " . $selectedEvent->getUid() . ")" ;

                    $view->assign('selectedEvent', $selectedEvent );
                    $view->assign('eventName', $eventName );



                    $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($selectedEvent->getRegistrationFormPid());
                    $serverFromSite =  $site->getBase()->getHost() ;

                    $lang = max(  $selectedEvent->getSysLanguageUid()  , 0 ) ;
                    $checkString =  $serverFromSite . "-" .  $selectedEvent->getUid()  . "-" . $selectedEvent->getCrdate() ;
                    $checkHash = GeneralUtility::hmac ( $checkString ) ;
                    // pid = 0 to load registrations from all pages for that event
                    $url = (string)$site->getRouter()->generateUri( $selectedEvent->getRegistrationFormPid() ,['_language' => $lang ,
                       'tx_jvevents_registrant' => ['action' => 'list' , 'controller' => 'Registrant' ,'event' =>  $selectedEvent->getUid()
                           , 'export' => '1' ,  'pid' => '0' ,  'hash' => $checkHash  ]]);
                    $view->assign('downloadUri', $url );

                    $registrants = $this->registrantRepository->findByFilter('', $eventID, 0 ,  [] , 999 );

                    $configuration = EmConfigurationUtility::getEmConf();
                    $previewPid = ( array_key_exists( 'DetailPid' , $configuration) && $configuration['DetailPid'] > 0 ) ? intval($configuration['DetailPid']) : 111 ;

                    try {
                        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($previewPid);

                        $previewUrl = (string)$site->getRouter()->generateUri( $previewPid ,['_language' => max(  $selectedEvent->getSysLanguageUid()  , 0 ) ,
                           'tx_jvevents_event' => ['action' => 'show' , 'controller' => 'Event' ,'event' =>  $selectedEvent->getUid() ]]);

                    } catch (\Exception) {
                        $previewUrl = false ;
                    }
                    $view->assign('previewUri', $previewUrl );
                }
            }
         }
        if ( $eventID == 0) {
            $registrants = $this->registrantRepository->findByFilter('', $eventID, $pageId ,  $this->settings , 999 );
        }
        if (ExtensionManagementUtility::isLoaded('direct_mail')) {
            $this->settings['directmail'] = TRUE ;
        }
        /** @var QueryResultInterface $events */


        $eventsOptions = [] ;
        if( $pageId > 0 ) {
            $events = $this->eventRepository->findForBackend($pageId , $this->settings ) ;
        }
        if ( $events) {
            /** @var Event $event */
            foreach ($events as $event) {
                $location = " no location !!! " ;
                  if ($event->getLocation() instanceof  Location )  {
                      $location = ( $event->getLocation()->getCity() ?? " no city " ) ;
                      if( strtolower($event->getLocation()->getName() != "online"  )) {
                          if ( (int)$event->getLocation()->getLat()  == 0 ) {
                              $location .=  " (no map position !!) " ;
                          }
                      }
                  }

                $eventsOptions[] = [
                    'uid' => $event->getUid(),
                    'name' => $event->getStartDate()->format("Y-m-d - ") . substr( (string) $event->getName() , 0 , 40 ) . " | " . $location . " (ID: " . $event->getUid() . " | " .  $event->getRegisteredSeats() . " + " . $event->getUnconfirmedSeats() . " regist.)",
                ];
            }
        }

        $view->assign('event', $eventID);
        $view->assign('itemsPerPage', $itemsPerPage);
        $view->assign('eventsOptions', $eventsOptions );
        $view->assign('registrants', $registrants);
        $view->assign('settings', $this->settings);
        $view->assign('onlyActual', $onlyActual);
        $view->assign('currentPage', $currentPage);
        $view->assign('recursive', $recursive);
        $view->assign('pageId', $pageId );
        return $view->renderResponse('/EventBackend/List.html');
    }


    /**
     * action new
     * @param \JVelletti\JvEvents\Domain\Model\Registrant
     * @return void
     */
    // public function confirmAction(\JVelletti\JvEvents\Domain\Model\Registrant $registrant )
    public function confirmAction()
    {
        $eventID = 0 ;
        if ( $this->request->hasArgument("eventID")) {
            $eventID = $this->request->getArgument("eventID");
        }
        if ( $this->request->hasArgument("registrant")) {
            $regId = $this->request->getArgument("registrant") ;


            if( $regId > 0 ) {
                /** @var Registrant $registrant */
                $registrant = $this->registrantRepository->findByUid($regId) ;
                if( $registrant ) {

                    /** @var Event $event */
                    $event = $this->eventRepository->findByUidAllpages($registrant->getEvent() ) ;

                    if( $event ) {

                        $pid = (int)$event[0]->getRegistrationFormPid() ;
                        $lng = (int)$event[0]->getSysLanguageUid() ;
                        if( $lng < 1) {
                            $lng = 0 ;
                        }
                        $path = \JVelletti\JvTyposcript\Utility\TyposcriptUtility::getPath($pid , $lng , "tx_jvevents_events") ;
                        $typoScript = \JVelletti\JvTyposcript\Utility\TyposcriptUtility::loadTypoScriptviaCurl($path) ;
                        $this->settings = array_merge( $this->settings ,  ($typoScript['settings'] ?? []) ) ;
                        if( !isset($this->settings['register']['senderEmail'] ) ) {
                            $this->addFlashMessage(" settings['register']['senderEmail']  not set "  , 'ERROR', ContextualFeedbackSeverity::ERROR);
                            return $this->redirect('list' , NULL , NULL , ['event' => $eventID]) ;
                        }
                        $this->settings['pageId'] = $pid ;
                        $this->settings['sys_language_uid'] = $lng ;


                        if( !$this->settings['LayoutRegister'] ) {
                            $this->settings['LayoutRegister'] = "2Allplan" ;
                        }
                        //  echo "<pre>" ;
                        // var_dump($this->settings) ;
                        // die;

                        $registrant->setConfirmed("1") ;
                        $name = trim( $registrant->getFirstName() . " " . $registrant->getLastName())  ;
                        if( strlen( $name ) < 3 ) {
                            $name = "RegistrantId: " . $registrant->getUid() ;
                        } else {
                            $name  = '=?utf-8?B?'. base64_encode( $name) .'?=' ;
                        }

                        $this->sendEmail($event[0] , $registrant ,"Registrant" , [$registrant->getEmail() => $name] , FALSE )  ;

                        $this->registrantRepository->update($registrant) ;
                        $this->addFlashMessage($this->settings['register']['senderEmail'] . " -> Email send to " . $registrant->getEmail() . " - layout: " . $this->settings['LayoutRegister'] , '', ContextualFeedbackSeverity::INFO);
                    }
                }
            }

        }
        return $this->redirect('list' , NULL , NULL , ['event' => $eventID]) ;
    }

    // ########################################   functions ##################################
    /**
     * helper for Formvalidation
     * @param string $action
     * @return string
     */
    public function generateToken($action = "action")
    {
        /** @var FrontendFormProtection $formClass */
        $formClass =  GeneralUtility::makeInstance( FrontendFormProtection::class) ;

        return $formClass->generateToken(
           'event', $action ,   "P" . $this->settings['pageId'] . "-L" .$this->settings['sys_language_uid']
        );

    }

}