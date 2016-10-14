<?php
namespace JVE\JvEvents\ViewHelpers;
/***************************************************************
 * Copyright notice
 *
 * (c) 2011-13 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Viewhelper to render a selectbox with values
 * in given steps from start to end value
 *
 * <code title="Usage">
 * {namespace register=\JVE\JvEvents\\iewHelpers}
 * <register:form.required fieldName="'username"/>
 * </code>
 */
class RequiredViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper   {
	/**
	 * Configuration manager to fetch settings from
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * Settings of the plugin
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Configuration of the framework
	 *
	 * @var array
	 */
	protected $frameworkConfiguration = array();

	/**
	 * Injection of configuration manager
	 *
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(
		\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
		);
		$layout = $this->settings['LayoutRegister'] ;

		$fields = $this->settings['register']['requiredFields'][$layout] ;
		$this->settings['register']['requiredFields'] = $fields ;
		$this->frameworkConfiguration = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
	}
	public function __initialize() {
		$this->registerArgument('fieldName', 'string', 'field name stat should be in requiredFields', false);
	}



	/**
	 * Render a special sign if the field is required
	 *
	 * @param string $fieldName Name of the field to render the required marker to
	 * @return string
	 */
	public function render($fieldName) {

		$fieldSettings = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode( $this->settings['register']['requiredFields'] ) ;
		if ( is_array($fieldSettings) && in_array( $fieldName, $fieldSettings)) {
			return  TRUE ;
		}

		return false;
	}
}
