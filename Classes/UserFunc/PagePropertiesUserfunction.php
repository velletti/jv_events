<?php
namespace JVE\JvEvents\UserFunc;


use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PagePropertiesUserfunction{

	/**
	 * Reference to the parent (calling) cObject set from TypoScript
	 */
	protected $cObj;

    /**
     * Get the title from tx_jvevents_domain_model_event
     * @param $content
     * @param $conf
     *
     * @return string
     */
    public function getEventTitle($content, $conf) {

        $queryBuilder = $this->getQueryBuilder('tx_jvevents_domain_model_event');
        $parameters = $this->getParameters();

        // Case 1: Default language
        if($parameters['sysLanguageUid'] === 0){

            return $this->getEventTitleFromDb($parameters['eventUid']);

        }

        // Case 2: Not default language and l10n_parent > 0
        if($parameters['sysLanguageUid'] > 0){

            // Check if this event is translated from a parent news
            $result = $queryBuilder
                ->select('uid')
                ->from('tx_jvevents_domain_model_event')->where($queryBuilder->expr()->eq(
                'l10n_parent',
                $queryBuilder->createNamedParameter($parameters['eventUid'], \PDO::PARAM_INT)
            ))->executeQuery()
                ->fetch();

            // Old style
            // --------------------------------------------
            // $db = $this->getDb();
            // $result = $db->exec_SELECTgetSingleRow('uid', 'tx_jvevents_domain_model_event', 'l10n_parent=' . $parameters['eventUid']);

            // Event is translated from parent page => take the title from the result
            if(!empty($result)){

                return $this->getEventTitleFromDb($result['uid']);

                // Event is not translated from parent page => take the title from this data record
            }else{

                return $this->getEventTitleFromDb($parameters['eventUid']);

            }

        }

        // Fallback, never reached
        return 'Event';

    }

    /**
     * Get the description (teaser) from tx_jvevents_domain_model_event
     * @param $content
     * @param $conf
     *
     * @return string
     */
    public function getEventDescription($content, $conf) {

        $queryBuilder = $this->getQueryBuilder();
        $parameters = $this->getParameters();
        $eventDescription = '';

        // Case 1: Default language
        if($parameters['sysLanguageUid'] === 0){

            $eventDescription = $this->getEventDescriptionFromDb($parameters['eventUid']);
            if(empty($eventDescription)){
                $eventDescription = $conf['defaultDescription'];
            }

            return $eventDescription;
        }

        // Case 2: Not default language and l10n_parent > 0
        if($parameters['sysLanguageUid'] > 0){

            // Check if this event is translated from a parent news
            $result = $queryBuilder
                ->select('uid')
                ->from('tx_jvevents_domain_model_event')->where($queryBuilder->expr()->eq(
                'l10n_parent',
                $queryBuilder->createNamedParameter($parameters['eventUid'], \PDO::PARAM_INT)
            ))->executeQuery()
                ->fetch();

            // Old style
            // --------------------------------------------
            // $db = $this->getDb();
            // $result = $db->exec_SELECTgetSingleRow('uid', 'tx_jvevents_domain_model_event', 'l10n_parent=' . $parameters['eventUid']);

            // Event is translated from parent page => take the title from the result
            if(!empty($result)){

                $eventDescription = $this->getEventDescriptionFromDb($result['uid']);

                // Event is not translated from parent page => take the title from this data record
            }else{

                $eventDescription = $this->getEventDescriptionFromDb($parameters['eventUid']);

            }

        }

        if(empty($eventDescription)){
            $eventDescription = $conf['defaultDescription'];
        }

        return $eventDescription;

    }



    /**
     * Get the parameters given by URL
     * @return array
     */
    private function getParameters() {

        // Get the parameters
        $sysLanguageUid = intval(GeneralUtility::_GP('L'));
        $pageUid = GeneralUtility::_GP('id');

        $tx_news_pi1 = GeneralUtility::_GP('tx_news_pi1');
        $newsUid = 0;
        if(isset($tx_news_pi1['news'])){
            $newsUid = intval($tx_news_pi1['news']);
        }

        $tx_jvevents_events = GeneralUtility::_GP('tx_jvevents_events');
        $eventUid = 0;
        if(isset($tx_jvevents_events['event'])){
            $eventUid = intval($tx_jvevents_events['event']);
        }

        unset($tx_news_pi1, $tx_jvevents_events);

        return [
            'sysLanguageUid' => $sysLanguageUid,
            'pageUid' => $pageUid,
            'newsUid' => $newsUid,
            'eventUid' => $eventUid
        ];

    }

    /**
     * Fetch the event title from DB
     * @param $eventUid
     * @return mixed
     */
    private function getEventTitleFromDb($eventUid){

        $queryBuilder = $this->getQueryBuilder();

        $result = $queryBuilder
            ->select('name', 'start_date')
            ->from('tx_jvevents_domain_model_event')->where($queryBuilder->expr()->eq(
            'uid',
            $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)
        ))->executeQuery()->fetch();

        $title =  date('d.m.Y', $result['start_date']) . ' - ' .$result['name']  ;

        unset($queryBuilder);

        $queryBuilder = $this->getQueryBuilder();
        $resultCategories = $queryBuilder
            ->select('c.title')
            ->from('tx_jvevents_event_category_mm', 'mm')
            ->leftJoin(
                'mm',
                'tx_jvevents_domain_model_category',
                'c',
                $queryBuilder->expr()->eq('mm.uid_foreign', $queryBuilder->quoteIdentifier('c.uid'))
            )->where($queryBuilder->expr()->eq('mm.uid_local', $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)))->executeQuery()->fetchAll();

        // echo $queryBuilder->getSQL();

        $categoriesArray = array();
        foreach($resultCategories as $resultCategory){
            $categoriesArray[] = $resultCategory['title'];
        }
        $categories = implode(', ', $categoriesArray);

        if(!empty($categories)){
            $title.= ' (' . $categories . ')';
        }

        return $title;



    }

    /**
     * Fetch the event description from DB
     * @param $eventUid
     * @return mixed
     */
    private function getEventDescriptionFromDb($eventUid)
    {

        $queryBuilder = $this->getQueryBuilder('tx_jvevents_domain_model_event');

        $result = $queryBuilder
            ->select('name', 'teaser', 'organizer', 'start_date')
            ->from('tx_jvevents_domain_model_event')->where($queryBuilder->expr()->eq(
            'uid',
            $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)
        ))->executeQuery()
            ->fetch();
        $meta = false;

        if (is_array($result)) {

            $meta = '<title>' . date('d.m.Y', $result['start_date']) . ' - ' . $result['name'] . "</title>\n";
            $meta .= '<meta name="description" content="' . substr($result['teaser'], 0, 155) . '" />' . "\n";
            $meta .= '<meta property="og:title" content="' . substr($result['name'], 0, 35) . '" />' . "\n";
            $meta .= '<meta property="og:description" content="' . substr($result['teaser'], 0, 65) . '" />' . "\n";
            $meta .= '<meta property="og:type" content="article" />' . "\n";
            $meta .= '<meta property="og:url" content="' . GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL') . '" />' . "\n";
            // $meta .= '<meta property="og:url" content="" />' . "\n";
            $meta .= '<meta property="og:site_name" content="' . GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY') . '" />' . "\n";


            $queryBuilderRef = $this->getQueryBuilder('sys_file_reference');

            $sysReference = $queryBuilderRef
                ->select('uid_local')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilderRef->expr()->eq('tablenames', $queryBuilderRef->createNamedParameter('tx_jvevents_domain_model_event', \PDO::PARAM_STR))
                )->andWhere($queryBuilderRef->expr()->eq('fieldname', $queryBuilderRef->createNamedParameter('teaser_image', \PDO::PARAM_STR))
                )->andWhere($queryBuilderRef->expr()->eq('uid_foreign', $queryBuilderRef->createNamedParameter($eventUid, \PDO::PARAM_INT)))->executeQuery()
                ->fetch();

            if (intval($sysReference['uid_local']) > 0) {
                $queryBuilderFile = $this->getQueryBuilder('sys_file');
                $sysFile = $queryBuilderFile
                    ->select('identifier')
                    ->from('sys_file')->where($queryBuilder->expr()->eq('uid', $queryBuilderFile->createNamedParameter($sysReference['uid_local'], \PDO::PARAM_INT)))->executeQuery()
                    ->fetch();

                if (is_array($sysFile)) {
                    // todo : make this correct with FileStorage ..
                    $meta .= '<meta property="og:image" content="' . GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . "/fileadmin" . $sysFile['identifier'] . "\" />\n";
                }
            }

        }


        return $meta;
    }

    /**
     * @param string $table
     * Returns the doctrine query builder
     * @return QueryBuilder
     */
    private function getQueryBuilder( $table = 'pageProperties'){

        /**
         * @var ConnectionPool $connectionPool
         */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($table);

        return $queryBuilder;

    }

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }

}