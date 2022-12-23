<?php
namespace JVE\JvEvents\Utility;

use TYPO3\CMS\Backend\Utility\BackendUtility;

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
	 */
	public static function loadTypoScriptFromScratch($pageUid = 0, $extKey = '' , $conditions = false , $getConstants = false  ) {

		/**
		 * @var $extendedTemplateService \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService
		 */
        $pageService =  clone $GLOBALS['TSFE']->sys_page;
		$rootLine = $pageService->getRootLine($pageUid);

		$extendedTemplateService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\ExtendedTemplateService::class);

		$extendedTemplateService->tt_track = 0;
		// $extendedTemplateService->init();

		// To get static files also
		$extendedTemplateService->setProcessExtensionStatics(true);
		$extendedTemplateService->runThroughTemplates($rootLine);
		if( $conditions) {
            $extendedTemplateService->matchAlternative = $conditions ;
        }
		$extendedTemplateService->generateConfig();
        if( $getConstants ) {
            if(!empty($extKey)){
                $typoScript = self::removeDotsFromTypoScriptArray($extendedTemplateService->setup_constants['plugin.'][$extKey . '.']);
            }else{
                $typoScript = self::removeDotsFromTypoScriptArray($extendedTemplateService->setup_constants);
            }
        } else {
            if(!empty($extKey)){
                $typoScript = self::removeDotsFromTypoScriptArray($extendedTemplateService->setup['plugin.'][$extKey . '.']);
            }else{
                $typoScript = self::removeDotsFromTypoScriptArray($extendedTemplateService->setup);
            }
        }

		return $typoScript;

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