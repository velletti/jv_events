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
class TeaserImgLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'img';

    
  
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
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
        $withProtocol = false,
        $configuration = []
    ) {


        $this->tag->addAttribute('class', "jv_events-teaser-img");


        return $this->tag->render();
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
