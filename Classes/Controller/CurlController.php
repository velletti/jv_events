<?php
namespace JVE\JvEvents\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Jörg Velletti <jVelletti@allplan.com>, Allplan GmbH
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

use JVE\JvBanners\Utility\AssetUtility;
use JVE\JvEvents\Domain\Model\Event;
use JVE\JvEvents\Domain\Model\Location;
use JVE\JvEvents\Domain\Model\Organizer;
use JVE\JvEvents\Domain\Repository\BannerRepository;
use JVE\JvEvents\Utility\SlugUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * EventController
 */
class CurlController extends BaseController
{


	public function initializeAction() {
        $this->timeStart = $this->microtime_float() ;

	    $this->debugArray[] = "Start:" . intval(1000 * $this->timeStart ) . " Line: " . __LINE__ ;
        parent::initializeAction() ;
        $this->debugArray[] = "Init Done:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
	}

    /**
     * action list
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function externalEventsAction()
    {
        $this->debugArray[] = "After Init :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " Line: " . __LINE__ ;
        $this->settings['filter']['distance']['doNotOverrule'] = "false" ;
        //  'https://tangov10.ddev.site/?id=110&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax
        //  &tx_jvevents_ajax[eventsFilter][categories]=1&tx_jvevents_ajax[eventsFilter][startDate]=0&tx_jvevents_ajax[mode]=onlyJson'
        //  'https://tangov10.ddev.site/?id=110&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=1&tx_jvevents_ajax[eventsFilter][startDate]=0&tx_jvevents_ajax[mode]=onlyJson'

        $this->settings['data'] = [ "id" => 150 ] ;
        $this->settings['data']['tx_jvevents_ajax'] = [ "action" => 'eventList' , 'controller' => 'Ajax' , 'mode' => 'onlyJson'] ;

        $f["startDate"] = $this->settings['filter']["startDate"] ;
        if(  $this->settings['filter']["organizers"] ) {
            $f["organizers"] = $this->settings['filter']["organizers"] ;
        }
        if(  $this->settings['filter']["categories"] ) {
            $f["categories"] = $this->settings['filter']["categories"] ;
        }

        $this->settings['data']['tx_jvevents_ajax']['eventsFilter'] = $f  ;





        $this->debugArray[] = "Load Events:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
        /** @var QueryResultInterface $events */

        $events = $this->getEventsViaCurl($this->settings );
        var_dump($events);
        die;
        $this->view->assign('events', $events);

        $dtz = $this->eventRepository->getDateTimeZone() ;

       // $this->settings['checkInstallation'] = 2 ;
        $this->view->assign('settings', $this->settings );
        $this->debugArray[] = "before Render:" . intval(1000 * ($this->microtime_float()  - $this->timeStart ) ) . " Line: " . __LINE__ ;
        $this->view->assign('debugArray', $this->debugArray );

        // overruleFilterStartDate Nnext

    }

    public function getEventsViaCurl( $settings ) {

        //  'https://tangov10.ddev.site/?id=110&tx_jvevents_ajax[action]=eventList&tx_jvevents_ajax[controller]=Ajax&tx_jvevents_ajax[eventsFilter][categories]=1&tx_jvevents_ajax[eventsFilter][startDate]=0&tx_jvevents_ajax[mode]=onlyJson'
        https://www.tangomuenchen.deid=150&tx_jvevents_ajax%5Baction%5D=eventList&tx_jvevents_ajax%5Bcontroller%5D=Ajax&tx_jvevents_ajax%5Bmode%5D=onlyJson&tx_jvevents_ajax%5BeventsFilter%5D%5BstartDate%5D=-1&tx_jvevents_ajax%5BeventsFilter%5D%5Bcategories%5D=3
        $url = trim( $settings["externalUrl"] )  . http_build_query( $settings['data']) ;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }

    // ########################################   functions ##################################
	/**
	 * helper for Formvalidation
	 * @param string $action
	 * @return string
	 */
	public function generateToken($action = "action"): string
    {
		/** @var \TYPO3\CMS\Core\FormProtection\FrontendFormProtection $formClass */
		$formClass =  $this->objectManager->get( \TYPO3\CMS\Core\FormProtection\FrontendFormProtection::class) ;

		return $formClass->generateToken(
			'event', $action ,   "P" . $this->settings['pageId'] . "-L" .$this->settings['sys_language_uid']
		);

	}


}