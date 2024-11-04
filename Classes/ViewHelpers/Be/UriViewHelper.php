<?php
declare(strict_types = 1);
namespace JVelletti\JvEvents\ViewHelpers\Be;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * UriViewHelper
 */
class UriViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'identifier important if more widgets on same page', false, 'widget');
        $this->registerArgument('arguments', 'array', 'Arguments', false, []);
    }

    /**
     * Build an uri to current action with &tx_ext_plugin[currentPage]=2
     *
     * @return string The rendered uri
     */
    public function render(): string
    {
        $finalArguments = [] ;
        $arguments = ($this->arguments['arguments'] ?? [] ) ;
        $class = ($this->arguments['class'] ?? 'page-link' ) ;
        foreach ( ["id" , "recursive" , "onlyActual" , "event" , "currentPage" ] as $special ) {
            if( isset($arguments[$special] )) {
                $finalArguments[$special]= $arguments[$special] ;
            }
        }
        $route = false ;
        if ( isset($arguments['route'] )) {
            $route = $arguments['route'];
        } else {
            return '' ;
        }

        try {
            /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);

            $uri = $uriBuilder->buildUriFromRoute( $route , $finalArguments ) ;

            $this->tag->setTagName("a") ;

            $this->tag->addAttribute('href', $uri  );
            $this->tag->addAttribute('class', $class  );
            $this->tag->setContent($this->renderChildren());
            $this->tag->forceClosingTag(TRUE);
            return $this->tag->render();

        } catch (\TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException $e) {

        }
        return '' ;
    }
}
