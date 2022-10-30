<?php
namespace JVE\JvEvents\Validation\Validator;

/**
 * Validator for $registrant
 *
 */
class EventRegistrantValidator extends \JVE\JvEvents\Validation\Validator\BaseValidator {

	/** @var array   */
	public $emConf = NULL ;

	/**
	 * Check if $value is valid
	 *
	 * @param  \JVE\JvEvents\Domain\Model\Event $event
	 * @return boolean
	 */
	public function isValid($event) {
		$isValid = true ;

        /**
         * @var \TYPO3\CMS\Extbase\Error\Error $error
         */

		if (! $event->isIsRegistrationPossible()  ) {


            $error = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,
                $this->translate('register.deadline_expired_text') , time());
            $this->result->forProperty('uid')->addError($error);

           $isValid = false ;
		}

		// ######### darf man sich überhaupt noch anmelden .. ggf. ist inzwischen der letzte Platz weg
		if ( $isValid && $event->isIsNoFreeSeats() ) {
            $error = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Error\Error::class,
                $this->translate('register.overbooked_text') , time());
            $this->result->forProperty('uid')->addError($error);

            $isValid = false ;
		}


		return $isValid;

	}

}