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

use JVelletti\JvEvents\Utility\MigrationUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use JVelletti\JvEvents\Domain\Repository\TagRepository;
use JVelletti\JvEvents\Domain\Repository\CategoryRepository;
use JVelletti\JvEvents\Domain\Repository\RegistrantRepository;
use JVelletti\JvEvents\Domain\Repository\LocationRepository;
use JVelletti\JvEvents\Domain\Repository\OrganizerRepository;
use JVelletti\JvEvents\Domain\Repository\EventRepository;
use JVelletti\JvEvents\Domain\Repository\SubeventRepository;
use JVelletti\JvEvents\Domain\Repository\StaticCountryRepository;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use JVelletti\JvEvents\Utility\EmConfigurationUtility;
use JVelletti\JvEvents\Domain\Model\Subevent;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use JVelletti\JvEvents\Domain\Model\Organizer;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use JVelletti\JvEvents\Domain\Repository\FrontendUserRepository;
use JVelletti\JvEvents\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Core\FormProtection\FrontendFormProtection;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use EBT\ExtensionBuilder\Exception;
use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Location;
use JVelletti\JvEvents\Utility\AjaxUtility;
use JVelletti\JvEvents\Utility\TyposcriptUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use JVelletti\JvEvents\Utility\ShowAsJsonArrayUtility;
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
     * @var TypoScriptFrontendController
     */
    public $tsFEController ;

    /** @var array  */
    public $arguments = '' ;

    /** @var array */
    public $frontendUser ;


    /**
     * @var StandaloneView
     */
    public $standaloneView;

    public function initializeAction() {

        parent::initializeAction() ;
    }

    /**
     * @internal only to be used within Extbase, not part of TYPO3 Core API.
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    public function initializeRepositorys()
    {

        $this->tagRepository        = GeneralUtility::makeInstance(TagRepository::class);
        $this->categoryRepository        = GeneralUtility::makeInstance(CategoryRepository::class);
        $this->registrantRepository        = GeneralUtility::makeInstance(RegistrantRepository::class);
        $this->locationRepository        = GeneralUtility::makeInstance(LocationRepository::class);
        $this->organizerRepository        = GeneralUtility::makeInstance(OrganizerRepository::class);
        $this->eventRepository        = GeneralUtility::makeInstance(EventRepository::class);
        $this->subeventRepository        = GeneralUtility::makeInstance(SubeventRepository::class);
        $this->staticCountryRepository        = GeneralUtility::makeInstance(StaticCountryRepository::class);
    }

    public function dispatcher()
    {
        /**
         * Gets the Ajax Call Parameters   | OLD school without middleWare ..
         * Maybe obsolete and may not work in V12 anymore . ?? 
         */
        $_gp = GeneralUtility::_GPmerged('tx_jvevents_ajax');


        $ajax = [];
        $ajax['arguments'] = $_gp;
        $ajax['vendor'] = 'JVE';
        $ajax['vendorName'] = 'JVE';
        $ajax['extensionName'] = 'JvEvents';
        $ajax['pluginName'] = 'Events';
        $ajax['controller'] = 'Ajax';
        $ajax['action'] = $_gp['action'];

        /*        * check if action is allowed    */
        if (!in_array($ajax['action'], ["eventMenu", "eventList", "locationList", "activate", "eventUnlink"])) {
            $ajax['action'] = "eventMenu";
        }

        /** @var Request $request */
        $request = GeneralUtility::makeInstance(Request::class, \JVelletti\JvEvents\Controller\AjaxController::class );

        /** @var Typo3Version $version */
        $version = GeneralUtility::makeInstance(Typo3Version::class);


        $request->setControllerExtensionName($ajax['extensionName']);
        $request->setPluginName($ajax['pluginName']);
        $request->setControllerName($ajax['controller']);
        $request->setControllerActionName($ajax['action']);
        $request->setArguments($ajax['arguments']);

        $response = GeneralUtility::makeInstance(ResponseInterface::class);
        $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);

        if( key_exists('event' , $_gp)) {
            $request->setArgument('event',  intval( $_gp['event'] )) ;
        }

        $dispatcher->dispatch($request);
        die;

    }




    /**
     * action list
     *
     * @return array
     */
    public function eventsListMenuSub(array $arguments )
    {

       $location = null;
        $citys = [];
        $organizer = null;
        //  $this->frontendUser->user->fe_user->user = [ 'uid' => 476 , "username" => "jvel@test.de" , "1,2,3,4,5,6,7"] ;
        /* ************************************************************************************************************ */
        /*   Prepare the Output :
        /* ************************************************************************************************************ */
        $feuser = intval(  $this->frontendUser->user['uid']) ;

        $output = [
            "requestId" =>   $detailPid = MigrationUtility::getPageId(),
            "event" => [],
            "events" => [],
            "eventsFilter" => [],
            "eventsByFilter" => [],
            "mode" => $arguments['mode'],
            "feuser" =>
                [ "uid" => $this->frontendUser->user['uid'],
                    "username" => $this->frontendUser->user['username'],
                    "usergroup" => $this->frontendUser->user['usergroup'],
                    "isOrganizer" => $this->isUserOrganizer()
                ],
            "organizer" => [],
            "location" => []
        ] ;
        if ( $output["feuser"]["isOrganizer"]) {
            $feuserOrganizer = $this->organizerRepository->findByUserAllpages(intval($this->frontendUser->user['uid']), FALSE, TRUE);
            if ( is_object($feuserOrganizer->getFirst())) {
                $output["feuser"]["organizer"]['uid'] = $feuserOrganizer->getFirst()->getUid() ;
            }

        }


        if(  $arguments['returnPid']  > 0 ) {
            $output['returnPid'] = $arguments['returnPid']  ;
        }

        $configuration = EmConfigurationUtility::getEmConf();
        $singlePid = ( array_key_exists( 'DetailPid' , $configuration) && $configuration['DetailPid'] > 0 ) ? intval($configuration['DetailPid']) : 111 ;
        $output['DetailPid'] = $singlePid  ;
        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($singlePid);
        } catch( \Exception) {
            // ignore
            $site = false ;
        }





        $needToStore = FALSE ;
        /* ************************************************************************************************************ */
        /*   Get infos about: EVENT
        /* ************************************************************************************************************ */

        if( $arguments['event']  > 0 ) {
            $output['event']['requestId'] =  $arguments['event']  ;

            /** @var Event $event */
            $event = $this->eventRepository->findByUidAllpages( $output['event']['requestId'] , FALSE  , TRUE );
            if( is_object($event )) {
                if ( !str_starts_with((string) $output['mode'], "only") ) {
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
                        'tx_jvevents_event' => ['action' => 'show' , 'controller' => 'Event' ,'event' =>  $event->getUid() ]]);
                    } catch( \EXCEPTION ) {
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
                        $output['event']['teaserImageUrl'] .=  trim((string) $this->settings['EmConfiguration']['imgUrl2']) ;
                    } else {
                        $output['event']['TeaserImageFrom'] =  "config-imgUrl" ;
                        $output['event']['teaserImageUrl'] = GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") . trim((string) $this->settings['EmConfiguration']['imgUrl']) ;
                    }
                }
                $output['event']['filesAfterReg'] = $this->getFilesArray( $event->getFilesAfterReg() )  ;
                $output['event']['filesAfterEvent'] = $this->getFilesArray( $event->getFilesAfterEvent() )  ;

                $output['event']['categoryId'] = false ;
                if( is_object( $event->getEventCategory() ) ) {
                    $output['event']['categoryId'] = "object" ;
                    if ( is_array($event->getEventCategory()->toArray() ) ) {
                        $output['event']['categoryId'] = "array" ;
                        if ( count($event->getEventCategory()->toArray() ) > 0 ) {

                            $output['event']['categoryId'] = $event->getEventCategory()->toArray()[0]->getUid() ;
                        }
                    }
                }

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
                    $querysettings->setStoragePageIds([$event->getPid()]) ;

                    $this->subeventRepository->setDefaultQuerySettings( $querysettings );

                    $subeventsArr = $this->subeventRepository->findByEventAllpages($event->getUid() , TRUE   ) ;
                    /** @var Subevent $subevent */
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
                    $querysettings = $this->subeventRepository->getTYPO3QuerySettings();
                    $querysettings->setStoragePageIds([$event->getPid()]);

                    $this->eventRepository->setDefaultQuerySettings($querysettings);
                    $filter = [];
                    $filter['startDate'] = $event->getStartDate()->getTimestamp();
                    $filter['maxDays'] = 999;
                    $filter['skipEvent'] = $event->getUid();
                    $filter['masterId'] = $event->getMasterId();


                    $output['event']['masterId'] = $event->getMasterId();
                    $sameMaster = $this->eventRepository->findByFilter($filter);

                    if ($sameMaster) {
                        $output['event']['sameMasterId'] = $sameMaster->count();
                        foreach ($sameMaster as $sameMasterEvent) {
                            if (is_object($sameMasterEvent)) {
                                $temp = [];
                                $temp['uid'] = $sameMasterEvent->getUid();
                                $temp['name'] = $sameMasterEvent->getName();
                                $temp['startDate'] = $sameMasterEvent->getStartDate()->format("d.m.Y");
                                $output['event']['sameMaster'][] = $temp;
                                unset($temp);
                            }
                        }
                    }

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
                        $filter = ["city" =>  $location->getCity()]  ;
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
            /** @var QueryResultInterface $events */
            $events = $this->eventRepository->findByFilter( $output['eventsFilter'], $arguments['limit'],  $this->settings );
            if( count( $events ) > 0 ) {
                $output['events'] = $events ;
                /** @var Event $tempEvent */
                $tempEvent =  $events->getFirst() ;
                if( is_object( $tempEvent )) {
                    if ( !str_starts_with((string) $output['mode'], "only")) {
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
                            $tempEventArray['price'] = $tempEvent->getPrice();
                            $tempEventArray['startDate'] = $tempEvent->getStartDate()->format("d.m.Y");
                            $tempEventArray['created'] = date("d.m.Y" , $tempEvent->getCrdate() );
                            $tempEventArray['lastUpdated'] = date("d.m.Y" , $tempEvent->getLastUpdated());
                            $tempEventArray['teaser'] = $tempEvent->getTeaser();

                            if (  $tempEvent->getTeaserImage() && is_object( $tempEvent->getTeaserImage()->getOriginalResource()) ) {
                                $tempEventArray['TeaserImageFrom'] =  "Event" ;
                                $tempEventArray['teaserImage'] =  trim( (string) GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") , "/" ) . "/" .
                                    $tempEvent->getTeaserImage()->getOriginalResource()->getPublicUrl() ;
                            } elseif( is_object($tempEvent->getLocation()) && $tempEvent->getLocation()->getTeaserImage()
                                && is_object($tempEvent->getLocation()->getTeaserImage()->getOriginalResource() ) )  {
                                $tempEventArray['TeaserImageFrom'] =  "Location" ;
                                $tempEventArray['teaserImage'] =  trim( (string) GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") , "/" ) . "/" .
                                    $tempEvent->getLocation()->getTeaserImage()->getOriginalResource()->getPublicUrl() ;
                            } else {
                                $tempEventArray['TeaserImageFrom'] =  "NotFound" ;
                            }

                            if (is_object($tempEvent->getLocation())) {
                                $tempEventArray['LocationCity'] = $tempEvent->getLocation()->getCity();
                                $tempEventArray['Location']['uid'] = $tempEvent->getLocation()->getUid();
                                $tempEventArray['Location']['city'] = $tempEvent->getLocation()->getCity();
                                $tempEventArray['Location']['streetAndNr'] = $tempEvent->getLocation()->getStreetAndNr();
                                $tempEventArray['Location']['additionalInfo'] = $tempEvent->getLocation()->getAdditionalInfo();

                            }
                            if ( $site ) {
                                try {
                                    $tempEventArray['slug'] = (string)$site->getRouter()->generateUri( $singlePid ,['_language' => max( $tempEvent->getLanguageUid() ,0 ) ,
                                        'tx_jvevents_event' => ['action' => 'show' , 'controller' => 'Event' ,'event' =>  $tempEvent->getUid() ]]);
                                } catch( \EXCEPTION ) {
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

            /** @var Event $event */
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

            /** @var Organizer $organizer */
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
    public function eventListAction(array $arguments=Null): \Psr\Http\Message\ResponseInterface
    {
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser */
        $this->frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');
        $this->initSettings($this->request);


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
            /** @var StandaloneView $renderer */
            $renderer = $this->standaloneView  ;
            if( $arguments['rss'] ) {
                $renderer->setTemplate("EventListRss");
            } else {
                $renderer->setTemplate("EventList");
            }
        } else {
            /** @var StandaloneView $renderer */
            if( $arguments['rss'] ) {
                $renderer = $this->getEmailRenderer('', '/Ajax/EventListRss' );
            } else {
                $renderer = $this->getEmailRenderer( '', '/Ajax/EventList' );
            }
        }

        $layoutPath = GeneralUtility::getFileAbsFileName("EXT:jv_events/Resources/Private/Layouts/");

        $renderer->setLayoutRootPaths([0 => $layoutPath]);

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
            ShowAsJsonArrayUtility::show( ['values' => $output, 'html' => $return] ) ;
            die;
        }
        return $this->htmlResponse();

    }
    /**
     * action Menu
     *
     * @return void
     */
    public function eventMenuAction(array $arguments=Null)
    {
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser */
        $this->frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');

        $pid =  GeneralUtility::_GP('id');
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        // https://www.allplan.com.ddev.local/index.php?uid=82&eID=jv_events&L=1&tx_jvevents_ajax[event]=94&tx_jvevents_ajax[action]=eventMenu&tx_jvevents_ajax[controller]=Ajax
        // https://www.allplan.com.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[event]=2934&tx_jvevents_ajax[action]=eventMenu&tx_jvevents_ajax[mode]=onlyValues
        $output = $this->eventsListMenuSub($arguments) ;
        if( ExtensionManagementUtility::isLoaded("jv_banners")) {
            $output = $this->getBannerById( $output ) ;
        }
        if ( $output['mode'] == "onlyValues") {
            unset( $output['events'] ) ;
            ShowAsJsonArrayUtility::show(  $output ) ;
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

        $layoutPath = GeneralUtility::getFileAbsFileName("EXT:jv_events/Resources/Private/Layouts/");

        // Fallback to Partial Tango, as this actually is the only one using this.
        if( ! $this->settings['LayoutSingle'] ) {
            $this->settings['LayoutSingle'] = '5Tango' ;
        }
        $checkString = $_SERVER["SERVER_NAME"] . "-" . $output['event']['eventId'] . "-" . $output['event']['crdate'];
        $checkHash = GeneralUtility::hmac ( $checkString );
        $this->settings['hash'] =  $checkHash ;
        $this->settings['cookie'] =  $_COOKIE ;

        $renderer->setLayoutRootPaths([0 => $layoutPath]);
        $renderer->assign('output' , $output) ;
        $this->settings['Ajax']['Action'] = "Main" ;
        $this->settings['EmConfiguration'] = EmConfigurationUtility::getEmConf();
        $clearCachePids = GeneralUtility::trimExplode("," , $this->settings['EmConfiguration']['clearCachePids']) ;
        $this->settings['Ajax']['noClearCachePid'] = true ;
        if( is_array($clearCachePids) || count($clearCachePids) > 0 ) {
            if ( in_array(  MigrationUtility::getPageId() , $clearCachePids) ) {
                if ( !isset( $output['event']['eventId'] ) || $output['event']['eventId'] < 1 ) {
                    $this->settings['Ajax']['noClearCachePid'] = false ;
                }
            }
        }


        $renderer->assign('settings' , $this->settings ) ;

        $returnMain = str_replace( ["\n", "\r", "\t", "    ", "   ", "  "] , ["", "", "", " ", " ", " "] , trim( $renderer->render() )) ;

        $this->settings['Ajax']['Action'] = "Single" ;
        $renderer->assign('settings' , $this->settings ) ;
        $returnSingle = str_replace( ["\n", "\r", "\t", "    ", "   ", "  "] , ["", "", "", " ", " ", " "] , trim( $renderer->render() )) ;


        $return = ["main" => $returnMain, "single" => $returnSingle] ;
        // debug : enalbe next line
        // $output['settings'] = $this->settings ;
        ShowAsJsonArrayUtility::show( ['values' => $output, 'html' => $return] ) ;
        die;
    }

    public function cleanHistory(array $arguments=Null) {
        $return = '' ;
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        if(!$arguments) {
            ShowAsJsonArrayUtility::show( ['status' => false, 'html' => $return] ) ;
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

            /** @var Organizer $organizer */
            $organizer = $this->organizerRepository->findByUidAllpages(  $arguments['organizer'], FALSE, TRUE);
        }

        if(  isset($arguments['hash']) ) {
            $checkString = $_SERVER["SERVER_NAME"] . "-" . $organizer->getUid() . "-" . $organizer->getCrdate();
            if ( $arguments['hash'] !== GeneralUtility::hmac ( $checkString ) ) {
                ShowAsJsonArrayUtility::show( ['status' => false, 'html' => 'invalid hash for Org: ' . $organizer->getUid()] ) ;
                die;
            }

        } else {
            ShowAsJsonArrayUtility::show( ['status' => false, 'html' => 'no hash'] ) ;
            die;
        }

        // Location is set either by Event OR by location uid from request
        if( is_object($organizer )) {
            $output['organizer']['organizerId'] = $organizer->getUid() ;
            $output['organizer']['hasAccess'] = $this->hasUserAccess( $organizer ) ;
        } else {
            ShowAsJsonArrayUtility::show( ['status' => false, 'html' => 'Organizer not found by given ID: ' .  $output['organizer']['requestId']] ) ;
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
            ShowAsJsonArrayUtility::show( ['status' => false, 'html' => 'To Delete ALL Events not implemented.']) ;
        }

        $timeInPast = time() - ( 24*3600 * $output['keepDays'] ) ;
        $output['keepUntilFormated'] = date( "d.m.Y" , $timeInPast );

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event') ;
        /** @var Connection $connection */
        $connection = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_event') ;

        /** @var QueryBuilder $queryBuilder */
        $queryCount = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event');
        $output['countResult'] = $queryCount->count( '*' )->from('tx_jvevents_domain_model_event' )
            ->where( $queryBuilder->expr()->lte('start_date',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->lte('end_date', $timeInPast ))
            ->andWhere($queryBuilder->expr()->eq('deleted', 0 ))->andWhere($queryBuilder->expr()->eq('organizer', $output['organizer']['requestId'] ))
            ->executeQuery()
            ->fetchOne() ;

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
                $queryBuilder->executeStatement() ;
            } catch (\Exception $e ) {
                ShowAsJsonArrayUtility::show( ['status' => false, 'html' => $e->getMessage()])  ;
            }
        }

        ShowAsJsonArrayUtility::show( ['status' => true, 'html' => $output] ) ;
        die;
    }


    public function locationListAction(array $arguments=Null) {
        $orgArray = [];
        $locations = null;
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser */
        $this->frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');
        /* ************************************************************************************************************ */
        /*   render the HTML Output :
        /* ************************************************************************************************************ */
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }

        $output = $this->locationListSub($arguments) ;

        if( $output['feuser']['isOrganizer']) {
            $organizers = $this->organizerRepository->findByUserAllpages( $output['feuser']['uid'] , FALSE ) ;
            /** @var Organizer $organizer */
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
            /** @var StandaloneView $renderer */
            $renderer = $this->standaloneView  ;
            $renderer->setTemplate("locationList") ;
        } else {
            /** @var StandaloneView $renderer */
            $renderer = $this->getEmailRenderer( '', '/Ajax/locationList' );
        }

        $layoutPath = GeneralUtility::getFileAbsFileName("EXT:jv_events/Resources/Private/Layouts/");
        $renderer->setLayoutRootPaths([0 => $layoutPath]);

        $renderer->assign('output' , $output) ;
        $renderer->assign('settings' , $this->settings ) ;

        $output["locations"] = $locations === null ? 0 : count($locations) ;

        $return = str_replace( ["\n", "\r", "\t", "    ", "   ", "  "] , ["", "", "", " ", " ", " "] , trim( $renderer->render() )) ;
        ShowAsJsonArrayUtility::show( ['values' => $output, 'html' => $return] ) ;
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
        $feuser = intval($this->frontendUser->user['uid']);

        $output = [
            "requestId" => $detailPid = MigrationUtility::getPageId() ,
            "mode" => $arguments['mode'],
            "feuser" => [
                "uid" => $feuser,
                "username" => $this->frontendUser->user['username'],
                "usergroup" => $this->frontendUser->user['usergroup'],
                "isOrganizer" => $this->isUserOrganizer(),
                "organizerGroudIds" => $this->settings['feEdit']['organizerGroudIds']
            ],
            "organizer" => [],
            "locations" => []
          ];



        return $output ;
    }

    /**
     * @param int $organizerUid
     * @param int $userUid
     * @param string $hmac
     * @param int $rnd
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function activateAction($organizerUid=0 , $userUid=0 , $hmac='invalid' , $rnd = 0 ) {

        // https://www.allplan.com.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[organizerUid]=111&tx_jvevents_ajax[action]=activate&tx_jvevents_ajax[userUid]=1&tx_jvevents_ajax[hmac]=hmac1234&&tx_jvevents_ajax[rnd]=11234
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser */
        $this->frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');

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
        /** @var Organizer $organizer */
        $organizer = $this->organizerRepository->findByUidAllpages($organizerUid, FALSE, TRUE);



        /** @var FrontendUserRepository $userRepository */
        $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class) ;
        /** @var FrontendUser $user */
        $user = $userRepository->findByUid($userUid) ;


        if( !$organizer  ) {
            $this->addFlashMessage("Organizer not found by ID : " . $organizerUid , "" , AbstractMessage::WARNING) ;
            try {
                $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
            } catch(StopActionException) {
                foreach (  $this->response->getHeaders() as $header ) {
                    header( $header) ;
                }

                die;
            }
        }
        if( !$user ) {
            $this->addFlashMessage("User not found by ID : " . $userUid , "" , AbstractMessage::WARNING) ;
            try {
                $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
            } catch(StopActionException) {
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
            $this->addFlashMessage("Hmac does not fit to: " . $tokenStr , "ERROR" , AbstractMessage::ERROR) ;
            try {
                $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
            } catch(StopActionException) {
                foreach (  $this->response->getHeaders() as $header ) {
                    header( $header) ;
                }

                die;
            }

        }
        $groups =  $user->getUsergroup()->toArray() ;
        $groupsMissing = [2 => TRUE, 7 => TRUE] ;

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
                $userGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class) ;
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
        $this->addFlashMessage("User : " . $userUid . " (" . $user->getEmail() . ") enabled | " . $msg . "  " , "Success" , AbstractMessage::OK) ;
        $this->addFlashMessage("Organizer : " . $organizerUid . " (" . $organizer->getName() . ")  enabled " , AbstractMessage::OK) ;
        try {
            $this->redirect('assist' , "Organizer", Null , NULL , $this->settings['pageIds']['organizerAssist'] );
        } catch(StopActionException) {
            foreach (  $this->response->getHeaders() as $header ) {
                header( $header) ;
            }

            die;
        }

    }
    /**
     * @param array|null $arguments
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function eventChangeLocIdAction( array $arguments=null ) {
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser */
        $this->frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');
        $output = [];
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        // https:/tango.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[event]=2264&tx_jvevents_ajax[action]=eventUnlink&
        $output['success'] = false ;
        $output['msg'] = 'Starting Link to other Location ';
        if( $arguments['event'] > 0 ) {
            $output['event']['requestId'] =  $arguments['event'] ;
            $output['msg'] = 'got Event';
            if ( intval( $this->frontendUser->user['uid'] ) > 0 ) {
                $output['event']['user'] =  intval( $this->frontendUser->user['uid'] ) ;
                $output['msg'] = 'got Event and user is logged in';
                /** @var Event $event */
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
                                    $target .= "&tx_jvevents_event%5Bevent%5D=" . $event->getUid() ;
                                    $target .= "&tx_jvevents_event%5Baction%5D=edit"  ;
                                    $target .= "&tx_jvevents_event%5Bcontroller%5D=Event"  ;
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
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function eventUnlinkAction( array $arguments=null ) {
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser */
        $this->frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');
        $output = [];
        if(!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        // https:/tango.ddev.local/index.php?uid=82&eID=jv_events&tx_jvevents_ajax[event]=2264&tx_jvevents_ajax[action]=eventUnlink&
        $output['success'] = false ;
        $output['msg'] = 'Starting unLink';
        if( $arguments['event'] > 0 ) {
            $output['event']['requestId'] =  $arguments['event'] ;
            $output['msg'] = 'got Event';
            if ( intval( $this->frontendUser->user['uid'] ) > 0 ) {
                $output['event']['user'] =  intval( $this->frontendUser->user['uid'] ) ;
                $output['msg'] = 'got Event and user is logged in';
                /** @var Event $event */
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


        $output = [];
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
        return htmlspecialchars(str_replace( ["," , ";" , "\r\n" , "\n"] , ["\," , "\;" , "\\n", "\\n"] , (string) $text )) ;
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

    /**
  * @return array
  */
 public function getFilesArray( ObjectStorage $resource ) {
        /** @var FileReference $tempFile */
        $return = [] ;
        if(is_object($resource) && $resource->count() > 0 ) {
            foreach ($resource as $tempFile) {

                try {
                    $single = [] ;
                    if( is_object($tempFile) && $tempFile->getOriginalResource() ) {
                        $single['url'] =  GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") . $tempFile->getOriginalResource()->getPublicUrl() ;
                        $single['filename'] =  $tempFile->getOriginalResource()->getName() ;
                        $single['title'] =  $tempFile->getOriginalResource()->getTitle() ;
                        if(!$single['title'] ) {
                            $single['title']  =  str_replace( "_" , " " , (string) $tempFile->getOriginalResource()->getNameWithoutExtension() ) ;
                        }
                        $single['ext'] =  $tempFile->getOriginalResource()->getExtension() ;
                        $single['mimeType'] =  $tempFile->getOriginalResource()->getMimeType() ;
                        $single['width'] =  $tempFile->getOriginalResource()->getProperties()['width'];
                        $single['height'] =  $tempFile->getOriginalResource()->getProperties()['height'];
                        $single['size'] =  $tempFile->getOriginalResource()->getSize() ;
                    }
                    $return[] = $single ;
                } catch(\Exception) {

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
                /** @var ConnectionPool $connectionPool */
                $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);

                $connection = $connectionPool->getConnectionForTable('tx_sfbanners_domain_model_banner') ;

                /** @var QueryBuilder $queryBuilder */
                $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sfbanners_domain_model_banner') ;
                $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

                $row = $queryBuilder ->select('uid' , 'starttime', 'endtime', 'impressions','clicks'   )
                    ->from('tx_sfbanners_domain_model_banner')
                    ->where( $queryBuilder->expr()->eq('link', $queryBuilder->createNamedParameter($output['event']["eventId"] , \PDO::PARAM_INT)) )
                    ->andWhere( $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0 , \PDO::PARAM_INT)) )
                    ->andWhere( $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0 , \PDO::PARAM_INT)) )
                    ->orderBy("endtime" , "DESC")
                    ->setMaxResults(1)
                    ->executeQuery()
                    ->fetchAssociative();

                if ( $row) {
                    $output['event']['banner'] = $row ;
                    if( $row['endtime'] > time() ) {
                        $output['event']['banner']['active'] = true ;
                    }
                }

                $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_sfbanners_domain_model_banner') ;
                $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

                $now = time() ;
                $endTime = $now + ( 3600 * 24 * 42) ;
                $queryBuilder ->select('uid' , 'title', 'starttime', 'endtime', 'impressions','clicks', 'fe_user' ,'organizer'   )
                    ->from('tx_sfbanners_domain_model_banner')
                    ->where( $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0 , \PDO::PARAM_INT)) )
                    ->andWhere( $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0 , \PDO::PARAM_INT)) )
                    ->andWhere( $queryBuilder->expr()->gte('endtime', $queryBuilder->createNamedParameter( $now , \PDO::PARAM_INT)) )
                    ->andWhere( $queryBuilder->expr()->lte('starttime', $queryBuilder->createNamedParameter( $endTime , \PDO::PARAM_INT)) )
                    ->orderBy("endtime" , "ASC")
                    ->setMaxResults(999) ;
                if( $output['event']["categoryId"] == 1 ) {
                    // banner tanzen
                    $queryBuilder->andWhere( $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter( 56 , \PDO::PARAM_INT)) ) ;
                } elseif ( $output['event']["categoryId"] == 2 ) {
                    // banner lernen
                    $queryBuilder->andWhere( $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(135 , \PDO::PARAM_INT)) ) ;
                }

                $rows = $queryBuilder
                    ->executeQuery()
                    ->fetchAllAssociative();


                if ( $rows) {
                    $output['event']['banners']['rows'] = $rows ;
                    $output['event']['banners']['now'] = time();
                    $output['event']['banners']['endTime'] = $endTime ;
                    $output['event']['banners']['count'] = count($rows) ;

                    // $output['event']['banners']['query'] = $queryBuilder->getSQL() ;
                    // $output['event']['banners']['params'] = $queryBuilder->getParameters() ;

                } else {
                    $output['event']['banners']['count'] = 0 ;
                }
                $organizerId = isset($output['organizer']['organizerId']) ? intval($output['organizer']['organizerId']) : 0 ;
                if ($organizerId > 0 ) {
                    $queryBuilder->andWhere( $queryBuilder->expr()->eq('organizer', $queryBuilder->createNamedParameter( $organizerId , \PDO::PARAM_INT)) ) ;
                    $rows = $queryBuilder->executeQuery()->fetchAllAssociative() ;
                    if ( $rows) {
                        $output['organizer']['banners']['organizerCount'] = count( $rows) ;
                    } else {
                        $output['organizer']['banners']['organizerCount'] = 0 ;
                     //   $output['organizer']['banners']['query'] = $queryBuilder->getSQL() ;
                     //   $output['organizer']['banners']['params'] = $queryBuilder->getParameters() ;
                    }
                }
                // no banner for current event set?
                // user has Group May create banner ?
                // count of existing banners for organizer < max from settings current 2  ?
                if ( !isset( $output['event']['banner'] ) && intval( $this->frontendUser->user['uid'] ) > 0  ) {
                    $userGroups = GeneralUtility::intExplode( "," , $this->frontendUser->user['usergroup'] ) ;
                    $hasGroup = false ;
                    $isAdmin = false ;
                    $isVip = false ;
                    foreach ( $userGroups as $groupId ) {
                        if ( in_array( $groupId , GeneralUtility::intExplode( "," , $this->settings['organizer']['groups']['BannerRequest'] ) ) ) {
                            $hasGroup = true ;
                        }
                        if ( in_array( $groupId , GeneralUtility::intExplode( "," , $this->settings['organizer']['groups']['adminOrganizer'] ) ) ) {
                            $isAdmin = true ;
                        }
                        if ( in_array( $groupId , GeneralUtility::intExplode( "," , $this->settings['organizer']['groups']['vip'] ) ) ) {
                            $isVip = true ;
                        }
                    }
                    if ( $hasGroup && $isVip ) {
                        $maxBanners = intval( $this->settings['organizer']['maxBanners'] ) ;
                        $maxBannersPerOrganizer = intval( $this->settings['organizer']['maxBannersPerOrganizer'] ) ;
                        if ( $maxBanners < 1 ) {
                            $maxBanners = 8 ;
                        }
                        if ( $maxBannersPerOrganizer < 1 ) {
                            $maxBannersPerOrganizer = 2 ;
                        }
                        if ( isset( $output['organizer']['banners']['organizerCount'] ) &&  $output['organizer']['banners']['organizerCount'] < $maxBannersPerOrganizer  ) {
                            if ( $output['event']['banners']['count'] < $maxBanners ) {
                                $output['organizer']['banners']['canCreateBanner'] = true ;
                            }
                        }
                    }
                } else {
                    $output['organizer']['banners']['canCreateBanner'] = 0 ;
                }




            } catch (Exception $e) {
                $output['event']['banners']['error'] = "Error in Banner Query" ;
                $output['event']['banners']['query'] = $e->getMessage();
            }
        }
        return $output ;
    }

    /**
     * @return void
     */
    public function initSettings($request): void
    {
        $ts = TyposcriptUtility::loadTypoScriptFromRequest($request, "tx_jvevents_events");
        if (is_array($this->settings) && is_array($ts) && is_array($ts['settings'])) {
            $this->settings = array_merge($ts['settings']);
        } elseif (is_array($ts) && is_array($ts['settings']) ) {
            $this->settings = $ts['settings'];
        }
    }

}