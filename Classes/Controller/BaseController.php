<?php
namespace JVelletti\JvEvents\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Jörg velletti <jVelletti@allplan.com>, Allplan GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 ***************************************************************/

use JVelletti\JvEvents\Utility\MigrationUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Context\Context;
use JVelletti\JvEvents\Utility\EmConfigurationUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use JVelletti\JvEvents\Domain\Model\Location;
use JVelletti\JvEvents\Domain\Model\Organizer;
use JVelletti\JvEvents\Domain\Model\Category;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Registrant;
use JVelletti\JvEvents\Domain\Model\Tag;
use JVelletti\JvEvents\Domain\Repository\CategoryRepository;
use JVelletti\JvEvents\Domain\Repository\EventRepository;
use JVelletti\JvEvents\Domain\Repository\LocationRepository;
use JVelletti\JvEvents\Domain\Repository\OrganizerRepository;
use JVelletti\JvEvents\Domain\Repository\RegistrantRepository;
use JVelletti\JvEvents\Domain\Repository\StaticCountryRepository;
use JVelletti\JvEvents\Domain\Repository\SubeventRepository;
use JVelletti\JvEvents\Domain\Repository\TagRepository;
use JVelletti\JvEvents\Domain\Repository\TokenRepository;

use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Messaging\AbstractMessage ;
use TYPO3\CMS\Extbase\Service\CacheService;
use Velletti\Mailsignature\Service\SignatureService;

/**
 * EventController
 */
class BaseController extends ActionController
{

    /**
     *  $eventRepository
     * @var EventRepository
     */
    protected $eventRepository = NULL;

    /**
     * subeventRepository
     *
     * @var SubeventRepository
     */
    protected $subeventRepository = NULL;

	/**
  * staticCountryRepository
  *
  * @var StaticCountryRepository
  */
 protected $staticCountryRepository = NULL;

    /**
     * organizerRepository
     *
     * @var OrganizerRepository
     */
    protected $organizerRepository = NULL;


    /**
     * locationRepository
     *
     * @var LocationRepository
     */
    protected $locationRepository = NULL;

    /**
     * persistencemanager
     *
     * @var PersistenceManager
     */
    protected $persistenceManager = NULL ;

    /**
     * registrantRepository
     *
     * @var RegistrantRepository
     */
    protected $registrantRepository = NULL;


    /**
     * categoryRepository
     *
     * @var CategoryRepository
     */
    protected $categoryRepository = NULL;

    /**
     * tagRepository
     *
     * @var TagRepository
     */
    protected $tagRepository = NULL;


    /**
     * @var TokenRepository
     */
    protected $tokenRepository;

    /**
     * @var array
     */
    public $debugArray ;


    /**
     * CacheService
     */

    public $cacheService ;

    /**
     * @var float
     */
    public $timeStart ;

    /** @var array */
    public $frontendUser ;

    /**
     * @return void
     */
    public function injectCacheService(CacheService $cacheService) {
        $this->cacheService = $cacheService ;
    }


    /**
     * Inject the TokenRepository
     *
     * @param TokenRepository $tokenRepository
     */
    public function injectTokenRepository(TokenRepository $tokenRepository): void
    {
        $this->tokenRepository = $tokenRepository;
    }

