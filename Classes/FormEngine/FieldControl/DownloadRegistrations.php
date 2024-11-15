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
use JVelletti\JvEvents\Utility\EmConfigurationUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds a Link to see event in Frontend
 */
class DownloadRegistrations extends AbstractNode
{
    /**
     * @return array
     */
    public function render(): array
    {

        $paramArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();
        $title = "Download Registrations Event as CSV" ;
        $resultArray['title'] = "Download Registrations" ;
        $resultArray['iconIdentifier'] = "actions-download" ;
        $resultArray['linkAttributes']['class'] = " " ;

        $configuration = EmConfigurationUtility::getEmConf();
        $singlePid = ($this->data['databaseRow']['registration_form_pid'][0]['uid'] ?? 0 ) ;

        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($singlePid);
            $serverFromSite =  $site->getBase()->getHost() ;

            $lang = max(  is_array( $this->data['databaseRow']['sys_language_uid'][0] ) ?
             $this->data['databaseRow']['sys_language_uid'][0] : $this->data['databaseRow']['sys_language_uid'] , 0 ) ;
            $checkString =  $serverFromSite . "-" . $this->data['databaseRow']['uid'] . "-" . $this->data['databaseRow']['crdate'] ;
            $checkHash = GeneralUtility::hmac ( $checkString ) ;
            // pid = 0 to load registrations from all pages for that event
            $url = (string)$site->getRouter()->generateUri( $singlePid ,['_language' => $lang ,
                'tx_jvevents_registrant' => ['action' => 'list' , 'controller' => 'Registrant' ,'event' =>  $this->data['databaseRow']['uid']
                    , 'export' => '1' ,  'pid' => '0' ,  'hash' => $checkHash  ]]);

            $resultArray['linkAttributes']['class'] = "showEventInFrontend windowOpenUri" ;
            $resultArray['linkAttributes']['data-uri'] = $url ;
            $resultArray['requireJsModules'][] = JavaScriptModuleInstruction::forRequireJS('showEventInFrontend.js'
            )->instance($paramArray['itemFormElName']);
            
        } catch (\Exception) {

            $resultArray['linkAttributes']['data-uri'] = '' ;
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
