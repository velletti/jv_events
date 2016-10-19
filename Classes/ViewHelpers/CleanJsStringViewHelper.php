<?php
namespace JVE\JvEvents\ViewHelpers;


class CleanJsStringViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Cleans a string for javascript (has to be quoted with ')
	 * @author Peter Benke <pbenke@allplan.com>
	 * @return string
	 */
	public function render() {

		$content = $this->renderChildren();

		// Strip new lines
		$content = str_replace("\r", '', $content);
		$content = str_replace("\n", '', $content);

		// Mask Quotes
		$content = str_replace("'", "\'", $content);

		return $content;

	}
}
