<?php
namespace JVE\JvEvents\Controller;

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
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Context\Context;
use JVE\JvEvents\Utility\EmConfigurationUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use JVE\JvEvents\Domain\Model\Location;
use JVE\JvEvents\Domain\Model\Organizer;
use JVE\JvEvents\Domain\Model\Category;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use JVE\JvEvents\Domain\Model\Event;
use JVE\JvEvents\Domain\Model\Registrant;
use JVE\JvEvents\Domain\Model\Tag;
use JVE\JvEvents\Domain\Repository\CategoryRepository;
use JVE\JvEvents\Domain\Repository\EventRepository;
use JVE\JvEvents\Domain\Repository\LocationRepository;
use JVE\JvEvents\Domain\Repository\OrganizerRepository;
use JVE\JvEvents\Domain\Repository\RegistrantRepository;
use JVE\JvEvents\Domain\Repository\StaticCountryRepository;
use JVE\JvEvents\Domain\Repository\SubeventRepository;
use JVE\JvEvents\Domain\Repository\TagRepository;
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

    /**
     * @param CacheService $cacheService
     * @return void
     */
    public function injectCacheService(CacheService $cacheService) {
        $this->cacheService = $cacheService ;
    }


    /**
     * @param TagRepository $tagRepository
     */
    public function injectTagRepository(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param RegistrantRepository $registrantRepository
     */
    public function injectRegistrantRepository(RegistrantRepository $registrantRepository)
    {
        $this->registrantRepository = $registrantRepository;
    }

    /**
     * @param LocationRepository $locationRepository
     */
    public function injectLocationRepository(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @param OrganizerRepository $organizerRepository
     */
    public function injectOrganizerRepository(OrganizerRepository $organizerRepository)
    {
        $this->organizerRepository = $organizerRepository;
    }

    /**
     * @param EventRepository $eventRepository
     */
    public function injectEventRepository(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param SubeventRepository $subeventRepository
     */
    public function injectSubeventRepository(SubeventRepository $subeventRepository)
    {
        $this->subeventRepository = $subeventRepository;
    }

    /**
     * @param StaticCountryRepository $staticCountryRepository
     */
    public function injectStaticCountryRepository(StaticCountryRepository $staticCountryRepository)
    {
        $this->staticCountryRepository = $staticCountryRepository;
    }



    /**
     * @param PersistenceManager $persistenceManager
     */
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

        $this->settings['pageId']						=  $GLOBALS['TSFE']->id ;
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
        $this->settings['phpTimeZone'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] ? $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] : "UTC" ;
        $fields = $this->settings['register']['requiredFields'][$layout] ;
        if( array_key_exists( 'Register' , $this->settings )  && array_key_exists( 'add_mandatory_fields' , $this->settings['Register'] ) && strlen( $this->settings['Register']['add_mandatory_fields'] ) > 1 ) {
            $fields .= "," . $this->settings['Register']['add_mandatory_fields'] ;
        }

        $required  = GeneralUtility::trimExplode( "," , $fields , true ) ;
        if ( count($required) > 0 ) {
            foreach( $required as $key => $field ) {
                $this->settings['register']['required'][$field] = TRUE ;
            }
        }

        if( isset($this->settings['register']['formFields'][$layout]) ) {
            $fields = $this->settings['register']['formFields'][$layout] ;
            $formFields  = GeneralUtility::trimExplode( "," , $fields , true ) ;
            if( count( $formFields ) > 0 ) {
                foreach( $formFields as $key => $field ) {
                    if( $field) {
                        $this->settings['register']['allformFields'][$field] = TRUE ;
                    }
                }
            }
        }



        if( !$this->objectManager) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class) ;
        }
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
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
                        $tags[] = array( "id" => $Id , "title" =>  $tag->getNameAfterColon() ) ;
                    } else {
                        $tags[] = array( "id" => $Id , "title" => $tag->getName()  ) ;
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
        $organizers = array();
        $tags2 = array();
        $categories2 = array();

        $filterTags = GeneralUtility::intExplode(',', $filter['tags'], true);
        if( is_array($filterTags)) {
            foreach ($filterTags as $Id ) {
                $tag = $this->tagRepository->findByUid($Id) ;
                $tags2[] = array( "id" => $Id , "title" => $tag->getName()  ) ;
            }
        }

        $filterCats = GeneralUtility::intExplode(',', $filter['categories'], true);
        if( is_array($filterCats)) {
            foreach ($filterCats as $Id ) {
                $cat = $this->categoryRepository->findByUid($Id) ;
                $categories2[] = array( "id" => $Id , "title" => $cat->getTitle()  , "description" => $cat->getDescription() , "sorting" => $cat->getSorting() ) ;
            }
        }
        $sortArray = array();
        foreach($categories2 as $key => $array) {
            if( $this->settings['filter']['sorttags'] == "sorting" ) {
                $sortArray[$key] = substr( "00000000000" . $array['sorting'], -12 , 12 )  ;
            } else {
                $sortArray[$key] = ucfirst ( $array['title']  ) ;
            }
        }

        array_multisort($sortArray, SORT_ASC, SORT_STRING , $categories2);

        $orgArr = $this->organizerRepository->findByFilterAllpages( FALSE , TRUE , FALSE , FALSE )  ;
        if( $orgArr ) {
            foreach ( $orgArr as $organizer ) {
                $organizers[$organizer->getUid() ] = $organizer->getName() ;
            }
        }

        return array(
            "organizers" => $organizers ,
            "tags2" => $tags2 ,
            "categories2" => $categories2 ,
            "category50proz" => intval ( (count($categories2) ) / 2 ) ,
            "tag50proz" => intval ( (count($tags2) +1) / 2 )
        ) ;
    }

    public function generateFilter(QueryResultInterface $events , $filter ) {
        $locations = array() ;
        $organizers = array() ;
        $citys = array() ;
        $tags = array() ;
        $tags2 = array() ;
        $categories = array() ;
        $categories2 = array() ;
        $months = array() ;


        /** @var Event $event */
        $eventsArray = $events->toArray() ;
        // while( $event instanceof  \JVE\JvEvents\Domain\Model\Event ) {

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
                $filterTags = GeneralUtility::intExplode(',', $filter['tags'], true);

                /** @var Tag $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                      //  if ( $filter['combinetags'] == "0" || count($filterTags) < 1 || in_array(  $obj->getUid() , $filterTags )) {
                            if ( $obj->getVisibility() < 1 ) {
                                $tags[$obj->getUid()] = $obj->getName($filter['tagShowAfterColon'] ) ;
                            }
                            $tags2[$obj->getUid()] = array( "id" => $obj->getUid() , "title" => $obj->getName($filter['tagShowAfterColon'] ) , "visibility"  => $obj->getVisibility()) ;
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
                        $categories2[$obj->getUid()] = array( "id" => $obj->getUid() , "title" => $obj->getTitle() , "description" => $obj->getDescription() , "sorting" => $obj->getSorting());
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

        $sortArray = array();
        foreach($categories2 as $key => $array) {
            if( $this->settings['filter']['sorttags'] == "sorting" ) {
                $sortArray[$key] = substr( "00000000000" . $array['sorting'], -12 , 12 )  ;
            } else {
                $sortArray[$key] = ucfirst ( $array['title']  ) ;
            }
        }

        array_multisort($sortArray, SORT_ASC, SORT_STRING , $categories2);

        $sortArray = array();
        foreach($tags as $key => $value) {
            $sortArray[$key] = ucfirst ( $value) ;
        }
        array_multisort($sortArray, SORT_ASC, SORT_NUMERIC, $tags);

        usort($tags2, function ($a, $b) { return strcmp(ucfirst($a["title"]), ucfirst($b["title"])); });
        return array(
            "locations" => $locations ,  
            "organizers" => $organizers ,  
            "citys" => $citys ,  
            "tags" => $tags ,  
            "tags2" => $tags2 ,
            "categories" => $categories ,
            "categories2" => $categories2 ,
            "months" => $months ,
            "category50proz" => intval ( (count($categories2) ) / 2 ) ,
            "tag50proz" => intval ( (count($tags2) +1) / 2 )
            ) ;
    }


    public function generateFilterWithoutTagsCats(QueryResultInterface $events , $filter ) {
        $locations = array() ;
        $organizers = array() ;
        $citys = array() ;
        $months = array() ;

        if( $this->settings['filter']['hideCityDropdown'] && $this->settings['filter']['hideMonthDropdown']
            && $this->settings['filter']['hideOrganizerDropdown']) {
            return array(
                "locations" => $locations ,
                "organizers" => $organizers ,
                "citys" => $citys ,
                "months" => $months ,
            ) ;
        }
        /** @var Event $event */
        $eventsArray = $events->toArray() ;
        $this->debugArray[] = "After converting to Array :" . intval( 1000 * ( $this->microtime_float() - 	$this->timeStart )) . " | Line: " . __LINE__ ;

        $x = 0 ;
      //  while( $event = $events->getOffest($x) instanceof  \JVE\JvEvents\Domain\Model\Event ) {
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

        return array(
            "locations" => $locations ,
            "organizers" => $organizers ,
            "citys" => $citys ,
            "months" => $months ,
        ) ;
    }

    public function generateOrgFilter($orgArray , $filter )
    {
        $tags = array();
        $tags2 = array();
        $categories = array();
        $categories2 = array();
        $years = array();


        /** @var Organizer $organizer */
        foreach ($orgArray as $key => $organizer ) {
            // first fill the Options for the Filters to have only options with Organizer
            /** @var Tag $obj */
            $objArray =  $organizer->getTags() ;
            if( is_object( $objArray)) {

                /** @var Tag $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                        $tags[$obj->getUid()] = $obj->getName( $filter['tagShowAfterColon'] ) ;
                        $tags2[$obj->getUid()] = array( "id" => $obj->getUid() , "title" => $obj->getName( $filter['tagShowAfterColon'] )  ) ;
                    }
                }
            }
            $objArray =  $organizer->getOrganizerCategory() ;

            if( is_object( $objArray)) {
                /** @var Category $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                        $categories[$obj->getUid()] = $obj->getTitle() ;
                        $categories2[$obj->getUid()] = array( "id" => $obj->getUid() , "title" => $obj->getTitle() , "description" => $obj->getDescription() , "sorting" => $obj->getSorting());
                    }
                }
            }

            unset($obj) ;
            unset($objArray) ;

            $year = date( "Y" , $organizer->getCrdate() ) ;
            $years[$year] = $year ;

        }
        $sortArray = array();
        foreach($categories2 as $key => $array) {
            if( $this->settings['filter']['sorttags'] == "sorting" ) {
                $sortArray[$key] = substr( "00000000000" . $array['sorting'], -12 , 12 )  ;
            } else {
                $sortArray[$key] = ucfirst ( $array['title']  ) ;
            }
        }

        array_multisort($sortArray, SORT_ASC, SORT_STRING , $categories2);

        $sortArray = array();
        foreach($tags as $key => $value) {
            $sortArray[$key] = ucfirst ( $value) ;
        }
        array_multisort($sortArray, SORT_ASC, SORT_NUMERIC, $tags);

        usort($tags2, function ($a, $b) { return strcmp(ucfirst($a["title"]), ucfirst($b["title"])); });

        ksort($years );

        return array(
            "tags" => $tags ,
            "tags2" => $tags2 ,
            "categories" => $categories ,
            "categories2" => $categories2 ,
            "years" => $years ,
            "category50proz" => intval ( (count($categories2) ) / 2 ) ,
            "tag50proz" => intval ( (count($tags2) +1) / 2 )
        ) ;

    }

    public function generateOrgFilterFast( $filter )
    {
        $tags = array();
        $tags2 = array();

        if ( array_key_exists( "tags.uid" , $filter ) && count( $filter["tags.uid"]  ) > 0  ) {
            $allTags = $this->tagRepository->findAllonAllPagesByUids(  $filter["tags.uid"]  ) ;
        } else {
            $allTags = $this->tagRepository->findAllonAllPages(2 ,$filter  ) ;
        }

        foreach ($allTags as $obj) {
            $tags[$obj->getUid()] = $obj->getName() ;
            $tags2[$obj->getUid()] = array( "id" => $obj->getUid() , "title" => $obj->getName()  ) ;
        }

        $sortArray = array();
        foreach($tags as $key => $value) {
            $sortArray[$key] = ucfirst ( $value) ;
        }
        array_multisort($sortArray, SORT_ASC, SORT_NUMERIC, $tags);
        usort($tags2, function ($a, $b) { return strcmp(ucfirst($a["title"]), ucfirst($b["title"])); });



        return array(
            "tags" => $tags ,
            "tags2" => $tags2
        ) ;

    }


    private function array_msort($array, $cols)
    {
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\''.$col.'\'],'.$order.',';
        }
        $eval = substr($eval,0,-1).');';
        eval($eval);
        $ret = array();
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
        $renderer = $this->objectManager->get(StandaloneView::class);



        // set the controller context
        $controllerContext = $this->buildControllerContext();

        $controllerContext->setRequest($this->request);
       // $controllerContext->getRequest()->setControllerActionName("Create") ;
        $renderer->setControllerContext($controllerContext);

        // override the template path with individual settings in TypoScript
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        if (isset($extbaseFrameworkConfiguration['view']['partialRootPaths']) && is_array($extbaseFrameworkConfiguration['view']['partialFilesRootPaths']) ) {
            $partialPaths = $extbaseFrameworkConfiguration['view']['partialFilesRootPaths'];
        } else {
            $partialPaths = array( 0 => GeneralUtility::getFileAbsFileName( "typo3conf/ext/jv_events/Resources/Private/Partials" ) ) ;
        }
        if (isset($extbaseFrameworkConfiguration['view']['layoutRootPaths']) && is_array($extbaseFrameworkConfiguration['view']['layoutRootPaths'])) {
            $layoutPaths = $extbaseFrameworkConfiguration['view']['layoutRootPaths'];
        } else {
            $layoutPaths =  array( 0 => GeneralUtility::getFileAbsFileName( "typo3conf/ext/jv_events/Resources/Private/Layouts" )) ;
        }
        # Jan 2021 : as we want to use same email layout in frontend as in backend, we need to remove "/Backend" from layout path
        #
        foreach ( $layoutPaths as $key => $layoutPath ) {
            $layoutPaths[$key] = str_replace("/Backend" , "" , $layoutPath ) ;
        }
        if ( $templatePath == '') {

			if (isset($extbaseFrameworkConfiguration['view']['templateRootPath']) && strlen($extbaseFrameworkConfiguration['view']['templateRootPath']) > 0) {
				$templatePath = GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath'][0]);
            } else {
                $templatePath =   GeneralUtility::getFileAbsFileName( "typo3conf/ext/jv_events/Resources/Private/Templates" );
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
     * @param Event $event
     * @param Registrant $registrant
     * @param string $partialName possible Values: organizer, registrant, developer or admin
     * @throws \Exception
     * @param array $recipient
     * @param array|bool $otherEvents Array with IDs of other Events or False
     * @param array|bool $replyTo  Array with Email and Name of replyto or false
     * @param Registrant $oldReg  Copy of a registrant. may dbe different if user is alread registered with same email.
     * @return boolean
     */

    public function sendEmail(Event $event = NULL, Registrant $registrant = NULL , $partialName ='', $recipient=array() , $otherEvents=false , $replyTo=false , Registrant $oldReg = NULL )
    {
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

        $sender = array($this->settings['register']['senderEmail']
                        =>
                        $this->settings['register']['senderName']
                    );



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
            $querysettings->setStoragePageIds(array( $event->getPid() )) ;

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
                if( strlen( trim( $registrant->getContactId() ) ) < 3 && $registrant->getMore6int() != 'unbekannt'  ) {
                    $registrant->setMore6int(1) ;
                }
            }
        }


        $renderer->assign('layoutName', 'EmailPlain');
        $plainMsg = $renderer->render();
        $renderer->assign('layoutName', 'EmailHtml');
        $emailBody = $renderer->render();

        /** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
        $message = $this->objectManager->get(MailMessage::class);
        $message->setTo($recipient)
            ->setFrom($sender)
            ->setSubject($subject);

        $message->setReplyTo($replyTo) ;
        $message->setReturnPath($returnPath);

        $rootPath = Environment::getPublicPath() ;

        if ( is_array( $attachments ) ) {
            if( $registrant->getMore6int() == 1  ) {
                foreach ($attachments as $attachment) {
                    if( substr( $attachment , 0 , 1 ) <> "/" ) {
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
            $message->setBody($emailBody, 'text/html');
            $message->addPart($plainMsg, 'text/plain');
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
        $message = $this->objectManager->get(MailMessage::class);


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
            $message->setBody($htmlMsg, 'text/html');
            $message->addPart($plainMsg, 'text/plain');
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
        $feuserGroups = GeneralUtility::trimExplode("," ,  $GLOBALS['TSFE']->fe_user->user['usergroup']  , TRUE ) ;
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
        $feuserGroups = GeneralUtility::trimExplode("," ,  $GLOBALS['TSFE']->fe_user->user['usergroup']  , TRUE ) ;
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
        $feuserGroups = GeneralUtility::trimExplode("," ,  $GLOBALS['TSFE']->fe_user->user['usergroup']  , TRUE ) ;
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
        $feuserUid = intval( $GLOBALS['TSFE']->fe_user->user['uid'] ) ;
        $users = GeneralUtility::trimExplode("," , $organizer->getAccessUsers() , TRUE ) ;
        if( in_array( $feuserUid  , $users )) {
            return true  ;
        } else {
            $groups = GeneralUtility::trimExplode("," , $organizer->getAccessGroups() , TRUE ) ;
            $feuserGroups = GeneralUtility::trimExplode("," ,  $GLOBALS['TSFE']->fe_user->user['usergroup']  , TRUE ) ;
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
        if (intval($GLOBALS['TSFE']->fe_user->user['uid']) < 1 ) {
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
        $organizer = $this->organizerRepository->findByUserAllpages( intval($GLOBALS['TSFE']->fe_user->user['uid'])  , FALSE )->getFirst() ;
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
        if( trim($email ) == '' || trim($email ) == '-'  ) {
            return ;
        }
        $domain  = explode('@', $email);
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
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

}