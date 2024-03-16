<?php
namespace JVelletti\JvEvents\Hooks ;
/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2016 jörg velletti Typo3@velletti.de
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * ************************************************************* */

use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Repository\EventRepository;
use JVelletti\JvEvents\Utility\SlugUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class ProcessCmdmap {

	protected $parentObject;
	protected $table;
	protected $command;
	protected $value;
	protected $id;
	protected $deleted;
	protected $fieldArray;

	/** @var  ObjectManager $this->objectManager */
	protected $objectManager ;

	/** @var  EventRepository $this->eventRepository */
	protected $eventRepository ;


	/** @var  Event $this->event */

	protected $event;


    /**
	 * Prevent deleting/moving of a news record if the editor doesn't have access to all categories of the news record
	 *
	 * @param string $command
	 * @param string $table
	 * @param int $id
	 * @param mixed $value
	 * @param mixed $Obj
	 * @param mixed $pasteUpdate
	 * @param mixed $pasteDatamap
	 */

	public function processCmdmap_postProcess(string $command, string $table, int $id, $value, $Obj, $pasteUpdate, $pasteDatamap) {
        if( is_object( $Obj )) {
            if ($table == 'tx_jvevents_domain_model_event') {
                $this->command = $command;
                $this->id = (MathUtility::canBeInterpretedAsInteger($id)?$id:$Obj->substNEWwithIDs[$id]);
                $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class) ->get('jv_events');

                if( $command == 'copy' ) {


                    $this->table = $table;
                    /** @var  ObjectManager $objectManager */
                    $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class) ;

                    /** @var PersistenceManager $persistenceManager */
                    $persistenceManager1 = $this->objectManager->get(PersistenceManager::class);

                    /** @var  EventRepository $eventRepository */
                    $this->eventRepository = $this->objectManager->get(EventRepository::class);


                    $mapping = $Obj ->copyMappingArray['tx_jvevents_domain_model_event'] ;
                    $newId = $mapping[$this->id] ;


                    if( intval( $newId ) > 0 ) {
                        /** @var  Event $event */
                        $this->event = $this->eventRepository->findByUidAllpages(intval($newId) , false ) ;

                        if( is_object( $this->event ) ) {
                            $fields =  $extConf['resetFieldListAfterCopy']   ;

                            // default: setUnconfirmedSeats:0;setRegisteredSeats:0;setSalesForceEventId:"";setSalesForceSessionId:""
                            $fieldsArray = explode(";" , trim($fields)  ) ;
                            if( is_array($fieldsArray)) {
                                foreach ($fieldsArray as $value ) {
                                    $fieldsArraySub = explode(":" , trim($value)  ) ;
                                    if( is_array($fieldsArraySub)) {
                                        $func = $fieldsArraySub[0] ;

                                        if(method_exists($this->event , $func )) {
                                            if(strlen($fieldsArraySub[1]) == 0 ) {
                                                $this->event->$func( "" ) ;
                                            } else {
                                                $this->event->$func( $fieldsArraySub[1] ) ;
                                            }


                                            // echo "<hr>event->" . $func . "(" . $fieldsArraySub[1] . ") ;" ;
                                        }
                                    }
                                }
                            }
                            $row['name'] =  $this->event->getName() ;
                            $row['pid'] =  $this->event->getPid() ;
                            $row['parentpid'] =  1 ;
                            $row['uid'] =  $this->event->getUid() ;
                            $row['sys_language_uid'] =  $this->event->getSysLanguageUid() ;
                            $row['slug'] =  $this->event->getSlug() ;

                            $slugGenerationDateFormat = "d-m-Y" ;
                            if( is_array($extConf) and array_key_exists( "slugGenerationDateFormat" , $extConf)) {
                                $slugGenerationDateFormat =  $extConf['slugGenerationDateFormat'] ;
                            }

                            $row['start_date'] =  $this->event->getStartDate()->format($slugGenerationDateFormat ) ;
                            $slug = SlugUtility::getSlug("tx_jvevents_domain_model_event", "slug", $row  )  ;
                            $this->event->setSlug( $slug ) ;

                            $this->event->setHidden(1) ;

                            try {
                                $this->eventRepository->update($this->event);
                            } catch (IllegalObjectTypeException $e) {
                            } catch (UnknownObjectException $e) {
                                // ignore
                            }
                            $persistenceManager1->persistAll() ;
                        }
                    }
                }

                if($this->command == 'delete'){
                    $this->deleted = 1;
                }

            }



            if ($table == 'tx_jvevents_domain_model_registrant') {
                if($command == 'delete'){

                    $regevent = BackendUtility::getRecord($table, $id , '*' , '' , false );
                    $eventId = $regevent['event'] ;
                    if( $eventId > 0 ) {
                        $event = BackendUtility::getRecord('tx_jvevents_domain_model_event', $eventId );

                        /** @var ConnectionPool $connectionPool */
                        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
                        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event') ;

                        $queryBuilder ->update('tx_jvevents_domain_model_event')
                            ->where( $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter(intval($eventId) , Connection::PARAM_INT )) ) ;

                        if( $regevent['hidden'] == 0 ) {
                            $registeredSeats = max( 0 , $event['registered_seats'] - 1 ) ;
                            $queryBuilder->set('registered_seats' , $registeredSeats ) ;

                        } else {
                            $unconfirmed_seats = max($event['unconfirmed_seats'] - 1 , 0 );
                            $queryBuilder->set('registered_seats' , $unconfirmed_seats ) ;

                        }
                        $queryBuilder->executeStatement() ;

                    }
                }

            }
        }

    }

}
