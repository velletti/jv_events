<?php
namespace JVelletti\JvEvents\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use Psr\Http\Message\ServerRequestInterface;

class TyposcriptUtility{

	/**
	 * Loads the typoscript from scratch
	 * @author Peter Benke <pbenke@allplan.com>
	 * @param int $pageUid
	 * @param string $extKey
	 * @param mixed $conditions array with Constants Conditions if needed
     *                          this
     *                          The Condition must be either :
     *                          a) one of the following Common Vars: (i did not test this, but found it in source !)
     *                         'usergroup' , 'treeLevel' , PIDupinRootline' or  'PIDinRootline':
     *                         f.e. : array( 'usergroup=2,4' )
     *
     *                          or ( this is tested)
     *                          b) the exact Condition from YOUR Constants.ts file
     *
     *                           this must be an array, also multiple Conditions can be handed over:
     *
	 * @param bool $getConstants default=false,  will return  Constants (all or those from an extension) instaed of Setup
	 * @return array
     * @deprecated V12  Maybe creating a new Reuest does not work. .. better use direct calling  !!
	 */
	public static function loadTypoScriptFromScratch($pageUid = 0, $extKey = '' , $conditions = false , $getConstants = false , $request=null  ) {
        if ( !$request ) {
            /** @var Request $request */
            $request = GeneralUtility::makeInstance(Request::class, ...$requestInterface) ;
            $request->withArguments(['uid' => $pageUid] ) ;

        }
        return self::loadTypoScriptFromRequest($request , $extKey = '' , $conditions = false , $getConstants = false ) ;
	}

    public static function loadTypoScriptFromRequest($request, $extKey = '' , $getConstants = false  ) {

        $tsFrontend = $request->getAttribute('frontend.typoscript') ;
        if ( $tsFrontend ) {
            $ts = $tsFrontend->getSetupArray();
            if( !isset($ts['plugin.']) ) {
                return []  ;
            }
        } else {
            return  [];
        }

        if( $getConstants ) {
            // Todo get Constants  is untestet
            if(!empty($extKey) && isset($ts['config.'][$extKey . '.'])){
                $ts = self::removeDotsFromTypoScriptArray($ts['config.'][$extKey . '.']);
            } else {
                $ts = self::removeDotsFromTypoScriptArray($ts['config.']);
            }
        } else {
            if(!empty($extKey) && isset($ts['plugin.'][$extKey . '.'])){
                $ts = self::removeDotsFromTypoScriptArray($ts['plugin.'][$extKey . '.']);
            } else {
                $ts = self::removeDotsFromTypoScriptArray($ts['plugin.']);
            }
        }
        return $ts ;
    }

	/**
	 * Removes the dots from an typoscript array
	 * @author Peter Benke <pbenke@allplan.com>
	 * @param $array
	 * @return array
	 */
	private static function removeDotsFromTypoScriptArray($array) {

		$newArray = Array();

		if(is_array($array)){

			foreach ($array as $key => $val) {

				if (is_array($val)) {

					// Remove last character (dot)
					$newKey = substr($key, 0, -1);
					$newVal = self::removeDotsFromTypoScriptArray($val);

				} else {

					$newKey = $key;
					$newVal = $val;

				}

				$newArray[$newKey] = $newVal;

			}

		}

		return $newArray;

	}

}