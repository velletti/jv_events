<?php

namespace JVE\JvEvents\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Ajax implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $arguments = $request->getQueryParams();
        if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) > 9004000) {
            $useMiddleware = true;
        }
        if( is_array($arguments) && key_exists("tx_jvevents_ajax" ,$arguments ) && key_exists("controller" ,$arguments['tx_jvevents_ajax'] ) && $useMiddleware == true) {
            // ToDo generate Output as before in ajax Controller
            /** @var \JVE\JvEvents\Controller\AjaxController $controller */
            $controller = GeneralUtility::makeInstance('JVE\JvEvents\Controller\AjaxController' ) ;
            $controller->dispatcher() ;
            die;


            $output["arguments"] = $arguments  ;
            $output["feUser"] =  $GLOBALS['TSFE']->fe_user->user  ;


            $result = json_encode( $output) ;
            $body = new Stream('php://temp', 'rw');
            $body->write($result);
            return (new Response())
                ->withHeader('content-type', 'application/json; charset=utf-8')
                ->withBody($body)
                ->withStatus(200);
        }
        return $handler->handle($request);
    }

    /**
     * @return mixed|string|null
     */
    protected function getUsername(): ?string
    {
        // $GLOBALS['TSFE']->initUserGroups();
        $user = $GLOBALS['TSFE']->fe_user->user;

        if ($user) {
            if ($user['first_name'] && $user['last_name']) {
                $content = $user['first_name'] . ' ' . $user['last_name'];
            } else {
                $content = $user['email'];
            }
            return $content;
        }
        return null;
    }
}
