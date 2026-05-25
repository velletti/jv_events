<?php
namespace JVelletti\JvEvents\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Viewhelper to render a selectbox with values
 * in given steps from start to end value
 *
 * <code title="Usage">
 * {namespace register=\JVelletti\JvEvents\\iewHelpers}
 * <register:form.required fieldName="'username"/>
 * </code>
 */
class RequiredViewHelper extends AbstractConditionViewHelper implements ViewHelperInterface  {
	/**
  * Configuration manager to fetch settings from
  *
  * @var ConfigurationManager
  */
 protected $configurationManager;

	/**
	 * Settings of the plugin
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Configuration of the framework
	 *
	 * @var array
	 */
	protected $frameworkConfiguration = [];

	public function __construct(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager)
 {
     $this->configurationManager = $configurationManager;
 }
	public function initializeArguments(): void {
		$this->registerArgument('fieldName', 'string', 'field name that should be in requiredFields', false);
        parent::initializeArguments();
	}



    /**
     * @param array|null $arguments
     * @return bool
     */
    public static function verdict(array $arguments, $settings = [] ): bool
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
