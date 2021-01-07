<?php

namespace JVE\JvEvents\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

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
            $function = $_gp['action'] ;

            /*             * check if action is allowed            */
            if (!in_array($function, array("eventMenu", "eventList", "locationList", "activate", "eventUnlink"))) {
                $function = "eventMenu";
            }


            // ToDo generate Output as before in ajax Controller here in Middleware with CORE features.
            /** @var \JVE\JvEvents\Controller\AjaxController $controller */
            $controller = GeneralUtility::makeInstance('JVE\JvEvents\Controller\AjaxController' ) ;


            $controller = $this->initController($_gp , $controller , $function ) ;
            $function .= "Action";
            $controller->$function()  ;

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

    /**
     * @param array $_gp
     * @param \JVE\JvEvents\Controller\AjaxController $controller
     * @return \JVE\JvEvents\Controller\AjaxController
     */
    private function initController(array $_gp , \JVE\JvEvents\Controller\AjaxController $controller , $function ) {
        $controller->initializeAction() ;
        /* First: move request parameter to Controller variables */
        if( key_exists('mode' , $_gp)) {
            $controller->mode = $_gp['mode'] ;
        }
        if( key_exists('returnPid' , $_gp)) {
            $controller->returnPid = intval($_gp['returnPid']) ;
        }
        if( key_exists('event' , $_gp)) {
            $controller->event = intval( $_gp['event'] ) ;
        }
        if( key_exists('eventsFilter' , $_gp)) {
            $controller->eventsFilter = $_gp['eventsFilter'] ;
        }
        if( key_exists('limit' , $_gp)) {
            $controller->limit = intval( $_gp['limit']) ;
        }
        if( key_exists('location' , $_gp)) {
            $controller->location = intval( $_gp['location']) ;
        }
        if( key_exists('organizer' , $_gp)) {
            $controller->organizer = intval( $_gp['organizer']) ;
        }

        if( key_exists('rss' , $_gp)) {
            $controller->rss = $_gp['rss'] ;
        }
        $controller->standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $controller->standaloneView->getRenderingContext()->setControllerName('Ajax');
        $controller->standaloneView->getRenderingContext()->setControllerAction($function);
        $controller->standaloneView->getRequest()->setControllerExtensionName('jvEvents');

        $controller->standaloneView->setTemplateRootPaths(array( 0 => ExtensionManagementUtility::extPath('jv_events') . 'Resources/Private/Templates') );
        $controller->standaloneView->setLayoutRootPaths(array( 0 => ExtensionManagementUtility::extPath('jv_events') . 'Resources/Private/Layouts'  ));
        $controller->standaloneView->setPartialRootPaths(array( 0 => ExtensionManagementUtility::extPath('jv_events') . 'Resources/Private/Partials' ));

        return $controller ;
    }


}
