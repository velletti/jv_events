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
use \TYPO3\CMS\Core\Utility\ArrayUtility;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \JVE\JvEvents\Utility\ShowAsJsonArrayUtility;
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
     * locationRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\LocationRepository
     * @inject
     */
    protected $locationRepository = NULL;


    /**
     * organizerRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\OrganizerRepository
     * @inject
     */
    protected $organizerRepository = NULL;


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
        $_gp = GeneralUtility::_GPmerged('tx_jvevents_ajax');
        $pid = intval(GeneralUtility::_GPmerged('uid') );
        $type =  intval(GeneralUtility::_GPmerged('type'));

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
        $TSFE = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController', $GLOBALS['TYPO3_CONF_VARS'],
            $pid,  // pageUid Homepage
            $type   // pageType
        );
        $GLOBALS['TSFE'] = $TSFE;


// Important: no Cache for Ajax stuff
        $GLOBALS['TSFE']->set_no_cache();

        EidUtility::initLanguage();
        EidUtility::initTCA();
// Get FE User Information
        $GLOBALS['TSFE']->initFEuser();
        $GLOBALS['TSFE']->initUserGroups();
        $GLOBALS['TSFE']->fe_user ;

        $GLOBALS['TSFE']->checkAlternativeIdMethods();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();
        \TYPO3\CMS\Core\Core\Bootstrap::getInstance();

        $GLOBALS['TSFE']->cObj = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
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
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        /**
         * Initialize Extbase bootstap
         */
        $bootstrapConf['extensionName'] = $ajax['extensionName'];
        $bootstrapConf['pluginName']	= $ajax['pluginName'];

        $bootstrap = new \TYPO3\CMS\Extbase\Core\Bootstrap();
        $bootstrap->initialize($bootstrapConf);
        $bootstrap->cObj = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');

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
    public function eventsListMenuSub()
    {
        /* ************************************************************************************************************ */
        /*   Prepare the Output :
        /* ************************************************************************************************************ */
        $feuser = intval(  $GLOBALS['TSFE']->fe_user->user['uid']) ;
        $output = array (
            "requestId" =>  intval( $GLOBALS['TSFE']->id ) ,
            "event" => array()  ,
            "events" => array()  ,
            "eventsFilter" => array()  ,
            "feuser" => array(
                "uid" => $GLOBALS['TSFE']->fe_user->user['uid'] ,
                "username" => $GLOBALS['TSFE']->fe_user->user['username'] ,
                "usergroup" => $GLOBALS['TSFE']->fe_user->user['usergroup']
            )  ,
            "organizer" => array() ,
            "location" => array() ,

        ) ;

        /* ************************************************************************************************************ */
        /*   Get infos about: EVENT
        /* ************************************************************************************************************ */

        if( $this->request->hasArgument('event')) {
            $output['event']['requestId'] =  intval( $this->request->getArgument('event') ) ;

            /** @var \JVE\JvEvents\Domain\Model\Event $event */
            $event = $this->eventRepository->findByUidAllpages( $output['event']['requestId'] , FALSE  , TRUE );
            if( is_object($event )) {
                $output['event']['eventId'] = $event->getUid() ;
                $output['event']['registration']['possible'] = $event->isIsRegistrationPossible() ;
                $output['event']['registration']['noFreeSeats'] = $event->isIsNoFreeSeats() ;
                $output['event']['registration']['freeSeats'] = $event->getAvailableSeats() ;

                if( is_object( $event->getOrganizer() )) {
                    $organizer = $event->getOrganizer() ;
                    $output['event']['organizerId'] = $organizer->getUid()  ;
                    $output['event']['organizerEmail'] = $organizer->getEmail()  ;
                    $output['event']['hasAccess'] = $this->hasUserAccess( $organizer ) ;
                }
                if( is_object( $event->getLocation() )) {

                    $location = $event->getLocation() ;
                    $output['event']['locationId'] = $event->getLocation()->getUid() ;
                }
            }
        }



        if( $this->request->hasArgument('eventsFilter')) {
            $limit = false ;
            if( $this->request->hasArgument('limit')) {
                $limit = $this->request->getArgument('limit') ;
            }
            $output['eventsFilter'] = $this->request->getArgument('eventsFilter') ;

            if ( $output['eventsFilter']['sameCity'] ) {
                $output['eventsFilter']['citys'] = $output['event']['locationId']  ;
            }


            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events */
            $events = $this->eventRepository->findByFilter( $output['eventsFilter'], $limit,  $this->settings );
            if( is_object( $events )) {
                /** @var \JVE\JvEvents\Domain\Model\Event $tempEvent */
                $tempEvent =  $events->getFirst() ;
                if( is_object( $tempEvent )) {
                    $tempEventArray['uid'] = $tempEvent->getUid();
                    $tempEventArray['name'] = $tempEvent->getName();
                    $tempEventArray['startDate'] = $tempEvent->getStartDate();
                    $tempEventArray['teaser'] = $tempEvent->getTeaser();

                    if (is_object($tempEvent->getLocation())) {
                        $tempEventArray['LocationCity'] = $tempEvent->getLocation()->getCity();
                    }

                    $output['events'][] = $tempEventArray;
                }
            }

        }
        /* ************************************************************************************************************ */
        /*   Get infos about: Location
        /* ************************************************************************************************************ */
        if( $this->request->hasArgument('location')) {
            $output['location']['requestId'] = $this->request->getArgument('location');

            /** @var \JVE\JvEvents\Domain\Model\Event $event */
            $location = $this->locationRepository->findByUidAllpages($output['location']['requestId'], FALSE, TRUE);

        }

        // Location is set either by Event OR by location uid from request
        if( is_object($location )) {
            $output['location']['locationId'] = $location->getUid() ;

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
        if( $this->request->hasArgument('organizer')) {
            $output['organizer']['requestId'] = $this->request->getArgument('organizer');

            /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
            $organizer = $this->organizerRepository->findByUidAllpages($output['organizer']['requestId'], FALSE, TRUE);
        }

        // Location is set either by Event OR by location uid from request
        if( is_object($organizer )) {
            $output['organizer']['organizerId'] = $organizer->getUid() ;
            $output['organizer']['hasAccess'] = $this->hasUserAccess( $organizer ) ;


        }
        return  $output  ;

    }



    /**
     * action list
     *
     * @return void
     */
    public function eventListAction()
    {

        //  https://www.allplan.com.ddev.local/index.php?uid=82&eID=jv_events&L=0&tx_jvevents_ajax[event]=94&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=14&tx_jvevents_ajax[eventsFilter][sameCity]=1&tx_jvevents_ajax[eventsFilter][skipEvent]=&tx_jvevents_ajax[eventsFilter][startDate]=30&&tx_jvevents_ajax[rss]=1

        $output = $this->eventsListMenuSub() ;



        /* ************************************************************************************************************ */
        /*   render the HTML Output :
        /* ************************************************************************************************************ */

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
        $renderer = $this->getEmailRenderer($templatePath = '', '/Ajax/EventListRss' );
        $layoutPath = GeneralUtility::getFileAbsFileName("typo3conf/ext/jv_events/Resources/Private/Layouts/");

        $renderer->setLayoutRootPaths(array(0 => $layoutPath));


        $renderer->assign('output' , $output) ;
        $renderer->assign('settings' , $this->settings ) ;
        $return = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" .  trim( $renderer->render() ) ;

        if( $this->request->hasArgument('rss')) {
            header_remove();
        //    http_response_code(200);
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
    public function eventMenuAction()
    {
        $output = $this->eventsListMenuSub() ;

        /* ************************************************************************************************************ */
        /*   render the HTML Output :
        /* ************************************************************************************************************ */

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
        $renderer = $this->getEmailRenderer($templatePath = '', '/Ajax/EventMenu' );
        $layoutPath = GeneralUtility::getFileAbsFileName("typo3conf/ext/jv_events/Resources/Private/Layouts/");
        $renderer->setLayoutRootPaths(array(0 => $layoutPath));

        $renderer->assign('output' , $output) ;
        $renderer->assign('settings' , $this->settings ) ;
        $return = str_replace( array( "\n" , "\r" , "\t" , "    " , "   " , "  ") , array("" , "" , "" , " " , " " , " " ) , trim( $renderer->render() )) ;
        ShowAsJsonArrayUtility::show( array( 'values' => $output , 'html' => $return ) ) ;
        die;
    }


    /**
     * @param \JVE\JvEvents\Domain\Model\Organizer | \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy  $organizer
     * @return bool
     */


    public function hasUserAccess( $organizer ) {

        $feuserUid = intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) ;
        $users = GeneralUtility::trimExplode("," , $organizer->getAccessUsers() , TRUE ) ;
        if( in_array( $feuserUid  , $users )) {
           return TRUE  ;
        } else {
            $groups = GeneralUtility::trimExplode("," , $organizer->getAccessGroups() , TRUE ) ;
            $feuserGroups = GeneralUtility::trimExplode("," ,  $GLOBALS['TSFE']->fe_user->user['usergroup']  , TRUE ) ;
            foreach( $groups as $group ) {
                if( in_array( $group  , $feuserGroups )) {
                    return TRUE  ;
                }
            }
        }
        return FALSE  ;
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