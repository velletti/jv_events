<?php
namespace JVE\JvEvents\Validation\Validator;




use JVE\JvEvents\Domain\Repository\EventRepository;
use JVE\JvEvents\Domain\Repository\RegistrantRepository;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Validator for ProfileData
 *
 */
class BaseValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

	/**
	 * eventRepository
	 *
	 * @var \JVE\JvEvents\Domain\Repository\EventRepository
	 */
	protected $eventRepository = NULL;

    /**
     * @var array
     */
	protected $unwanted =  array( "<script" , "<iframe" , "style=" , "class=" , "font:" ,"color:" ) ;

	/**
	 * registrantRepository
	 *
	 * @var \JVE\JvEvents\Domain\Repository\RegistrantRepository
	 */
	protected $registrantRepository = NULL;

	/** @var array   */
	public $emConf = NULL ;

	/** @var array   */
	public $settings = NULL ;


    /**
     * @param EventRepository $eventRepository
     */
    public function injectEventRepository(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param RegistrantRepository $registrantRepository
     */
    public function injectRegistrantRepository(RegistrantRepository $registrantRepository)
    {
        $this->registrantRepository = $registrantRepository;
    }

	public function __construct() {
		$this->emConf =\JVE\JvEvents\Utility\EmConfigurationUtility::getEmConf();

		$allSettings = $this->getSettings() ;
		$this->settings = $allSettings['plugin.']['tx_jvevents_events.']['settings.'];
		$this->settings = GeneralUtility::removeDotsFromTS($this->settings ) ;
		$this->settings['pageId']						=  $GLOBALS['TSFE']->id ;
		$this->settings['servername']					=  GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
        /** @var AspectInterface $languageAspect */
        $languageAspect = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getAspect('language') ;

		$this->settings['sys_language_uid']				=  $languageAspect->getId() ;


        parent::__construct() ;

	}
	public function getSettings() {
        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $configurationManager = $objectManager->get(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class);
        return  $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT );

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
     * @param array $arguments
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
			$AdminUserGroups = GeneralUtility::trimExplode( "," , $this->emConf['AdminUserGroups'] ) ;
			foreach ( $AdminUserGroups as $AdminUserGroup) {
				if (GeneralUtility::inList($GLOBALS['TSFE']->fe_user->user['usergroup'], $AdminUserGroup)) {
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
     * @param boolean $stringLengthIsValid
	 * @return boolean
	 */
	protected function stringLengthIsValid($min, $max, $propertyValue, $propertyName, $customErrorMessage = null , $stringLengthIsValid = true ) {

		/**
		 * @var \TYPO3\CMS\Extbase\Error\Error $error
		 */

		/** @var \TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator $validator */
		$validator = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator::class );
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

                $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,$errorMessage, time());
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

                $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,$errorMessage, time());
                $this->result->forProperty($propertyName)->addError($error);

                $stringLengthIsValid = false;

            }
        }


		return $stringLengthIsValid;

	}
    /**
     * urlIsValid function
     * @param string $propertyValue
     * @param string $propertyName
     * @param string|boolean $errorMessage
     * @param boolean $isValid Default Error Message
     * @return boolean
     */
    protected function urlIsValid( $propertyValue, $propertyName, $errorMessage = false , $isValid=true ) {


        if ( !$errorMessage ) {
            $errorMessage =  \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( "validator.urladdress.notvalid" , "jv_evetns") ;
        }
        if ( !$errorMessage ) {
            $errorMessage = "URL is not valid or not reachable. Must start with http:// or https://" ;
        }
        /**
         * @var \TYPO3\CMS\Extbase\Error\Error $error
         */
        $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,  $errorMessage , time());

        if(! filter_var($propertyValue, FILTER_VALIDATE_URL) || ! substr( strtolower($propertyValue), 0 , 4) == "http" ) {
            $isValid = false;
            /**
             * @var \TYPO3\CMS\Extbase\Error\Error $error
             */
            $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,  $errorMessage , time());

            $this->result->forProperty($propertyName)->addError($error);
        } else {
            $file_headers = @get_headers($propertyValue);
            if( $file_headers && $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                /**
                 * @var \TYPO3\CMS\Extbase\Error\Error $error
                 */
                $errorMessage = "URL is not reachable. Shure it is Correct ? if it is correct, your server is configured very strictly" ;
                $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,  $errorMessage , time());

                $this->result->forProperty($propertyName)->addError($error);
                $isValid = false;
            }
        }


        return $isValid ;
    }

    /**
     * emailIsValid function
     * @param string $propertyValue
     * @param string $propertyName
     * @param string|boolean $errorMessage
     * @param boolean $isValid
     * @return boolean
     */
    protected function emailIsValid( $propertyValue, $propertyName, $errorMessage = false , $isValid=true ) {


        /** @var \TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator $emailAddressValidator */
        $emailAddressValidator = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator::class);
        $emailAddressValidator->acceptsEmptyValues = false ;

        if ( !$errorMessage ) {
            $errorMessage =  \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate( "validator.emailaddress.notvalid" , "extbase") ;
        }
        /** @var \TYPO3\CMS\Extbase\Error\Error $error */
        $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,  $errorMessage , time());




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
     * @param boolean $default ( result of tests run before )
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	protected function isAlphaNumeric($value , $propertyName , $default = true) {

		if (is_string($value) && preg_match('/^[a-z0-9_]*$/i', $value)) return $default;
        /** @var \TYPO3\CMS\Extbase\Error\Error $error */
		$error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,'The given subject was not a valid alphanumeric string.', time());
		$this->result->forProperty($propertyName)->addError($error);

		return FALSE;
	}

    /**
     * Returns TRUE, if the given property ($propertyValue) is a valid
     * alphanumeric string, which is defined as [a-zA-Z0-9_]*.
     *
     * If at least one error occurred, the result is FALSE.
     *
     * @param mixed $value The value that should be validated
     * @param string $propertyName The propertyName that should be validated
     * @param boolean $default ( result of tests run before )
     * @param string $errorString The given subject was not a valid integer as default
     * @return boolean TRUE if the value is valid, FALSE if an error occured
     */
    protected function isNumeric($value , $propertyName , $default = true , $errorString = 'The given subject was not a valid integer or lower than 1.') {

        if (intval($value) > 0 ) return $default;
        /** @var \TYPO3\CMS\Extbase\Error\Error $error */
        $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,$errorString , time());
        $this->result->forProperty($propertyName)->addError($error);

        return FALSE;
    }


    /*  first easy check if there are htm CODE nippets in a given string input could be done better
     *
     * @param string $value
     * @param string $property
     * @param boolean $default ( result of tests run before )
     * @return boolean
     */
    public function securityChecks($value , $property = false , $default = true ) {
        // first Security Check: easy use of strip_tags in input Fields ..
        if( strip_tags( $value) != $value ) {
            if( $property ) {
                /** @var \TYPO3\CMS\Extbase\Error\Error $error */
                $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class, $this->translate('error.msg_error_html_code_not_allowed'), time());
                $this->result->forProperty($property)->addError($error);
            }

            return false ;
        }
        return $default ;
    }

    /**
     * Returns TRUE, if the given property ($propertyValue) is a valid
     * alphanumeric string, which is defined as [a-zA-Z0-9_]*.
     *
     * If at least one error occurred, the result is FALSE.
     *
     * @param mixed $value The value that should be validated
     * @param string $propertyName The propertyName that should be validated
     * @param boolean $isValid The result of previous validations
     * @return boolean TRUE if the value is valid, FALSE if an error occured
     */
    protected function isStringTimeValue($value , $propertyName , $isValid) {

        if (is_string($value) && preg_match('/^[0-2][0-9]:[0-5][0-9]$/i', $value)) return $isValid;

        /** @var \TYPO3\CMS\Extbase\Error\Error $error */
        $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,'The given Time \'' . $value . '\' ('. $propertyName .') was not a valid Time value like hh:mm.', time());
        $this->result->forProperty($propertyName)->addError($error);

        return FALSE;
    }

    /**
     * Returns TRUE, if the given property ($propertyValue) is a valid
     * alphanumeric string, which is defined as [a-zA-Z0-9_]*.
     *
     * If at least one error occurred, the result is FALSE.
     *
     * @param mixed $value The value that should be validated
     * @param string $propertyName The propertyName that should be validated
     * @param boolean $isValid The result of previous validations
     * @return boolean TRUE if the value is valid, FALSE if an error occured
     */
    protected function isStringDateValue($value , $propertyName , $isValid) {


        $stD = \DateTime::createFromFormat('d.m.Y', $value  );
        if( $stD instanceof \DateTime ) {
            if (is_string($value) && preg_match('/^[0-3][0-9].[0-1][0-9].[1-2][0-9]{3}$/i', $value)) return $isValid;
        }
        /** @var \TYPO3\CMS\Extbase\Error\Error $error */
        $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,'The given value \'' . $value .'\' was not a valid Date value like dd.mm.yyyy .', time());
        $this->result->forProperty($propertyName)->addError($error);

        return FALSE;
    }


    /**
     * Returns TRUE, if the given property ($propertyValue) is a valid
     * alphanumeric string, which is defined as [a-zA-Z0-9_]*.
     *
     * If at least one error occurred, the result is FALSE.
     *
     * @param mixed $value The value that should be validated
     * @param string $propertyName The propertyName that should be validated
     * @param boolean $isValid The result of previous validations
     * @return boolean TRUE if the value is valid, FALSE if an error occured
     */
    protected function isHasUnwantedHtmlCodeValue($value , $propertyName , $isValid) {

        $replace = array() ;
        for ( $i=0 ; $i < count( $this->unwanted ) ; $i++) {
            $replace[] = '' ;
        }


        $desc = str_replace( $this->unwanted, $replace , $value ) ;

        if ( $desc == $value ) { return $isValid ; }

        /** @var \TYPO3\CMS\Extbase\Error\Error $error */
        $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,$this->translate('error.msg_error_html_code_not_allowed') , time());
        $this->result->forProperty($propertyName)->addError($error);

        return FALSE;
    }
    /**
     * Returns TRUE, if the given property ($propertyValue) is a valid
     * intvalue , minum 1 tag is selected and not more than 5
     *
     * If at least one error occurred, the result is FALSE.
     *
     * @param mixed $value The value that should be validated
     * @param string $propertyName The propertyName that should be validated
     * @param boolean $isValid The result of previous validations
     * @return boolean TRUE if the value is valid, FALSE if an error occured
     */
    protected function isTagArray($value , $propertyName , $isValid) {
        $value = substr( $value , 0 , strlen($value ) -1 ) ;

        $tagArray= GeneralUtility::trimExplode("," , $value) ;

        if( strlen( trim($this->emConf['MaxTagsPerEvent'] )) > 0 && is_integer(intval($this->emConf['MaxTagsPerEvent']))) {
            $max = intval($this->emConf['MaxTagsPerEvent']) ;
        } else {
            $max = 5 ;
        }

        if( count($tagArray ) > $max ) {
            /** @var \TYPO3\CMS\Extbase\Error\Error $error */
            $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class, 'Too much Tags (' . count($tagArray ) .') selected (max ' . $max . ' allowed!)' , time());
            $this->result->forProperty($propertyName)->addError($error);
            return false ;
        }
        if( count($tagArray ) > 0 ) {
            foreach ($tagArray as $key => $tag ) {
                if( intval($tag) < 1 ) {
                    /** @var \TYPO3\CMS\Extbase\Error\Error $error */
                    $error = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class, 'Tags are not selected correctly' , time());
                    $this->result->forProperty($propertyName)->addError($error);
                    return false ;
                }
            }
        }

        return $isValid ;
    }


}