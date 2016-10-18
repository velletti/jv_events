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

/**
 * EventController
 */
class BaseController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * eventRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\EventRepository
     * @inject
     */
    protected $eventRepository = NULL;


	/**
	 * staticCountryRepository
	 *
	 * @var \JVE\JvEvents\Domain\Repository\StaticCountryRepository
	 * @inject
	 */
	protected $staticCountryRepository = NULL;


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

        $this->settings['EmConfiguration']	 			= \JVE\JvEvents\Utility\EmConfiguration::getEmConf();

        // get the list of Required Fields for this layout and store it to the  settings Array
        // seemed faster than separate via a Viewhelper for each Field

        $layout = $this->settings['LayoutRegister'] ;
        $fields = $this->settings['register']['requiredFields'][$layout] ;
        $required  = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode( "," , $fields ) ;
        foreach( $required as $key => $field ) {
            $this->settings['register']['required'][$field] = TRUE ;
        }
        $this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');


    }

    public function generateFilter($eventsArray) {
        $locations = array() ;
        $organizers = array() ;
        $citys = array() ;
        $tags = array() ;
        $categories = array() ;
        $months = array() ;
        /** @var \JVE\JvEvents\Domain\Model\Event $event */
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
                /** @var \JVE\JvEvents\Domain\Model\Tag $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                        $tags[$obj->getUid()] = $obj->getName() ;
                    }
                }
            }
            $objArray =  $event->getEventCategory() ;

            if( is_object( $objArray)) {
                /** @var \JVE\JvEvents\Domain\Model\Category $obj */
                foreach ($objArray as $obj ) {
                    if ( is_object($obj) ) {
                        $categories[$obj->getUid()] = $obj->getTitle() ;
                    }
                }
            }
           
            unset($obj) ;
            unset($objArray) ;

            $month = $event->getStartDate()->format("m.Y") ;
            $months[$month] = $month ;

        }
        return array(
            "locations" => $locations ,  
            "organizers" => $organizers ,  
            "citys" => $citys ,  
            "tags" => $tags ,  
            "categories" => $categories ,  
            "months" => $months ,  
            
            ) ;
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
     * @param array $recipient
     * @return void
     */

    public function sendEmail(\JVE\JvEvents\Domain\Model\Event $event = NULL, \JVE\JvEvents\Domain\Model\Registrant $registrant = NULL , $partialName , $recipient ) {
		if (! \TYPO3\CMS\Core\Utility\GeneralUtility::validEmail( $this->settings['register']['senderEmail']  ) ) {
			throw new \Exception('plugin.jv_events.settings.register.senderEmail is not a valid Email Address. Is needed as Sender E-mail');
		}
		$sender = array( $this->settings['register']['senderEmail']
					=>
						'=?utf-8?B?'. base64_encode( $this->settings['register']['sendername'] ) .'?='
					 ) ;

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $renderer */
    	$renderer = $this->getEmailRenderer($templatePath = '' , '/Registrant/Email/' . $this->settings['LayoutRegister']   ) ;

        $renderer->assign('event', $event);
        $renderer->assign('registrant', $registrant);
        $renderer->assign('partial', "Registrant/Partial" . $this->settings['LayoutRegister'] . "/Emails/" . $partialName);
        $renderer->assign('settings', $this->settings);

		$layoutPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName( "typo3conf/ext/jv_events/Resources/Private/Layouts/" ) ;
		$renderer->setLayoutRootPaths( array( 0 => $layoutPath) ) ;

		$renderer->assign('layoutName', 'EmailSubject');

		// read Colors and font settings from EmConfiguration as object
		$renderer->assign('emConf', \JVE\JvEvents\Utility\EmConfiguration::getEmConf( TRUE ) );

		// and do the rendering magic
        $subject =  '=?utf-8?B?'. base64_encode($renderer->render() ) .'?=' ;

		$renderer->assign('layoutName', 'EmailPlain');
		$plainMsg =  $renderer->render() ;

		$renderer->assign('layoutName', 'EmailHtml');
		$emailBody =  $renderer->render() ;


		/** @var $message \TYPO3\CMS\Core\Mail\MailMessage */
		$message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$message->setTo($recipient)
			->setFrom($sender)
			->setSubject($subject);

		// Possible attachments here
		//foreach ($attachments as $attachment) {
		//	$message->attach(\Swift_Attachment::fromPath($attachment));
		//}
		$message->setBody($emailBody, 'text/html');
		$message->addPart($plainMsg, 'text/plain');


		$message->send();
		return $message->isSent();


    }
}