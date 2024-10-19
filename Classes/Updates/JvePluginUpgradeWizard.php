<?php

declare(strict_types=1);

/*
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace JVelletti\JvEvents\Updates;

use JVelletti\JvEvents\Event\PluginUpdaterListTypeEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('jvePluginUpgradeWizard')]
class JvePluginUpgradeWizard implements UpgradeWizardInterface
{

    private const MIGRATION_SETTINGS = [
        [
            'switchableControllerActions' => 'Event->list;Event->search',
            'targetListType' => 'jvevents_events',
            'defaultOrganizerAction' => '',
            'single' => false ,
            'register' => false ,
        ],
        [
            'switchableControllerActions' => 'Event->show;Event->new;Event->create;Event->edit;Event->update;Event->register;Event->confirm;Event->delete;Registrant->new;Registrant->create;Registrant->list;Registrant->show;Registrant->confirm;Registrant->delete;Registrant->checkQrcode;Registrant->checkQrcode',
            'targetListType' => 'jvevents_registrant',
            'defaultOrganizerAction' => '',
            'single' => true ,
            'register' => true ,
        ],
        [
            'switchableControllerActions' => 'Event->show;Event->new;Event->create;Event->edit;Event->update;Event->register;Event->confirm;Event->delete;Registrant->new;Registrant->create;Registrant->list;Registrant->show;Registrant->confirm;Registrant->delete;Registrant->checkQrcode;Registrant->checkQrcode',
            'targetListType' => 'jvevents_event',
            'defaultOrganizerAction' => '',
            'single' => true ,
            'register' => false ,
        ],
        [
            'switchableControllerActions' => 'Event->new;Event->show;Event->create;Event->edit;Event->update;Event->delete;Event->copy;Event->cancel;',
            'targetListType' => 'jvevents_event',
            'defaultOrganizerAction' => '',
            'register' => false ,
        ],
        [
            'switchableControllerActions' => 'Location->list;Location->show;Location->new;Location->create;Location->edit;Location->update;Location->delete;Location->setDefault;',
            'targetListType' => 'jvevents_locations',
            'defaultOrganizerAction' => '',
            'single' => false ,
            'register' => false ,
        ],
        [
            'switchableControllerActions' => 'Location->list;Location->show;Location->new;Location->create;Location->edit;Location->update;Location->delete;Location->setDefault',
            'targetListType' => 'jvevents_locations',
            'defaultOrganizerAction' => '',
            'single' => false ,
            'register' => false ,
        ],

        [
            'switchableControllerActions' => 'Location->list;Location->show;Location->new;Location->create;Location->edit;Location->update;Location->delete;Location->setDefault;',
            'targetListType' => 'jvevents_location',
            'defaultOrganizerAction' => '',
            'single' => true ,
            'register' => false ,
        ],
        [
            'switchableControllerActions' => 'Location->list;Location->show;Location->new;Location->create;Location->edit;Location->update;Location->delete;',
            'targetListType' => 'jvevents_location',
            'defaultOrganizerAction' => '',
            'single' => true ,
            'register' => false ,
        ],

          [
            'switchableControllerActions' => 'Organizer->assist;Organizer->list;Organizer->activate;Organizer->show;Organizer->new;Organizer->create;Organizer->edit;Organizer->update;Organizer->delete;',
            'targetListType' => 'jvevents_organizer',
            'defaultOrganizerAction' => 'list',
              'single' => true ,
              'register' => false ,
        ],

        [
            'switchableControllerActions' => 'Organizer->assist;Organizer->list;Organizer->activate;Organizer->show;Organizer->new;Organizer->create;Organizer->edit;Organizer->update;Organizer->delete;',
            'targetListType' => 'jvevents_assist',
            'defaultOrganizerAction' => 'assist',
            'single' => true ,
            'register' => false ,
        ],
        [
            'switchableControllerActions' => 'Organizer->assist;Organizer->list;Organizer->show;Organizer->new;Organizer->create;Organizer->edit;Organizer->update;Organizer->delete;',
            'targetListType' => 'jvevents_organizer',
            'defaultOrganizerAction' => 'assist',
            'single' => true ,
            'register' => false ,
        ],
        [
            'switchableControllerActions' => 'Organizer->assist;Organizer->list;Organizer->activate;Organizer->show;Organizer->new;Organizer->create;Organizer->edit;Organizer->update;Organizer->delete;',
            'targetListType' => 'jvevents_organizers',
            'defaultOrganizerAction' => '',
            'single' => false ,
            'register' => false ,
        ],

    ];

    protected array $singlePids = [] ;
    protected array $registerPids = [] ;

    /** @var FlexFormService */
    protected $flexFormService;

    /**
     * @var FlexFormTools
     */
    protected $flexFormTools;

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct()
    {
        $this->flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        $this->flexFormTools = GeneralUtility::makeInstance(FlexFormTools::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    public function getTitle(): string
    {
        return 'EXT:JvEvents: Migrate plugins';
    }

    public function getDescription(): string
    {
        $description = 'The old plugin "Events" using switchableControllerActions has been split into separate plugins. ';
        $description .= 'This update wizard migrates all existing plugin settings and changes the plugin';
        $description .= 'to use the new plugins available. Count of plugins: ' . count($this->getMigrationRecords());
        return $description;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function updateNecessary(): bool
    {
        return $this->checkIfWizardIsRequired();
    }

    public function executeUpdate(): bool
    {
        return $this->performMigration();
    }

    public function checkIfWizardIsRequired(): bool
    {
        return  count($this->getMigrationRecords()) > 0;
    }

    public function performMigration(): bool
    {
        $records = $this->getMigrationRecords();
        if ( isset($_SERVER['argv'][3] ) && str_starts_with(  (string)$_SERVER['argv'][3] , "-v" )) {
            $this->verboseLevel = 64 ;
        }
        if ( isset($_SERVER['argv'][3] ) && str_starts_with(  (string)$_SERVER['argv'][3] , "-vv" )) {
            $this->verboseLevel = 128 ;
        }
        $this->setSinglePids( $records ) ;
        $this->debugOutput( 32,  "\nSinglePids:" . implode( "," , $this->singlePids  ) ) ;

        $this->setRegisterPids( ) ;
        $this->debugOutput( 32,  "\nRegisterPids:" . implode( "," ,  $this->registerPids ) ) ;

        // Initialize the global $LANG object if it does not exist.
        // This is needed by the ext:form flexforms hook in Core v11
        $GLOBALS['LANG'] = $GLOBALS['LANG'] ?? GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');
        $doneRows = 0 ;
        $skippedRows = 0 ;
        foreach ($records as $record) {
            $flexForm = $this->flexFormService->convertFlexFormContentToArray($record['pi_flexform']);
            $targetListType = $this->getTargetListType($flexForm ?? '' , $record['pid']);

            $targetListType = $this->eventDispatcher->dispatch(new PluginUpdaterListTypeEvent($flexForm, $record, $targetListType))->getListType();

            if ($targetListType === '') {
                $this->debugOutput( 0,  "\nPid:" . $record['pid'] . " - Uid:" . $record['uid'] . " Skipped: : " . $flexForm['switchableControllerActions'] ) ;
                $skippedRows ++ ;
                continue;
            }
            $this->debugOutput( 0,  "\nPid:" . $record['pid'] . " - Uid:" . $record['uid'] . " New: " . $targetListType . " from : " . $flexForm['switchableControllerActions'] ) ;
            $doneRows ++  ;
            // Update record with migrated types (this is needed because FlexFormTools
            // looks up those values in the given record and assumes they're up-to-date)
            $record['CType'] = $targetListType;
            $record['list_type'] = '';
            $newFlexform = $record['pi_flexform'] ;


            // Clean up flexform --  does not work ...

            $newFlexform = $this->flexFormTools->cleanFlexFormXML('tt_content', 'pi_flexform', $record);


           $flexFormData = GeneralUtility::xml2array($newFlexform);

           // Remove flexform data which do not exist in flexform of new plugin
           foreach ($flexFormData['data'] as $sheetKey => $sheetData) {
               // Remove empty sheets
               if (!count($flexFormData['data'][$sheetKey]['lDEF']) > 0) {
                   unset($flexFormData['data'][$sheetKey]);
               }
           }
           if (count($flexFormData['data']) > 0) {
               $newFlexform = $this->array2xml($flexFormData);
               $this->updateContentElement($record['uid'], $targetListType, $newFlexform);
           } else {
               $this->debugOutput( 0,  "\nPid:" . $record['pid'] . " - Uid:" . $record['uid'] . " Skipped: " . $targetListType . " Empty Flexform!! : " . $record['pi_flexform'] ) ;
           }

        }
        echo "\n" ;
        echo "\n" ;
        if ( $skippedRows > 0 ) {
            echo "\n" ;
            return false;
        }
        return true ;
    }
    protected function setSinglePids($records) {
        foreach ($records as $record) {
            $flexForm = $this->flexFormService->convertFlexFormContentToArray($record['pi_flexform']);
            $pid = isset( $flexForm['settings']['detailPid'] ) ? $flexForm['settings']['detailPid']  :  0 ;
            if ( !in_array($pid , $this->singlePids ) && (int)$pid > 0 ) {
                $this->singlePids[] = (int)$pid ;
            }
        }
    }

    protected function setRegisterPids()
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $rows = $queryBuilder
            ->select('registration_form_pid' )
            ->from('tx_jvevents_domain_model_event')
            ->groupBy('registration_form_pid')
            ->where(
                $queryBuilder->expr()->gt(
                    'registration_form_pid',
                    $queryBuilder->createNamedParameter(0 , Connection::PARAM_INT)
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();
        foreach ($rows as $row) {
            $this->registerPids[] = $row['registration_form_pid'] ;
        }

    }

    protected function getMigrationRecords(): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll() ;

        $queryBuilder
            ->select('uid', 'pid', 'CType', 'list_type', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('list', Connection::PARAM_STR)
                ),
                $queryBuilder->expr()->like(
                    'list_type',
                    $queryBuilder->createNamedParameter('jvevent%', Connection::PARAM_STR)
                )
            ) ;
        $rows = $queryBuilder->executeQuery()->fetchAllAssociative() ;
        return $rows;
    }

    protected function getTargetListType(array $ff , int $pid ): string
    {
        $this->debugOutput( 33,  "\nPid:" . $pid. " - value: " . $ff['switchableControllerActions'] ) ;
        if ( isset( $ff['settings.v12pluginName'] )) {
            return $ff['settings.v12pluginName'] ;
        }
        if ( isset( $ff['settings']['v12pluginName'] )) {
            return $ff['settings']['v12pluginName'] ;
        }

        foreach (self::MIGRATION_SETTINGS as $setting) {

            if (    str_replace( ";" , "" , $setting['switchableControllerActions'] )
                === str_replace( ";" , "" , $ff['switchableControllerActions'] )
            ) {
                // first check if EVENT  Registration Form Page.
                $this->debugOutput( 33,  "test: " . $setting['targetListType'] . ($setting['register'] ? " | is Register" : '' ) ) ;
                if ( in_array($pid , $this->registerPids ) && $setting['register']) {
                    if ( trim( (string)$setting['defaultControllerActions'])=== trim((string)$ff['defaultControllerActions'] ))
                    {
                        return $setting['targetListType'];
                    }
                } else {
                    $this->debugOutput( 33,  "PID: " . $pid . " not in " . implode( "," , $this->registerPids) ) ;

                    // Second check if EVENT / Location / Organizer Single Page.
                    if ( $setting['single'] ) {
                        if ( !in_array($pid , $this->singlePids)) {
                            if ( trim( (string)$setting['defaultControllerActions'])=== trim((string)$ff['defaultControllerActions'] ))
                            {
                                return $setting['targetListType'];
                            }
                        }
                    } else {
                        if ( trim( (string)$setting['defaultControllerActions'])=== trim((string)$ff['defaultControllerActions'] ))
                        {
                            return $setting['targetListType'];
                        }
                    }
                }
            }
        }

        return '';
    }

    /**
     * Updates list_type and pi_flexform of the given content element UID
     *
     * @param int $uid
     * @param string $newCtype
     * @param string $flexform
     */
    protected function updateContentElement(int $uid, string $newCtype, string $flexform): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('tt_content')
            ->set('CType', $newCtype)
            ->set('list_type', '')
            ->set('pi_flexform', $flexform)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }

    /**
     * Transforms the given array to FlexForm XML
     *
     * @param array $input
     * @return string
     */
    protected function array2xml(array $input = []): string
    {
        $options = [
            'parentTagMap' => [
                'data' => 'sheet',
                'sheet' => 'language',
                'language' => 'field',
                'el' => 'field',
                'field' => 'value',
                'field:el' => 'el',
                'el:_IS_NUM' => 'section',
                'section' => 'itemType',
            ],
            'disableTypeAttrib' => 2,
        ];
        $spaceInd = 4;
        $output = GeneralUtility::array2xml($input, '', 0, 'T3FlexForms', $spaceInd, $options);
        $output = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>' . LF . $output;
        return $output;
    }

    private function debugOutput( $minVerbosity , $text ) {
        if ( $this->verboseLevel > $minVerbosity  ) {
            echo "\n" . $text ;
        }
    }
}
