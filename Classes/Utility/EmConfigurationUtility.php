<?php

namespace JVE\JvEvents\Utility ;

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
 * The TYPO3 project - inspiring people to share!
 *
 * inspirerd from Georg Ringer news Extension
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class to get the settings from Extension Manager
 *
 */
class EmConfigurationUtility
{

    /**
     * Parses the extension settings.
     * @param boolean $asObject
     * @return array
     */
    public static function getEmConf($asObject=false)
    {
		$settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['jv_events']);

		if (!is_array($settings)) {
			$settings = [];
		}
		if( $settings['fontFamiliy ']) {
			$settings['fontFamiliy '] = str_replace("'" , "" , $settings['fontFamiliy '] ) ;
			$settings['fontFamiliy '] = str_replace('"' , "" , $settings['fontFamiliy '] ) ;
			$settings['fontFamiliy '] = str_replace(' ' , "" , $settings['fontFamiliy '] ) ;
		}
		if ( $asObject ) {
			$settingsObj = new \stdClass() ;
			foreach ($settings as $key => $value ) {
				$settingsObj->$key = $value ;
			}
			return $settingsObj ;
		}
		return $settings;
    }

	public static function getGoogleApiKey() {
		$configuration = self::getEmConf();
		return $configuration['googleApiKey'];
		// return $configuration['googleApiKey']['value'];
	}

}
