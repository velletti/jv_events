<?php
namespace JVelletti\JvEvents\Scheduler;
use JVelletti\JvEvents\Domain\Model\Organizer;
use JVelletti\JvEvents\Domain\Repository\EventRepository;
use JVelletti\JvEvents\Domain\Repository\OrganizerRepository;
use JVE\JvRanking\Domain\Repository\AnswerRepository;
use JVE\JvRanking\Domain\Repository\QuestionRepository;
use JVE\JvRanking\Utility\RankingUtility;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Locking\Exception\LockAcquireException;
use TYPO3\CMS\Core\Locking\Exception\LockAcquireWouldBlockException;
use TYPO3\CMS\Core\Locking\Exception\LockCreateException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidActionNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidControllerNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Locking\LockFactory;
use TYPO3\CMS\Core\Locking\LockingStrategyInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Velletti\Mailsignature\Service\SignatureService;

class CleanEventsTask extends AbstractTask
{


    /** @var int Amount of Day when Registration  are deleted  | if 0 = do nothing */
    private $delRegistratationsAfter = 60;

    /** @var int Amount of Day when Events  are deleted   | if 0 = do nothing*/
    private $delEventsAfter = 365;

    /** @var int Amount of Days a organizer should have an Event in the past. if now after that day, resorting to lower value is done. (if = 0 will do nothing)   */
    private $resortingOrganizer = 30 ;

    /** @var int Disable organizer, if lastlogin of maintainer is more than given days. (if = 0 will do nothing) */
    private $disableOrganizer = 180 ;


    /** @var int Disable organizer REALLY, if sorting is bigger than given value (if = 0 will do nothing default 123.456.789) */
    private $disableOrganizerSortingValue = 123456789 ;


    /** @var string email Address if set, debug output will be sent  */
    private $debugmail = '';

    private function fetchConfiguration()
    {

        $this->delRegistratationsAfter  = (int) $this->delRegistratationsAfter ;
        $this->delEventsAfter           = (int) $this->delEventsAfter ;
        $this->resortingOrganizer       = (int) $this->resortingOrganizer ;
        $this->disableOrganizer         = (int) $this->disableOrganizer ;
        $this->disableOrganizerSortingValue         = (int) $this->disableOrganizerSortingValue ;
        $this->debugmail                = trim( $this->debugmail) ;

        return true;
    }

    /**
     * This is the main method that is called when a task is executed
     * It MUST be implemented by all classes inheriting from this one
     * Note that there is no error handling, errors and failures are expected
     * to be handled and logged by the client implementations.
     * Should return TRUE on successful execution, FALSE on error.
     *
     * @return bool Returns TRUE on successful execution, FALSE on error
     * @throws LockAcquireException
     * @throws LockAcquireWouldBlockException
     * @throws LockCreateException
     * @throws InvalidActionNameException
     * @throws InvalidControllerNameException
     */
    public function execute()
    {
        $debug = array() ;
        $baseUrl = $_SERVER['SERVER_NAME'] ;
        $debug[] = date("d.m.Y H:i:s") . " Started on Server "  . "https://" . $baseUrl  . " ";

        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        $this->fetchConfiguration() ;
        $debug[]  = "config: delRegistratationsAfter=" . $this->delRegistratationsAfter  ;
        /** @var LockFactory $lockFactory */
        $lockFactory = GeneralUtility::makeInstance(LockFactory::class);
        $locker = $lockFactory->createLocker('jvevents_cleanevents', LockingStrategyInterface::LOCK_CAPABILITY_EXCLUSIVE | LockingStrategyInterface::LOCK_CAPABILITY_NOBLOCK);

        // Check if cronjob is already running:
        if (!$locker->acquire($locker::LOCK_CAPABILITY_EXCLUSIVE | $locker::LOCK_CAPABILITY_NOBLOCK)) {
            $this->outputLine('TYPO3 jvevents_cleanevents Task: ERROR: Cannot lock  ');

            return false;
        }

        /* ##############    Remove registrations ############################### */
        if ( $this->delRegistratationsAfter > 0 ) {
            $debug = $this->doCleanupRegistrations( $debug ) ;
        }

        /* ##############    Remove ld events ############################### */
        if ( $this->delEventsAfter > 0 ) {
            $debug = $this->doCleanupEvents( $debug ) ;
        }

        /* ##############    Remove ld events ############################### */
        if ( $this->resortingOrganizer > 0 ) {
            $debug = $this->doResortingOrganizer( $debug ) ;
        }

        /* ##############    Remove ld events ############################### */
        if ( $this->disableOrganizer > 0 ) {
            $debug = $this->doDisableOrganizer( $debug ) ;
        }

        if( GeneralUtility::validEmail( trim( $this->getDebugmail()) ) ) {
            /** @var SignatureService $mailService */
            $mailService = GeneralUtility::makeInstance(SignatureService::class);
            $params = array() ;
            $params['email_fromName'] = "Debug from " .$baseUrl ;
            $params['email_from'] = "info@tangomuenchen.de";
            $params['user']['email'] = trim( $this->getDebugmail());
            $params['sendCCmail'] = false  ;

            $params['message'] = "[tango][cleanup] Debug Output of Scheduler from " . $baseUrl . " \n\n" . var_export( $debug , true ) ;
            $mailService->sentHTMLmailService($params) ;
        }

        $locker->release();
        return true;
    }


