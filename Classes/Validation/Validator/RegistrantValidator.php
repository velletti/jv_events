<?php
namespace JVE\JvEvents\Validation\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Validator for $registrant
 *
 */
class RegistrantValidator extends \JVE\JvEvents\Validation\Validator\BaseValidator {

	/** @var array   */
	public $emConf = NULL ;

	/**
	 * Check if $value is valid
	 *
	 * @param  \JVE\JvEvents\Domain\Model\Registrant $registrant
	 * @return boolean
	 */
	public function isValid($registrant) {
		$isValid = true ;

		/**
		 * @var \TYPO3\CMS\Extbase\Error\Error $error
		 */

		// ##### First  test for Token , reg Form Fill Speed and Fingerprint against last registration
		$form= GeneralUtility::_POST('tx_jvevents_events') ;

		$formToken = $form['formToken'] ;
		$generatedTokenBase = ( "P" . $this->settings['pageId'] . "-L" .$this->settings['sys_language_uid'] . "-E" . $form['event'] );
		$generatedToken = md5( $generatedTokenBase);


       	if (! $formToken==$generatedToken ) {
			$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',
				$this->translate('register_mandatory_error_fingerprint'), time());
			$this->result->forProperty('fingerprint')->addError($error);
			$isValid = false ;
		}


		// ################ fingerprint of existing registration #######

		$fingerPrint = md5(strtolower( $registrant->getEmail() . $registrant->getFirstName() . $registrant->getLastName() . $registrant->getEvent() ) );

		/** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $otherReg */
		$otherReg = $this->registrantRepository->getByFingerprint($fingerPrint) ;
		if( is_object($otherReg)) {
			/** @var \JVE\JvEvents\Domain\Model\Registrant $otherReg */
			$otherReg = $otherReg->getFirst() ;
			if( is_object($otherReg)) {
				if($otherReg->getCrdate() > ( time() - $this->emConf['minFormSeconds'])  ) {
					// ToDo why is $otherReg->getCrdate() and $otherReg->getCreated() Empty ??
					// look to DB -> registrant with event ID and  Fingerprint
					$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',
						$this->translate('register_mandatory_error_fingerprint'), time());
					$this->result->forProperty('fingerprint')->addError($error);
					$isValid = false ;
				}

			}
		}

		// ##################   time diff how fast is the form filled .. ################
		$diff = time() - $registrant->getStartReg()  ;
		if ( $diff < $this->emConf['minFormSeconds']  ) {

			$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',
				$this->translate('register_mandatory_error_too_fast'), time());
			$this->result->forProperty('fingerprint')->addError($error);
			$isValid = false ;
		}


		// ############# now mandatory Fields and Honeypot Field hotprice that may not be filled

		if ( $isValid ) {

			$layout = $registrant->getLayoutRegister() ;

			$requiredFields = GeneralUtility::trimExplode( "," , $this->settings['register']['requiredFields'][$layout] ) ;

			foreach ($requiredFields as $field ) {
				$getter = "get" . trim(ucfirst($field)) ;
				if ( method_exists( $registrant , $getter)) {
					$value=  $registrant->$getter() ;
					switch ($getter) {
						// Field HotPrice is the honey Pot Field. Should be in every Form and should be empty .. Invisible by Css
						case 'getHotprice':
							if( trim($value) <> '' ) {
								$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',$this->translate('register_mandatory_error_honeypot'), time());
								$this->result->forProperty($field)->addError($error);
								$isValid = false ;
							}
							break;
						case 'getGender':
							if( $value < 1 ) {
								$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',$this->translate('register_mandatory_error'), time());
								$this->result->forProperty($field)->addError($error);
								$isValid = false ;
							}
							break;
						case 'getEmail':
							if(strlen(trim($value)) < 1 ) {
								$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',$this->translate('register_mandatory_error'), time());
								$this->result->forProperty($field)->addError($error);
								$isValid = false ;
							}
							if( ! GeneralUtility::validEmail( $value )) {
								$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',$this->translate('register_mandatory_email_error'), time());
								$this->result->forProperty($field)->addError($error);
								$isValid = false ;
							}
							break;

						case 'getPrivacy':
							if( !$value ) {
								$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',$this->translate('register_mandatory_error'), time());
								$this->result->forProperty($field)->addError($error);
								$isValid = false ;
							}
							break;
						default:
							if(strlen(trim($value)) < 1 ) {
								$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',$this->translate('register_mandatory_error'), time());
								$this->result->forProperty($field)->addError($error);
								$isValid = false ;
							}

							break;

					}


				} else {
					$error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',"TypoScript error : Required Fieldname : '" . $getter . "' is not Function in  object Registrant " , time());
					$this->result->forProperty($getter)->addError($error);
					$isValid = false ;
				}

			}
		}




		// ######### darf man sich überhaupt noch anmelden .. ggf. ist inzwischen der letzte Platz weg oder frist abgelaufen ?
		if ( $isValid ) {
			// ToDo : check if enough Seats ...
            if ($this->settings['register']['doNotallowSameEmail']) {
                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $otherReg */
                $otherReg = $this->registrantRepository->findByFilter(trim($registrant->getEmail() ), $registrant->getEvent() ,  0 , array('filter' => array( 'startDate' => 9999999999 ))  ) ;

                if (  $otherReg && is_object( $otherReg->getFirst()) && $otherReg->getFirst()->getUid()  ) {
                    $error = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Error',$this->translate('register_email_registered_same_event') , time());
                    $this->result->forProperty('email')->addError($error);
                    $isValid = false ;
                }
            }
		}


		return $isValid;

	}

}