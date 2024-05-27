<?php
declare(strict_types = 1);

namespace JVelletti\JvEvents\FormEngine\FieldControl;

/**
 * This file is part of the "jv_events" Extension for TYPO3 CMS.
 * and based to 99% on the work of "tt_address" Extension of friendsoftypo3
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds a Link to see event in Frontend
 */
class ShowEventInFrontend extends AbstractNode
{
    /**
     * @return array
     */
    public function render(): array
    {

        $paramArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();
        $title = $this->getLanguageService()->sL('LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:jv_events_model_location.geocoder.title');


        $configuration = \JVelletti\JvEvents\Utility\EmConfigurationUtility::getEmConf();
        $singlePid = ( array_key_exists( 'DetailPid' , $configuration) && $configuration['DetailPid'] > 0 ) ? intval($configuration['DetailPid']) : 111 ;

        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($singlePid);
            $lang = max(  is_array( $this->data['databaseRow']['sys_language_uid'][0] ) ?
                $this->data['databaseRow']['sys_language_uid'][0] : $this->data['databaseRow']['sys_language_uid'] , 0 ) ;

            $url = (string)$site->getRouter()->generateUri( $singlePid ,['_language' => $lang ,
                'tx_jvevents_event' => ['action' => 'show' , 'controller' => 'Event' ,'event' =>  $this->data['databaseRow']['uid']]]);

            $resultArray['title'] = "Show" ;
            $resultArray['iconIdentifier'] = "actions-document-view" ;
            $resultArray['linkAttributes']['class'] = "showEventInFrontend windowOpenUri " ;
            $resultArray['linkAttributes']['data-uri'] = $url ;

            $resultArray['requireJsModules'][] = JavaScriptModuleInstruction::forRequireJS('showEventInFrontend.js'
            )->instance($paramArray['itemFormElName']);
            
        } catch (\Exception $e) {
            $resultArray = [] ;
        }



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
