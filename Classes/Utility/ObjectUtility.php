<?php
namespace JVE\JvEvents\Utility;

/**
 * TYPO3
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ObjectUtility
{

	/**
	 * Returns the content object renderer
	 *
	 * @return ContentObjectRenderer
     * @author Peter Benke <pbenke@allplan.com>
	 */
	public static function getContentObjectRenderer(): ContentObjectRenderer
	{

		/**
		 * @var ConfigurationManagerInterface $configurationManager
		 */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
		return $configurationManager->getContentObject();

	}

}