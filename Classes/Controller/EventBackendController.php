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

use JVelletti\JvEvents\Domain\Repository\RegistrantRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Location;
use JVelletti\JvEvents\Domain\Model\Registrant;
use JVelletti\JvEvents\Utility\TyposcriptUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\FormProtection\FrontendFormProtection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use JVelletti\JvEvents\Utility\RegisterHubspotUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;

/**
 * EventBackendController
 */
class EventBackendController extends BaseController
{

    /**
     * registrantRepository
     *
     * @var RegistrantRepository
     */
    protected $registrantRepository = NULL;

    /**
     * @var RegisterHubspotUtility
     */
    protected RegisterHubspotUtility $hubspot  ;

    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected PageRenderer $pageRenderer;
    protected BackendUserAuthentication $backendUser;

    public function __construct(
       RegistrantRepository $registrantRepository,
       ModuleTemplateFactory $moduleTemplateFactory,
       PageRenderer $pageRenderer,
       BackendUserAuthentication $backendUser
    ) {
        $this->registrantRepository = $registrantRepository;
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->pageRenderer = $pageRenderer;
        $this->backendUser = $backendUser;
    }


	public function initializeAction() {
       // This works only, if moduleTemplateFactory is used for view => see action function(s)
       $this->pageRenderer->addCssFile('EXT:jv_events/Resources/Public/Css/backendModule.css', 'stylesheet', 'all', '', false);
       // $this->pageRenderer->loadJavaScriptModule('@peterBenke/pbNotifications/Notifications.js');


        // $this->hubspot = GeneralUtility::makeInstance(RegisterHubspotUtility::class);
        // parent::initializeAction() ;
	}
    /**
     * action list
     * http://master.dev/typo3/index.php?M=web_JvEventsEventmngt&moduleToken=0659e7b6ef0e5e1632d32734a03e2141563302d5
     *
     * @return void
     */
    public function listAction(): ResponseInterface
    {
        $view = $this->moduleTemplateFactory->create($this->request);

        $pageId = 0 ;
        if ( $this->request->hasArgument('id') ) {
            $pageId = $this->request->getArgument("id") ;
            $pageRow = BackendUtility::getRecord(
               'pages',
               $pageId,
               '*'
            );
/*
            if (!$this->backendUser->doesUserHaveAccess($pageRow, 1)) {
                $view->assign('error', 'No access to this page');
                return $view->renderResponse('/EventBackend/List.html');
            }
*/
        }


        $view->setTitle('Event Management');
        $view->setModuleName("web_JvEventsEventmngt");
        $view->setModuleId("jvevents_eventmngt");

        $eventID = null;
        $events = [];
        $itemsPerPage = 20 ;
        $pageId = GeneralUtility::_GP('id');
        $recursive = false ;

        if( $this->request->hasArgument('recursive')) {
            $recursive = $this->request->getArgument('recursive') ;
        }
        $currentPage = 1;
        if( $this->request->hasArgument('currentPage')) {
            $currentPage = $this->request->getArgument('currentPage') ;

        }
        $onlyActual = -999 ;
        if( $this->request->hasArgument('onlyActual') && $this->request->getArgument('onlyActual') ) {
            $onlyActual = $this->request->getArgument('onlyActual') ;

        }
        if ( $recursive ) {
            $this->settings['storagePids'] = $this->registrantRepository->getTreeList($pageId, 9999, 0, 1) ;
        }

        $this->settings['filter']['startDate']  = $onlyActual ;
        $this->settings['storagePid'] = $pageId ;


        $this->settings['directmail'] = FALSE  ;
        $eventID = 0 ;
        if( $this->request->hasArgument('event')) {
            $eventID = $this->request->getArgument('event') ;
            if( $eventID > 0 ) {
                $itemsPerPage = 100 ;
            }
        }
        if (ExtensionManagementUtility::isLoaded('direct_mail')) {
            $this->settings['directmail'] = TRUE ;
        }
        /** @var QueryResultInterface $events */
        $registrants = $this->registrantRepository->findByFilter($email, $eventID, $pageId ,  $this->settings , 999 );
        $events = [] ;

        $view->assign('event', $eventID);
        $view->assign('itemsPerPage', $itemsPerPage);
        $view->assign('events', $events);
        $view->assign('registrants', $registrants);
        $view->assign('settings', $this->settings);
        $view->assign('onlyActual', $onlyActual);
        $view->assign('currentPage', $currentPage);
        $view->assign('recursive', $recursive);
        $view->assign('pageId', $pageId );
        return $view->renderResponse('/EventBackend/List.html');
    }

}