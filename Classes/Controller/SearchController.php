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

use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * EventController
 */
class SearchController extends BaseController
{

    /**
     * eventRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\EventRepository
     */
    protected $eventRepository = NULL;

    /**
     * @var array
     */
    public $debugArray ;


    /**
     * @var float
     */
    public $timeStart ;


	public function initializeAction() {
        $this->timeStart = $this->microtime_float() ;

	    $this->debugArray[] = "Start:" . intval(1000 * $this->timeStart ) . " Line: " . __LINE__ ;

        parent::initializeAction() ;
        $this->debugArray[] = "Init Done:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
	}
    /**
     * action search
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @return void
     */
    public function searchAction()
    {

        // @param \JVE\JvEvents\Domain\Model\Filter $filter

        $this->debugArray[] = "After Init :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;

        $this->debugArray[] = "Load :" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events */
        // $events = $this->eventRepository->findByFilter(false, false,  $this->settings );

        $this->view->assign('settings', $this->settings );
        $this->debugArray[] = "before Render:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
        $this->view->assign('debugArray', $this->debugArray );

    }
    

}