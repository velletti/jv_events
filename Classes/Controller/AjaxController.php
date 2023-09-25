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

use EBT\ExtensionBuilder\Exception;
use JVE\JvEvents\Domain\Model\Event;
use JVE\JvEvents\Domain\Model\Location;
use JVE\JvEvents\Utility\AjaxUtility;
use JVE\JvEvents\Utility\TyposcriptUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use JVE\JvEvents\Utility\ShowAsJsonArrayUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

use TYPO3\CMS\Fluid\View\StandaloneView;


/**/


/**
 * AjaxController
 */
class AjaxController extends BaseController
{

    /**
     * @var array
     */
    protected $user ;

    /**
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public $tsFEController ;

    /** @var array  */
    public $arguments = '' ;


    /**
     * @var StandaloneView
     */
    public $standaloneView;

    public function initializeAction() {



        parent::initializeAction() ;
    }
    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext $controllerContext
     */
    public function injectControllerContext( \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext $controllerContext) {

        $this->controllerContext = $controllerContext;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     * @internal only to be used within Extbase, not part of TYPO3 Core API.
     */
    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    public function initializeRepositorys()
    {

        $this->tagRepository        = $this->objectManager->get(\JVE\JvEvents\Domain\Repository\TagRepository::class);
        $this->categoryRepository        = $this->objectManager->get(\JVE\JvEvents\Domain\Repository\CategoryRepository::class);
        $this->registrantRepository        = $this->objectManager->get(\JVE\JvEvents\Domain\Repository\RegistrantRepository::class);
        $this->locationRepository        = $this->objectManager->get(\JVE\JvEvents\Domain\Repository\LocationRepository::class);
        $this->organizerRepository        = $this->objectManager->get(\JVE\JvEvents\Domain\Repository\OrganizerRepository::class);
        $this->eventRepository        = $this->objectManager->get(\JVE\JvEvents\Domain\Repository\EventRepository::class);
        $this->subeventRepository        = $this->objectManager->get(\JVE\JvEvents\Domain\Repository\SubeventRepository::class);
        $this->staticCountryRepository        = $this->objectManager->get(\JVE\JvEvents\Domain\Repository\StaticCountryRepository::class);
    }

    public function dispatcher()
    {
        /**
         * Gets the Ajax Call Parameters   | OLD school without middleWare ..
         */
        $_gp = GeneralUtility::_GPmerged('tx_jvevents_ajax');


        $ajax = array();
        $ajax['arguments'] = $_gp;
        $ajax['vendor'] = 'JVE';
        $ajax['vendorName'] = 'JVE';
        $ajax['extensionName'] = 'JvEvents';
        $ajax['pluginName'] = 'Events';
        $ajax['controller'] = 'Ajax';
        $ajax['action'] = $_gp['action'];

        /*        * check if action is allowed    */
        if (!in_array($ajax['action'], array("eventMenu", "eventList", "locationList", "activate", "eventUnlink"))) {
            $ajax['action'] = "eventMenu";
        }


        /**
         * @var $objectManager \TYPO3\CMS\Extbase\Object\ObjectManager
         */
        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Mvc\Request::class, \JVE\JvEvents\Controller\AjaxController::class );

        /** @var \TYPO3\CMS\Core\Information\Typo3Version $version */
        $version = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);



        $request->setControllerExtensionName($ajax['extensionName']);
        $request->setPluginName($ajax['pluginName']);
        $request->setControllerName($ajax['controller']);
        $request->setControllerActionName($ajax['action']);
        $request->setArguments($ajax['arguments']);

        $response = $objectManager->get(\TYPO3\CMS\Extbase\Mvc\ResponseInterface::class);
        $dispatcher = $objectManager->get(\TYPO3\CMS\Extbase\Mvc\Dispatcher::class);

        if( key_exists('event' , $_gp)) {
            $request->setArgument('event',  intval( $_gp['event'] )) ;
        }

