<?php

namespace JVE\JvEvents\Middleware;

use JVE\JvEvents\Utility\AjaxUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Ajax
 * @package JVE\JvEvents\Middleware
 */
class Ajax implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $_gp = $request->getQueryParams();
        // examples:
        // https://wwwv10.allplan.com.ddev.site/?id=110&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues
        // https://tangov10.ddev.site/?id=110&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues

        // FIx Broken URL : https://tangov10.ddev.site/de/eventlist.html?tx_jvevents_events  but no values
        if( is_array($_gp) && key_exists("tx_jvevents_events" ,$_gp ) && is_string($_gp['tx_jvevents_events'] )  ) {
            return (new Response())
                ->withHeader('Location', $request->getUri()->getScheme() . "://" .$request->getUri()->getHost() )
                ->withStatus("301");
        }
        // FIx Broken URL : https://tangov10.ddev.site/de/eventlist.html?tx_jvevents_events  but no values
        if( is_array($_gp) && key_exists("tx_jvevents_events" ,$_gp )) {
            if (
                ( array_key_exists( "controller" , $_gp['tx_jvevents_events'] ) && strpos( $_gp['tx_jvevents_events']['controller']  , "'" ) > 0  )
                ||
                ( array_key_exists( "action" , $_gp['tx_jvevents_events'] ) && strpos( $_gp['tx_jvevents_events']['action']  , "'" ) > 0  )
                ||
                ( array_key_exists( "event" , $_gp['tx_jvevents_events'] ) && strpos( $_gp['tx_jvevents_events']['event']  , "'" ) > 0  )


            )
            {
                return (new Response())
                    ->withHeader('Location', $request->getUri()->getScheme() . "://" .$request->getUri()->getHost() )
                    ->withStatus("301");
            }

            //  registrant Controller: redirect if Event ID missing or FIx if controller Name is written in Lowercase
            if(  array_key_exists( "controller" , $_gp['tx_jvevents_events'] ) && strtolower( $_gp['tx_jvevents_events']['controller'] )  == "registrant" ) {
                $action =  array_key_exists( "action" , $_gp['tx_jvevents_events'] ) ? $_gp['tx_jvevents_events']['action'] : "" ;
                if ( in_array($action , ['create' , 'list' , 'checkQrcode']) && (int)$_gp['tx_jvevents_events']['event'] < 0 ) {
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
            if( $function != "activateXX"  ) {
                $GLOBALS['TSFE']->set_no_cache();
                    /** @var AjaxUtility $ajaxUtility */
                $ajaxUtility = GeneralUtility::makeInstance('JVE\JvEvents\Utility\AjaxUtility') ;

                // ToDo generate Output as before in ajax Controller here in Middleware with CORE features.
                $controller = $ajaxUtility->initController($_gp , $function ) ;
                $controller->initializeRepositorys() ;

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
                        $controller->eventListAction($_gp["tx_jvevents_ajax"]) ;
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
                    default:
                        $controller->eventMenuAction($_gp["tx_jvevents_ajax"]) ;
                        break;
                }







            }
        }
        return $handler->handle($request);
    }




}
