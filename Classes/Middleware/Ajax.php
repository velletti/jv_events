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
    ): ResponseInterface {
        $_gp = $request->getQueryParams();
        // examples:
        // https://wwwv10.allplan.com.ddev.site/?id=110&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues
        // https://tangov10.ddev.site/?id=110&L=1&tx_jvevents_ajax[event]=4308&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&tx_jvevents_ajax[mode]=onlyValues

        if( is_array($_gp) && key_exists("tx_jvevents_ajax" ,$_gp ) && key_exists("controller" ,$_gp['tx_jvevents_ajax'] ) ) {
            $GLOBALS['TSFE']->set_no_cache();

            $function = trim($_gp['action']) ;

            /** @var AjaxUtility $ajaxUtility */
            $ajaxUtility = GeneralUtility::makeInstance('JVE\JvEvents\Utility\AjaxUtility') ;

            $controller = $ajaxUtility->initController($_gp , $function ) ;
            $controller->initializeRepositorys() ;

            $function .= "Action";

            switch ($function) {
                case 'eventList' :
                    $controller->eventListAction($_gp["tx_jvevents_ajax"]) ;
                    break;
                case 'locationList' :
                    $controller->locationListAction($_gp["tx_jvevents_ajax"]) ;
                    break;
                case 'activate' :
                    // $organizerUid=0 , $userUid=0 , $hmac='invalid' , $rnd = 0
                    $organizerUid  =    key_exists( 'organizerUid' , $_gp["tx_jvevents_ajax"]) ?  $_gp["tx_jvevents_ajax"]['organizerUid'] : 0 ;
                    $userUid  =         key_exists( 'userUid' , $_gp["tx_jvevents_ajax"]) ?  $_gp["tx_jvevents_ajax"]['userUid'] : 0 ;
                    $hmac  =            key_exists( 'hmac' , $_gp["tx_jvevents_ajax"]) ?  $_gp["tx_jvevents_ajax"]['hmac'] : 'invalid' ;
                    $rnd  =             key_exists( 'rnd' , $_gp["tx_jvevents_ajax"]) ?  $_gp["tx_jvevents_ajax"]['rnd'] : 0 ;
                    $controller->activateAction( $organizerUid , $userUid , $hmac , $rnd ) ;
                    break;
                case 'eventUnlink' :
                    $controller->eventUnlinkAction($_gp["tx_jvevents_ajax"]) ;
                    break;
                default:
                    $controller->eventMenuAction($_gp["tx_jvevents_ajax"]) ;
                    break;
            }

            die;

/*
            $result = json_encode( $output['data']) ;
            $body = new Stream('php://temp', 'rw');
            $body->write($result);
            return (new Response())
                ->withHeader('content-type', $output['content-type'] . '; charset=utf-8')
                ->withBody($body)
                ->withStatus($output['status']);
*/
        }
        return $handler->handle($request);
    }




}
