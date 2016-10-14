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
}