<?php
namespace JVelletti\JvEvents\ViewHelpers\Content\Record;

use JVelletti\JvEvents\Utility\ArrayUtility;
use JVelletti\JvEvents\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception as TYPO3ExtbaseObjectException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * TYPO3
 */

/**
 * TYPO3Fluid
 */

/**
 * Class GetTtContentViewHelper
 * Returns the content of a tt_content-data-record
 * @see http://blog.teamgeist-medien.de/2014/01/extbase-fluid-viewhelper-fuer-tt_content-elemente-mit-namespaces.html
 * @package Allplan\AllplanTemplate\ViewHelpers
 * @author Peter Benke <pbenke@allplan.com>
 */
class GetTtContentViewHelper extends AbstractViewHelper
{

	/**
	 * Initialize arguments
	 * @author Peter Benke <pbenke@allplan.com>
	 */
	public function initializeArguments()
	{
		parent::initializeArguments();
		$this->registerArgument('uid', 'string', 'Uid of the tt_content record', true);
		$this->registerArgument('wrap', 'string', 'Wrapping the content e.g. <div class="123">|</div>');
		$this->registerArgument('templateTagId', 'string', 'Id of the template tag');
	}

	/**
	 * Parse content element and return the rendered content
	 * Note to the template tag id:
	 * If given, the function searches for something like <template id="templateTagId">Part of the content...</template>
	 * => Everything else will be deleted, only the content inside this template-tag will be rendered
	 *
	 * @return string
	 * @throws TYPO3ExtbaseObjectException
	 * @author Peter Benke <pbenke@allplan.com>
	 */
	public function render(): string
	{

		$uid = intval(ArrayUtility::getValueByKey($this->arguments, 'uid'));
		$wrap = ArrayUtility::getValueByKey($this->arguments, 'wrap');
		$templateTagId = ArrayUtility::getValueByKey($this->arguments, 'templateTagId');

		if(empty($uid)){
			return '';
		}

		$conf = [
			'tables' => 'tt_content',
			'source' => $uid,
			'dontCheckPid' => 1,
		];

		$content = ObjectUtility::getContentObjectRenderer()->cObjGetSingle('RECORDS', $conf);

		if(!empty($wrap)){
			$content = str_replace('|', $content, $wrap);
		}

		if(!empty($templateTagId)){

			// Get all between the desired template tag
			$pattern[] = "/<template(.*)id=\"" . $templateTagId . "\">(.*)<\/template>/siU";
			$replace[] = "$2";

			// Empty all other template tags
			$pattern[] = "/<template(.*)>(.*)<\/template>/siU";
			$replace[] = "";

			$content = preg_replace($pattern, $replace, $content);

		}

		return $content;

	}

}