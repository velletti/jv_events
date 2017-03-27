<?php
namespace JVE\JvEvents\ViewHelpers;

/**
 * WriteDateAbbreviationViewHelper
 * @package TYPO3
 * @subpackage Fluid
 */
class WriteDateAbbreviationViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper{

	/**
	 * Parse content element
	 *
	 * @param string $month (2 numbers, e.g. '03')
	 * @return string
	 */
	public function render($month) {

		$abbreviation = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_jvevents_event.dateFormat.month.abbreviation.' . $month, 'jv_events');
		return $abbreviation;

	}


}