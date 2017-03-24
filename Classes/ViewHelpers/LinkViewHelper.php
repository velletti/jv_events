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
class LinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'a';

    
  
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('section', 'string', 'Anchor for links', false);
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
    public function render(
        \JVE\JvEvents\Domain\Model\Event $event,
        array $settings = [],
        $uriOnly = false,
        $withProtocol = false,
        $configuration = [],
        $content = ''
    ) {
        $configuration = array() ;
        $this->init();
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
        if (isset($settings['link']['typesOpeningInNewWindow'])) {
            if (GeneralUtility::inList($settings['link']['typesOpeningInNewWindow'], $EventType)) {
                $this->tag->addAttribute('target', '_blank');
            }
        }
        if ($this->hasArgument('section')) {
            $url .= '#' . $this->arguments['section'];
        }
        if ($uriOnly) {
            $configuration['forceAbsoluteUrl'] = 1 ;
        }
        $url = $this->cObj->typoLink_URL($configuration);
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
    protected function getLinkToEventItem(
        \JVE\JvEvents\Domain\Model\Event $event,
        $settings,
        array $configuration = []
    ) {

        if (!isset($configuration['parameter'])) {
            $detailPid = intval( $settings['detailPid']) ;
            
            if (!$detailPid) {
                $detailPid = $GLOBALS['TSFE']->id;
            }
            $configuration['parameter'] = $detailPid;
        }

        $configuration['useCacheHash'] = $GLOBALS['TSFE']->sys_page->versioningPreview ? 0 : 1;
        if( $settings['link']['doNotuseCacheHash'] ) {
            $configuration['useCacheHash'] = 0 ;
        }
        if( $settings['link']['addNoCache'] ) {
            $configuration['useCacheHash'] = 0 ;
            $configuration['noCache'] = 1 ;
        }
        $configuration['additionalParams'] .= '&tx_jvevents_events[event]=' . ($event->getUid() );

        if ((int)$settings['link']['skipControllerAndAction'] !== 1) {
            $configuration['additionalParams'] .= '&tx_jvevents_events[controller]=Event' .
                '&tx_jvevents_events[action]=show';
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
        $configuration['additionalParams'] .= '&tx_jvevents_events[eventTitle]=' . urlencode( $catTitles . $event->getName() );
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
