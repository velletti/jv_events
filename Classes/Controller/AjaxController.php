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
use \TYPO3\CMS\Core\Core\Bootstrap;
use \TYPO3\CMS\Core\Utility\ArrayUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Frontend\Utility\EidUtility;/**/


/**
 * AjaxController
 */
class AjaxController extends BaseController
{

    /**
     * eventRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository ;

    /**
     * @var array
     */
    protected $user ;

    /**
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public $tsFEController ;

    public function dispatcher() {

        /**
         * Gets the Ajax Call Parameters
         */
        $_gp = \TYPO3\CMS\Core\Utility\GeneralUtility::_GPmerged('tx_jvevents_ajax');
        $pid = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GPmerged('uid') );
        $type =  intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GPmerged('type'));

        $ajax = array();
        $ajax['arguments']	= $_gp;
        $ajax['vendor'] 	= 'JVE';
        $ajax['extensionName'] 	= 'JvEvents';
        $ajax['pluginName'] 	= 'Events';
        $ajax['controller'] 	= 'Ajax';
        $ajax['action'] 	= $_gp['action'] ;

        /**
         * @var $TSFE \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
         */
        $TSFE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'],
            $pid,  // pageUid Homepage
            $type   // pageType
        );
        $GLOBALS['TSFE'] = $TSFE;


// Important: no Cache for Ajax stuff
        $GLOBALS['TSFE']->set_no_cache();

        \TYPO3\CMS\Frontend\Utility\EidUtility::initLanguage();
        \TYPO3\CMS\Frontend\Utility\EidUtility::initTCA();
// Get FE User Information
        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->initUserGroups();
        $GLOBALS['TSFE']->fe_user ;

        $GLOBALS['TSFE']->checkAlternativeIdMethods();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();
        \TYPO3\CMS\Core\Core\Bootstrap::getInstance();

        $GLOBALS['TSFE']->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        $GLOBALS['TSFE']->settingLanguage();
        $GLOBALS['TSFE']->settingLocale();

        /**
         * Initialize Backend-User (if logged in)
         */
        // $GLOBALS['BE_USER'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Authentication\BackendUserAuthentication');
        // $GLOBALS['BE_USER']->start();

        /**
         * Initialize Database
         */
        $GLOBALS['TSFE']->connectToDB();

        /**
         * @var $objectManager \TYPO3\CMS\Extbase\Object\ObjectManager
         */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        /**
         * Initialize Extbase bootstap
         */
        $bootstrapConf['extensionName'] = $ajax['extensionName'];
        $bootstrapConf['pluginName']	= $ajax['pluginName'];

        $bootstrap = new \TYPO3\CMS\Extbase\Core\Bootstrap();
        $bootstrap->initialize($bootstrapConf);
        $bootstrap->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');

        /**
         * Build the request
         */
        $request = $objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Request');

        $request->setControllerVendorName($ajax['vendor']);
        $request->setcontrollerExtensionName($ajax['extensionName']);
        $request->setPluginName($ajax['pluginName']);
        $request->setControllerName($ajax['controller']);
        $request->setControllerActionName($ajax['action']);
        $request->setArguments($ajax['arguments']);


//$ajaxDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Nng\Nnsubscribe\Controller\EidController');
//echo $ajaxDispatcher->processRequestAction();
        $response = $objectManager->get('TYPO3\CMS\Extbase\Mvc\ResponseInterface');
        $dispatcher = $objectManager->get('TYPO3\CMS\Extbase\Mvc\Dispatcher');
        $dispatcher->dispatch($request, $response);

        echo $response->getContent();

        die;
    }


	public function initializeAction() {
		parent::initializeAction() ;
	}
    /**
     * action list
     *
     * @return void
     */
    public function eventMenuAction()
    {
        // ToDO replace Hardcoded Feuser ...
        $feuser = $GLOBALS['TSFE']->fe_user->user['uid'] ;
        $output = array (
            "requestId" =>  $GLOBALS['TSFE']->id ,
            "event" => array()  ,
            "feuser" => array(
                "uid" => $GLOBALS['TSFE']->fe_user->user['uid'] ,
                "username" => $GLOBALS['TSFE']->fe_user->user['username'] ,
                "usergroup" => $GLOBALS['TSFE']->fe_user->user['usergroup']
            )  ,
            "organizer" => array() ,
            "location" => array() ,

        ) ;
        if( $this->request->hasArgument('event')) {
            $output['event']['requestId'] =  $this->request->getArgument('event') ;

            /** @var \JVE\JvEvents\Domain\Model\Event $event */
            $event = $this->eventRepository->findByUidAllpages( $output['requestId'] , FALSE  , TRUE );
            if( is_object($event )) {
                $output['event']['eventId'] = $event->getUid() ;
                if( is_object( $event->getOrganizer() )) {
                    $output['event']['organizerId'] = $event->getOrganizer()->getUid()  ;
                    $users = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $event->getOrganizer()->getAccessUsers() , TRUE ) ;
                    if( in_array( $feuser  , $users )) {

                        $output['event']['hasAccess'] = TRUE  ;
                    }
                }
                if( is_object( $event->getLocation() )) {
                    $output['event']['locationId'] = $event->getLocation()->getUid() ;
                }
            }
        }
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
        $renderer = $this->getEmailRenderer($templatePath = '', '/Ajax/EventMenu' );
        $layoutPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName("typo3conf/ext/jv_events/Resources/Private/Layouts/");
        $renderer->setLayoutRootPaths(array(0 => $layoutPath));


        $renderer->assign('layout' , 'Ajax') ;
        $renderer->assign('output' , $output) ;
        $return = str_replace( array( "\n" , "\r" , "\t" , "    " , "   " , "  ") , array("" , "" , "" , " " , " " , " " ) , trim( $renderer->render() )) ;
        $this->showArrayAsJson( array( 'values' => $output , 'html' => $return ) ) ;
        die;
    }
    /**
     * @param $output
     */
    public function showArrayAsJson($output) {
        $jsonOutput = json_encode($output);
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($jsonOutput));
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Transfer-Encoding: 8bit');

        $callbackId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("callback");
        if ( $callbackId == '' ) {
            echo $jsonOutput;
        } else {
            echo $callbackId . "(" . $jsonOutput . ")";
        }

        die();
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
		$formClass =  $this->objectManager->get( "TYPO3\\CMS\\Core\\FormProtection\\FrontendFormProtection") ;

		return $formClass->generateToken(
			'event', $action ,   "P" . $this->settings['pageId'] . "-L" .$this->settings['sys_language_uid']
		);

	}

}