<?php
namespace JVE\JvEvents\Validation\Validator;




/**
 * Validator for ProfileData
 *
 */
class BaseValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	/**
	 * eventRepository
	 *
	 * @var \JVE\JvEvents\Domain\Repository\EventRepository
	 * @inject
	 */
	protected $eventRepository = NULL;

	/**
	 * registrantRepository
	 *
	 * @var \JVE\JvEvents\Domain\Repository\RegistrantRepository
	 * @inject
	 */
	protected $registrantRepository = NULL;

	/** @var array   */
	public $emConf = NULL ;

	/** @var array   */
	public $settings = NULL ;

	public function __construct() {
		$this->emConf =\JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();

		/** @var \JVE\JvEvents\UserFunc\Flexforms $flexhelper */
        $flexhelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JVE\\JvEvents\\UserFunc\\Flexforms');
		$allSettings = $flexhelper->getSettings() ;

		$this->settings = $allSettings['plugin.']['tx_jvevents_events.']['settings.'];
		$this->settings = \TYPO3\CMS\Core\Utility\GeneralUtility::removeDotsFromTS($this->settings ) ;
		$this->settings['pageId']						=  $GLOBALS['TSFE']->id ;
		$this->settings['servername']					=  \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
		$this->settings['sys_language_uid']				=  $GLOBALS['TSFE']->sys_language_uid ;


	}

	/**
	 * Check if $value is valid
	 * @param array $value Dummy overwritten in extending Validator
	 * @return boolean
	 */
	public function isValid($value) {
		return true;

	}

	/**
	 * translate function
	 * @param string $label the locallang label to translate
	 * @return string the localized String
	 */
	protected function translate($label, $arguments = NULL) {
		return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($label, 'JvEvents', $arguments);
	}


	/**
	 * getActualUser and AdminStauts
	 * @return array $return
	 */
	protected function getLoginUser() {
		$return = array( 'uid' => 0 ,
						'username' => 	'' ,
						'isAdmin' => FALSE
		) ;

		if ( is_array($GLOBALS['TSFE']->fe_user->user)  ) {

			$return['uid'] = $GLOBALS['TSFE']->fe_user->user['uid'] ;
			$return['username'] = $GLOBALS['TSFE']->fe_user->user['username'] ;
			$AdminUserGroups = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode( "," , $this->emConf['AdminUserGroups'] ) ;
			foreach ( $AdminUserGroups as $AdminUserGroup) {
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($GLOBALS['TSFE']->fe_user->user['usergroup'], $AdminUserGroup)) {
					$return['isAdmin'] = TRUE;
				}
			}

		}

		return $return ;
	}

	/**
	 * stringLengthIsValid function
	 * @param int $min
	 * @param int $max
	 * @param string $propertyValue
	 * @param string $propertyName
	 * @param string $customErrorMessage
	 * @return boolean
	 */
	protected function stringLengthIsValid($min, $max, $propertyValue, $propertyName, $customErrorMessage = null , $stringLengthIsValid = true ) {

		/**
		 * @var \TYPO3\CMS\Extbase\Error\Error $error
		 */

		$errorMessage = '';
		$length = '';
		/** @var \TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator $validator */
		$validator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Validation\\Validator\\StringLengthValidator' );
		$validator->acceptsEmptyValues = FALSE ;

		// Settings for min length check
		if (!empty($min)) {
			$validator->options['minimum'] = $min;
            $errorMessage =  \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( "validator.stringlength.less" , "extbase" , array( $min )) ;
            // Not valid (properties see also: /typo3/sysext/extbase/Classes/Error/Result.php)
            if($validator->validate($propertyValue)->hasErrors()){

                if(!empty($customErrorMessage)){
                    $errorMessage = $this->translate($customErrorMessage);
                }

                $error = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',$errorMessage, time());
                $this->result->forProperty($propertyName)->addError($error);

                $stringLengthIsValid = false;

            }
		}




        // Settings for max length check
        if (!empty($max)) {
            $validator->options['minimum'] = NULL ;
            $validator->options['maximum'] = $max;
            $errorMessage =  \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( "validator.stringlength.exceed" , "extbase" , array( $max )) ;
            // Not valid (properties see also: /typo3/sysext/extbase/Classes/Error/Result.php)
            if($validator->validate($propertyValue)->hasErrors()){

                if(!empty($customErrorMessage)){
                    $errorMessage = $this->translate($customErrorMessage);
                }

                $error = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',$errorMessage, time());
                $this->result->forProperty($propertyName)->addError($error);

                $stringLengthIsValid = false;

            }
        }


		return $stringLengthIsValid;

	}
    /**
     * stringLengthIsValid function
     * @param int $min
     * @param int $max
     * @param string $propertyValue
     * @param string $propertyName
     * @param string $customErrorMessage
     * @param boolean $default
     * @return boolean
     */
    protected function emailIsValid( $propertyValue, $propertyName, $errorMessage = false , $isValid=true ) {


        /** @var \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator $emailAddressValidator */
        $emailAddressValidator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Validation\\Validator\\EmailAddressValidator');
        $emailAddressValidator->acceptsEmptyValues = false ;
        /**
         * @var \TYPO3\CMS\Extbase\Error\Error $error
         */

        if ( !$errorMessage ) {
            $errorMessage =  \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( "validator.emailaddress.notvalid" , "extbase") ;
        }
        $error = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',  $errorMessage , time());




        if($emailAddressValidator->validate( $propertyValue )->hasErrors()){
            $this->result->forProperty($propertyName)->addError($error);
            $isValid = false;
        }
        return $isValid ;
    }

	/**
	 * Returns TRUE, if the given property ($propertyValue) is a valid
	 * alphanumeric string, which is defined as [a-zA-Z0-9_]*.
	 *
	 * If at least one error occurred, the result is FALSE.
	 *
	 * @param mixed $value The value that should be validated
	 * @param string $propertyName The propertyName that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	protected function isAlphaNumeric($value , $propertyName) {

		$this->errors = array();
		if (is_string($value) && preg_match('/^[a-z0-9_]*$/i', $value)) return TRUE;

		$error = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error','The given subject was not a valid alphanumeric string.', time());
		$this->result->forProperty($propertyName)->addError($error);

		return FALSE;
	}

    /*  first easy check if there are htm CODE nippets in a given string input could be done better
     *
     * @var string $value
     * @var string $property
     * $var boolean $default ( result of tests run before )
     * @return boolean
     */
    public function securityChecks($value , $property = false , $default = true ) {
        // first Security Check: easy use of strip_tags in input Fields ..
        if( strip_tags( $value) != $value ) {
            if( $property ) {
                $error = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error', $this->translate('msg_error_html_code_not_allowed'), time());
                $this->result->forProperty($property)->addError($error);
            }

            return false ;
        }
        return $default ;
    }


}