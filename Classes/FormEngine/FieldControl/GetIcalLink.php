<?php
declare(strict_types = 1);

namespace JVE\JvEvents\FormEngine\FieldControl;

/**
 * This file is part of the "jv_events" Extension for TYPO3 CMS.
 * and based to 99% on the work of "tt_address" Extension of friendsoftypo3
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds a link to doewnload ICAL File
 */
class GetIcalLink extends AbstractNode
{
    /**
     * @return array
     */
    public function render(): array
    {

        $paramArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();
        $title = $this->getLanguageService()->sL('LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:jv_events_model_location.geocoder.title');
        $singlePid = 111 ;
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($singlePid);

        $url = (string)$site->getRouter()->generateUri( $singlePid ,['_language' => max( $this->data['databaseRow']['sys_language_uid'][0],0 ) ,
                                                      'tx_jvevents_ajax' => ['action' => 'downloadical' , 'controller' => 'Ajax' ,'uid' =>  $this->data['databaseRow']['uid']]]);

        $resultArray['title'] = "Download Ical File" ;
        $resultArray['iconIdentifier'] = "actions-calendar" ;
        $resultArray['linkAttributes']['class'] = "getIcalLink windowOpenUri btn-primary" ;
        $resultArray['linkAttributes']['data-uri'] = $url ;
        $resultArray['requireJsModules'][] = 'TYPO3/CMS/JvEvents/ShowEventInFrontend' ;


        return $resultArray;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