        $dispatcher->dispatch($request, $response);
        echo $response->getContent();
        die;

    }




    /**
     * action list
     *
     * @return array
     */
    public function eventsListMenuSub(array $arguments )
    {

       //  $GLOBALS['TSFE']->fe_user->user = [ 'uid' => 476 , "username" => "jvel@test.de" , "1,2,3,4,5,6,7"] ;
        /* ************************************************************************************************************ */
        /*   Prepare the Output :
        /* ************************************************************************************************************ */
        $feuser = intval(  $GLOBALS['TSFE']->fe_user->user['uid']) ;

        $output = array (
            "requestId" =>  intval( $GLOBALS['TSFE']->id ) ,

            "event" => array()  ,
            "events" => array()  ,
            "eventsFilter" => array()  ,
            "eventsByFilter" => array()  ,
            "mode" => $arguments['mode'] ,
            "feuser" => array(
                "uid" => $GLOBALS['TSFE']->fe_user->user['uid'] ,
                "username" => $GLOBALS['TSFE']->fe_user->user['username'] ,
                "usergroup" => $GLOBALS['TSFE']->fe_user->user['usergroup'] ,
                "isOrganizer" => $this->isUserOrganizer(),

            )  ,
            "organizer" => array() ,
            "location" => array() ,

        ) ;
        if ( $output["feuser"]["isOrganizer"]) {
            $feuserOrganizer = $this->organizerRepository->findByUserAllpages(intval($GLOBALS['TSFE']->fe_user->user['uid']), FALSE, TRUE);
            if ( is_object($feuserOrganizer->getFirst())) {
                $output["feuser"]["organizer"]['uid'] = $feuserOrganizer->getFirst()->getUid() ;
            }

        }


        if(  $arguments['returnPid']  > 0 ) {
            $output['returnPid'] = $arguments['returnPid']  ;
        }

        $configuration = \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();
        $singlePid = ( array_key_exists( 'DetailPid' , $configuration) && $configuration['DetailPid'] > 0 ) ? intval($configuration['DetailPid']) : 111 ;
        $output['DetailPid'] = $singlePid  ;
        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($singlePid);
        } catch( \Exception $e) {
            // ignore
            $site = false ;
        }





        $needToStore = FALSE ;
        /* ************************************************************************************************************ */
        /*   Get infos about: EVENT
        /* ************************************************************************************************************ */

        if( $arguments['event']  > 0 ) {
            $output['event']['requestId'] =  $arguments['event']  ;

            /** @var \JVE\JvEvents\Domain\Model\Event $event */
            $event = $this->eventRepository->findByUidAllpages( $output['event']['requestId'] , FALSE  , TRUE );
            if( is_object($event )) {
                if ( substr($output['mode'], 0 , 4 )  != "only" ) {
                    $event->increaseViewed();
                    $this->eventRepository->update($event) ;
                    $needToStore = TRUE ;
                }


                $output['event']['eventId'] = $event->getUid() ;
                $output['event']['viewed'] = $event->getViewed();
                $output['event']['canceled'] = $event->getCanceled();

                $output['event']['startDate'] = $event->getStartDate()->format("d.m.Y") ;
                $output['event']['startTime'] = date( "H:i" , $event->getStartTime()) ;
                $output['event']['endTime'] = date( "H:i" , $event->getEndTime()) ;
                $output['event']['creationTime'] = date( "d.m.Y H:i" , $event->getCrdate() ) ;
                $output['event']['crdate'] =  $event->getCrdate()  ;
                $output['event']['noNotification'] = $event->getNotifyRegistrant() ;

                if ( $site ) {
                    try {
                    $output['event']['slug'] = (string)$site->getRouter()->generateUri( $singlePid ,['_language' => max( $event->getLanguageUid() ,0 ) ,
                        'tx_jvevents_events' => ['action' => 'show' , 'controller' => 'Event' ,'event' =>  $event->getUid() ]]);
                    } catch( \EXCEPTION $e ) {
                        $output['event']['slug'] = "pid=" . $singlePid . "&L=" . $event->getLanguageUid() ;
                    }
                }


                if( $event->getNotifyRegistrant() == 0  ) {
                    $reminder2 = new \DateInterval("P1D") ;
                    $reminderDate2 =  new \DateTime($event->getStartDate()->format("c")) ;

                    $reminder1 = new \DateInterval("P7D") ;
                    $reminderDate1 =  new \DateTime($event->getStartDate()->format("c")) ;
                    $now =  new \DateTime() ;
                    if ( $reminderDate1 > $now ) {
                        $output['event']['reminderDate1'] =  $reminderDate1->sub( $reminder1 )->format("d.m.Y") ;
                    }
                    if ( $reminderDate2 > $now ) {
                        $output['event']['reminderDate2'] =  $reminderDate2->sub( $reminder2 )->format("d.m.Y") ;
                    }
                }

                $output['event']['name'] = $event->getName() ;
                $output['event']['teasterText'] = $event->getTeaser();
                $output['event']['teaserImageUrl'] = GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST")  ;
                if (  $event->getTeaserImage() && is_object( $event->getTeaserImage()->getOriginalResource()) ) {
                    $output['event']['TeaserImageFrom'] =  "Event" ;
                    $output['event']['teaserImageUrl'] .=  $event->getTeaserImage()->getOriginalResource()->getPublicUrl() ;
                } else {
                    if( $this->settings['EmConfiguration']['imgUrl2'] ) {
                        $output['event']['TeaserImageFrom'] =  "config-imgUrl2" ;
                        $output['event']['teaserImageUrl'] .=  trim($this->settings['EmConfiguration']['imgUrl2']) ;
                    } else {
                        $output['event']['TeaserImageFrom'] =  "config-imgUrl" ;
                        $output['event']['teaserImageUrl'] = GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") . trim($this->settings['EmConfiguration']['imgUrl']) ;
                    }
                }
                $output['event']['filesAfterReg'] = $this->getFilesArray( $event->getFilesAfterReg() )  ;
                $output['event']['filesAfterEvent'] = $this->getFilesArray( $event->getFilesAfterEvent() )  ;



                $output['event']['price'] = round( $event->getPrice() , 2 ) ;
                $output['event']['currency'] = $event->getCurrency() ;
                $output['event']['priceReduced'] = $event->getPriceReduced();
                $output['event']['priceReducedText'] = $event->getPriceReducedText();

                $output['event']['registration']['possible'] = $event->isIsRegistrationPossible() ;
                $output['event']['registration']['formPid'] = $event->getRegistrationFormPid() ;
                $output['event']['registration']['noFreeSeats'] = $event->isIsNoFreeSeats() ;
                $output['event']['registration']['freeSeats'] = $event->getAvailableSeats() ;
                $output['event']['registration']['freeSeatsWaitinglist'] = $event->getAvailableWaitingSeats();
                $output['event']['registration']['registeredSeats'] = $event->getRegisteredSeats();
                $output['event']['registration']['unconfirmedSeats'] = $event->getUnconfirmedSeats();

                $output['event']['registration']['sfCampaignId'] = $event->getSalesForceCampaignId() ;
                $output['event']['notification']['waitinglist'] = $event->getIntrotextRegistrant() ;
                $output['event']['notification']['confirmed'] = $event->getIntrotextRegistrantConfirmed() ;

                if( is_object( $event->getOrganizer() )) {
                    $organizer = $event->getOrganizer() ;
                    $output['event']['organizerId'] = $organizer->getUid()  ;
                    $output['organizer']['organizerName'] = $organizer->getname()  ;
                    $output['organizer']['organizerEmail'] = $organizer->getEmail()  ;
                    $output['organizer']['organizerPhone'] = $organizer->getPhone()  ;
                    $output['organizer']['organizerSFID'] = $organizer->getSalesForceUserId() ;
                    $output['event']['registration']['registrationInfo'] = $organizer->getRegistrationInfo() ;
                    $output['event']['hasAccess'] = $this->hasUserAccess( $organizer ) ;
                }
                if( is_object( $event->getLocation() )) {

                    $location = $event->getLocation() ;
                    $output['event']['locationId'] = $event->getLocation()->getUid() ;
                }
                $output['event']['days'] = $event->getSubeventCount() ;
                if( $event->getSubeventCount() > 0 ) {
                    $querysettings =$this->subeventRepository->getTYPO3QuerySettings() ;
                    $querysettings->setStoragePageIds(array( $event->getPid() )) ;

                    $this->subeventRepository->setDefaultQuerySettings( $querysettings );

                    $subeventsArr = $this->subeventRepository->findByEventAllpages($event->getUid() , TRUE   ) ;
                    /** @var \JVE\JvEvents\Domain\Model\Subevent $subevent */
                    foreach ( $subeventsArr as $subevent) {
                        if( is_object( $subevent )) {
                            $temp = [] ;
                            $temp['date'] = $subevent->getStartDate()->format("d.m.Y") ;
                            $temp['starttime'] = date( "H:i" ,$subevent->getStartTime() ) ;
                            $temp['endtime'] = date( "H:i" ,$subevent->getEndTime() ) ;
                            $output['event']['moreDays'][] = $temp ;
                            unset($temp) ;
                        }

                    }

                } else {
                    $output['event']['moreDays'] = [] ;
                }
                if( $event->getMasterId() > 0 ) {
                    $querysettings =$this->subeventRepository->getTYPO3QuerySettings() ;
                    $querysettings->setStoragePageIds(array( $event->getPid() )) ;

                    $this->eventRepository->setDefaultQuerySettings( $querysettings );
                    $filter = array() ;
                    $filter['startDate'] = $event->getStartDate()->getTimestamp() ;
                    $filter['maxDays'] = 999 ;
                    $filter['skipEvent'] = $event->getUid() ;
                    $filter['masterId']  = $event->getMasterId() ;

                    $sameMaster = $this->eventRepository->findByFilter($filter ) ;
                    $output['event']['masterId'] =  $event->getMasterId() ;
                    $output['event']['sameMasterId'] =  $sameMaster->count()  ;

                } else {
                    $output['event']['masterId'] = false ;
                }
            }
        }

        if( $arguments['eventsFilter']  ) {

            $output['eventsFilter'] = $arguments['eventsFilter'] ;

            if ( $output['eventsFilter']['sameCity'] ) {
                $output['eventsFilter']['citys'] = $output['event']['locationId']  ;
                if( is_object( $location )) {
                    $dist = intval( $output['eventsFilter']['sameCity'] ) ;
                    if ( $dist == 1 ||  $dist > 500 || intval( $location->getLng() == 0 )) {
                        $filter = array( "city" =>  $location->getCity() )  ;
                    } else {
                        $filter = $this->locationRepository->getBoundingBox(  $location->getLat() , $location->getLng() , $dist ) ;
                    }

                    $locations = $this->locationRepository->findByFilterAllpages( $filter , true , true , false , '-10 YEAR') ;
                    if(is_array($locations)) {
                        /** @var Location $otherLocation */
                        foreach ($locations as $otherLocation ) {
                            $citys[] = $otherLocation->getUid() ;
                        }
                        $output['eventsFilter']['citys'] = implode("," , $citys) ;
                    }
                }
            }

            // $this->settings['debug'] = 2 ;

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events */
            $events = $this->eventRepository->findByFilter( $output['eventsFilter'], $arguments['limit'],  $this->settings );
            if( count( $events ) > 0 ) {
                $output['events'] = $events ;
                /** @var \JVE\JvEvents\Domain\Model\Event $tempEvent */
                $tempEvent =  $events->getFirst() ;
                if( is_object( $tempEvent )) {
                    if ( substr($output['mode'], 0, 4 )  != "only") {
                        $tempEvent->increaseViewed();

                        $this->eventRepository->update($tempEvent);
                        $needToStore = TRUE;
                        $output['events'] = $events ;
                    } else {

                        $tempEventsArray = $events->toArray() ;
                        foreach ( $tempEventsArray as $tempEvent ) {

                            $tempEventArray = [] ;
                            $tempEventArray['uid'] = $tempEvent->getUid();
                            $tempEventArray['name'] = $tempEvent->getName();
                            $tempEventArray['startDate'] = $tempEvent->getStartDate()->format("d.m.Y");
                            $tempEventArray['teaser'] = $tempEvent->getTeaser();

                            if (is_object($tempEvent->getLocation())) {
                                $tempEventArray['LocationCity'] = $tempEvent->getLocation()->getCity();
                            }
                            if ( $site ) {
                                try {
                                    $tempEventArray['slug'] = (string)$site->getRouter()->generateUri( $singlePid ,['_language' => max( $tempEvent->getLanguageUid() ,0 ) ,
                                        'tx_jvevents_events' => ['action' => 'show' , 'controller' => 'Event' ,'event' =>  $tempEvent->getUid() ]]);
                                } catch( \EXCEPTION $e ) {
                                    $tempEventArray['slug'] = "pid=" . $singlePid . "&L=" . $tempEvent->getLanguageUid() ;
                                }
                            }

                            $output['eventsByFilter'][] = $tempEventArray;
                            unset( $tempEventArray );
                        }

                    }


                }
            }

        }
        /* ************************************************************************************************************ */
        /*   Get infos about: Location
        /* ************************************************************************************************************ */
        if( $arguments['location'] > 0 && !is_object($location ) ) {
            $output['location']['requestId'] = $arguments['location'] ;

            /** @var \JVE\JvEvents\Domain\Model\Event $event */
            $location = $this->locationRepository->findByUidAllpages( $arguments['location'], FALSE, TRUE);

        }

        // Location is set either by Event OR by location uid from request
        if( is_object($location )) {
            $output['location']['locationId'] = $location->getUid() ;
            $output['location']['locationName'] = $location->getName();
            $output['location']['streetAndNr'] = $location->getStreetAndNr() ;
            $output['location']['zip'] = $location->getZip() ;
            $output['location']['city'] = $location->getCity() ;
            $output['location']['link'] = $location->getLink() ;
            $output['location']['description'] = $location->getDescription() ;
            $output['location']['country'] = $location->getCountry() ;
            $output['location']['lat'] = $location->getLat() ;
            $output['location']['lng'] = $location->getLng() ;

            if( is_object( $location->getOrganizer() )) {
                $organizer = $location->getOrganizer() ;
                $output['location']['organizerId'] = $organizer->getUid()  ;
                $output['location']['organizerEmail'] = $organizer->getEmail()  ;
                $output['location']['hasAccess'] = $this->hasUserAccess( $organizer ) ;

            }

        }
        /* ************************************************************************************************************ */
        /*   Get infos about: Organizer
        /* ************************************************************************************************************ */
        if(  $arguments['organizer'] > 0  && !is_object($organizer ) ) {
            $output['organizer']['requestId'] =  $arguments['organizer'];

            /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
            $organizer = $this->organizerRepository->findByUidAllpages(  $arguments['organizer'], FALSE, TRUE);
        }

        // Location is set either by Event OR by location uid from request
        if( is_object($organizer )) {
            $output['organizer']['organizerId'] = $organizer->getUid() ;
            $output['organizer']['hasAccess'] = $this->hasUserAccess( $organizer ) ;
        }
        if( $needToStore) {
            $this->persistenceManager->persistAll();
        }
        return  $output  ;

    }


    /**
     * action list
     *
     * @param array|null $arguments
     * @return void
     */
    public function eventListAction(array $arguments=Null)
    {
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        $this->initSettings();

        // 6.2.2020 with teaserText and files
        // 27.1.2021 LTS 10 : wegfall &eID=jv_events und uid, dafür Page ID der Seite mit der Liste : z.b. "id=110"
        // https://wwwv11.allplan.com.ddev.site/index.php?uid=82&eID=jv_events&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues
        // wird zu :
        // https://wwwv11.allplan.com.ddev.site/?id=110&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues


        // https://wwwv11.allplan.com.ddev.site/de/?uid=82&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues

        // https://wwwv11.allplan.com.ddev.site/index.php?uid=82&eID=jv_events&L=1&tx_jvevents_ajax[event]=94&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[rss]=1
        // https://wwwv11.allplan.com.ddev.site/index.php?uid=82&eID=jv_events&L=1&tx_jvevents_ajax[event]=94&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues

        // https://www-dev.allplan.com/index.php?uid=82&eID=jv_events&L=1&tx_jvevents_ajax[event]=2049&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][sameCity]=&tx_jvevents_ajax[eventsFilter][skipEvent]=2049&tx_jvevents_ajax[eventsFilter][startDate]=1&tx_jvevents_ajax[rss]=1
        // https://www-dev.allplan.com/index.php?uid=82&eID=jv_events&L=1&tx_jvevents_ajax[event]=2049&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][sameCity]=&tx_jvevents_ajax[eventsFilter][skipEvent]=2049&tx_jvevents_ajax[eventsFilter][startDate]=1&tx_jvevents_ajax[mode]=onlyValues

        // get all Access infos, Location infos , find similar events etc
        $output = $this->eventsListMenuSub($arguments) ;

        if ( $output['mode'] == "onlyValues") {
            unset( $output['events'] ) ;
            ShowAsJsonArrayUtility::show(  $output ) ;
        }
        if ( $output['mode'] == "onlyJson") {
            ShowAsJsonArrayUtility::show(  $output ) ;
        }


        /* ************************************************************************************************************ */
        /*   render the HTML Output :
        /* ************************************************************************************************************ */

        if( $this->standaloneView ) {
            /** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
            $renderer = $this->standaloneView  ;
            if( $arguments['rss'] ) {
                $renderer->setTemplate("EventListRss");
            } else {
                $renderer->setTemplate("EventList");
            }
        } else {
            /** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
            if( $arguments['rss'] ) {
                $renderer = $this->getEmailRenderer('', '/Ajax/EventListRss' );
            } else {
                $renderer = $this->getEmailRenderer( '', '/Ajax/EventList' );
            }
        }

        $layoutPath = GeneralUtility::getFileAbsFileName("typo3conf/ext/jv_events/Resources/Private/Layouts/");

        $renderer->setLayoutRootPaths(array(0 => $layoutPath));

        $renderer->assign('output' , $output) ;
        $renderer->assign('settings' , $this->settings ) ;
        $return = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" .  trim( $renderer->render() ) ;

        if( $arguments['rss'] ) {
            header_remove();
            header("content-type: application/rss+xml;charset=utf-8") ;

         //   header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        //    header('Cache-Control: no-cache, must-revalidate');
        //    header('Pragma: no-cache');
        //    header('Content-Length: ' . strlen($return));
          //  header('Content-Transfer-Encoding: 8bit');
            echo $return ;
            die;
        } else {
            header("Content-Type:application/json;charset=utf-8") ;
            ShowAsJsonArrayUtility::show( array( 'values' => $output , 'html' => $return ) ) ;
            die;
        }

    }
    /**
     * action Menu
     *
     * @return void
     */
    public function eventMenuAction(array $arguments=Null)
    {
        $pid =  GeneralUtility::_GP('id');
        $ts = TyposcriptUtility::loadTypoScriptFromScratch( $pid , "tx_jvevents_events") ;
        if( is_array($this->settings) && is_array($ts) && array_key_exists('settings' , $ts )) {
            $this->settings = array_merge( $this->settings , $ts['settings']);
        } elseif ( is_array($ts) && array_key_exists('settings' , $ts )) {
            $this->settings = $ts['settings'] ;
        }

        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        // https://www.allplan.com.ddev.local/index.php?uid=82&eID=jv_events&L=1&tx_jvevents_ajax[event]=94&tx_jvevents_ajax[action]=eventMenu&tx_jvevents_ajax[controller]=Ajax
        // https://www.allplan.com.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[event]=2934&tx_jvevents_ajax[action]=eventMenu&tx_jvevents_ajax[mode]=onlyValues
        $output = $this->eventsListMenuSub($arguments) ;
        if ( $output['mode'] == "onlyValues") {
            unset( $output['events'] ) ;
            ShowAsJsonArrayUtility::show(  $output ) ;
        }

        if( ExtensionManagementUtility::isLoaded("jv_banners")) {
            $output = $this->getBannerById( $output ) ;
        }

        /* ************************************************************************************************************ */
        /*   render the HTML Output :
        /* ************************************************************************************************************ */


        if( $this->standaloneView ) {
            $renderer = $this->standaloneView  ;
            $renderer->setTemplate("EventMenu") ;
        } else {
            $renderer = $this->getEmailRenderer('', '/Ajax/EventMenu' );
        }

        $layoutPath = GeneralUtility::getFileAbsFileName("typo3conf/ext/jv_events/Resources/Private/Layouts/");

        // Fallback to Partial Tango, as this actually is the only one using this.
        if( ! $this->settings['LayoutSingle'] ) {
            $this->settings['LayoutSingle'] = '5Tango' ;
        }
        $checkString = $_SERVER["SERVER_NAME"] . "-" . $output['event']['eventId'] . "-" . $output['event']['crdate'];
        $checkHash = GeneralUtility::hmac ( $checkString );
        $this->settings['hash'] =  $checkHash ;
        $this->settings['cookie'] =  $_COOKIE ;

        $renderer->setLayoutRootPaths(array(0 => $layoutPath));

        $renderer->assign('output' , $output) ;
        $this->settings['Ajax']['Action'] = "Main" ;
        $renderer->assign('settings' , $this->settings ) ;
        $returnMain = str_replace( array( "\n" , "\r" , "\t" , "    " , "   " , "  ") , array("" , "" , "" , " " , " " , " " ) , trim( $renderer->render() )) ;

        $this->settings['Ajax']['Action'] = "Single" ;
        $renderer->assign('settings' , $this->settings ) ;
        $returnSingle = str_replace( array( "\n" , "\r" , "\t" , "    " , "   " , "  ") , array("" , "" , "" , " " , " " , " " ) , trim( $renderer->render() )) ;


        $return = array( "main" => $returnMain , "single" => $returnSingle ) ;
        // debug : enalbe next line
        // $output['settings'] = $this->settings ;
        ShowAsJsonArrayUtility::show( array( 'values' => $output , 'html' => $return ) ) ;
        die;
    }

    public function cleanHistory(array $arguments=Null) {
        $return = '' ;
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        if(!$arguments) {
            ShowAsJsonArrayUtility::show( array( 'status' => false , 'html' => $return ) ) ;
            die;
        }


        $output = $this->locationListSub($arguments) ;
        $organizer = false;
        $output['organizer']['requestId'] = 0 ;
        /* ************************************************************************************************************ */
        /*   Get infos about: Organizer
        /* ************************************************************************************************************ */
        if(  isset($arguments['organizer']) && $arguments['organizer'] > 0  ) {
            $output['organizer']['requestId'] =  $arguments['organizer'];

            /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
            $organizer = $this->organizerRepository->findByUidAllpages(  $arguments['organizer'], FALSE, TRUE);
        }

        if(  isset($arguments['hash']) ) {
            $checkString = $_SERVER["SERVER_NAME"] . "-" . $organizer->getUid() . "-" . $organizer->getCrdate();
            if ( $arguments['hash'] !== GeneralUtility::hmac ( $checkString ) ) {
                ShowAsJsonArrayUtility::show( array( 'status' => false , 'html' => 'invalid hash for Org: ' . $organizer->getUid() ) ) ;
                die;
            }

        } else {
            ShowAsJsonArrayUtility::show( array( 'status' => false , 'html' => 'no hash' ) ) ;
            die;
        }

        // Location is set either by Event OR by location uid from request
        if( is_object($organizer )) {
            $output['organizer']['organizerId'] = $organizer->getUid() ;
            $output['organizer']['hasAccess'] = $this->hasUserAccess( $organizer ) ;
        } else {
            ShowAsJsonArrayUtility::show( array( 'status' => false , 'html' => 'Organizer not found by given ID: ' .  $output['organizer']['requestId'] ) ) ;
        }
        $output['keepDays']  = 400 ;
        if( isset($arguments['keepDays']) ) {
            $output['keepDays'] = intval( $arguments['keepDays'] ) ;
            if ( !in_array(  $output['keepDays'] , [ 0 , 1 , 7 , 30 , 100 , 400 ])) {
                $output['keepDays'] = 400 ;
            }
        }
        if ( $output['keepDays']  == 0 ) {
            // Todo : Add confirm hash to Url, send New URL to user and ask for confirmation.
            ShowAsJsonArrayUtility::show( array( 'status' => false , 'html' => 'To Delete ALL Events not implemented.'  )) ;
        }

        $timeInPast = time() - ( 24*3600 * $output['keepDays'] ) ;
        $output['keepUntilFormated'] = date( "d.m.Y" , $timeInPast );

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event') ;
        /** @var Connection $connection */
        $connection = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_event') ;

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryCount = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event');
        $output['countResult'] = $queryCount->count( '*' )->from('tx_jvevents_domain_model_event' )
            ->where( $queryBuilder->expr()->lte('start_date',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->lte('end_date', $timeInPast ))
            ->andWhere($queryBuilder->expr()->eq('deleted', 0 ))
            ->andWhere($queryBuilder->expr()->eq('organizer', $output['organizer']['requestId'] ))
            ->execute()->fetchColumn(0) ;

        if ( $output['countResult'] > 0 ) {
            $queryBuilder ->update('tx_jvevents_domain_model_event')
                ->where( $queryBuilder->expr()->lte('start_date',  $timeInPast ) )
                ->andWhere($queryBuilder->expr()->lte('end_date', $timeInPast ))
                ->andWhere($queryBuilder->expr()->eq('deleted', 0 ))
                ->andWhere($queryBuilder->expr()->eq('organizer', $output['organizer']['requestId'] ))
                ->set('deleted', 1 )
                ->set('tstamp', $queryBuilder->quoteIdentifier('tstamp') , false )
            ;
            try {
                $queryBuilder->execute() ;
            } catch (\Exception $e ) {
                ShowAsJsonArrayUtility::show( array( 'status' => false  , 'html' => $e->getMessage() ))  ;
            }
        }

        ShowAsJsonArrayUtility::show( array( 'status' => true , 'html' => $output ) ) ;
        die;
    }


    public function locationListAction(array $arguments=Null) {
        /* ************************************************************************************************************ */
        /*   render the HTML Output :
        /* ************************************************************************************************************ */
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }

        $output = $this->locationListSub($arguments) ;

        if( $output['feuser']['isOrganizer']) {
            $organizers = $this->organizerRepository->findByUserAllpages( $output['feuser']['uid'] , FALSE ) ;
            /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
            if( $organizers > 0 ) {
                foreach ( $organizers as  $organizer ) {
                    $orgArray[] = $organizer->getUid() ;
                }
                $output['organizer'] = $orgArray ;

                $locations = $this->locationRepository->findByOrganizersAllpages( $orgArray ) ;
                if($locations) {
                    $output['locations'] = $locations ;
                }
            }
        }
        if( $this->standaloneView ) {
            /** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
            $renderer = $this->standaloneView  ;
            $renderer->setTemplate("locationList") ;
        } else {
            /** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
            $renderer = $this->getEmailRenderer( '', '/Ajax/locationList' );
        }

        $layoutPath = GeneralUtility::getFileAbsFileName("typo3conf/ext/jv_events/Resources/Private/Layouts/");
        $renderer->setLayoutRootPaths(array(0 => $layoutPath));

        $renderer->assign('output' , $output) ;
        $renderer->assign('settings' , $this->settings ) ;

        $output["locations"] = count($locations) ;

        $return = str_replace( array( "\n" , "\r" , "\t" , "    " , "   " , "  ") , array("" , "" , "" , " " , " " , " " ) , trim( $renderer->render() )) ;
        ShowAsJsonArrayUtility::show( array( 'values' => $output , 'html' => $return ) ) ;
        die;
    }

    /**
     * action list
     *
     * @return array
     */
    public function locationListSub(array $arguments)
    {
        /* ************************************************************************************************************ */
        /*   Prepare the Output :
        /* ************************************************************************************************************ */
        $feuser = intval($GLOBALS['TSFE']->fe_user->user['uid']);

        $output = array(
            "requestId" => intval($GLOBALS['TSFE']->id),
            "mode" => $arguments['mode'] ,
            "feuser" => array(
                "uid" => $feuser ,
                "username" => $GLOBALS['TSFE']->fe_user->user['username'],
                "usergroup" => $GLOBALS['TSFE']->fe_user->user['usergroup'],
                "isOrganizer" => $this->isUserOrganizer() ,
                "organizerGroudIds" => $this->settings['feEdit']['organizerGroudIds']
            ),
            "organizer" => array(),
            "locations" => array(),

        );



        return $output ;
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

        // https://www.allplan.com.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[organizerUid]=111&tx_jvevents_ajax[action]=activate&tx_jvevents_ajax[userUid]=1&tx_jvevents_ajax[hmac]=hmac1234&&tx_jvevents_ajax[rnd]=11234

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
                foreach (  $this->response->getHeaders() as $header ) {
                    header( $header) ;
                }

                die;
            }
        }
        if( !$user ) {
            $this->addFlashMessage("User not found by ID : " . $userUid , "" , \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING) ;
            try {
                $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
            } catch(StopActionException $e) {
                foreach (  $this->response->getHeaders() as $header ) {
                    header( $header) ;
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
        $groupsMissing = array( 2 => TRUE , 7 => TRUE ) ;

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
        try {
            $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
        } catch(StopActionException $e) {
            foreach (  $this->response->getHeaders() as $header ) {
                header( $header) ;
            }

            die;
        }

    }
    /**
     * @param array|null $arguments
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function eventChangeLocIdAction( array $arguments=null ) {
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        // https:/tango.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[event]=2264&tx_jvevents_ajax[action]=eventUnlink&
        $output['success'] = false ;
        $output['msg'] = 'Starting Link to other Location ';
        if( $arguments['event'] > 0 ) {
            $output['event']['requestId'] =  $arguments['event'] ;
            $output['msg'] = 'got Event';
            if ( intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) > 0 ) {
                $output['event']['user'] =  intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) ;
                $output['msg'] = 'got Event and user is logged in';
                /** @var \JVE\JvEvents\Domain\Model\Event $event */
                $event = $this->eventRepository->findByUidAllpages( $output['event']['requestId'] , FALSE  , TRUE );
                if( is_object($event )) {
                    $output['msg'] = 'Found Event and user is logged in';
                    //    $output['event']['Id'] = $event->getUid() ;
                    $organizer = $event->getOrganizer() ;
                    if( is_object( $organizer )) {
                        $output['msg'] = 'got Event and has organizer';
                        //     $output['event']['organizer'] = $organizer->getUid();
                        if ($this->hasUserAccess($organizer)) {
                            $output['msg'] = 'user has access';
                            $output['event']['hasAccess'] = true ;
                            /** @var Location $location */
                            if( $location = $this->locationRepository->findByUidAllpages( intval($arguments['location']) , false ) ) {
                                $output['msg'] = 'got Location from URI';
                                if( $this->hasUserAccess ( $location->getOrganizer()) ) {
                                    $event->setLocation( $location ) ;
                                    $this->eventRepository->update($event) ;
                                    $this->persistenceManager->persistAll() ;
                                    $output['success'] = true ;
                                    $output['msg'] = '';
                                    // does not work - Uri is not generated !
                                    // $this->redirect("show" , "Event" , "JvEvents", ['event' => $event->getUid() ] , intval($arguments['returnToPid'] )) ;

                                    $target = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . "/index.php?id=". intval($arguments['returnToPid'] ) ;
                                    $target .= "&tx_jvevents_events%5Bevent%5D=" . $event->getUid() ;
                                    $target .= "&tx_jvevents_events%5Baction%5D=edit"  ;
                                    $target .= "&tx_jvevents_events%5Bcontroller%5D=Event"  ;
                                    header("Location: " .  $target   );
                                    exit;

                                }
                            }
                        }
                    }

                }
            }

        }
        ShowAsJsonArrayUtility::show(  $output ) ;
        exit ;
    }


    /**
     * @param array|null $arguments
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function eventUnlinkAction( array $arguments=null ) {
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        // https:/tango.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[event]=2264&tx_jvevents_ajax[action]=eventUnlink&
        $output['success'] = false ;
        $output['msg'] = 'Starting unLink';
        if( $arguments['event'] > 0 ) {
            $output['event']['requestId'] =  $arguments['event'] ;
            $output['msg'] = 'got Event';
            if ( intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) > 0 ) {
                $output['event']['user'] =  intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) ;
                $output['msg'] = 'got Event and user is logged in';
                /** @var \JVE\JvEvents\Domain\Model\Event $event */
                $event = $this->eventRepository->findByUidAllpages( $output['event']['requestId'] , FALSE  , TRUE );
                if( is_object($event )) {
                    $output['msg'] = 'Found Event and user is logged in';
                //    $output['event']['Id'] = $event->getUid() ;
                    $organizer = $event->getOrganizer() ;
                    if( is_object( $organizer )) {
                        $output['msg'] = 'got Event and has organizer';
                   //     $output['event']['organizer'] = $organizer->getUid();
                        if ($this->hasUserAccess($organizer)) {
                            $output['msg'] = 'user has access';
                            $output['event']['hasAccess'] = true ;
                            $event->setMasterId(0) ;
                            $this->eventRepository->update($event) ;
                            $this->persistenceManager->persistAll() ;
                            $output['success'] = true ;
                            $output['msg'] = '';
                        }
                    }

                }
            }

        }
        ShowAsJsonArrayUtility::show(  $output ) ;
        exit ;
    }



    public function downloadIcal($arguments) {

        $uid = intval( $arguments["uid"] ) ;
        if( $uid ) {
            /** @var Event $event */
           $event = $this->eventRepository->findByUidAllpages($uid ,  false) ;
            if ( $event && $event->getUid() == $uid ) {
                $output['status'] = 200 ;
                $output['content-type'] = "text/calendar" ;
                $output['filename'] = "event-import-" . date("dmy-His" ) . ".ics";


                $output['data'] = "BEGIN:VCALENDAR" . PHP_EOL
                    ."VERSION:2.0" . PHP_EOL
                    ."CALSCALE:GREGORIAN" . PHP_EOL
                    ."BEGIN:VEVENT" . PHP_EOL
                    ."SUMMARY:" .  $this->escapeIcal( $event->getName()) . PHP_EOL
                    ."DTSTART;TZID=Europe/Berlin:" . $event->getStartUTCDateTime() . PHP_EOL
                    ."DTEND;TZID=Europe/Berlin:" . $event->getEndUTCDateTime(). PHP_EOL
                    . ($event->getLocation() ? "LOCATION:" . $this->escapeIcal( $event->getLocation()->getStreetAndNr() . ", " . $event->getLocation()->getZip() . " " . $event->getLocation()->getCity() . ", " . $event->getLocation()->getCountry())  . PHP_EOL : "LOCATION: none" . PHP_EOL )
                    ."DESCRIPTION:" .  $this->escapeIcal(   $event->getTeaser())  . PHP_EOL
                    ."STATUS:CONFIRMED" . PHP_EOL
                    . ( $event->getOrganizer() ? "ORGANIZER:mailto:" . $event->getOrganizer()->getEmail() .  PHP_EOL : '' )
                    ."SEQUENCE:3" . PHP_EOL
                    ."BEGIN:VALARM" . PHP_EOL
                    ."TRIGGER:-PT120M" . PHP_EOL
                    ."ACTION:DISPLAY" . PHP_EOL
                    ."END:VALARM" . PHP_EOL
                    ."END:VEVENT" . PHP_EOL
                    ."END:VCALENDAR" . PHP_EOL ;
                return $output ;
            }
        }


       return false;
    }
    private function escapeIcal( $text ) {
        return htmlspecialchars(str_replace( ["," , ";" , "\r\n" , "\n"] , ["\," , "\;" , "\\n", "\\n"] , $text )) ;
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
		$formClass =  $this->objectManager->get( \TYPO3\CMS\Core\FormProtection\FrontendFormProtection::class) ;

		return $formClass->generateToken(
			'event', $action ,   "P" . $this->settings['pageId'] . "-L" .$this->settings['sys_language_uid']
		);

	}

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $resource
     * @return array
     *
     */
	public function getFilesArray( \TYPO3\CMS\Extbase\Persistence\ObjectStorage $resource ) {
        /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $tempFile */
        $return = array() ;
        if(is_object($resource) && $resource->count() > 0 ) {
            foreach ($resource as $tempFile) {

                try {
                    $single = array() ;
                    if( is_object($tempFile) && $tempFile->getOriginalResource() ) {
                        $single['url'] =  GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") . $tempFile->getOriginalResource()->getPublicUrl() ;
                        $single['filename'] =  $tempFile->getOriginalResource()->getName() ;
                        $single['title'] =  $tempFile->getOriginalResource()->getTitle() ;
                        if(!$single['title'] ) {
                            $single['title']  =  str_replace( "_" , " " , $tempFile->getOriginalResource()->getNameWithoutExtension() ) ;
                        }
                        $single['ext'] =  $tempFile->getOriginalResource()->getExtension() ;
                        $single['mimeType'] =  $tempFile->getOriginalResource()->getMimeType() ;
                        $single['width'] =  $tempFile->getOriginalResource()->getProperties()['width'];
                        $single['height'] =  $tempFile->getOriginalResource()->getProperties()['height'];
                        $single['size'] =  $tempFile->getOriginalResource()->getSize() ;
                    }
                    $return[] = $single ;
                } catch(\Exception $e) {

                }

            }
        }
        return $return ;
    }

    /**
     * @return Array
     */
    public function getBannerById( $output )
    {
        if( $output['event'] && $output['event']["eventId"] > 0 ) {
            // tx_sfbanners_domain_model_banner
            try {
                /** @var \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool */
                $connectionPool = GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class);

                $connection = $connectionPool->getConnectionForTable('tx_sfbanners_domain_model_banner') ;

                /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
                $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sfbanners_domain_model_banner') ;
                $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

                $row = $queryBuilder ->select('uid' , 'starttime', 'endtime', 'impressions','clicks'   ) ->from('tx_sfbanners_domain_model_banner')
                    ->where( $queryBuilder->expr()->eq('link', $queryBuilder->createNamedParameter($output['event']["eventId"] , \PDO::PARAM_INT)) )
                    ->andWhere( $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0 , \PDO::PARAM_INT)) )
                    ->andWhere( $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0 , \PDO::PARAM_INT)) )
                    ->orderBy("endtime" , "DESC")
                    ->setMaxResults(1)
                    ->execute()
                    ->fetchAssociative();
                if ( $row) {
                    $output['event']['banner'] = $row ;
                    if( $row['endtime'] > time() ) {
                        $output['event']['banner']['active'] = true ;
                    }
                }
            } catch (Exception $e) {
                // ignore
            }
        }
        return $output ;
    }

    /**
     * @return void
     */
    public function initSettings(): void
    {
        $pid = GeneralUtility::_GP('id');
        if ( !$pid ) {
            $pid = $GLOBALS['TSFE']->id;
        }
        $ts = TyposcriptUtility::loadTypoScriptFromScratch($pid, "tx_jvevents_events");
        if (is_array($this->settings) && is_array($ts) && is_array($ts['settings'])) {
            $this->settings = array_merge($ts['settings']);
        } elseif (is_array($ts) && is_array($ts['settings']) ) {
            $this->settings = $ts['settings'];
        }
    }

}