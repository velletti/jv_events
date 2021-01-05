<?php
namespace JVE\JvEvents\Scheduler;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * A task that should be run regularly that deletes
 * datasets flagged as "deleted" from the DB.
 */
class CleanEventsTaskAdditionalFieldProvider extends  AbstractAdditionalFieldProvider implements AdditionalFieldProviderInterface
{


    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array $taskInfo Values of the fields from the add/edit task form
     * @param \JVE\JvEvents\Scheduler\CleanEventsTask $task The task object being edited. NULL when adding a task!
     * @param SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule): array
    {

        if ($schedulerModule->getCurrentAction()  === 'edit') {
            $taskInfo['IndexerDelRegistratationsAfter'] = $task->getDelRegistratationsAfter();
            $taskInfo['IndexerDelEventsAfter']          = $task->getDelEventsAfter();
            $taskInfo['IndexerResortingOrganizer']      = $task->getResortingOrganizer();
            $taskInfo['IndexerDisableOrganizer']        = $task->getDisableOrganizer();
            $taskInfo['IndexerDisableOrganizerSortingValue']        = $task->getDisableOrganizerSortingValue();
            $taskInfo['IndexerDebugmail'] = $task->getDebugmail();
        }
        $additionalFields = array() ;

        $additionalFields = $this->generateFormField($additionalFields , "DelRegistratationsAfter" , "text" , $taskInfo ) ;
        $additionalFields = $this->generateFormField($additionalFields , "DelEventsAfter"          , "text" , $taskInfo ) ;
        $additionalFields = $this->generateFormField($additionalFields , "ResortingOrganizer" , "text" , $taskInfo ) ;
        $additionalFields = $this->generateFormField($additionalFields , "DisableOrganizer" , "text" , $taskInfo ) ;
        $additionalFields = $this->generateFormField($additionalFields , "DisableOrganizerSortingValue" , "text" , $taskInfo ) ;
        $additionalFields = $this->generateFormField($additionalFields , "Debugmail" , "text" , $taskInfo ) ;



        return $additionalFields;
    }

    /**
     * @param array $additionalFields
     * @param string $name
     * @param string $type
     * @param array $taskInfo
     * @param string $cshKey
     * @param string $class
     * @return array
     */
    public function generateFormField(array $additionalFields , string $name, string $type , array $taskInfo, $cshKey = '' , $class='form-control'): array
    {
        $formField = array() ;
        switch ($type) {
            case 'bool':
                $checked = '' ;
                if ( $taskInfo['Indexer' .$name ] == 1 ) {
                    $checked = ' checked="checked"'  ;
                }
                $formField['code'] = '<input type="checkbox" class=" ' . $class . '" name="tx_scheduler[Indexer' . $name . ']" value="1" ' . $checked . '>' ;
                break;
            case 'text':
                $formField['code'] = '<input type="text" class="' . $class . '" name="tx_scheduler[Indexer' . $name . ']" value="' . $taskInfo['Indexer' .$name ] . '">' ;
                break;
            case 'password':
                $formField['code'] = '<input type="password" class="' . $class . '" name="tx_scheduler[Indexer' . $name . ']" value="' . $taskInfo['Indexer' .$name ] . '">' ;
                break;
        }
        $formField['label'] = 'LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:indexerTask_' . $name ;
        $formField['cshKey'] = $cshKey ;
        $formField['cshLabel'] = 'task_indexerTask_' . $name ;
        $additionalFields['Indexer'. $name] = $formField ;
        return $additionalFields ;
    }
     /**
     * Validates the additional fields' values
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param SchedulerModuleController $schedulerModule Reference to the scheduler backend module
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        return $this->validateAdditionalFieldStoragePid($submittedData['IndexerStoragePid']);
    }


    /**
     * Validates the input of period
     *
     * @param int $storagePid The given $storagePid as integer
     * @return bool TRUE if validation was ok, FALSE otherwise
     */
    protected function validateAdditionalFieldStoragePid($storagePid): bool
    {
        if (empty($storagePid) ||   filter_var($storagePid, FILTER_VALIDATE_INT) !== false  ) {
            $validStoragePid = true;
        } else {
            $this->addMessage(
                //$this->getLanguageService()->sL('LLL:EXT:allplan_ke_search_extended/Resources/Private/Language/locallang_tasks.xlf:indexerTaskErrorStoragePid', true),
                'Error Checking storagePid' ,
                FlashMessage::ERROR

            );
            $validStoragePid = false;
        }

        return $validStoragePid;
    }

    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param AbstractTask $task Reference to the scheduler backend module
     * @return void
     * @throws \InvalidArgumentException
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        if (!$task instanceof AbstractTask ) {
            throw new \InvalidArgumentException(
                'Expected a task of type \JVE\JvEvents\Scheduler\CleanEventsTask, but got ' . get_class($task),
                1329219449
            );
        }

        $task->setDelRegistratationsAfter($submittedData['IndexerDelRegistratationsAfter']);
        $task->setDelEventsAfter($submittedData['IndexerDelEventsAfter']);
        $task->setResortingOrganizer($submittedData['IndexerResortingOrganizer']);
        $task->setDisableOrganizer($submittedData['IndexerDisableOrganizer']);
        $task->setDisableOrganizerSortingValue($submittedData['IndexerDisableOrganizerSortingValue']);
        $task->setDebugmail($submittedData['IndexerDebugmail']);


    }


    /**
     * @return array|LanguageService
     */
    protected function getLanguageService()
    {
        if( key_exists("LANG" , $GLOBALS ) && is_array($GLOBALS['LANG'])) {
            return $GLOBALS['LANG'];
        }
        /** @var LanguageService $lang */
        $lang = GeneralUtility::makeInstance(LanguageService::class) ;
        if (TYPO3_MODE === 'BE') {
            $lng = $GLOBALS['BE_USER']->uc['lang'] ;
        } else {
            $lng = $GLOBALS['TSFE']->config['config']['language'] ;
        }
        if ( $lng == '' ) { $lng = "en" ;}
        $lang->init($lng) ;
        $GLOBALS['LANG'] = $lang ;
        return $lang ;

    }




}