    public function injectTagRepository(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function injectRegistrantRepository(RegistrantRepository $registrantRepository)
    {
        $this->registrantRepository = $registrantRepository;
    }

    public function injectLocationRepository(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function injectOrganizerRepository(OrganizerRepository $organizerRepository)
    {
        $this->organizerRepository = $organizerRepository;
    }

    public function injectEventRepository(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function injectSubeventRepository(SubeventRepository $subeventRepository)
    {
        $this->subeventRepository = $subeventRepository;
    }

    public function injectStaticCountryRepository(StaticCountryRepository $staticCountryRepository)
    {
        $this->staticCountryRepository = $staticCountryRepository;
    }



    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }


    /**
     * action list
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->settings['register']['allowedCountrys'] 	=  GeneralUtility::trimExplode("," , $this->settings['register']['allowedCountrys'] , true );
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser */
        $this->frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');

        $this->settings['pageId'] = MigrationUtility::getPageId($this) ;
        $this->settings['servername']					=  GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
        /** @var AspectInterface $languageAspect */
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language') ;

        $this->settings['sys_language_uid']				=  $languageAspect->getId() ;

        $this->settings['EmConfiguration']	 			= EmConfigurationUtility::getEmConf();

        // get the list of Required Fields for this layout and store it to the  settings Array
        // seemed faster than separate via a Viewhelper for each Field

        if( array_key_exists( 'LayoutRegister' , $this->settings ) && $this->settings['LayoutRegister'] ) {
            $layout = $this->settings['LayoutRegister'] ;
        } else {
            $layout = "5Tango" ;
        }
        $this->settings['phpTimeZone'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] ?: "UTC" ;
        $fields = $this->settings['register']['requiredFields'][$layout] ;
        if( array_key_exists( 'Register' , $this->settings )  && array_key_exists( 'add_mandatory_fields' , $this->settings['Register'] ) && strlen( (string) $this->settings['Register']['add_mandatory_fields'] ) > 1 ) {
            $fields .= "," . $this->settings['Register']['add_mandatory_fields'] ;
        }

        $required  = GeneralUtility::trimExplode( "," , $fields , true ) ;
        if ( (is_countable($required) ? count($required) : 0) > 0 ) {
            foreach( $required as $key => $field ) {
                $this->settings['register']['required'][$field] = TRUE ;
            }
        }

        if( isset($this->settings['register']['formFields'][$layout]) ) {
            $fields = $this->settings['register']['formFields'][$layout] ;
            $formFields  = GeneralUtility::trimExplode( "," , $fields , true ) ;
            if( (is_countable($formFields) ? count( $formFields ) : 0) > 0 ) {
                foreach( $formFields as $key => $field ) {
                    if( $field) {
                        $this->settings['register']['allformFields'][$field] = TRUE ;
                    }
                }
            }
        }


        if( isset($this->settings['register']['tracking']) ) {

            $ga = $this->settings['register']['tracking'] ;
            if (isset($ga['trackParam']) && isset( $ga['trackValue'])) {
                $gaMarketo = GeneralUtility::getIndpEnv('QUERY_STRING');
                parse_str($gaMarketo, $queryParams);

                if (isset( $queryParams[ $ga['trackParam']]) && $queryParams[ $ga['trackParam']] ===$ga['trackValue'] ) {
                    if (isset($ga['trackParams'] ) && is_array($ga['trackParams'])) {
                        foreach ($ga['trackParams'] as $param) {
                            if (isset($queryParams[$param])) {
                                // Set cookies with a 48-hour expiration time using TYPO3 API
                                setcookie($param, $queryParams[$param], [
                                    'expires' => time() + (48 * 60 * 60),
                                    'path' => '/',
                                    'secure' => true,
                                    'httponly' => true,
                                    'samesite' => 'Strict',
                                ]);
                            }
                        }
                    }
                }

            }
        }

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
    }

    public function generateFilterBox( $filter , $tagShowAfterColon=0 ) {
        $filterTags = GeneralUtility::intExplode(',', $filter , true);
        $tags = [] ;
        if( is_array($filterTags)) {
            foreach ($filterTags as $Id ) {
                /** @var Tag $tag */
                $tag = $this->tagRepository->findByUid($Id) ;
                if( $tag ) {
                    if( $tagShowAfterColon > 0 ) {
                        $tags[] = ["id" => $Id, "title" =>  $tag->getNameAfterColon()] ;
                    } else {
                        $tags[] = ["id" => $Id, "title" => $tag->getName()] ;
                    }
                }
            }
        }
        if( count($tags) > 0) {
            return $tags ;
        }
        return false;
    }
    public function generateFilterAll( $filter )
    {
        $organizers = [];
        $tags2 = [];
        $categories2 = [];

        $filterTags = GeneralUtility::intExplode(',', $filter['tags'], true);
        if( is_array($filterTags)) {
            foreach ($filterTags as $Id ) {
                $tag = $this->tagRepository->findByUid($Id) ;
                $tags2[] = ["id" => $Id, "title" => $tag->getName()] ;
            }
        }

        $filterCats = GeneralUtility::intExplode(',', $filter['categories'], true);
        if( is_array($filterCats)) {
            foreach ($filterCats as $Id ) {
                $cat = $this->categoryRepository->findByUid($Id) ;
                $categories2[] = ["id" => $Id, "title" => $cat->getTitle(), "description" => $cat->getDescription(), "sorting" => $cat->getSorting()] ;
            }
        }
        $sortArray = [];
        foreach($categories2 as $key => $array) {
            if( $this->settings['filter']['sorttags'] == "sorting" ) {
                $sortArray[$key] = substr( "00000000000" . $array['sorting'], -12 , 12 )  ;
            } else {
                $sortArray[$key] = ucfirst ( (string) $array['title']  ) ;
            }
        }

        array_multisort($sortArray, SORT_ASC, SORT_STRING , $categories2);

        $orgArr = $this->organizerRepository->findByFilterAllpages( FALSE , TRUE , FALSE , FALSE )  ;
        if( $orgArr ) {
            foreach ( $orgArr as $organizer ) {
                $organizers[$organizer->getUid() ] = $organizer->getName() ;
            }
        }

        return ["organizers" => $organizers, "tags2" => $tags2, "categories2" => $categories2, "category50proz" => intval ( (count($categories2) ) / 2 ), "tag50proz" => intval ( (count($tags2) +1) / 2 )] ;
    }

    public function generateFilter(QueryResultInterface $events , $filter ) {
        $locations = [] ;
        $organizers = [] ;
        $citys = [] ;
        $tags = [] ;
        $tags2 = [] ;
        $categories = [] ;
        $categories2 = [] ;
        $months = [] ;


        /** @var Event $event */
        $eventsArray = $events->toArray() ;
        // while( $event instanceof  \JVelletti\JvEvents\Domain\Model\Event ) {

        foreach ($eventsArray as $key => $event ) {
            // first fill the Options for the Filters to have only options with Events
            if( ! $this->settings['filter']['hideCityDropdown']) {
                $citys["-"] = $this->translate("tx_jvevents_event.filter.city.all") ;

                /** @var Location $obj */
                $obj =  $event->getLocation() ;
                if ( is_object($obj) ) {
                    $locations[$obj->getUid()] = $obj->getName()  ;

                    if(! in_array( urlencode( $obj->getCity() ) , $citys )) {
                        // no online Events in CITY Filter
                        if( intval( $obj->getLat())  != 0 || intval($obj->getLng() != 0 )) {
                            $citys[urlencode( $obj->getCity() )] = ucfirst($obj->getCity()) ;
                        }
                    }
                }
            }


            if( ! $this->settings['filter']['hideOrganizerDropdown']) {
                /** @var Organizer $obj */
                $obj = $event->getOrganizer();
                if (is_object($obj)) {
                    if ( $this->settings['ShowFilter'] == 6 || $this->settings['ShowFilter'] == 7  ) {
                        $orgName = str_replace(" " , "+" , $obj->getName() ) ;
                        if(! in_array($orgName , $organizers )) {
                            $organizers[$orgName] = $obj->getName() ;
                        }
                    } else {
                        $organizers[$obj->getUid()] = $obj->getName();
                    }

                }
            }


            $objArray =  $event->getTags() ;
            if( is_object( $objArray)) {
                // Plugin settings: are there TAGS as filter defined in the Plugin
                $filterTags = (!is_null($filter['tags']) ) ? GeneralUtility::intExplode(',', $filter['tags'], true) : '' ;

                /** @var Tag $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                      //  if ( $filter['combinetags'] == "0" || count($filterTags) < 1 || in_array(  $obj->getUid() , $filterTags )) {
                            if ( $obj->getVisibility() < 1 ) {
                                $tags[$obj->getUid()] = $obj->getName() ;
                            }
                            $tags2[$obj->getUid()] = ["id" => $obj->getUid(), "title" => $obj->getName(), "visibility"  => $obj->getVisibility()] ;
                     //   }
                    }
                }
            }

            $objArray =  $event->getEventCategory() ;

            if( is_object( $objArray)) {
                /** @var Category $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                        $categories[$obj->getUid()] = $obj->getTitle() ;
                        $categories2[$obj->getUid()] = ["id" => $obj->getUid(), "title" => $obj->getTitle(), "description" => $obj->getDescription(), "sorting" => $obj->getSorting()];
                    }
                }
            }

            unset($obj) ;
            unset($objArray) ;

            if( ! $this->settings['filter']['hideMonthDropdown']) {
                $month = $event->getStartDate()->format("m.Y");
                $months[$month] = $month;
            }
        }

        // Now do sorting

        $sortArray = [];
        foreach($categories2 as $key => $array) {
            if( $this->settings['filter']['sorttags'] == "sorting" ) {
                $sortArray[$key] = substr( "00000000000" . $array['sorting'], -12 , 12 )  ;
            } else {
                $sortArray[$key] = ucfirst ( $array['title']  ) ;
            }
        }

        array_multisort($sortArray, SORT_ASC, SORT_STRING , $categories2);

        $sortArray = [];
        foreach($tags as $key => $value) {
            $sortArray[$key] = ucfirst ( $value) ;
        }
        array_multisort($sortArray, SORT_ASC, SORT_NUMERIC, $tags);

        usort($tags2, fn($a, $b) => strcmp(ucfirst((string) $a["title"]), ucfirst((string) $b["title"])));
        return ["locations" => $locations, "organizers" => $organizers, "citys" => $citys, "tags" => $tags, "tags2" => $tags2, "categories" => $categories, "categories2" => $categories2, "months" => $months, "category50proz" => intval ( (count($categories2) ) / 2 ), "tag50proz" => intval ( (count($tags2) +1) / 2 )] ;
    }


    public function generateFilterWithoutTagsCats(QueryResultInterface $events , $filter ) {
        $locations = [] ;
        $organizers = [] ;
        $citys = [] ;
        $months = [] ;

        if( $this->settings['filter']['hideCityDropdown'] && $this->settings['filter']['hideMonthDropdown']
            && $this->settings['filter']['hideOrganizerDropdown']) {
            return ["locations" => $locations, "organizers" => $organizers, "citys" => $citys, "months" => $months] ;
        }
        /** @var Event $event */
        $eventsArray = $events->toArray() ;
        $this->debugArray[] = "After converting to Array :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " | Line: " . __LINE__ ;

        $x = 0 ;
      //  while( $event = $events->getOffest($x) instanceof  \JVelletti\JvEvents\Domain\Model\Event ) {
         foreach ($eventsArray as $key => $event ) {
             $x++ ;
            // first fill the Options for the Filters to have only options with Events

            if( ! $this->settings['filter']['hideCityDropdown']) {
                /** @var Location $obj */
                $obj =  $event->getLocation() ;
                if ( is_object($obj) ) {
                    $locations[$obj->getUid()] = $obj->getName()  ;

                    if(! in_array(ucfirst($obj->getCity()) , $citys )) {
                        // no online Events in CITY Filter
                        if( intval( $obj->getLat())  != 0 || intval($obj->getLng() != 0 )) {
                            $citys[$obj->getCity()] = ucfirst($obj->getCity()) ;
                        }
                    }
                }
            }

            if( ! $this->settings['filter']['hideOrganizerDropdown']) {
                /** @var Organizer $obj */
                $obj = $event->getOrganizer();
                if (is_object($obj)) {
                    if ( $this->settings['ShowFilter'] == 6 ) {
                        $orgName = str_replace(" " , "+" , $obj->getName() ) ;
                        if(! in_array($orgName , $organizers )) {
                            $organizers[$orgName] = $obj->getName() ;
                        }
                    } else {
                        $organizers[$obj->getUid()] = $obj->getName();
                    }

                }
            }


            unset($obj) ;

            if( ! $this->settings['filter']['hideMonthDropdown']) {
                $month = $event->getStartDate()->format("m.Y");
                $months[$month] = $month;
            }
        }

        return ["locations" => $locations, "organizers" => $organizers, "citys" => $citys, "months" => $months] ;
    }

    public function generateOrgFilter($orgArray , $filter )
    {
        $tags = [];
        $tags2 = [];
        $categories = [];
        $categories2 = [];
        $years = [];


        /** @var Organizer $organizer */
        foreach ($orgArray as $key => $organizer ) {
            // first fill the Options for the Filters to have only options with Organizer
            /** @var Tag $obj */
            $objArray =  $organizer->getTags() ;
            if( is_object( $objArray)) {

                /** @var Tag $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                        $tags[$obj->getUid()] = $obj->getName() ;
                        $tags2[$obj->getUid()] = ["id" => $obj->getUid(), "title" => $obj->getName()] ;
                    }
                }
            }
            $objArray =  $organizer->getOrganizerCategory() ;

            if( is_object( $objArray)) {
                /** @var Category $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                        $categories[$obj->getUid()] = $obj->getTitle() ;
                        $categories2[$obj->getUid()] = ["id" => $obj->getUid(), "title" => $obj->getTitle(), "description" => $obj->getDescription(), "sorting" => $obj->getSorting()];
                    }
                }
            }

            unset($obj) ;
            unset($objArray) ;

            $year = date( "Y" , $organizer->getCrdate() ) ;
            $years[$year] = $year ;

        }
        $sortArray = [];
        foreach($categories2 as $key => $array) {
            if( $this->settings['filter']['sorttags'] == "sorting" ) {
                $sortArray[$key] = substr( "00000000000" . $array['sorting'], -12 , 12 )  ;
            } else {
                $sortArray[$key] = ucfirst ( $array['title']  ) ;
            }
        }

        array_multisort($sortArray, SORT_ASC, SORT_STRING , $categories2);

        $sortArray = [];
        foreach($tags as $key => $value) {
            $sortArray[$key] = ucfirst ( $value) ;
        }
        array_multisort($sortArray, SORT_ASC, SORT_NUMERIC, $tags);

        usort($tags2, fn($a, $b) => strcmp(ucfirst((string) $a["title"]), ucfirst((string) $b["title"])));

        ksort($years );

        return ["tags" => $tags, "tags2" => $tags2, "categories" => $categories, "categories2" => $categories2, "years" => $years, "category50proz" => intval ( (count($categories2) ) / 2 ), "tag50proz" => intval ( (count($tags2) +1) / 2 )] ;

    }

    public function generateOrgFilterFast( $filter )
    {
        $tags = [];
        $tags2 = [];

        if ( array_key_exists( "tags.uid" , $filter ) && (is_countable($filter["tags.uid"]) ? count( $filter["tags.uid"]  ) : 0) > 0  ) {
            $allTags = $this->tagRepository->findAllonAllPagesByUids(  $filter["tags.uid"]  ) ;
        } else {
            $allTags = $this->tagRepository->findAllonAllPages(2 ,$filter  ) ;
        }

        foreach ($allTags as $obj) {
            $tags[$obj->getUid()] = $obj->getName() ;
            $tags2[$obj->getUid()] = ["id" => $obj->getUid(), "title" => $obj->getName()] ;
        }

        $sortArray = [];
        foreach($tags as $key => $value) {
            $sortArray[$key] = ucfirst ( (string) $value) ;
        }
        array_multisort($sortArray, SORT_ASC, SORT_NUMERIC, $tags);
        usort($tags2, fn($a, $b) => strcmp(ucfirst((string) $a["title"]), ucfirst((string) $b["title"])));



        return ["tags" => $tags, "tags2" => $tags2] ;

    }


    private function array_msort($array, $cols)
    {
        $colarr = [];
        foreach ($cols as $col => $order) {
            $colarr[$col] = [];
            foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower((string) $row[$col]); }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\''.$col.'\'],'.$order.',';
        }
        $eval = substr($eval,0,-1).');';
        eval($eval);
        $ret = [];
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k,1);
                if (!isset($ret[$k])) $ret[$k] = $array[$k];
                $ret[$k][$col] = $array[$k][$col];
            }
        }
        return $ret;

    }



    /**
     * @param string $templatePath
     * @param string $templateName
     * @param string $format  default html
     *
     * @return StandaloneView object
     */
    public function getEmailRenderer($templatePath = '' , $templateName = 'default' , $format='html') {
        // create another instance of Fluid
        /** @var StandaloneView $renderer */
        $renderer = GeneralUtility::makeInstance(StandaloneView::class);
        // set the request directly on the renderer
        $renderer->setRequest($this->request);

        // override the template path with individual settings in TypoScript
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        if (isset($extbaseFrameworkConfiguration['view']['partialRootPaths']) && is_array($extbaseFrameworkConfiguration['view']['partialFilesRootPaths']) ) {
            $partialPaths = $extbaseFrameworkConfiguration['view']['partialFilesRootPaths'];
        } else {
            $partialPaths = [0 => GeneralUtility::getFileAbsFileName( "EXT:jv_events/Resources/Private/Partials" )] ;
        }
        if (isset($extbaseFrameworkConfiguration['view']['layoutRootPaths']) && is_array($extbaseFrameworkConfiguration['view']['layoutRootPaths'])) {
            $layoutPaths = $extbaseFrameworkConfiguration['view']['layoutRootPaths'];
        } else {
            $layoutPaths =  [0 => GeneralUtility::getFileAbsFileName( "EXT:jv_events/Resources/Private/Layouts" )] ;
        }
        # Jan 2021 : as we want to use same email layout in frontend as in backend, we need to remove "/Backend" from layout path
        #
        foreach ( $layoutPaths as $key => $layoutPath ) {
            $layoutPaths[$key] = str_replace("/Backend" , "" , (string) $layoutPath ) ;
        }
        if ( $templatePath == '') {

			if (isset($extbaseFrameworkConfiguration['view']['templateRootPath']) && strlen((string) $extbaseFrameworkConfiguration['view']['templateRootPath']) > 0) {
				$templatePath = GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath'][0]);
            } else {
                $templatePath =   GeneralUtility::getFileAbsFileName( "EXT:jv_events/Resources/Private/Templates" );
            }
        }


        $templateFile = $templatePath . $templateName . '.html';

        // set the e-mail template
        $renderer->setLayoutRootPaths( $layoutPaths);
        $renderer->setTemplatePathAndFilename($templateFile);
        $renderer->setPartialRootPaths($partialPaths);
        $renderer->setFormat($format) ;
        // and return the new Fluid instance
        return $renderer;
    }


    
    /**
     * function sendEmail
     *
     * @param string $partialName possible Values: organizer, registrant, developer or admin
     * @throws \Exception
     * @param array $recipient
     * @param array|bool $otherEvents Array with IDs of other Events or False
     * @param array|bool $replyTo  Array with Email and Name of replyto or false
     * @param Registrant $oldReg  Copy of a registrant. may dbe different if user is alread registered with same email.
     * @return boolean
     */
    public function sendEmail(Event $event = NULL, Registrant $registrant = NULL , $partialName ='', $recipient=[] , $otherEvents=false , $replyTo=false , Registrant $oldReg = NULL )
    {
        $subevents = null;
        if (!GeneralUtility::validEmail($this->settings['register']['senderEmail'])) {
            throw new \Exception('plugin.jv_events.settings.register.senderEmail is not a valid Email Address. Is needed as Sender E-mail');
        }
        if( !$replyTo ) {
            $replyTo = $this->settings['register']['senderEmail'] ;
        }

        $returnPath = MailUtility::getSystemFromAddress();
        if ( $returnPath == "no-reply@example.com" ) {
            $returnPath = $this->settings['register']['senderEmail'] ;
        } else {
            $this->settings['register']['senderEmail'] = $returnPath ;
        }

        if( ! $this->settings['register']['senderName']) {
            // in old version sendername was written wrong .. fix this
            if( $this->settings['register']['sendername']) {
                $this->settings['register']['senderName'] = $this->settings['register']['sendername'];
            } else {
                // we did not find any Senders Name , use Email als From Name
                $this->settings['register']['senderName'] = $this->settings['register']['senderEmail'] ;
            }
        }

        $sender = [$this->settings['register']['senderEmail']
                        =>
                        $this->settings['register']['senderName']];



        foreach ($recipient as $key => $value ) {
            if (!GeneralUtility::validEmail($key )) {
                throw new \Exception(var_export( $recipient , true ) . "( " . $key . ") " . ' is not a valid -recipient- Email Address. ');
            }
        }

        $signature = false;
        if (ExtensionManagementUtility::isLoaded('mailsignature')) {
            /** @var SignatureService $signatureService */
            $signatureService = GeneralUtility::makeInstance(SignatureService::class);
            $signature = $signatureService->getSignature($this->settings['signature']['uid']);
        }

        if( $event ) {
            $querysettings =$this->subeventRepository->getTYPO3QuerySettings() ;
            $querysettings->setStoragePageIds([$event->getPid()]) ;

            $this->subeventRepository->setDefaultQuerySettings( $querysettings );
            $subevents = $this->subeventRepository->findByEventAllpages($event->getUid() , FALSE ) ;
        }

        /** @var StandaloneView $renderer */
        $renderer = $this->getEmailRenderer( '' ,  '/Registrant/Email/' . $this->settings['LayoutRegister']);


        if ( ! is_object( $subevents ) ) {
            $renderer->assign('subevents', null );
            $renderer->assign('subeventcount', 0 );
        } else {
            $renderer->assign('subevents', $subevents);
            $renderer->assign('subeventcount', $subevents->count() + 1 );
        }

        $renderer->assign('signature', $signature);
        $renderer->assign('registrant', $registrant);
        if( !is_object($oldReg) ) {
            $oldReg = $registrant ;
        }
        $renderer->assign('oldReg', $oldReg );
        $renderer->assign('event', $event);
        $renderer->assign('otherEvents', $otherEvents);
        $renderer->assign('partial', "Registrant/Partial" . $this->settings['LayoutRegister'] . "/Emails/" . $partialName);
        $renderer->assign('settings', $this->settings);


        // read Colors and font settings from EmConfigurationUtility as object
        $renderer->assign('emConf', EmConfigurationUtility::getEmConf(TRUE));
        $renderer->assign('registrant', $registrant);

        $renderer->assign('event', $event);
        $renderer->assign('registrant', $registrant);
        $renderer->assign('layoutName', 'EmailSubject' . $partialName);

        // and do the rendering magic
        $subject = str_replace("\r", "", $renderer->render());
        $subject = str_replace("\n", "", $subject);
        $subject = str_replace("\t", "", $subject);

        $subject = '=?utf-8?B?' . base64_encode($subject) . '?=';

        // Possible attachments here ... Need to integrate to do a better check for Contact  ID
        $layout = $this->settings['LayoutRegister'] ;
        $attachments = $this->settings['register']['attachments'][$layout] ;
        if ( is_array( $attachments ) ) {
            if ($registrant->getMore6int() == 0) {
                if( strlen( trim( (string) $registrant->getContactId() ) ) < 3 && $registrant->getMore6int() != 0  ) {
                    $registrant->setMore6int(1) ;
                }
            }
        }


        $renderer->assign('layoutName', 'EmailPlain');
        $plainMsg = $renderer->render();
        $renderer->assign('layoutName', 'EmailHtml');
        $emailBody = $renderer->render();

        /** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
        $message = GeneralUtility::makeInstance(MailMessage::class);
        $message->setTo($recipient)
            ->setFrom($sender)
            ->setSubject($subject);

        $message->setReplyTo($replyTo) ;
        $message->setReturnPath($returnPath);

        $rootPath = Environment::getPublicPath() ;

        if ( is_array( $attachments ) ) {
            if( $registrant->getMore6int() == 1  ) {
                foreach ($attachments as $attachment) {
                    if( !str_starts_with((string) $attachment, "/") ) {
                        $attachment = "/" . $attachment ;
                    }
                    $attachment = $rootPath . $attachment ;
                    if ( file_exists($attachment )) {
                        $message->attachFromPath( $attachment) ;
                    }
                }
            }
        }

        /** @var Typo3Version $tt */
        $tt = GeneralUtility::makeInstance( Typo3Version::class ) ;
        if( $tt->getMajorVersion()  < 10 ) {
            $message->html($emailBody);
            $message->text($plainMsg);
        } else {
            $message->html($emailBody, 'utf-8');
            $message->text($plainMsg, 'utf-8');
        }



        $message->send();
        return $message->isSent();


    }
    /**
     * @param string $recipient Who shall get the debug Email
     * @param string $sender debug Email $sender
     * @param string $subject use a Subject that can be filtered like: [EVENT][ERROR] ...
     * @param string $plainMsg the message it self. Newlines will be splittet to br. Tcan be formated a litte bit
     * @param string $htmlMsg the message it self.
     * @return bool
     */
    public function sendDebugEmail($recipient,$sender ,$subject , $plainMsg , $htmlMsg = '') {
        /** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
        $message = GeneralUtility::makeInstance(MailMessage::class);


        $returnPath = MailUtility::getSystemFromAddress();
        if ( $returnPath != "no-reply@example.com") {
            $message->setReturnPath($returnPath);
        } else {
            $message->setReturnPath($sender);
        }

        /** @var Typo3Version $tt */
        $tt = GeneralUtility::makeInstance( Typo3Version::class ) ;
        if ( !$htmlMsg || $htmlMsg == '' ) {
            $htmlMsg =  nl2br( $plainMsg );
            $subject .= " - converted" ;
        }

        if( $tt->getMajorVersion()  < 10 ) {
            $message->html($htmlMsg);
            $message->text($plainMsg);
        } else {
            $message->html($htmlMsg, 'utf-8');
            $message->text($plainMsg, 'utf-8');
        }


        $message->setTo($recipient)
            ->setFrom($sender)
            ->setSubject($subject);

        $message->send();
    }

    /**
     * @return bool
     */
    public function isUserOrganizer() {
        $groups = GeneralUtility::trimExplode("," , $this->settings['feEdit']['organizerGroudIds'] , TRUE ) ;
        $feuserGroups = GeneralUtility::trimExplode("," ,  $this->frontendUser->user['usergroup']  , TRUE ) ;
        foreach( $groups as $group ) {
            if( in_array( $group  , $feuserGroups )) {
                return true  ;
            }
        }
        return false  ;
    }

    /**
     * @return bool
     */
    public function isAdminOrganizer() {
        $groups = GeneralUtility::trimExplode("," , $this->settings['feEdit']['adminOrganizerGroudIds'] , TRUE ) ;
        $feuserGroups = GeneralUtility::trimExplode("," ,  $this->frontendUser->user['usergroup']  , TRUE ) ;
        foreach( $groups as $group ) {
            if( in_array( $group  , $feuserGroups )) {
                return true  ;
            }
        }
        return false  ;
    }

    /**
     * @param $groupId
     * @return bool
     */
    public function hasUserGroup($groupId) {
        $feuserGroups = GeneralUtility::trimExplode("," ,  $this->frontendUser->user['usergroup']  , TRUE ) ;
        return in_array( $groupId  , $feuserGroups ) ;
    }

    /**
     * @param Organizer|LazyLoadingProxy $organizer
     * @return bool
     */
    public function hasUserAccess( $organizer ) {
        if(! is_object( $organizer ) ) {
           return false ;
        }
        $feuserUid = (int)$this->frontendUser->user['uid']  ;
        $users = GeneralUtility::trimExplode("," , $organizer->getAccessUsers() , TRUE ) ;
        if( in_array( $feuserUid  , $users )) {
            return true  ;
        } else {
            $groups = GeneralUtility::trimExplode("," , $organizer->getAccessGroups() , TRUE ) ;
            $feuserGroups = GeneralUtility::trimExplode("," ,  $this->frontendUser->user['usergroup']  , TRUE ) ;
            foreach( $groups as $group ) {
                if( in_array( $group  , $feuserGroups )) {
                    return true  ;
                }
            }
        }
        return false  ;
    }

    /**
     * @param bool $checkAccess
     * @return array|bool|Organizer|object
     * @throws NoSuchArgumentException
     * @throws InvalidQueryException
     */
    public function getOrganizer($doNotCheckAccess=true) {
        $id = null;
        $organizer = null;
        $frontendUser = $this->request->getAttribute('frontend.user');
        $this->frontendUser = $frontendUser ;
        if ( !is_object($frontendUser) ||  (int)$frontendUser->user['uid'] < 1 ) {
            return false ;
        }
        /** @var Organizer $organizer */
        if( $this->request->hasArgument('organizer')) {
            $id = intval($this->request->getArgument('organizer'));
        }
        if( $id > 0 ) {
            $organizer = $this->organizerRepository->findByUidAllpages($id , FALSE);
        }

        if ($organizer instanceof Organizer) {
            if( $doNotCheckAccess || $this->hasUserAccess($organizer) ) {
                return $organizer ;
            }
        }

        // TODo : think about a better solution how to manage that a user can be linked to more than one Organizer
        // actually it will not work
        $organizer = $this->organizerRepository->findByUserAllpages( (int)$frontendUser->user['uid'] , FALSE )->getFirst() ;
        if ($organizer instanceof Organizer) {
            return $organizer ;
        }

        return false ;
    }

    /**
     * translate function
     * @param string $label the locallang label to translate
     * @return string the localized String
     */
    protected function translate($label, $arguments = NULL) {
        return LocalizationUtility::translate($label, 'JvEvents', $arguments);
    }

    protected function showNoDomainMxError($email ) {
        if( trim((string) $email ) == '' || trim((string) $email ) == '-'  ) {
            return ;
        }
        $domain  = explode('@', (string) $email);
        if( count($domain) > 0 ) {
            if( ! checkdnsrr($domain[1], 'MX') ) {
                $msg = sprintf( $this->translate('error.email.noMxRecord') , "@" . $domain[1] ) . " ";

                $this->addFlashMessage( $msg , 'No MX entry for Maildomain!', AbstractMessage::WARNING);
            }
        }

    }

    /**
     * Get floatet microtime
     * @return float
     */
    public function microtime_float() {
        [$usec, $sec] = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

}