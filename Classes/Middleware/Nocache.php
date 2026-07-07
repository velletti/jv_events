<?php

namespace JVelletti\JvEvents\Middleware;

use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Fluid\View\StandaloneView;
use JVelletti\JvEvents\Utility\EmConfigurationUtility;
use JVelletti\JvEvents\Domain\Model\Event;
use JVelletti\JvEvents\Domain\Model\Subevent;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use JVelletti\JvEvents\Domain\Model\Category;
use JVelletti\JvEvents\Domain\Model\Location;
use JVelletti\JvEvents\Domain\Model\Organizer;
use JVelletti\JvEvents\Domain\Repository\CategoryRepository;
use JVelletti\JvEvents\Domain\Repository\EventRepository;
use JVelletti\JvEvents\Domain\Repository\LocationRepository;
use JVelletti\JvEvents\Domain\Repository\OrganizerRepository;
use JVelletti\JvEvents\Domain\Repository\RegistrantRepository;
use JVelletti\JvEvents\Domain\Repository\StaticCountryRepository;
use JVelletti\JvEvents\Domain\Repository\SubeventRepository;
use JVelletti\JvEvents\Domain\Repository\TagRepository;
use JVelletti\JvEvents\Domain\Repository\TokenRepository;
use JVelletti\JvEvents\Utility\AjaxUtility;
use JVelletti\JvEvents\Utility\ShowAsJsonArrayUtility;
use JVelletti\JvEvents\Utility\TokenUtility;
use JVelletti\JvEvents\Utility\TyposcriptUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\Cache\CacheInstruction;

/**
 * Class Ajax
 * @package JVelletti\JvEvents\Middleware
 */
class Nocache implements MiddlewareInterface
{



    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidExtensionNameException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $_gp = $request->getQueryParams();
        if( isset($_gp["tx_jvevents_ajax"]["action"] ) ) {

            $function = strtolower( trim($_gp['tx_jvevents_ajax']['action'])) ;
            if( $function == "eventlist"  ) {

                /** @var CacheInstruction $cacheInstruction */
                $cacheInstruction =  new CacheInstruction() ;
                $cacheInstruction->disableCache('EXT:Jv_events: "Ajax requests" disables cache.');
                $request = $request->withAttribute('frontend.cache.instruction', $cacheInstruction);

            }
        }
        return $handler->handle($request);
    }

}
