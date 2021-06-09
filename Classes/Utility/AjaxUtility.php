<?php


namespace JVE\JvEvents\Utility;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class AjaxUtility
 * @package JVE\JvEvents\Utility
 */
class AjaxUtility {


    /**
     * @param array $_gp
     * @return \JVE\JvEvents\Controller\AjaxController
     */
    public function initController(array $_gp , $function ) {
        /** @var \JVE\JvEvents\Controller\AjaxController $controller */
        $controller = GeneralUtility::makeInstance('JVE\JvEvents\Controller\AjaxController' ) ;

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