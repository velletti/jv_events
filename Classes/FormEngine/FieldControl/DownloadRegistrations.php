<?php
declare(strict_types=1);

namespace JVelletti\JvEvents\FormEngine\FieldControl;

use JVelletti\JvEvents\Utility\EmConfigurationUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Crypto\HashService;

class DownloadRegistrations extends AbstractNode
{
    public function render(): array
    {
        $paramArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();

        $resultArray['title'] = 'Download Registrations';
        $resultArray['iconIdentifier'] = 'actions-download';
        $resultArray['linkAttributes']['class'] = '';

        $configuration = EmConfigurationUtility::getEmConf();
        $singlePid = ($this->data['databaseRow']['registration_form_pid'][0]['uid'] ?? 0);

        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($singlePid);
            $serverFromSite = $site->getBase()->getHost();

            $lang = max(
                is_array($this->data['databaseRow']['sys_language_uid'][0])
                    ? $this->data['databaseRow']['sys_language_uid'][0]
                    : $this->data['databaseRow']['sys_language_uid'],
                0
            );

            $checkString = $serverFromSite . '-' .
                $this->data['databaseRow']['uid'] ;

            $checkHash = GeneralUtility::makeInstance(HashService::class)
                ->hmac($checkString, "-" . $this->data['databaseRow']['crdate']);

            $url = (string)$site->getRouter()->generateUri(
                $singlePid,
                [
                    '_language' => $lang,
                    'tx_jvevents_registrant' => [
                        'action' => 'list',
                        'controller' => 'Registrant',
                        'event' => $this->data['databaseRow']['uid'],
                        'export' => '1',
                        'pid' => '0',
                        'hash' => $checkHash
                    ]
                ]
            );

            $resultArray['linkAttributes']['class'] = 'showEventInFrontend windowOpenUri';
            $resultArray['linkAttributes']['data-uri'] = $url;

            $resultArray['javaScriptModules'][] =  JavaScriptModuleInstruction::create('showEventInFrontend.js');
        } catch (\Exception) {
            $resultArray['linkAttributes']['data-uri'] = '';
        }

        return $resultArray;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}