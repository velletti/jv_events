<?php

namespace JVelletti\JvEvents\Middleware;

use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Fluid\View\StandaloneView;
use JVelletti\JvEvents\Utility\EmConfigurationUtility;
use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Subevent;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use JVelletti\JvEvents\Domain\Model\Category;
use JVelletti\JvEvents\Domain\Model\Location;
use JVelletti\JvEvents\Domain\Model\Organizer;
use JVelletti\JvEvents\Domain\Repository\CategoryRepository;
use JVelletti\JvEvents\Domain\Repository\EventRepository;
use JVelletti\JvEvents\Domain\Repository\LocationRepository;
use JVelletti\JvEvents\Domain\Repository\OrganizerRepository;
use JVelletti\JvEvents\Domain\Repository\RegistrantRepository;
use JVelletti\JvEvents\Domain\Repository\StaticCountryRepository;
use JVelletti\JvEvents\Domain\Repository\SubeventRepository;
use JVelletti\JvEvents\Domain\Repository\TagRepository;
use JVelletti\JvEvents\Domain\Repository\TokenRepository;
use JVelletti\JvEvents\Utility\AjaxUtility;
use JVelletti\JvEvents\Utility\ShowAsJsonArrayUtility;
use JVelletti\JvEvents\Utility\TokenUtility;
use JVelletti\JvEvents\Utility\TyposcriptUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class Ajax
 * @package JVelletti\JvEvents\Middleware
 */
class Ajax implements MiddlewareInterface
{

    /**
     * @var TokenRepository
     */
    protected $tokenRepository;


    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidExtensionNameException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $_gp = $request->getQueryParams();

