<?php
namespace JVelletti\JvEvents\ViewHelpers;

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

use JVelletti\JvEvents\Utility\MigrationUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Category;
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
class LinkViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'a';

    
  
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('section', 'string', 'Anchor for links', false);

        $this->registerArgument('event', Event::class, 'Event', false);
        $this->registerArgument('eventId', 'int', 'Event as id', false);
        $this->registerArgument('settings', 'array', 'settings Array', false , [] );
        $this->registerArgument('configuration', 'array', 'configuration Array', false , [] );
        $this->registerArgument('content', 'string', ' the content', false ,'' );
        $this->registerArgument('uriOnly', 'boolean', ' render only the uri ', false ,false );
        $this->registerArgument('withProtocol', 'boolean', ' render withProtocol https or http etc ', false ,false );

    }

    /**
     * Render link to event item or internal/external pages
     *
     * @return string link
     */
    public function render() {
        $configuration = $this->arguments['configuration'] ;
        $this->init();
        $event = $this->arguments['event'] ;
        $eventId = $this->arguments['eventId'] ;
        $settings = $this->arguments['settings'] ;
        $uriOnly = $this->arguments['uriOnly'] ;
        $withProtocol = $this->arguments['withProtocol'] ;

        $content = $this->arguments['content'] ;

        if(  $eventId > 0 ) {
            $configuration = $this->getLinkToEventItem($eventId, $settings, $configuration );
        } else {
            if ( !$event) {
                return '' ;
            }
            $EventType = (int)$event->getEventType();

            switch ($EventType) {
                // ToDo Link to internal event page  should be 0
                case 011:
                    // $configuration['parameter'] = $event->getInternalurl();
                    break;
                // ToDo external Link should not be used - should be 1
                case 111:
                    //   $configuration['parameter'] = $event->getExternalurl();
                    break;
                // normal event record
                default:

                    $configuration = $this->getLinkToEventItem($event, $settings, $configuration );
            }
        }


        if (isset($settings['link']['typesOpeningInNewWindow']) && isset( $EventType )) {
            if ( is_array( $settings['link']['typesOpeningInNewWindow'] ) && in_array( $EventType , $settings['link']['typesOpeningInNewWindow'] )) {
                $this->tag->addAttribute('target', '_blank');
            } else {
                if (GeneralUtility::inList( $settings['link']['typesOpeningInNewWindow'], $EventType)) {
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
     * @param Event|int $event
     * @return array
     */
    protected function getLinkToEventItem(
        $event,
        array $settings,
        array $configuration = []
    ) {

        if (!isset($configuration['parameter'])) {
            $detailPid = intval( $settings['detailPid']) ;
            
            if (!$detailPid) {
                $detailPid = MigrationUtility::getPageId() ;
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
        if ((int)$settings['link']['skipControllerAndAction'] !== 1) {
            $configuration['additionalParams'] = '&tx_jvevents_event[controller]=Event' .
                '&tx_jvevents_event[action]=show';
        }

        if( is_int( $event )) {
            $configuration['additionalParams'] .= '&tx_jvevents_event[event]=' . $event ;
        } else {
            $configuration['additionalParams'] .= '&tx_jvevents_event[event]=' . ($event->getUid() );
            $categories = $event->getEventCategory() ;
            $catTitles = "" ;
            if ( $categories ) {
                /** @var Category $category */
                foreach( $categories as $category ) {
                    if( is_object($category)) {
                        $catTitles .= $category->getTitle() . " - ";
                    }
                }

            }
        }

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
