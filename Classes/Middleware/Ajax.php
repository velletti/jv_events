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
        // examples:
        // https://wwwv11.allplan.com.ddev.site/?id=110&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues
        // https://tangov10.ddev.site/?id=110&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues

        // FIx Broken URL : https://tangov10.ddev.site/de/eventlist.html?tx_jvevents_events  but no values
        if( is_array($_gp) && key_exists("tx_jvevents_events" ,$_gp ) && is_string($_gp['tx_jvevents_events'] )  ) {
            return (new Response())
                ->withHeader('Location', $request->getUri()->getScheme() . "://" .$request->getUri()->getHost() )
                ->withStatus("301");
        }
        // FIx Broken URL : https://tangov10.ddev.site/de/eventlist.html?tx_jvevents_events  but no values
        if( is_array($_gp) && key_exists("tx_jvevents_events" ,$_gp )) {

            $params = [ "controller" , "action" , "event" , "registrant" , "organizer" , "location"] ;
            foreach( $params as $param ) {
                if ( array_key_exists( $param , $_gp['tx_jvevents_events'] ) )
                {
                    if( $this->hasBadValue( $_gp['tx_jvevents_events'][$param]) ) {
                        return (new Response())
                            ->withHeader('Location', $request->getUri()->getScheme() . "://" .$request->getUri()->getHost() )
                            ->withStatus("301");
                    }

                }
            }


            //  registrant Controller: redirect if Event ID missing or FIx if controller Name is written in Lowercase
            if(  array_key_exists( "controller" , $_gp['tx_jvevents_events'] ) && strtolower( $_gp['tx_jvevents_events']['controller'] )  == "registrant" ) {
                $action =  array_key_exists( "action" , $_gp['tx_jvevents_events'] ) ? $_gp['tx_jvevents_events']['action'] : "" ;
                if ( in_array($action , [ 'list' , 'checkQrcode'])
                    && ( isset($_gp['tx_jvevents_events']['event']) && (int)$_gp['tx_jvevents_events']['event'] < 0 )) {
                    return (new Response())
                        ->withHeader('Location', $request->getUri()->getScheme() . "://" .$request->getUri()->getHost() )
                        ->withStatus("301");
                }
                if(   $_gp['tx_jvevents_events']['controller']   == "registrant") {
                    $_gp['tx_jvevents_events']['controller'] = ucfirst( $_gp['tx_jvevents_events']['controller'] ) ;
                    $loc =  $request->getUri()->getScheme() . "://" . $request->getUri()->getHost() . $request->getUri()->getPath() ."?" . http_build_query( $_gp ) ;
                    return (new Response())
                        ->withHeader('Location', $loc )
                        ->withStatus("301");
                }
            }

        }






        if( is_array($_gp) && key_exists("tx_sfregister_create" ,$_gp ) && key_exists("action" ,$_gp['tx_sfregister_create']  )
            && $_gp['tx_sfregister_create']['action'] == "save" && !$request->getParsedBody() ) {
            return (new Response())
                ->withHeader('Location', $request->getUri()->getScheme() . "://" .$request->getUri()->getHost() )
                ->withStatus("301");
        }


        if( is_array($_gp) && key_exists("tx_jvevents_ajax" ,$_gp ) && key_exists("action" ,$_gp['tx_jvevents_ajax']  ) ) {

            $function = strtolower( trim($_gp['tx_jvevents_ajax']['action'])) ;
            if( $function == "eventlist"  ) {
                $this->getEventList( $_gp["tx_jvevents_ajax"] ) ;

            } else {

                $GLOBALS['TSFE']->set_no_cache();
                    /** @var AjaxUtility $ajaxUtility */
                $ajaxUtility = GeneralUtility::makeInstance(AjaxUtility::class) ;

                // ToDo generate Output as before in ajax Controller here in Middleware with CORE features.
                $controller = $ajaxUtility->initController($_gp , $function , $request ) ;
                $controller->initializeRepositorys( ) ;
                $controller->initSettings()  ;

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
                    case 'eventlist' :
                        // ToDo : remove
                        $controller->eventListAction($_gp["tx_jvevents_ajax"]) ;
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
     * check if value contain one of unwanted sign that fills log with tried  SQL injections
     *
     * @param string $value
     * @return bool
     */
    public function hasBadValue( string $value ) {
        $badValues= [ "'" , ";" , "("] ;
        foreach( $badValues as $badValue) {
            if( strpos( $value , $badValue ) > 0  ) { return true ;}
        }
        return false ;
    }

    /**
     * action list
     *
     * @param array|null $arguments
     * @return void
     */
    public function getEventList(array $arguments=Null)
    {

        $this->tagRepository        = GeneralUtility::makeInstance(TagRepository::class);
        $this->categoryRepository        = GeneralUtility::makeInstance(CategoryRepository::class);
        $this->registrantRepository        = GeneralUtility::makeInstance(RegistrantRepository::class);
        $this->locationRepository        = GeneralUtility::makeInstance(LocationRepository::class);
        $this->organizerRepository        = GeneralUtility::makeInstance(OrganizerRepository::class);
        $this->eventRepository        = GeneralUtility::makeInstance(EventRepository::class);
        $this->subeventRepository        = GeneralUtility::makeInstance(SubeventRepository::class);
        $this->staticCountryRepository        = GeneralUtility::makeInstance(StaticCountryRepository::class);

        if (!$arguments) {
            $arguments = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        }
        $pid = GeneralUtility::_GP('id');
        $ts = TyposcriptUtility::loadTypoScriptFromScratch($pid, "tx_jvevents_events");
        if (is_array($this->settings) && is_array($ts)) {
            $this->settings = array_merge($ts['settings']);
        } elseif (is_array($ts)) {
            $this->settings = $ts['settings'];
        }

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
        $output = $this->eventsListMenuSub($arguments);

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
     * action list
     *
     * @return array
     */
    public function eventsListMenuSub(array $arguments )
    {
        $isAutorized = TokenUtility::checkToken(($arguments['apiToken']) ?? null ) ;

        $arguments['limit'] = $isAutorized ? 250 : 10 ;

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
        /*   Get infos about: EVENT
        /* ************************************************************************************************************ */

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


                $output['event']['eventId'] = $event->getUid() ;
                $output['event']['viewed'] = $event->getViewed();
                $output['event']['canceled'] = $event->getCanceled();

                $output['event']['startDate'] = $event->getStartDate()->format("d.m.Y") ;
                if  ($event->getAllDay() ) {
                    $output['event']['allDay'] = true ;
                } else {
                    $output['event']['allDay'] = false ;
                    $output['event']['startTime'] = date( "H:i" , $event->getStartTime()) ;
                    $output['event']['endTime'] = date( "H:i" , $event->getEndTime()) ;
                }

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
                                $tempEventArray['tags']=[] ;
                                foreach ($tempEvent->getTags() as $tag) {
                                    $tempEventArray['tags'][] = [ "uid" => $tag->getUid() , "name" => $tag->getName() ] ;
                                }
                                /** @var Category $category */
                                foreach ( $tempEvent->getEventCategory() as $category) {
                                    $tempEventArray['category'] = [ "uid" => $category->getUid() , "title" => $category->getTitle() ] ;
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
