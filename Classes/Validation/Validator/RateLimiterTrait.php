<?php

namespace JVelletti\JvEvents\Validation\Validator;

use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory as SymfonyRateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\RateLimiter\Storage\CachingFrameworkStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait RateLimiterTrait
{
    public function rateLimit($limiterId ,  $limit = 5, $interval = '15 minutes' , $title = "Error Request Rate Limited") : ?bool
    {
        $loginRateLimiter = $this->generateLimiter($limiterId ,  $limit, $interval);
        $limit = $loginRateLimiter->consume();
        if (!$limit->isAccepted()) {
            $ipAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR') ;
            $dateformat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
            $lockedUntil = $limit->getRetryAfter()->getTimestamp() > 0 ?
                ' until ' . date($dateformat, $limit->getRetryAfter()->getTimestamp()) : '';

            if ( file_exists( Environment::getProjectPath() . "/allplan/config/MatomoConfiguration.php") ) {
                include_once ( Environment::getProjectPath() . "/allplan/config/MatomoConfiguration.php") ;
                if ( $GLOBALS['TYPO3_CONF_VARS']['ALLPLAN']['MATOMO'] ) {
                    $matomo = new \Allplan\Library\Matomo\Service\Matomo( $GLOBALS['TYPO3_CONF_VARS']['ALLPLAN']['MATOMO']);
                    if( $matomo instanceof \Allplan\Library\Matomo\Service\Matomo ) {
                        $track = $matomo->track( "Exception" , "/blocked/ipaddress/" . $ipAddress  ) ;
                    }
                }
            }

            http_response_code(403);

            echo '<html>
            <head>
                <title>' .$title . '</title>
            </head>
            <body>
                <div style="text-align: center; margin: 20px auto; width: 600px; max-width: vw;">
                    <span style="float: left; margin-right: 20px; width: 80px; height: 80px; color:red ">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="red">
                        <!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z"/></svg>
                    </span>
                    <h2>' .$title . '</h2>
                    <p>
                       
                    The IP is locked until <b>' . $lockedUntil . '</b><br />' .
                    ' UTC due to too many failed requests from your IP address. ' .
                    '</p>
                </div>
                </html>';
            exit();

        } else {
            return true ;
        }
    }

    public function generateLimiter($limiterId ,  $limit , $interval) : LimiterInterface
    {
        $remoteIp = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        $enabled = true ;
        // If not enabled, return a null limiter
        // $enabled = !$this->isIpExcluded($loginType, $remoteIp) && $limit > 0;

        $config = [
            'id' => $limiterId,
            'policy' => ($enabled ? 'sliding_window' : 'no_limit'),
            'limit' => $limit,
            'interval' => $interval,
        ];
        $storage = ($enabled ? GeneralUtility::makeInstance(CachingFrameworkStorage::class) : new InMemoryStorage());
        $limiterFactory = new SymfonyRateLimiterFactory(
            $config,
            $storage
        );
        return $limiterFactory->create($remoteIp);
    }
}