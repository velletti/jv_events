<?php
namespace JVE\JvEvents\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * WriteDateAbbreviationViewHelper
 * @package TYPO3
 * @subpackage Fluid
 */
class WriteDateAbbreviationViewHelper extends AbstractViewHelper{

	/**
	 * Parse content element
	 *
	 * @param string $month (2 numbers, e.g. '03')
	 * @return string
	 */

    /** * Constructor *
     * @api
     */
    public function initializeArguments() {
        $this->registerArgument('month', 'string', 'Month value (1 - 12) as String for translation ', false);
        parent::initializeArguments() ;
    }

	public function render() {
        $month = $this->arguments['month'] ;
		$abbreviation = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('tx_jvevents_event.dateFormat.month.abbreviation.' . $month, 'jv_events');
		return $abbreviation;

	}


}