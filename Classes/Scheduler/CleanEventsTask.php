<?php
namespace JVE\JvEvents\Scheduler;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Locking\Exception\LockAcquireException;
use TYPO3\CMS\Core\Locking\Exception\LockAcquireWouldBlockException;
use TYPO3\CMS\Core\Locking\Exception\LockCreateException;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidActionNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidControllerNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Locking\LockFactory;
use TYPO3\CMS\Core\Locking\LockingStrategyInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Velletti\Mailsignature\Service\SignatureService;

class CleanEventsTask extends AbstractTask
{


    /** @var int Amount of Day when Registration  are deleted  | if 0 = do nothing */
    private $delRegistratationsAfter = 60;

    /** @var int Amount of Day when Events  are deleted   | if 0 = do nothing*/
    private $delEventsAfter = 365;

    /** @var int Amount of Days a organizer should have an Event in the past. if less, resorting to lower value is done. (if = 0 will do nothing)   */
    private $resortingOrganizer = 30 ;

    /** @var int Disable organizer, if lastlogin of maintainer is more than given days. (if = 0 will do nothing) */
    private $disableOrganizer = 180 ;


    /** @var string email Address if set, debug output will be sent  */
    private $debugmail = '';

    /** @var  Logger */
    protected $logger;

    private function fetchConfiguration()
    {

        $this->delRegistratationsAfter  = (int) $this->delRegistratationsAfter ;
        $this->delEventsAfter           = (int) $this->delEventsAfter ;
        $this->resortingOrganizer       = (int) $this->resortingOrganizer ;
        $this->disableOrganizer         = (int) $this->disableOrganizer ;
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
            $debug = $this->cleanupRegistrations( $debug ) ;
        }

        /* ##############    Remove ld events ############################### */
        if ( $this->delEventsAfter > 0 ) {
            $debug = $this->cleanupEvents( $debug ) ;
        }

        /* ##############    Remove ld events ############################### */
        if ( $this->resortingOrganizer > 0 ) {
            $debug = $this->resortingOrganizer( $debug ) ;
        }

        /* ##############    Remove ld events ############################### */
        if ( $this->disableOrganizer > 0 ) {
            $debug = $this->disableOrganizer( $debug ) ;
        }

        if( GeneralUtility::validEmail( trim( $this->getDebugmail()) ) ) {
            /** @var SignatureService $mailService */
            $mailService = GeneralUtility::makeInstance("Velletti\\Mailsignature\\Service\\SignatureService");
            $params = array() ;
            $params['email_fromName'] = "Debug from " .$baseUrl ;
            $params['email_from'] = "info@tangomuenchen.de";
            $params['user']['email'] = trim( $this->getDebugmail());
            $params['sendCCmail'] = false  ;

            $params['message'] = "Debug Output " . implode(" \n" , $debug ) ;
            $mailService->sentHTMLmailService($params) ;
        }