        if( is_array($_gp) && key_exists("tx_jvevents_ajax" ,$_gp ) && key_exists("action" ,$_gp['tx_jvevents_ajax']  ) ) {

            $function = strtolower( trim($_gp['tx_jvevents_ajax']['action'])) ;
            if( $function == "eventlist"  ) {

                $this->getEventList( $_gp["tx_jvevents_ajax"] , $request ) ;

            } else {
                // todo rebuld here insteead of using the Ajax Controller

                    /** @var AjaxUtility $ajaxUtility */
                $ajaxUtility = GeneralUtility::makeInstance(AjaxUtility::class) ;

                // ToDo generate Output as before in ajax Controller here in Middleware with CORE features.
                $controller = $ajaxUtility->initController($_gp , $function , $request ) ;
                $controller->initializeRepositorys( ) ;
                /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser */
                $controller->frontendUser = $request->getAttribute('frontend.user');
                $controller->initSettings($request)  ;


                switch ($function) {
                    case 'downloadical' :
                        $output = $controller->downloadIcal( $_gp["tx_jvevents_ajax"] ) ;
                        if($output ) {
                            $result = $output['data'];
                            $file = $output['filename'] ? $output['filename'] : "calendar.ics" ;
                            $body = new Stream('php://temp', 'rw');
                            $body->write($result);
                            return (new Response())
                                ->withHeader('content-type', $output['content-type'] . '; charset=utf-8')
                                ->withHeader('Content-Disposition' , 'inline; filename=' . $file )
                                ->withBody($body)
                                ->withStatus($output['status']);
                        }
                        break;
                    case 'cleanhistory' :
                        $controller->cleanHistory($_gp["tx_jvevents_ajax"]) ;
                        break;

                    case 'locationlist' :
                        $controller->locationListAction($_gp["tx_jvevents_ajax"]) ;
                        break;
                    case 'activate' :
                        // $organizerUid=0 , $userUid=0 , $hmac='invalid' , $rnd = 0
                        // this is not used anymore
                        $organizerUid  =    key_exists( 'organizerUid' , $_gp["tx_jvevents_ajax"]) ?  $_gp["tx_jvevents_ajax"]['organizerUid'] : 0 ;
                        $userUid  =         key_exists( 'userUid' , $_gp["tx_jvevents_ajax"]) ?  $_gp["tx_jvevents_ajax"]['userUid'] : 0 ;
                        $hmac  =            key_exists( 'hmac' , $_gp["tx_jvevents_ajax"]) ?  $_gp["tx_jvevents_ajax"]['hmac'] : 'invalid' ;
                        $rnd  =             key_exists( 'rnd' , $_gp["tx_jvevents_ajax"]) ?  $_gp["tx_jvevents_ajax"]['rnd'] : 0 ;
                        $controller->activateAction( $organizerUid , $userUid , $hmac , $rnd ) ;
                        break;
                    case 'eventunlink' :
                        $controller->eventUnlinkAction($_gp["tx_jvevents_ajax"]) ;
                        break;
                    case 'eventchangelocid' :

                        $controller->eventChangeLocIdAction($_gp["tx_jvevents_ajax"]) ;
                        break;

                    default:
                        $controller->eventMenuAction($_gp["tx_jvevents_ajax"]) ;
                        break;
                }

            }
        }
        return $handler->handle($request);
    }

    /**
     * action list
     *
     * @param array|null $arguments
     * @return void
     */
    public function getEventList(array $arguments=Null , $request = null)
    {

        // 6.2.2020 with teaserText and files
        // 27.1.2021 LTS 10 : wegfall &eID=jv_events und uid, dafür Page ID der Seite mit der Liste : z.b. "id=110"

        // 2024
        // https://wwwv12.allplan.com.ddev.site/?id=13001&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyJson&tx_jvevents_ajax[apiToken]=testTestTest&&tx_jvevents_ajax[user]=11
        // https://www.allplan.com/?id=13001&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyJson&tx_jvevents_ajax[apiToken]=LN-2030-€nTeR-qRm8o-WLcM
        // https://tangov10.ddev.site/index.php?id=150&L=0&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=3&tx_jvevents_ajax[eventsFilter][startDate]=-1&tx_jvevents_ajax[mode]=onlyJson&tx_jvevents_ajax[user]=479&tx_jvevents_ajax[apiToken]=testTestTest


        $this->tagRepository        = GeneralUtility::makeInstance(TagRepository::class);
        $this->categoryRepository        = GeneralUtility::makeInstance(CategoryRepository::class);
        $this->registrantRepository        = GeneralUtility::makeInstance(RegistrantRepository::class);
        $this->locationRepository        = GeneralUtility::makeInstance(LocationRepository::class);
        $this->organizerRepository        = GeneralUtility::makeInstance(OrganizerRepository::class);
        $this->eventRepository        = GeneralUtility::makeInstance(EventRepository::class);
        $this->subeventRepository        = GeneralUtility::makeInstance(SubeventRepository::class);
        $this->staticCountryRepository        = GeneralUtility::makeInstance(StaticCountryRepository::class);
        $this->tokenRepository        = GeneralUtility::makeInstance(TokenRepository::class);

        if (!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        $apiToken = $request->getHeaderLine('jve-apitoken');

        if (strlen($apiToken) > 10)  {
            $arguments['apiToken'] = $apiToken;
        }

        $pid = ( $_GET[$var] ?? 0 ) ;
        $ts = TyposcriptUtility::loadTypoScriptFromRequest($request, "tx_jvevents_events");
        if (is_array($this->settings) && is_array($ts)) {
            $this->settings = array_merge($ts['settings']);
        } elseif (is_array($ts)) {
            $this->settings = $ts['settings'];
        }



        // get all Access infos, Location infos , find similar events etc
        $output = $this->eventsListMenuSub($arguments , $request);

        if ($output['mode'] == "onlyValues") {
            unset($output['events']);
            ShowAsJsonArrayUtility::show($output);
        }
        if ($output['mode'] == "onlyJson") {
            ShowAsJsonArrayUtility::show($output);
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
     * action list
     *
     * @return array
     */
    public function eventsListMenuSub(array $arguments , $request )
    {

        /* ************************************************************************************************************ */
        /*   Check if Authorized /has License
        /* ************************************************************************************************************ */


        $isAutorized = FALSE ;
        $debug[] = date( "H:i:s") . " - " . __METHOD__ . " - " . __LINE__ ;
        $apiLicense = TokenUtility::checkLicense (isset($arguments['apiToken'])  ? (string)$arguments['apiToken'] : null ,
                                                                                  isset($arguments['user'] ) ? (int)$arguments['user'] : null ) ;
        $debug[] = "apiLicense: " . $apiLicense ;
        if( ! $apiLicense ) {
            $isAutorized = TokenUtility::checkToken(($arguments['apiToken']) ?? null ) ;
            $apiLicense = $isAutorized ? "BASIC" : "DEMO" ;
            $debug[] = "apiLicense: " . $apiLicense ;
        } else {
            $isAutorized = TRUE ;
        }


        if( ! $isAutorized ) {
            // ToDO : BLOCK Request URL by referrer . For now we just set the License to DEMO if not authorized
            $isReferrerBlocked = FALSE ;
            if ( $isReferrerBlocked ) {
                return [
                    "license" => $apiLicense ,
                    "isAutorized" => $isAutorized
                ] ;
            }
        }
        // ToDO make this configurable
        $licencseLimits = [ "DEMO" => 2 , "BASIC" => 3 , "FULL" => 250 , "ENTERPRISE" => 2000 ] ;


        $arguments['limit'] = ($licencseLimits[$apiLicense] ?? 1 ) ;

        //  $GLOBALS['TSFE']->fe_user->user = [ 'uid' => 476 , "username" => "jvel@test.de" , "1,2,3,4,5,6,7"] ;
        /* ************************************************************************************************************ */
        /*   Prepare the Output :
        /* ************************************************************************************************************ */


        $output = array (
            "requestId" =>  intval( $GLOBALS['TSFE']->id ) ,
            "requestFrom" =>  $_SERVER['REMOTE_ADDR'] ,
            "requestedUser" =>  ($arguments['user'] ?? 0),

            "event" => [] ,
            "events" => []  ,
            "eventsFilter" => []  ,
            "eventsByFilter" => []  ,
            "license" => $apiLicense ,
            "licenseLimit" => $arguments['limit'] ,
            "isAutorized" => $isAutorized ,
            "feuser" => [] ,
            "mode" => $arguments['mode'] ,
            "organizer" => [] ,
            "location" => [] ,

        ) ;

        if( $apiLicense == "FULL") {
            // Todo : Restrict this to only requests from same server
            if( $request && $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR']) {
                $frontendUser = $request->getAttribute('frontend.user');
                $output["feuser"] =[
                    "uid" => $frontendUser->user['uid'],
                    "username" => $frontendUser->user['username'],
                    "usergroup" => $frontendUser->user['usergroup'],
                    "isOrganizer" => $this->isUserOrganizer()
                ] ;
                if ( $output["feuser"]["isOrganizer"]) {
                    $feuserOrganizer = $this->organizerRepository->findByUserAllpages(intval($GLOBALS['TSFE']->fe_user->user['uid']), FALSE, TRUE);
                    if ( is_object($feuserOrganizer->getFirst())) {
                        $output["feuser"]["organizer"]['uid'] = $feuserOrganizer->getFirst()->getUid() ;
                    }
                }
                if( $output["feuser"]['uid'] != $output["requestedUser"] ) {
                    unset( $output["feuser"]) ;
                }

            }
        } else {
            $output["licenseRestrictions"]["maxEvents"] = "restricted to max: " . $arguments['limit'] . " Events" ;
        }

        if(  $arguments['returnPid']  > 0 ) {
            $output['returnPid'] = $arguments['returnPid']  ;
        }

        $configuration = EmConfigurationUtility::getEmConf();
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
        /*   Get infos about: Single EVENT but not if DEMO
        /* ************************************************************************************************************ */
        if ($apiLicense == "DEMO") {
            if( isset($event)) {
                $output['licenseRestrictions']['event'] = "removed: argument event" ;
                unset($arguments['event']);
            }
        }
        if( $arguments['event']  > 0 ) {
            $output['event']['requestId'] =  $arguments['event']  ;

            /** @var Event $event */
            $event = $this->eventRepository->findByUidAllpages( $output['event']['requestId'] , FALSE  , TRUE );
            if( is_object($event )) {
                if ( substr($output['mode'], 0 , 4 )  != "only" ) {
                    $event->increaseViewed();
                    $this->eventRepository->update($event) ;
                    $needToStore = TRUE ;
                }


                ### nmode to sub FUnction
                $output['event'] = $this->mapEvent2Output($site, $event, $singlePid , $apiLicense);

                if( is_object( $event->getOrganizer() )) {
                    $organizer = $event->getOrganizer() ;
                    $return['organizerId'] = $organizer->getUid()  ;
                    $output['organizer']['organizerName'] = $organizer->getname()  ;
                    $output['organizer']['organizerEmail'] = $organizer->getEmail()  ;
                    $output['organizer']['organizerPhone'] = $organizer->getPhone()  ;
                    $output['organizer']['organizerSFID'] = $organizer->getSalesForceUserId() ;
                 }
                if( is_object( $event->getLocation() )) {
                    $location = $event->getLocation() ;
                    $output['event']['locationId'] = $event->getLocation()->getUid() ;
                }

                if( $event->getMasterId() > 0 ) {
                    $querysettings =$this->subeventRepository->getTYPO3QuerySettings() ;
                    $querysettings->setStoragePageIds(array( $event->getPid() )) ;

                    $this->eventRepository->setDefaultQuerySettings( $querysettings );
                    $filter = array() ;
                    $filter['startDate'] = $event->getStartDate()->getTimestamp() ;
                    $filter['maxDays']  = $isAutorized ? 365 : 30 ;
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

        /* ************************************************************************************************************ */
        /*   Get infos about: EVENTS by Filter
        /* ************************************************************************************************************ */
        if( $arguments['eventsFilter']  ) {
            $arguments['eventsFilter']['maxEvents'] = min((int)$arguments['limit'] , (int)(  $arguments['eventsFilter']['maxEvents'] ) ? (int)$arguments['eventsFilter']['maxEvents'] : (int)$arguments['limit'] ) ;
            /* ************************************************************************************************************ */
            // First unset arguments that require a License
            /* ************************************************************************************************************ */

            if ($apiLicense != "FULL") {

                if ($apiLicense == "DEMO") {
                    if (isset($arguments['eventsFilter']['organizer'])) {
                        $output['licenseRestrictions']['organizer'] = "removed: argument organizer";
                        unset($arguments['eventsFilter']['organizer']);
                    }
                    if( isset($arguments['eventsFilter']['categories'])) {
                        $output['licenseRestrictions']['categories'] = "removed: argument categories" ;
                        unset( $arguments['eventsFilter']['categories'] ) ;
                    }
                }
                if( isset($arguments['eventsFilter']['location'])) {
                    $output['licenseRestrictions']['location'] = "removed: argument location" ;
                    unset($arguments['eventsFilter']['location']);
                }
                if( isset($arguments['eventsFilter']['sameCity'])) {
                    $output['licenseRestrictions']['sameCity'] = "removed: argument sameCity" ;
                    unset($arguments['eventsFilter']['sameCity']);
                }
                if( isset($arguments['eventsFilter']['startDate'])) {
                    $output['licenseRestrictions']['startDate'] = "argument startDate set to -1" ;
                    $arguments['eventsFilter']['startDate'] = 0 ;
                }

            }

            $output['eventsFilter'] = $arguments['eventsFilter'] ;

            if ( isset($output['eventsFilter']['sameCity']) ) {
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
            // $this->settings['debugQuery'] = 2 ;
            /** @var QueryResultInterface $events */
            $events = $this->eventRepository->findByFilter( $output['eventsFilter'], $arguments['limit'],  $this->settings );
            if( count( $events ) > 0 ) {
                $output['events'] = $events ;
                /** @var Event $tempEvent */
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
                            $tempEventArray['canceled'] = $tempEvent->getCanceled();

                            $tempEventArray['startDate'] = $tempEvent->getStartDate()->format("d.m.Y");
                            $tempEventArray['startDateTstamp'] = $tempEvent->getStartDate()->getTimestamp();

                            // todo :check by License DEMO, BASIC, FULL
                            if ( $isAutorized ) {
                                $tempEventArray['created'] = date("d.m.Y" , $tempEvent->getCrdate() );
                                $tempEventArray['lastUpdated'] = date("d.m.Y" , $tempEvent->getLastUpdated());
                                $tempEventArray['price'] = $tempEvent->getPrice();

                                if (  $tempEvent->getTeaserImage() && is_object( $tempEvent->getTeaserImage()->getOriginalResource()) ) {
                                    $tempEventArray['TeaserImageFrom'] =  "Event" ;
                                    $tempEventArray['teaserImage'] =  trim( GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") , "/" ) . "/" .
                                        $tempEvent->getTeaserImage()->getOriginalResource()->getPublicUrl() ;
                                } elseif( is_object($tempEvent->getLocation()) && $tempEvent->getLocation()->getTeaserImage()
                                    && is_object($tempEvent->getLocation()->getTeaserImage()->getOriginalResource() ) )  {
                                    $tempEventArray['TeaserImageFrom'] =  "Location" ;
                                    $tempEventArray['teaserImage'] =  trim( GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") , "/" ) . "/" .
                                        $tempEvent->getLocation()->getTeaserImage()->getOriginalResource()->getPublicUrl() ;
                                } elseif( is_object($tempEvent->getOrganizer()) && $tempEvent->getOrganizer()->getTeaserImage()
                                    && is_object($tempEvent->getOrganizer()->getTeaserImage()->getOriginalResource() ) )  {
                                    $tempEventArray['TeaserImageFrom'] =  "Organizer" ;
                                    $tempEventArray['teaserImage'] =  trim( GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") , "/" ) . "/" .
                                        $tempEvent->getOrganizer()->getTeaserImage()->getOriginalResource()->getPublicUrl() ;

                                } else {
                                    $tempEventArray['TeaserImageFrom'] =  "NotFound" ;
                                }
                                if ($apiLicense == "FULL") {
                                    $tempEventArray['description']= $tempEvent->getDescription();
                                    $tempEventArray['tags']=[] ;
                                    foreach ($tempEvent->getTags() as $tag) {
                                        $tempEventArray['tags'][] = [ "uid" => $tag->getUid() , "name" => $tag->getName() ] ;
                                    }
                                    /** @var Category $category */
                                    foreach ( $tempEvent->getEventCategory() as $category) {
                                        $tempEventArray['category'] = [ "uid" => $category->getUid() , "title" => $category->getTitle() ] ;
                                    }
                                }

                            }

                            if  ($tempEvent->getAllDay() ) {
                                $tempEventArray['allDay']   = true ;
                            } else {
                                $tempEventArray['allDay']   = false ;
                                $tempEventArray['startTime'] = date( "H:i" , $tempEvent->getStartTime() );
                                $tempEventArray['endTime'] =  date( "H:i" , $tempEvent->getEndTime() );
                            }

                            $tempEventArray['teaser'] = $tempEvent->getTeaser();

                            if (is_object($tempEvent->getLocation())) {
                                $tempEventArray['LocationCity'] = $tempEvent->getLocation()->getCity();
                                if ( $isAutorized ) {
                                    $tempEventArray['location']['uid'] = $tempEvent->getLocation()->getUid();
                                    $tempEventArray['location']['country'] = $tempEvent->getLocation()->getCountry();
                                    $tempEventArray['location']['city'] = $tempEvent->getLocation()->getCity();
                                    $tempEventArray['location']['lat'] = $tempEvent->getLocation()->getLat() ;
                                    $tempEventArray['location']['lng'] = $tempEvent->getLocation()->getLng() ;
                                    $tempEventArray['location']['streetAndNr'] = $tempEvent->getLocation()->getStreetAndNr();
                                    $tempEventArray['location']['additionalInfo'] = $tempEvent->getLocation()->getAdditionalInfo();
                                }
                            }
                            if ( $site ) {
                                try {
                                    $tempEventArray['slug'] = (string)$site->getRouter()->generateUri( $singlePid ,['_language' => max( $tempEvent->getLanguageUid() ,0 ) ,
                                        'tx_jvevents_event' => ['action' => 'show' , 'controller' => 'Event' ,'event' =>  $tempEvent->getUid() ]]);
                                } catch( \EXCEPTION $e ) {
                                    $tempEventArray['slug'] = $e->getMessage() ;
                                }
                            }
                            if ( $isAutorized && !is_array($output['event'] ) ) {
                                $output['event'] = $this->mapEvent2Output( $site , $tempEvent , $singlePid , $apiLicense) ;
                            }
                            $output['eventsByFilter'][] = $tempEventArray;
                            unset( $tempEventArray );
                        }

                    }


                }
            }

        }
        /* ************************************************************************************************************ */
        /*   Get infos about: Location only if is Authorized
        /* ************************************************************************************************************ */
        if ( $isAutorized) {
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
                if ($apiLicense == "FULL") {
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
            }

        }
        /* ************************************************************************************************************ */
        /*   Get infos about: Organizer
        /* ************************************************************************************************************ */
        if ( $isAutorized) {
            if(  isset( $arguments['organizer']) &&  $arguments['organizer'] > 0  && !is_object($organizer ) ) {
                $output['organizer']['requestId'] =  $arguments['organizer'];

                /** @var Organizer $organizer */
                $organizer = $this->organizerRepository->findByUidAllpages(  $arguments['organizer'], FALSE, TRUE);
            }

            // Location is set either by Event OR by location uid from request
            if( is_object($organizer )) {
                $output['organizer']['organizerId'] = $organizer->getUid() ;
                $output['organizer']['hasAccess'] = $this->hasUserAccess( $organizer ) ;
            }
        }
        if( $needToStore) {
            $this->persistenceManager->persistAll();
        }
        return  $output  ;

    }

    public function mapEvent2Output( $site , $event , $singlePid , $apiLicense) {
        $return['eventId'] = $event->getUid() ;
        $return['viewed'] = $event->getViewed();
        $return['canceled'] = $event->getCanceled();

        $return['startDate'] = $event->getStartDate()->format("d.m.Y") ;
        if  ($event->getAllDay() ) {
            $return['allDay'] = true ;
        } else {
            $return['allDay'] = false ;
            $return['startTime'] = date( "H:i" , $event->getStartTime()) ;
            $return['endTime'] = date( "H:i" , $event->getEndTime()) ;
        }



        if ( $site ) {
            try {
                $return['slug'] = (string)$site->getRouter()->generateUri( $singlePid ,['_language' => max( $event->getLanguageUid() ,0 ) ,
                   'tx_jvevents_event' => ['action' => 'show' , 'controller' => 'Event' ,'event' =>  $event->getUid() ]]);
            } catch( \EXCEPTION $e ) {
                $return['slug'] = "pid=" . $singlePid . "&L=" . $event->getLanguageUid() ;
            }
        }


        if( $event->getNotifyRegistrant() == 0  ) {
            $reminder2 = new \DateInterval("P1D") ;
            $reminderDate2 =  new \DateTime($event->getStartDate()->format("c")) ;

            $reminder1 = new \DateInterval("P7D") ;
            $reminderDate1 =  new \DateTime($event->getStartDate()->format("c")) ;
            $now =  new \DateTime() ;
            if ( $reminderDate1 > $now ) {
                $return['reminderDate1'] =  $reminderDate1->sub( $reminder1 )->format("d.m.Y") ;
            }
            if ( $reminderDate2 > $now ) {
                $return['reminderDate2'] =  $reminderDate2->sub( $reminder2 )->format("d.m.Y") ;
            }
        }

        $return['name'] = $event->getName() ;
        $return['teasterText'] = $event->getTeaser();
        $return['teaserImageUrl'] = GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST")  ;
        if (  $event->getTeaserImage() && is_object( $event->getTeaserImage()->getOriginalResource()) ) {
            $return['TeaserImageFrom'] =  "Event" ;
            $return['teaserImageUrl'] .=  $event->getTeaserImage()->getOriginalResource()->getPublicUrl() ;
        } else {
            if( $this->settings['EmConfiguration']['imgUrl2'] ) {
                $return['TeaserImageFrom'] =  "config-imgUrl2" ;
                $return['teaserImageUrl'] .=  trim($this->settings['EmConfiguration']['imgUrl2']) ;
            } else {
                $return['TeaserImageFrom'] =  "config-imgUrl" ;
                $return['teaserImageUrl'] = GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") . trim($this->settings['EmConfiguration']['imgUrl']) ;
            }
        }
        $return['price'] = round( $event->getPrice() , 2 ) ;
        $return['currency'] = $event->getCurrency() ;

        if ($apiLicense != "FULL") {
            return $return ;
        }
        $return['creationTime'] = date( "d.m.Y H:i" , $event->getCrdate() ) ;
        $return['crdate'] =  $event->getCrdate()  ;
        $return['noNotification'] = $event->getNotifyRegistrant() ;

        $return['filesAfterReg'] = $this->getFilesArray( $event->getFilesAfterReg() )  ;
        $return['filesAfterEvent'] = $this->getFilesArray( $event->getFilesAfterEvent() )  ;




        $return['priceReduced'] = $event->getPriceReduced();
        $return['priceReducedText'] = $event->getPriceReducedText();

        $return['registration']['possible'] = $event->isIsRegistrationPossible() ;
        $return['registration']['formPid'] = $event->getRegistrationFormPid() ;
        $return['registration']['noFreeSeats'] = $event->isIsNoFreeSeats() ;
        $return['registration']['freeSeats'] = $event->getAvailableSeats() ;
        $return['registration']['freeSeatsWaitinglist'] = $event->getAvailableWaitingSeats();
        $return['registration']['registeredSeats'] = $event->getRegisteredSeats();
        $return['registration']['unconfirmedSeats'] = $event->getUnconfirmedSeats();
        $return['registration']['slug'] = '' ;
        if( $event->isIsRegistrationPossible() && $event->getRegistrationFormPid() > 0  ) {
            $lang = max( $event->getLanguageUid() ,0 ) ;
            $argumentString = http_build_query( ['tx_jvevents_registrant' => ['action' => 'new' , 'controller' => 'Register' ,'event' =>  $event->getUid()  ]] ) ;
            $return['registration']['slug'] = GeneralUtility::getIndpEnv("TYPO3_REQUEST_HOST") . "/index.php?id=" . $event->getRegistrationFormPid() . "&L=" . $lang  . "&" . $argumentString;
            if( $site ) {
                try {
                    $return['registration']['slug'] = (string)$site->getRouter()->generateUri( $event->getRegistrationFormPid() ,['_language' => $lang ,
                                 'tx_jvevents_registrant' => ['action' => 'new' , 'controller' => 'Registrant' ,'event' =>  $event->getUid() ]]);

                 } catch( \EXCEPTION $e ) {
                    $return['registration']['slug'] = $e->getMessage() ;
                }

            }
        }

        $return['registration']['sfCampaignId'] = $event->getSalesForceCampaignId() ;
        $return['notification']['waitinglist'] = $event->getIntrotextRegistrant() ;
        $return['notification']['confirmed'] = $event->getIntrotextRegistrantConfirmed() ;

        $return['days'] = $event->getSubeventCount() ;
        if( $event->getSubeventCount() > 0 ) {
            $querysettings =$this->subeventRepository->getTYPO3QuerySettings() ;
            $querysettings->setStoragePageIds(array( $event->getPid() )) ;

            $this->subeventRepository->setDefaultQuerySettings( $querysettings );

            $subeventsArr = $this->subeventRepository->findByEventAllpages($event->getUid() , TRUE   ) ;
            /** @var Subevent $subevent */
            foreach ( $subeventsArr as $subevent) {
                if( is_object( $subevent )) {
                    $temp = [] ;
                    $temp['date'] = $subevent->getStartDate()->format("d.m.Y") ;
                    $temp['starttime'] = date( "H:i" ,$subevent->getStartTime() ) ;
                    $temp['endtime'] = date( "H:i" ,$subevent->getEndTime() ) ;
                    $return['moreDays'][] = $temp ;
                    unset($temp) ;
                }

            }
        } else {
            $return['moreDays'] = [] ;
        }
        if( is_object( $event->getOrganizer() )) {
            $organizer = $event->getOrganizer() ;

            $return['registration']['registrationInfo'] = $organizer->getRegistrationInfo() ;
            $return['hasAccess'] = $this->hasUserAccess( $organizer ) ;
        }
        return $return ;
    }

    /**
     * @param ObjectStorage $resource
     * @return array
     */
    public function getFilesArray( ObjectStorage $resource ) {
        /** @var FileReference $tempFile */
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
     * @param Organizer | LazyLoadingProxy  $organizer
     * @return bool
     */


    public function hasUserAccess( $organizer ): bool
    {
        if(! is_object( $organizer ) ) {
            return false ;
        }
        $feuserUid = intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) ;
        $users = GeneralUtility::trimExplode("," , $organizer->getAccessUsers() , TRUE ) ;
        if( in_array( $feuserUid  , $users )) {
            return true  ;
        } else {
            $groups = GeneralUtility::trimExplode("," , $organizer->getAccessGroups() , TRUE ) ;
            $feuserGroups = GeneralUtility::trimExplode("," ,  $GLOBALS['TSFE']->fe_user->user['usergroup']  , TRUE ) ;
            foreach( $groups as $group ) {
                if( in_array( $group  , $feuserGroups )) {
                    return true  ;
                }
            }
        }
        return false  ;
    }


    /**
     *  $eventRepository
     * @var EventRepository
     */
    protected $eventRepository = NULL;

    /**
     * subeventRepository
     *
     * @var SubeventRepository
     */
    protected $subeventRepository = NULL;

    /**
     * staticCountryRepository
     *
     * @var StaticCountryRepository
     */
    protected $staticCountryRepository = NULL;

    /**
     * organizerRepository
     *
     * @var \JVelletti\JvEvents\Domain\Repository\OrganizerRepository
     */
    protected $organizerRepository = NULL;


    /**
     * locationRepository
     *
     * @var LocationRepository
     */
    protected $locationRepository = NULL;

    /**
     * persistencemanager
     *
     * @var PersistenceManager
     */
    protected $persistenceManager = NULL ;

    /**
     * registrantRepository
     *
     * @var RegistrantRepository
     */
    protected $registrantRepository = NULL;


    /**
     * categoryRepository
     *
     * @var CategoryRepository
     */
    protected $categoryRepository = NULL;

    /**
     * tagRepository
     *
     * @var TagRepository
     */
    protected $tagRepository = NULL;


    /**
     * @var array
     */
    public $debugArray ;


    /**
     * @var float
     */
    public $timeStart ;

    /**
     * @param TagRepository $tagRepository
     */
    public function injectTagRepository(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param RegistrantRepository $registrantRepository
     */
    public function injectRegistrantRepository(RegistrantRepository $registrantRepository)
    {
        $this->registrantRepository = $registrantRepository;
    }

    /**
     * @param LocationRepository $locationRepository
     */
    public function injectLocationRepository(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @param OrganizerRepository $organizerRepository
     */
    public function injectOrganizerRepository(OrganizerRepository $organizerRepository)
    {
        $this->organizerRepository = $organizerRepository;
    }

    /**
     * @param EventRepository $eventRepository
     */
    public function injectEventRepository(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param SubeventRepository $subeventRepository
     */
    public function injectSubeventRepository(SubeventRepository $subeventRepository)
    {
        $this->subeventRepository = $subeventRepository;
    }

    /**
     * @param StaticCountryRepository $staticCountryRepository
     */
    public function injectStaticCountryRepository(StaticCountryRepository $staticCountryRepository)
    {
        $this->staticCountryRepository = $staticCountryRepository;
    }



    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @return bool
     */
    public function isUserOrganizer() {
        $groups = GeneralUtility::trimExplode("," , $this->settings['feEdit']['organizerGroudIds'] , TRUE ) ;
        $feuserGroups = GeneralUtility::trimExplode("," ,  $GLOBALS['TSFE']->fe_user->user['usergroup']  , TRUE ) ;
        foreach( $groups as $group ) {
            if( in_array( $group  , $feuserGroups )) {
                return true  ;
            }
        }
        return false  ;
    }

    private $settings  ;
}
