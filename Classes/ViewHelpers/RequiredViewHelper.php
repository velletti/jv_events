<?php
namespace JVE\JvEvents\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Viewhelper to render a selectbox with values
 * in given steps from start to end value
 *
 * <code title="Usage">
 * {namespace register=\JVE\JvEvents\\iewHelpers}
 * <register:form.required fieldName="'username"/>
 * </code>
 */
class RequiredViewHelper extends AbstractConditionViewHelper implements \TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface  {
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
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
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
        if ( $layout == '' ) { $layout = "1Allplan" ; }

		$fields = $this->settings['register']['requiredFields'][$layout] ;
		$this->settings['register']['requiredFields'] = $fields ;
		$this->frameworkConfiguration = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
	}
	public function initializeArguments() {
		$this->registerArgument('fieldName', 'string', 'field name that should be in requiredFields', false);
        parent::initializeArguments();
	}



    /**
     * @param array|null $arguments
     * @return bool
     */
    protected static function evaluateCondition($arguments = null, $settings = array() )
    {
        $fieldSettings = GeneralUtility::trimExplode( "," , $settings['register']['requiredFields'] ) ;
        if ( is_array($fieldSettings) && in_array( $arguments['fieldName'], $fieldSettings)) {
            return  TRUE ;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function render()
    {
        if (static::evaluateCondition($this->arguments , $this->settings )) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }


}
