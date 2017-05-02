<?php
/**
 * Created by PhpStorm.
 * User: velletti
 * Date: 21.09.2016
 * Time: 13:39
 */

namespace JVE\JvEvents\UserFunc;

class Flexforms {
	public function getSettings() {
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
		$settings = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT );

		return $settings ;
	}
	public function selectLayout($PA, $fObj) {
		
		$allSettings = $this->getSettings() ;
		$settings = $allSettings['plugin.']['tx_jvevents_events.']['settings.'];

		$layoutType = $PA['parameters']['layoutType'] ;

		if ( $layoutType == '' ) {
			return '<div class="alert alert-error">Typo Script of this Extension : settings.list.layouts.layout1 .. not set ! </div>' ;
		}
		$layouts = $settings[$layoutType]['layouts.'] ;
		if ( !is_array($layouts)) {
			return '<div class="alert alert-error">Typo Script of this Extension : settings.list.layouts.layout1 .. not an Array ! </div>' ;
		}
		$formField = '<select name="' . $PA['itemFormElName'] . '"';
		$formField .= ' onchange="' . htmlspecialchars(implode('', $PA['fieldChangeFunc'])) . '"';
		$formField .= $PA['onFocus'];
		$formField .= ' >';
		foreach ( $layouts as $key => $layout) {
			$selected = '' ;
			if ( $key == htmlspecialchars($PA['itemFormElValue']) ) {
				$selected = ' selected="selected"' ;
			}
			$formField .= '<option ' . $selected . ' value="' . $key .  '"> ' . $layout . '</option>';
		}
		$formField .= '</select>';
		// $formField .= ' Old Value ' . $PA['itemFormElValue'] ;

		return $formField;
	}

	/** used in event Model TCA to show the Translations of Tags and Categories, but still work with the uid of the default record
     * */
	public function TranslateMMvalues($config) {
	    if( $config['row']['sys_language_uid'][0] < 1 ) {
	        return $config ;
        }

        // Works only Out Of the Box if label Field and Foreign_table in TCA is set AND  only ONE Field / Table Name is set and not splitted, joined  with a komma
        $table = $config['config']['foreign_table'] ;

        $nameField = $GLOBALS['TCA'][$table]['ctrl']['label'] ;

        if( $table . $nameField == '' || strpos( $table . $nameField  , ",") > 0 ) {
            return $config ;
        }

        /**
         * @var $db \TYPO3\CMS\Core\Database\DatabaseConnection
         */
        $db = $GLOBALS['TYPO3_DB'];
        $rows = $db->exec_SELECTgetRows('uid,l10n_parent,' . $nameField ,  $table , 'sys_language_uid=' . $config['row']['sys_language_uid'][0]
            , '',  '', '', $uidIndexField = 'l10n_parent') ;

        foreach ( $config['items'] as $key => $item ) {
            $uid = $item[1] ;
            if( is_array(  $rows[$uid] ) ) {
                $config['items'][$key][0] = $config['items'][$key][0] . " (" . $rows[$uid][ $nameField ] . ")" ;
            }
            $config['items'][$key][0] = "[" . $config['items'][$key][0] = $config['items'][$key][1] . "] ". $config['items'][$key][0] ;
        }

        return $config ;
    }
}