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
        $arguments = $request->getQueryParams();
        if( is_array($arguments) && key_exists("tx_jvevents_ajax" ,$arguments ) && key_exists("controller" ,$arguments['tx_jvevents_ajax'] ) ) {
            $GLOBALS['TSFE']->set_no_cache();

            /**
             * Gets the Ajax Call Parameters
             */
            $_gp = GeneralUtility::_GPmerged('tx_jvevents_ajax');
            $function = trim($_gp['action']) ;

            /** @var AjaxUtility $ajaxUtility */
            $ajaxUtility = GeneralUtility::makeInstance('JVE\JvEvents\Utility\AjaxUtility') ;

            // ToDo generate Output as before in ajax Controller here in Middleware with CORE features.
            $controller = $ajaxUtility->initController($_gp , $function ) ;
            $controller->initializeRepositorys() ;

            $function .= "Action";

            switch ($function) {
                case 'eventList' :
                    $controller->eventListAction($_gp) ;
                    break;
                case 'locationList' :
                    $controller->locationListAction($_gp) ;
                    break;
                case 'activate' :
                    // $organizerUid=0 , $userUid=0 , $hmac='invalid' , $rnd = 0
                    $controller->activateAction( $_gp['organizerUid'] , $_gp['userUid'] , $_gp['hmac'] , $_gp['rnd'] ) ;
                    break;
                case 'eventUnlink' :
                    $controller->eventUnlinkAction($_gp) ;
                    break;
                default:
                    $controller->eventMenuAction($_gp) ;
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
