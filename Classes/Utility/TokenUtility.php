<?php
namespace JVelletti\JvEvents\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TokenUtility
{

	/**
	 * generate a Token for  Current FEUser will cahnge on changing the Password or email Address !
	 * @param int $uid the Users UID
	 * @return string|null
	 * @author Jörg Velletti <jvelletti@allplan.com>
	 */
	public static function getToken(int $uid , $encoded=true ): ?string
    {

		if($uid > 0 ){
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
            $query = $queryBuilder
                ->select('uid', 'email', 'crdate', 'password')
                ->from('fe_users')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
                );

            $result = $query->executeQuery()->fetchAssociative();

            if($result === false) {
                return null;
            }
            $jsonValues = json_encode($result);


            $encryptionKey = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] ?? 'fallback' . __DIR__ ;
            if( $encoded ) {
                return base64_encode( $uid . "-" . GeneralUtility::hmac($jsonValues, $encryptionKey));
            }
            return GeneralUtility::hmac($jsonValues, $encryptionKey) ;

		}
		return null;
	}

    /**
     * check if  given Token is valid:  !
     * @param string|null $givenTokenEncoded
     * @return bool
     * @author Jörg Velletti <jvelletti@allplan.com>
     */
    public static function checkToken( ?string $givenTokenEncoded ): bool
    {
        if ( !$givenTokenEncoded ) {
            return false ;
        }
        $split = GeneralUtility::trimExplode( "-" , base64_decode( $givenTokenEncoded ) , true , 2 ) ;
        if ( count( $split) != 2 ) {
            return false ;
        }
        list($uid, $givenToken) = $split;

        $tokenShouldBe = self::getToken( (int)$uid ,false) ;
        if ( !$tokenShouldBe ||  !$givenToken ) {
            return false ;
        }
        if ( $tokenShouldBe == $givenToken ) {
            return true ;
        }
        return false ;
    }

    public static function checkLicense( ?string $apiToken , ?int $user = 0 ) :?string
    {

        if ( !$apiToken ||  strlen($apiToken ) < 10  ) {
            return "BLOCKED-no-token" ;
        }

        $apiToken = trim($apiToken) ;
         $user = intval($user) ;
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_jvevents_domain_model_token');
        $queryBuilder->getRestrictions()->removeByType( \TYPO3\CMS\Core\Database\Query\Restriction\PageIdListRestriction::class);
        $query = $queryBuilder
            ->select('license' , 'feuser' , 'referrer')
            ->from('tx_jvevents_domain_model_token')
            ->where(
                $queryBuilder->expr()->eq('token', $queryBuilder->createNamedParameter($apiToken, \PDO::PARAM_STR))
            )->andWhere(
                  $queryBuilder->expr()->eq('feuser', $queryBuilder->createNamedParameter($user, \PDO::PARAM_INT))
           );


        $result = $query->executeQuery()->fetchAssociative();
        if($result === false) {
            return "BLOCKED-no-license" ;
        }
        if ( !empty( $result['referrer'] )) {
            $referrer = GeneralUtility::trimExplode( "," , $result['referrer'] , true  ) ;
            $referrerDomain = parse_url( $_SERVER['HTTP_REFERER'] , PHP_URL_HOST ) ;
            if ( !empty( $referrer ) && trim($referrerDomain) != '' && !in_array( $referrerDomain , $referrer ) ) {
              return  "BLOCKED-wrong-referrer-domain"  . $referrerDomain ;
            }
        }
        if ( $result['feuser'] > 0 && $result['feuser'] != $user ) {
            return "BLOCKED-wrong-user" ;
        }
        return $result['license'];
    }


}