        $locker->release();
        return true;
    }


    private function outputLine($msg)
    {
        $this->logger->error($msg);
    }

    private function cleanupRegistrations($debug ) {
        $timeInPast  =  time() - intval( $this->delRegistratationsAfter )* 60 * 60 *24 ;


        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_registrant') ;
        /** @var Connection $connection */
        $connection = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_registrant') ;

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryCount = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_registrant');
        $countResult = $queryCount->count( '*' )->from('tx_jvevents_domain_model_registrant' )
            ->where( $queryBuilder->expr()->lte('endtime',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->gt('endtime', 0 ))
            ->andWhere($queryBuilder->expr()->eq('deleted', 0 ))
            ->execute()->fetchColumn(0) ;



        $queryBuilder ->update('tx_jvevents_domain_model_registrant')
            ->where( $queryBuilder->expr()->lte('endtime',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->gt('endtime', 0 ))
            ->andWhere($queryBuilder->expr()->eq('deleted', 0 ))
            ->set('deleted', 1 ) ;

       //  $this->debugQuery($queryBuilder) ;

        $queryBuilder->execute() ;

        if ( !$connection->errorInfo() ) {
            $debug[] = "removed  '" . $countResult . "'' registrations where events older than " . $timeInPast . " - " . date( "d.m.Y H:i" , $timeInPast ) ;
            return $debug;
        } else {
            $debug[] = array('faultstring' => 'Line: ' . __LINE__ . ' Error on update ', 'mode' => 'update', " error " => $connection->errorInfo() );
            return $debug ;
        }

    }


    private function cleanupEvents($debug ) {
        $timeInPast  =  time() - intval( $this->delEventsAfter )* 60 * 60 *24 ;


        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event') ;
        /** @var Connection $connection */
        $connection = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_event') ;

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryCount = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_event');
        $countResult = $queryCount->count( '*' )->from('tx_jvevents_domain_model_event' )
            ->where( $queryBuilder->expr()->lte('start_date',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->lte('end_date', $timeInPast ))
            ->andWhere($queryBuilder->expr()->eq('deleted', 0 ))
            ->execute()->fetchColumn(0) ;



        $queryBuilder ->update('tx_jvevents_domain_model_event')
            ->where( $queryBuilder->expr()->lte('start_date',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->lte('end_date', $timeInPast ))
            ->andWhere($queryBuilder->expr()->eq('deleted', 0 ))
            ->set('deleted', 1 ) ;

        //  $this->debugQuery($queryBuilder) ;

        $queryBuilder->execute() ;

        if ( !$connection->errorInfo() ) {
            $debug[] = "removed  '" . $countResult . "'' Events that are older than " . $timeInPast . " - " . date( "d.m.Y H:i" , $timeInPast ) ;
            return $debug;
        } else {
            $debug[] = array('faultstring' => 'Line: ' . __LINE__ . ' Error on update ', 'mode' => 'update', " error " => $connection->errorInfo() );
            return $debug ;
        }

    }

    private function resortingOrganizer($debug ) {
        $timeInPast  =  time() - intval( $this->resortingOrganizer ) * 60 * 60 *24 ;


        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_organizer') ;
        /** @var Connection $connection */
        $connection = $connectionPool->getConnectionForTable('tx_jvevents_domain_model_organizer') ;



        $queryBuilder ->update('tx_jvevents_domain_model_organizer')
            ->where( $queryBuilder->expr()->lte('latest_event',  $timeInPast ) )
            ->andWhere($queryBuilder->expr()->gt('sorting', 20 ))
            ->andWhere($queryBuilder->expr()->lt('sorting', 100099999 ))
            ->set('sorting', $queryBuilder->quoteIdentifier('sorting') . " + " . $this->resortingOrganizer   , false );

        // $this->debugQuery($queryBuilder) ;

        $queryBuilder->execute() ;

        if ( !$connection->errorInfo() ) {
            $debug[] = "Updated  sorting of Organizers  with latest_event older than " . $timeInPast . " - " . date( "d.m.Y H:i" , $timeInPast ) ;
            return $debug;
        } else {
            $debug[] = array('faultstring' => 'Line: ' . __LINE__ . ' Error on update ', 'mode' => 'update', " error " => $connection->errorInfo() );
            return $debug ;
        }

    }


    private function disableOrganizer($debug ) {
        $timeInPast  =  time() - intval( $this->disableOrganizer ) * 60 * 60 *24 ;


        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        /** @var QueryBuilder $queryBuilder */
        /** @var QueryBuilder $queryBuilderUpdate */
        /** @var QueryBuilder $queryFeUser */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_organizer') ;
        $queryBuilderUpdate = $connectionPool->getQueryBuilderForTable('tx_jvevents_domain_model_organizer') ;
        $queryFeUser = $connectionPool->getQueryBuilderForTable('fe_users') ;

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
        $result = $queryBuilder->execute() ;

        while ($row = $result->fetch()) {
            $debug[] = $row ;
            if(is_array($row )) {
                $lastLogin = 0 ;
                $users = GeneralUtility::trimExplode("," , $row['access_users'] , true) ;
                if(is_array($users)) {
                    foreach ( $users as $userUid ) {
                        $feuser = $queryFeUser->select('lastlogin' , "username")->from('fe_users')->where(
                            $queryFeUser->expr()->eq('uid' , $queryFeUser->createNamedParameter($userUid , Connection::PARAM_INT )
                        ))->execute()->fetch() ;
                        $debug[] = $feuser ;
                        if( $feuser) {
                            if( $feuser['lastlogin'] > $lastLogin ) {
                                $lastLogin = $feuser['lastlogin'] ;
                            }
                        }
                    }
                }
                if ( $lastLogin < $timeInPast ) {

                    if(  $row['sorting']  > 109999999) {
                        $queryBuilderUpdate->update('tx_jvevents_domain_model_organizer')
                            ->where( $queryBuilder->expr()->eq('uid',   $row['uid'] ) )
                            ->set('hidden', 1 )
                            ->execute() ;
                        $debug[] = "Organizer : " . $row['uid'] . " - "  . $row['name'] . " set Hidden . managed by  user(s) " . $row['access_users'] ;
                    } else {
                        $queryBuilderUpdate->update('tx_jvevents_domain_model_organizer')
                            ->where( $queryBuilder->expr()->eq('uid',   $row['uid'] ) )
                            ->set('sorting', $queryBuilder->quoteIdentifier('sorting') . " + " . 5000000   , false )
                            ->execute() ;

                        $debug[] = "Organizer : " . $row['uid'] . " - "  . $row['name'] . " changed sorting from " . $row['sorting']  . "  . managed by  user(s) " . $row['access_users'] ;
                    }
                }
            }
        }
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
