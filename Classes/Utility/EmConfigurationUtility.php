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
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
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
     * @return array|object
     */
    public static function getEmConf($asObject=false)
    {
        $settings = GeneralUtility::makeInstance(ExtensionConfiguration::class) ->get('jv_events');

        if (!is_array($settings)) {
			$settings = [];
		}
		if( isset( $settings['fontFamily']) ) {
			$settings['fontFamily'] = str_replace("'" , "" , $settings['fontFamily'] ) ;
			$settings['fontFamily'] = str_replace('"' , "" , $settings['fontFamily'] ) ;
			$settings['fontFamily'] = str_replace(' ' , "" , $settings['fontFamily'] ) ;
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
        if( Environment::getContext()->isDevelopment() ) {
            return trim( $configuration['googleApiKeyDEV'] );
        }
		return trim($configuration['googleApiKey'] );
	}

}
