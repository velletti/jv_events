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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * ViewHelper to render links from event records to detail view or page
 *
 * # Example: Basic link
 * <code>
 * <n:youtube uri="{organizer.youtubeLink}" settings="{settings}" class="btn btn-secondary">
 *
 * </n:youtube>
 * </code>
 * <output>
 * A link to the given youTube URL or embed if singel Video
 * </output>

 *
 */
class YoutubeViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'a';

    /**
     * Disable escaping of tag based ViewHelpers so that the rendered tag is not htmlspecialchar'd
     *
     * @var bool
     */
    protected $escapeOutput = false;

  
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerArgument('uri', 'string', 'Youtube Url', true);
        $this->registerArgument('settings', 'array', 'settings Array', false , array() );

    }

    /**
     * Render link to event item or internal/external pages
     *
     * @return string link
     */
    public function render() {
        $this->init();
        $uri = $this->arguments['uri'] ;
        // maybe we need settings
        $settings = $this->arguments['settings'] ?? [] ;
        $class = $this->arguments['class'] ?? '' ;
        $videoUrl = parse_url($uri);

        if ( isset($_COOKIE['tx_events_youtube_consens'])) {

            if (array_key_exists("query", $videoUrl)) {
                parse_str($videoUrl['query'], $params);
                if (is_array($params)) {
                    if (strpos($uri, "watch") > 0 && isset($params["v"])) {
                        // single Video we try to embedd
                        // <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/F2zTd_YwTvo" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>

                        // height and width are overwritten by css

                        $frame = '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/'
                            . $params["v"] . '"'
                            . ' title="YouTube video player" frameborder="0" '
                            . ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
                            . ' allowfullscreen></iframe>';
                        return '<div class="embed-container">' . $frame . "</div>";

                    }
                    // todo .. fix playlist embeddng
                    if (strpos($uri, "XXX-playlist-XXX") > 0 && isset($params["list"])) {
                        // playlist Video we try to embedd
                        // <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/videoseries?list=PL1COG6YIRDMDIYGsK7ZkGfji99W_etnoG" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>

                        // height and width are overwritten by css
                        $frame = '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/videoseries?list='
                            . $params["list"] . '"'
                            . ' title="YouTube video player" frameborder="0" '
                            . ' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
                            . ' allowfullscreen></iframe>';
                        return '<div class="embed-container">' . $frame . "</div>";

                    }
                }

            }
            $youTubeConsense = '' ;
        } else {
            $youTubeConsense = '<div class="alert alert-light"><h3><i class="fa fa-youtube-square mr-2"></i> Datenschutz Hinweis.</h3>Du kannst dir einzelne You Tube Videos der Veranstalter auch direkt in Tango München anzeigen lassen. <bR>'
                .'Wenn du das möchtest, wird aber deine IP Adresse und Browserkennung etc. an Google in die USA übertragen. Und Youtube setzt weitere Cookies!<br>'
                .'Durch Klick auf den Button setzt der Tango Server einen Cookie: tx_events_youtube_consens. Und läd die Seite neu,'
                .'von nun an auch mit Videos direkt in der Webseite an. Um das zu ändern, den Cookie: tx_events_youtube_consens wieder löschen. <br>'
                . '<br><span class="btn btn-secondary" id="allowYoutubeConsens"><i class="fa fa-youtube-square mr-2"></i> Youtube Zugriff erlauben</span><br></div>' ;
        }

        $this->tag->addAttribute('target', '_blank');
        $this->tag->addAttribute('href', $uri);
        $this->tag->addAttribute('class', $class);
        $this->tag->setContent( '<i class="fa fa-youtube-square mr-2"></i>' . $uri);

        return $youTubeConsense . $this->tag->render();


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
