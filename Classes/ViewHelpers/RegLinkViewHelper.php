<?php
namespace JVE\JvEvents\ViewHelpers;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share
 * 
 * Based on the news extension of Georg Ringer 
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * ViewHelper to render links from event records to detail view or page
 *
 * # Example: Basic link
 * <code>
 * <n:link event="{event}" settings="{settings}" >
 *    {event.title}
 * </n:link>
 * </code>
 * <output>
 * A link to the given event record using the event title as link text
 * </output>
 *
 * # Example: Set an additional attribute
 * # Description: Available: class, dir, id, lang, style, title, accesskey, tabindex, onclick
 * <code>
 * <n:link event="{event}" settings="{settings}" class="a-link-class">fo</n:link>
 * </code>
 * <output>
 * <a href="link" class="a-link-class">fo</n:link>
 * </output>
 *
 * # Example: Return the link only
 * <code>
 * <n:link newsItem="{newsItem}" settings="{settings}" uriOnly="1" />
 * </code>
 * <output>
 * The uri is returned
 * </output>
 *
 */
class RegLinkViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'a';

    
  
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('section', 'string', 'Anchor for links', false);
        $this->registerArgument('event', '\JVE\JvEvents\Domain\Model\Event', 'Event', false);
        $this->registerArgument('settings', 'array', 'settings Array', false , array() );
        $this->registerArgument('configuration', 'array', 'configuration Array', false , array() );
        $this->registerArgument('content', 'string', ' the content', false ,'' );
        $this->registerArgument('uriOnly', 'boolean', ' render only the uri ', false ,false );
        $this->registerArgument('withProtocol', 'boolean', ' render withProtocol https or http etc ', false ,false );
    }

    /**
     * Render link to event item or internal/external pages
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event current Event object
     * @param array $settings
     * @param bool $uriOnly return only the url without the a-tag
     * @param bool $withProtocol return only the url without the a-tag
     * @param array $configuration optional typolink configuration
     * @param string $content optional content which is linked
     * @return string link
     */
    public function render() {
        $configuration = $this->arguments['configuration'] ;
        $this->init();
        $event = $this->arguments['event'] ;
        $settings = $this->arguments['settings'] ;
        $uriOnly = $this->arguments['uriOnly'] ;
        $withProtocol = $this->arguments['withProtocol'] ;

        $content = $this->arguments['content'] ;

       if( $event->getWithRegistration() ) {

            $configuration = $this->getLinkToEventRegistration($event, $settings, $configuration );
        } else {
           if( $event->getRegistrationUrl() <> '' ) {
               $regUrl = GeneralUtility::trimExplode( " " , $event->getRegistrationUrl() ) ;
               $configuration['parameter'] = $regUrl[0] ;

               if ( $regUrl[1] =="_blank")  {
                   $this->tag->addAttribute('target', '_blank');
               }
           }
       }


        if ($uriOnly) {
            $configuration['forceAbsoluteUrl'] = 1 ;
        }

        if ( intval( $GLOBALS['TSFE']->config['config']['sys_language_uid'] ) > 0 ) {
            $configuration['additionalParams'] .= "&L=" . intval( $GLOBALS['TSFE']->config['config']['sys_language_uid'] ) ;
        }
        $url = $this->cObj->typoLink_URL($configuration);

        if ($this->hasArgument('section')) {
            $url .= '#' . $this->arguments['section'];
        }

        if ($uriOnly) {
            return $url;
        }

        $this->tag->addAttribute('href', $url);

        if (empty($content)) {
            $content = $this->renderChildren();
        }
        $this->tag->setContent($content);

        return $this->tag->render();
    }

    /**
     * Generate the link configuration for the link to the news item
     *
     * @param \JVE\JvEvents\Domain\Model\Event $event
     * @param array $settings
     * @param array $configuration
     * @return array
     */
    protected function getLinkToEventRegistration(
        \JVE\JvEvents\Domain\Model\Event $event,
        $settings,
        array $configuration = []
    ) {

        if (!isset($configuration['parameter'])) {
        	// try to get PID from Event
			$detailPid = $event->getRegistrationFormPid() ;
			// if not set, show registration an the same page like Single View
			if (!$detailPid) {
				$detailPid = intval($settings['detailPid']);
			}
			// still not set: try the same page . will not work on pages with Event list view but on single View
            if (!$detailPid) {
                $detailPid = $GLOBALS['TSFE']->id;
            }
            $configuration['parameter'] = $detailPid;
        }

        // $configuration['useCacheHash'] = $GLOBALS['TSFE']->sys_page->versioningPreview ? 0 : 1;
        if( $settings['link']['doNotuseCacheHash'] ) {
            $configuration['useCacheHash'] = 0 ;
        }
        if( $settings['link']['addNoCache'] ) {
            $configuration['useCacheHash'] = 0 ;
            $configuration['noCache'] = 1 ;
        }

        $categories = $event->getEventCategory() ;
        $catTitles = "" ;
        if ( $categories ) {
            /** @var  \JVE\JvEvents\Domain\Model\Category $category */
            foreach( $categories as $category ) {
                if( is_object($category)) {
                    $catTitles .= $category->getTitle() . " - ";
                }
            }

        }

        $configuration['additionalParams'] .= '&tx_jvevents_events[event]=' . ($event->getUid() );
        $configuration['additionalParams'] .= '&tx_jvevents_events[eventTitle]=' . urlencode( $catTitles . $event->getName() );
        $configuration['additionalParams'] .= '&tx_jvevents_events[controller]=Registrant' . '&tx_jvevents_events[action]=new';

        $configuration['additionalParams'] .= '&tx_jvevents_events[date]=' . $event->getStartDate()->format( $settings['link']['dateFormat']  );

        return $configuration;
    }



    /**
     * Initialize properties
     *
     * @return void
     */
    protected function init()
    {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }
}