    private function outputLine($msg)
    {
        $this->logger->error($msg);
    }

    private function doCleanupRegistrations($debug ) {
        $timeInPast  =  time() - intval( $this->delRegistratationsAfter )* 60 * 60 *24 ;


        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_registrant') ;
        /** @var Connection $connection */
        $connection = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_registrant') ;

        /** @var QueryBuilder $queryBuilder */
        $queryCount = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_registrant');
        $countResult = $queryCount->count( '*' )->from('tx_jvevents_domain_model_registrant' )
            ->where( $queryBuilder->expr()->lte('endtime',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->gt('endtime', 0 ))->andWhere($queryBuilder->expr()->eq('deleted', 0 ))->executeQuery()->fetchColumn(0) ;



        $queryBuilder ->update('tx_jvevents_domain_model_registrant')
            ->where( $queryBuilder->expr()->lte('endtime',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->gt('endtime', 0 ))
            ->andWhere($queryBuilder->expr()->eq('deleted', 0 ))
            ->set('deleted', 1 )
            ->set('tstamp', $queryBuilder->quoteIdentifier('tstamp') , false )
        ;

       //  $this->debugQuery($queryBuilder) ;

        $queryBuilder->executeStatement() ;

        if ( !$connection->errorInfo() ) {
            $debug[] = "removed  '" . $countResult . "'' registrations where events older than " . $timeInPast . " - " . date( "d.m.Y H:i" , $timeInPast ) ;
            return $debug;
        } else {
            $debug[] = array('faultstring' => 'Line: ' . __LINE__ . ' Error on update ', 'mode' => 'update', " error " => $connection->errorInfo() );
            return $debug ;
        }

    }


    private function doCleanupEvents($debug ) {
        $timeInPast  =  time() - intval( $this->delEventsAfter )* 60 * 60 *24 ;


        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event') ;
        /** @var Connection $connection */
        $connection = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_event') ;

        /** @var QueryBuilder $queryBuilder */
        $queryCount = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event');
        $countResult = $queryCount->count( '*' )->from('tx_jvevents_domain_model_event' )
            ->where( $queryBuilder->expr()->lte('start_date',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->lte('end_date', $timeInPast ))->andWhere($queryBuilder->expr()->eq('deleted', 0 ))->executeQuery()->fetchColumn(0) ;



        $queryBuilder ->update('tx_jvevents_domain_model_event')
            ->where( $queryBuilder->expr()->lte('start_date',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->lte('end_date', $timeInPast ))
            ->andWhere($queryBuilder->expr()->eq('deleted', 0 ))
            ->set('deleted', 1 )
            ->set('tstamp', $queryBuilder->quoteIdentifier('tstamp') , false )
        ;

        //  $this->debugQuery($queryBuilder) ;

        $queryBuilder->executeStatement() ;

        if ( !$connection->errorInfo() ) {
            $debug[] = "removed  '" . $countResult . "'' Events that are older than " . $timeInPast . " - " . date( "d.m.Y H:i" , $timeInPast ) ;
            return $debug;
        } else {
            $debug[] = array('faultstring' => 'Line: ' . __LINE__ . ' Error on update ', 'mode' => 'update', " error " => $connection->errorInfo() );
            return $debug ;
        }

    }

    private function doResortingOrganizer($debug ) {
        $debug[] = "\n ***************************************************" ;
        $debug[] = "\n now calculating the New sorting value" ;





        /** @var QuestionRepository $questionRepository */
        $questionRepository =  GeneralUtility::makeInstance(QuestionRepository::class) ;

        /** @var PersistenceManager $persistanceManager */
        $persistanceManager =  GeneralUtility::makeInstance(PersistenceManager::class) ;

        /** @var EventRepository $eventRepository */
        $eventRepository =  GeneralUtility::makeInstance(EventRepository::class) ;
        /** @var AnswerRepository $answerRepository */
        $answerRepository =  GeneralUtility::makeInstance(AnswerRepository::class) ;
        /** @var OrganizerRepository $organizerRepository */
        $organizerRepository =  GeneralUtility::makeInstance(OrganizerRepository::class) ;
        $organizers = $organizerRepository->findByFilterAllpages(FALSE , true ) ;

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
        /** @var QueryBuilder $queryFeUser */
        $queryFeUser = $connectionPool->getQueryBuilderForTable('fe_users') ;

        if( $organizers ) {
            $debug[] = "Found Organizers: " . count( $organizers) . " now searching for those with lower Sorting Value than : " . 20005 ;
            /** @var Organizer $organizer */
            foreach ($organizers as $organizer ) {
                if ( $organizer->getSorting() <  ( 20000 + 5 ) ) {
                    $isVip = false ;

                    $lastLogin = 0 ;
                    $users = GeneralUtility::trimExplode("," ,$organizer->getAccessUsers() , true) ;
                    $usersData = array() ;
                    if(is_array($users)) {
                        foreach ( $users as $userUid ) {
                            $feuser = $queryFeUser->select('uid' , 'lastlogin' , "username", 'usergroup' , 'is_online')->from('fe_users')->where($queryFeUser->expr()->eq('uid' , $queryFeUser->createNamedParameter($userUid , Connection::PARAM_INT )
                            ))->executeQuery()->fetch() ;
                            if( $feuser) {
                                $debug[] = "lastLogin: " . date('d.m.Y H:i' , $feuser['lastlogin'] ) . ": uid= '" . $userUid . "' - ". $feuser['username'] . " : groups: " . $feuser['usergroup'];
                                $usersData[] =  $feuser ;
                                if( $feuser['lastlogin'] > $lastLogin ) {
                                    $lastLogin = $feuser['lastlogin'] ;
                                }
                                if( $feuser['is_online'] > $lastLogin ) {
                                    $lastLogin = $feuser['is_online'] ;
                                }
                                $userGroups = GeneralUtility::trimExplode("," , $feuser['usergroup']  ) ;
                                if(in_array("3" , $userGroups ) ) {
                                    $isVip = true ;
                                }
                            }
                        }
                    }

                    $result = RankingUtility::calculate($questionRepository, $organizer , $eventRepository , $answerRepository , $isVip , $lastLogin ) ;
                    if ($organizer->getUid() == 2157 && 1==2  ) {
                        echo $result['newsorting'] ;
                        echo"<hr>" ;
                        echo nl2br($result['debug']) ;
                        echo"<hr>" ;
                        echo  "Organizer: " . $organizer->getUid() . " - " . $organizer->getName() . " Old: " . $organizer->getSorting() . " -> " . $result['newsorting'] ;
                        echo"<hr>" ;
                        die;
                    }

                    $debug[] = "Organizer: " . $organizer->getUid() . " - " . $organizer->getName() . " Old: " . $organizer->getSorting() . " -> " . $result['newsorting'] ;

                    /** @var ConnectionPool $connectionPool */
                    $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
                    /** @var QueryBuilder $queryBuilder */
                    $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_organizer') ;
                    $queryBuilder->update("tx_jvevents_domain_model_organizer")->set("sorting" , $result['newsorting'])->where($queryBuilder->expr()->eq("uid" , $queryBuilder->createNamedParameter( $organizer->getUid() , Connection::PARAM_INT)))->executeStatement() ;
                }

            }
        }
        return $debug ;

    }


    private function doDisableOrganizer($debug ) {
        $timeInPast  =  time() - intval( $this->disableOrganizer ) * 60 * 60 *24 ;
        $debug[] = " *********  now uses with really last login " ;

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */
        /** @var QueryBuilder $queryBuilderUpdate */
        /** @var QueryBuilder $queryEvents */
        /** @var QueryBuilder $queryFeUserUpdate */
        /** @var QueryBuilder $queryFeUser */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_organizer') ;
        $queryBuilderUpdate = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_organizer') ;
        $queryFeUser = $connectionPool->getQueryBuilderForTable('fe_users') ;
        $queryFeUserUpdate = $connectionPool->getQueryBuilderForTable('fe_users') ;
        $queryEvents = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_events') ;
        /** @var QueryBuilder $queryCount */
        $queryCount = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_events');


        /** @var Connection $connection */
        $connection = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_organizer') ;

        $queryBuilder ->select("uid" , "name" ,"access_users" , "sorting")
            ->from('tx_jvevents_domain_model_organizer')
            ->where( $queryBuilder->expr()->neq('access_users',  $queryBuilder->createNamedParameter('', Connection::PARAM_STR)  ))
            ->andWhere( $queryBuilder->expr()->lt('latest_event',  $queryBuilder->createNamedParameter($timeInPast , Connection::PARAM_INT )  ))
            ->andWhere( $queryBuilder->expr()->eq('hidden',  0 ))
            ->orderBy("uid")
        ;
       // $this->debugQuery($queryBuilder) ;
        $result = $queryBuilder->executeQuery() ;
        $countTotalResult = 0 ;
        $debug2 = [] ;

        while ($row = $result->fetch()) {
            // $debug[] = $row ;
            if(is_array($row )) {
                $lastLogin = 0 ;
                $users = GeneralUtility::trimExplode("," , $row['access_users'] , true) ;
                $usersData = array() ;
                if(is_array($users)) {
                    foreach ( $users as $userUid ) {
                        $feuser = $queryFeUser->select('uid' , 'lastlogin' , "username", 'usergroup' , 'is_online')->from('fe_users')->where($queryFeUser->expr()->eq('uid' , $queryFeUser->createNamedParameter($userUid , Connection::PARAM_INT )
                    ))->executeQuery()->fetch() ;
                        if( $feuser) {
                            $debug[] = "lastLogin: " . date('d.m.Y H:i' , $feuser['lastlogin'] ) . ": uid= '" . $userUid . "' - ". $feuser['username'] . " : groups: " . $feuser['usergroup'];
                            $usersData[] =  $feuser ;
                            if( $feuser['lastlogin'] > $lastLogin ) {
                                $lastLogin = $feuser['lastlogin'] ;
                            }
                            if( $feuser['is_online'] > $lastLogin ) {
                                $lastLogin = $feuser['is_online'] ;
                            }
                        }
                    }
                }
                if ( $lastLogin < $timeInPast ) {

                    if(  $row['sorting']  > $this->disableOrganizerSortingValue &&  $this->disableOrganizerSortingValue  > 0 ) {
                        $debug2[] = "Organizer : " . $row['uid'] . " - "  . $row['name'] . " set Hidden . managed by  user(s) " . $row['access_users'] ;
                        /*
                        $queryBuilderUpdate->update('tx_jvevents_domain_model_organizer')
                            ->where( $queryBuilder->expr()->eq('uid',   $row['uid'] ) )
                            ->set('hidden', 1 )
                            ->set('tstamp', $queryBuilder->quoteIdentifier('tstamp') ,false)
                            ->execute() ;
                        $debug[] = "Organizer : " . $row['uid'] . " - "  . $row['name'] . " set Hidden . managed by  user(s) " . $row['access_users'] ;
 */
                        foreach ( $usersData as $user ) {
                            $orig = $user['usergroup'];

                            $items = explode(',',  $user['usergroup'] );

                            foreach ($items as $k => $v) {
                                if ( in_array($v , [1,2,5,6,7] ) ) {
                                    unset($items[$k]);
                                }
                            }
                            $user['usergroup'] = "1," . implode(',', $items);
                            $debug[] = "Reduced Group access of user : " . $user['uid'] . " from: " . $orig ." to " .   $user['usergroup'];

                            $queryFeUserUpdate->update('fe_users')
                                ->where( $queryBuilder->expr()->eq('uid',   $user['uid'] ))->set('usergroup', $user['usergroup'])->executeStatement();
                        }


                    } else {
                        $queryBuilderUpdate->update('tx_jvevents_domain_model_organizer')
                            ->where( $queryBuilder->expr()->eq('uid',   $row['uid'] ) )
                            ->set('tstamp', $queryBuilder->quoteIdentifier('tstamp') , false )->set('sorting', $queryBuilder->quoteIdentifier('sorting') . " + " . 50000, false)->executeStatement() ;

                        $debug[] = "Organizer : " . $row['uid'] . " - "  . $row['name'] . " moved sorting from " . $row['sorting']  . " + 50.000 ! managed by  user(s) " . $row['access_users'] ;
                    }
                    $countResult = $queryCount->count( '*' )->from('tx_jvevents_domain_model_event' )
                        ->where($queryBuilder->expr()->eq('canceled',   1 ))
                        ->andWhere($queryBuilder->expr()->eq('organizer' , $row['uid'] ) )->andWhere($queryBuilder->expr()->gt('start_date' , time() ))->executeQuery()->fetchColumn(0) ;

                    if( $countResult > 0 ) {
                        $queryEvents->update('tx_jvevents_domain_model_event')
                            ->where($queryBuilder->expr()->eq('canceled',   1 ))
                            ->andWhere($queryBuilder->expr()->eq('organizer' , $row['uid'] ) )
                            ->andWhere($queryBuilder->expr()->gt('start_date' , time() ) )
                            ->set('deleted' , 1)->set('tstamp', $queryBuilder->quoteIdentifier('tstamp'), false)->executeStatement()
                        ;
                        $debug[] = "Number of Removed canceled Events of this organizer: " . $countResult ;
                    }
                    $countTotalResult = $countTotalResult + $countResult ;

                }
            }
        }
        $debug[] = "Number of Removed canceled Events all organizer: " . $countTotalResult ;
        $debug[] = "" ;
        $debug[] = "List of Organizer that should be disabled" ;
        $debug[] =  $debug2 ;

        return $debug;

    }

    /**
     * @return int
     */
    public function getDelRegistratationsAfter()
    {
        return $this->delRegistratationsAfter;
    }

    /**
     * @param int $delRegistratationsAfter
     */
    public function setDelRegistratationsAfter($delRegistratationsAfter)
    {
        $this->delRegistratationsAfter = $delRegistratationsAfter;
    }

    /**
     * @return string
     */
    public function getDebugmail()
    {
        return $this->debugmail;
    }

    /**
     * @param string $debugmail
     */
    public function setDebugmail($debugmail)
    {
        $this->debugmail = $debugmail;
    }

    /**
     * @return int
     */
    public function getDelEventsAfter(): int
    {
        return $this->delEventsAfter;
    }

    /**
     * @param int $delEventsAfter
     */
    public function setDelEventsAfter(int $delEventsAfter)
    {
        $this->delEventsAfter = $delEventsAfter;
    }

    /**
     * @return int
     */
    public function getResortingOrganizer(): int
    {
        return $this->resortingOrganizer;
    }

    /**
     * @param int $resortingOrganizer
     */
    public function setResortingOrganizer(int $resortingOrganizer)
    {
        $this->resortingOrganizer = $resortingOrganizer;
    }

    /**
     * @return int
     */
    public function getDisableOrganizer(): int
    {
        return $this->disableOrganizer;
    }

    /**
     * @param int $disableOrganizer
     */
    public function setDisableOrganizer(int $disableOrganizer)
    {
        $this->disableOrganizer = $disableOrganizer;
    }

    /**
     * @return int
     */
    public function getDisableOrganizerSortingValue(): int
    {
        return $this->disableOrganizerSortingValue;
    }

    /**
     * @param int $disableOrganizerSortingValue
     */
    public function setDisableOrganizerSortingValue(int $disableOrganizerSortingValue)
    {
        $this->disableOrganizerSortingValue = $disableOrganizerSortingValue;
    }



    function debugQuery($query) {
        // new way to debug typo3 db queries
        $querystr = $query->getSQL() ;
        echo $querystr ;
        echo "<hr>" ;
        $queryParams = array_reverse ( $query->getParameters()) ;
        var_dump($queryParams);
        echo "<hr>" ;

        foreach ($queryParams as $key => $value ) {
            $search[] = ":" . $key ;
            $replace[] = "'$value'" ;

        }
        echo str_replace( $search , $replace , $querystr ) ;

        die;
    }


}
