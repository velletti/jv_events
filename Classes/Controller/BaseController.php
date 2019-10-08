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
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Messaging\AbstractMessage ;

/**
 * EventController
 */
class BaseController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     *  $eventRepository
     * @var \JVE\JvEvents\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository = NULL;

    /**
     * subeventRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\SubeventRepository
     * @inject
     */
    protected $subeventRepository = NULL;

	/**
	 * staticCountryRepository
	 *
	 * @var \JVE\JvEvents\Domain\Repository\StaticCountryRepository
	 * @inject
	 */
	protected $staticCountryRepository = NULL;

    /**
     * organizerRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\OrganizerRepository
     * @inject
     */
    protected $organizerRepository = NULL;


    /**
     * locationRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\LocationRepository
     * @inject
     */
    protected $locationRepository = NULL;

    /**
     * persistencemanager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager = NULL ;

    /**
     * registrantRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\RegistrantRepository
     * @inject
     */
    protected $registrantRepository = NULL;


    /**
     * categoryRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\CategoryRepository
     * @inject
     */
    protected $categoryRepository = NULL;

    /**
     * tagRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\TagRepository
     * @inject
     */
    protected $tagRepository = NULL;


    /**
     * @var array
     */
    public $debugArray ;


    /**
     * @var float
     */
    public $timeStart ;

    /**
     * action list
     *
     * @return void
     */
    public function initializeAction()
    {
          

        $this->settings['register']['allowedCountrys'] 	=  \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode("," , $this->settings['register']['allowedCountrys'] , true );

        $this->settings['pageId']						=  $GLOBALS['TSFE']->id ;
        $this->settings['servername']					=  \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
        $this->settings['sys_language_uid']				=  $GLOBALS['TSFE']->sys_language_uid ;

        $this->settings['EmConfiguration']	 			= \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();

        // get the list of Required Fields for this layout and store it to the  settings Array
        // seemed faster than separate via a Viewhelper for each Field

        $layout = $this->settings['LayoutRegister'] ;
        $this->settings['phpTimeZone'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] ;
        $fields = $this->settings['register']['requiredFields'][$layout] ;
        $required  = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode( "," , $fields ) ;
        foreach( $required as $key => $field ) {
            $this->settings['register']['required'][$field] = TRUE ;
        }
        $this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
    }

    public function generateFilterAll( $filter )
    {
        $organizers = array();
        $tags2 = array();
        $categories2 = array();

        $filterTags = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $filter['tags'], true);
        if( is_array($filterTags)) {
            foreach ($filterTags as $Id ) {
                $tag = $this->tagRepository->findByUid($Id) ;
                $tags2[] = array( "id" => $Id , "title" => $tag->getName()  ) ;
            }
        }

        $filterCats = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $filter['categories'], true);
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

    public function generateFilter(\TYPO3\CMS\Extbase\Persistence\QueryResultInterface $events , $filter ) {
        $locations = array() ;
        $organizers = array() ;
        $citys = array() ;
        $tags = array() ;
        $tags2 = array() ;
        $categories = array() ;
        $categories2 = array() ;
        $months = array() ;


        /** @var \JVE\JvEvents\Domain\Model\Event $event */
        $eventsArray = $events->toArray() ;
        // while( $event instanceof  \JVE\JvEvents\Domain\Model\Event ) {
        foreach ($eventsArray as $key => $event ) {
            // first fill the Options for the Filters to have only options with Events

            /** @var \JVE\JvEvents\Domain\Model\Location $obj */
            $obj =  $event->getLocation() ;
            if ( is_object($obj) ) {
                $locations[$obj->getUid()] = $obj->getName() ;

                if(! in_array($obj->getCity() , $citys )) {
                    $citys[$obj->getCity()] = $obj->getCity() ;
                }
            }
          
            /** @var \JVE\JvEvents\Domain\Model\Tag $obj */
            $obj =  $event->getOrganizer() ;
            if ( is_object($obj) ) {
                $organizers[$obj->getUid()] = $obj->getName() ;
            }

            $objArray =  $event->getTags() ;
            if( is_object( $objArray)) {
                // Plugin settings: are there TAGS as filter defined in the Plugin
                $filterTags = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $filter['tags'], true);

                /** @var \JVE\JvEvents\Domain\Model\Tag $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                      //  if ( $filter['combinetags'] == "0" || count($filterTags) < 1 || in_array(  $obj->getUid() , $filterTags )) {
                            $tags[$obj->getUid()] = $obj->getName() ;
                            $tags2[$obj->getUid()] = array( "id" => $obj->getUid() , "title" => $obj->getName()  ) ;
                     //   }
                    }
                }
            }
            $objArray =  $event->getEventCategory() ;

            if( is_object( $objArray)) {
                /** @var \JVE\JvEvents\Domain\Model\Category $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                        $categories[$obj->getUid()] = $obj->getTitle() ;
                        $categories2[$obj->getUid()] = array( "id" => $obj->getUid() , "title" => $obj->getTitle() , "description" => $obj->getDescription() , "sorting" => $obj->getSorting());
                    }
                }
            }
           
            unset($obj) ;
            unset($objArray) ;

            $month = $event->getStartDate()->format("m.Y") ;
            $months[$month] = $month ;
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

    public function generateOrgFilter($orgArray , $filter )
    {
        $tags = array();
        $tags2 = array();
        $categories = array();
        $categories2 = array();
        $years = array();


        /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
        foreach ($orgArray as $key => $organizer ) {
            // first fill the Options for the Filters to have only options with Organizer


            /** @var \JVE\JvEvents\Domain\Model\Tag $obj */

            $objArray =  $organizer->getTags() ;
            if( is_object( $objArray)) {

                /** @var \JVE\JvEvents\Domain\Model\Tag $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                        $tags[$obj->getUid()] = $obj->getName() ;
                        $tags2[$obj->getUid()] = array( "id" => $obj->getUid() , "title" => $obj->getName()  ) ;
                    }
                }
            }
            $objArray =  $organizer->getOrganizerCategory() ;

            if( is_object( $objArray)) {
                /** @var \JVE\JvEvents\Domain\Model\Category $obj */
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

        $allTags = $this->tagRepository->findAllonAllPages(2) ;
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
     *
     * @return \TYPO3\CMS\Fluid\View\StandaloneView object
     */
    public function getEmailRenderer($templatePath = '' , $templateName = 'default') {
        // create another instance of Fluid
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
        $renderer = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        
        // set the controller context
        $controllerContext = $this->buildControllerContext();
        $controllerContext->setRequest($this->request);
        $renderer->setControllerContext($controllerContext);
        
        if ( $templatePath == '') {
            // override the template path with individual settings in TypoScript
            $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

			if (isset($extbaseFrameworkConfiguration['view']['partialRootPaths']) && strlen($extbaseFrameworkConfiguration['view']['partialFilesRootPaths']) > 0) {
				$partialFiles = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['partialFilesRootPaths']);
			} else {
				$partialFiles = array( 0 => \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName( "typo3conf/ext/jv_events/Resources/Private/Partials" ) ) ;
			}

			if (isset($extbaseFrameworkConfiguration['view']['templateRootPath']) && strlen($extbaseFrameworkConfiguration['view']['templateRootPath']) > 0) {
				$templatePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
            } else {
                $templatePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName( "typo3conf/ext/jv_events/Resources/Private/Templates" ) ;
            }
        }
        
        $templateFile = $templatePath . $templateName . '.html';

        // set the e-mail template
        $renderer->setTemplatePathAndFilename($templateFile);
        $renderer->setPartialRootPaths($partialFiles);
        // and return the new Fluid instance
        return $renderer;
    }
    
    /**
     * function sendEmail
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @param \JVE\JvEvents\Domain\Model\Registrant $registrant
     * @param string $partialName possible Values: organizer, registrant, developer or admin
     * @throws \Exception
     * @param array $recipient
     * @param array|bool $otherEvents Array with IDs of other Events or False
     * @return boolean
     */

    public function sendEmail(\JVE\JvEvents\Domain\Model\Event $event = NULL, \JVE\JvEvents\Domain\Model\Registrant $registrant = NULL , $partialName ='', $recipient=array() , $otherEvents=false)
    {
        if (!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($this->settings['register']['senderEmail'])) {
            throw new \Exception('plugin.jv_events.settings.register.senderEmail is not a valid Email Address. Is needed as Sender E-mail');
        }
        $sender = array($this->settings['register']['senderEmail']
        =>
            $this->settings['register']['sendername']
        );

        foreach ($recipient as $key => $value ) {
            if (!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($key )) {
                throw new \Exception(var_export( $recipient , true ) . "( " . $key . ") " . ' is not a valid -recipient- Email Address. ');
            }
        }

        $signature = false;
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('mailsignature')) {
            /** @var \Velletti\Mailsignature\Service\SignatureService $signatureService */
            $signatureService = $this->objectManager->get("Velletti\\Mailsignature\\Service\\SignatureService");
            $signature = $signatureService->getSignature($this->settings['signature']['uid']);
        }
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('nem_signature')) {
            if (!$signature) {
                /** @var \tx_nemsignature $signatureService */
                $signatureService = $this->objectManager->get("tx_nemsignature");
                $signature = $signatureService->getSignature($this->settings['signature']['uid']);
            }

        }
        if( $event ) {
            $querysettings = new \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings ;
            $querysettings->setStoragePageIds(array( $event->getPid() )) ;

            $this->subeventRepository->setDefaultQuerySettings( $querysettings );
            $subevents = $this->subeventRepository->findByEventAllpages($event->getUid() , FALSE ) ;
        }


        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
        $renderer = $this->getEmailRenderer($templatePath = '', '/Registrant/Email/' . $this->settings['LayoutRegister']);


        if ( ! is_object( $subevents ) ) {
            $renderer->assign('subevents', null );
            $renderer->assign('subeventcount', 0 );
        } else {
            $renderer->assign('subevents', $subevents);
            $renderer->assign('subeventcount', $subevents->count() + 1 );

        }



        $renderer->assign('signature', $signature);
        $renderer->assign('registrant', $registrant);
        $renderer->assign('event', $event);
        $renderer->assign('otherEvents', $otherEvents);

        $renderer->assign('partial', "Registrant/Partial" . $this->settings['LayoutRegister'] . "/Emails/" . $partialName);
        $renderer->assign('settings', $this->settings);

        $layoutPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName("typo3conf/ext/jv_events/Resources/Private/Layouts/");
        $renderer->setLayoutRootPaths(array(0 => $layoutPath));

        $renderer->assign('layoutName', 'EmailSubject' . $partialName);

        // read Colors and font settings from EmConfigurationUtility as object
        $renderer->assign('emConf', \JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf(TRUE));

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

        $renderer->assign('registrant', $registrant);
        $renderer->assign('layoutName', 'EmailPlain');
        $plainMsg = $renderer->render();

        $renderer->assign('layoutName', 'EmailHtml');
        $emailBody = $renderer->render();
        /** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
        $message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
        $message->setTo($recipient)
            ->setFrom($sender)
            ->setSubject($subject);

        $returnPath = \TYPO3\CMS\Core\Utility\MailUtility::getSystemFromAddress();
        if ( $returnPath != "no-reply@example.com") {
            $message->setReturnPath($returnPath);
        }

        if ( TYPO3_branch < 9 ) {
            $rootPath = PATH_site ;
        } else {
            /* available from typo3 V9 */
            $rootPath = \TYPO3\CMS\Core\Core\Environment::getPublicPath() ;
        }

        if ( is_array( $attachments ) ) {
            if( $registrant->getMore6int() == 1  ) {
                foreach ($attachments as $attachment) {
                    if( substr( $attachment , 0 , 1 ) <> "/" ) {
                        $attachment = "/" . $attachment ;
                    }
                    $attachment = $rootPath . $attachment ;
                    if ( file_exists($attachment )) {
                        $message->attach(\Swift_Attachment::fromPath($attachment));
                    }
                }
            }
        }

        $message->setBody($emailBody, 'text/html');
        $message->addPart($plainMsg, 'text/plain');


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
        $message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');


        $returnPath = \TYPO3\CMS\Core\Utility\MailUtility::getSystemFromAddress();
        if ( $returnPath != "no-reply@example.com") {
            $message->setReturnPath($returnPath);
        }

        $message->setBody(strip_tags( $plainMsg ), 'text/plain');
        if ( !$htmlMsg || $htmlMsg == '' ) {
            $htmlMsg =  nl2br( $plainMsg );
            $subject .= " - converted" ;
        }

        $message->addPart( $htmlMsg , 'text/html');

        $message->setTo($recipient)
            ->setFrom($sender)
            ->setSubject($subject);

        $message->send();
    }

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

    public function hasUserGroup( $groupId) {
        $feuserGroups = GeneralUtility::trimExplode("," ,  $GLOBALS['TSFE']->fe_user->user['usergroup']  , TRUE ) ;
        return in_array( $groupId  , $feuserGroups ) ;
    }

    /**
     * @param \JVE\JvEvents\Domain\Model\Organizer | \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy  $organizer
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
    public function getOrganizer() {
        if (intval($GLOBALS['TSFE']->fe_user->user['uid']) < 1 ) {
            return false ;
        }
        /** @var \JVE\JvEvents\Domain\Model\Organizer $organizer */
        if( $this->request->hasArgument('organizer')) {
            $id = intval($this->request->getArgument('organizer'));
        }
        if( $id > 0 ) {
            $organizer = $this->organizerRepository->findByUidAllpages($id , FALSE);
        }
        if ($organizer instanceof \JVE\JvEvents\Domain\Model\Organizer) {
            return $organizer ;
        }

        // TODo : think about a better solution how to manage that a user can be linked to more than one Organizer
        // actually it will not work
        $organizer = $this->organizerRepository->findByUserAllpages( intval($GLOBALS['TSFE']->fe_user->user['uid'])  , FALSE )->getFirst() ;
        if ($organizer instanceof \JVE\JvEvents\Domain\Model\Organizer) {
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
        return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($label, 'JvEvents', $arguments);
    }

    protected function showNoDomainMxError($email ) {
        if( trim($email ) == '' ) {
            return ;
        }
        $domain  = explode('@', $email);
        if( count($domain) > 0 ) {
            if( ! checkdnsrr($domain[1], 'MX') ) {
                $msg = sprintf( $this->translate('error.email.noMxRecord') , "@" . $domain[1] ) . " ";

                $this->addFlashMessage( $msg , '', AbstractMessage::WARNING);
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