<?php
namespace JVelletti\JvEvents\Validation\Validator;

class LocationValidator extends BaseValidator {

    /**
     * @var array
     */
    protected $minLength = array(
        'email'				=> 0,
        'phone'				=> 0,
        'link'				=> 0,

        'name'				=> 3,
        'streetAndNr'		=> 0,
        'additionalInfo'	=> 0,
        'zip'		        => 0,
        'city'		        => 1,
        'country'		    => 2,

        'lat'		        => 5,
        'lng'		        => 5,
    );

    /**
	 * @var array
	 */
	protected $maxLength = array(
		'email'				=> 80,
        'phone'				=> 30,
        'link'				=> 80,

		'name'				=> 60,
		'streetAndNr'		=> 50,
		'additionalInfo'	=> 50,
		'zip'		        => 10,
		'city'		        => 30,
		'country'		    => 2,

		'lat'		        => 20,
		'lng'		        => 20,
	);

	/**
	 * Check if $value is valid
	 *
	 * @param \JVelletti\JvEvents\Domain\Model\Location $location
	 * @return void
	 */
	public function isValid(mixed $location): void
    {

        $isValid = true ;

        $isValid = $this->securityChecks( $location->getName() , 'name' , $isValid ) ;

        $isValid = $this->securityChecks( $location->getStreetAndNr() , 'streetAndNr' , $isValid ) ;
        $isValid = $this->securityChecks( $location->getAdditionalInfo() , 'additionalInfo' , $isValid ) ;
        $isValid = $this->securityChecks( $location->getZip() , 'zip' , $isValid ) ;
        $isValid = $this->securityChecks( $location->getCity() , 'city' , $isValid ) ;
        $isValid = $this->securityChecks( $location->getCountry() , 'country' , $isValid ) ;
        $isValid = $this->securityChecks( $location->getLat() , 'lat' , $isValid ) ;
        $isValid = $this->securityChecks( $location->getLng() , 'lng' , $isValid ) ;
        $isValid = $this->securityChecks( $location->getLng() , 'lng' , $isValid ) ;

        $isValid = $this->securityChecks( $location->getLink() , 'link' , $isValid ) ;
        if( $location->getLink() && $location->getLink()  != "-") {
            $isValid = $this->urlIsValid( trim($location->getLink()) , 'link' , NULL , $isValid ) ;
        }


        $isValid = $this->securityChecks( $location->getPhone() , 'phone' , $isValid ) ;

        if( $location->getEmail() ) {
            $isValid = $this->securityChecks( $location->getEmail() , 'email' , $isValid ) ;
            if( $location->getEmail()!= "-") {
                $isValid = $this->emailIsValid( trim($location->getEmail()) , 'email' , false , $isValid ) ;
            }
        }

        $isValid = $this->stringLengthIsValid($this->minLength['name'] , $this->maxLength['name'] , $location->getName() , 'name' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['phone'] , $this->maxLength['phone'] , $location->getPhone() , 'phone' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['streetAndNr'] , $this->maxLength['streetAndNr'] , $location->getStreetAndNr() , 'streetAndNr' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['additionalInfo'] , $this->maxLength['additionalInfo'] , $location->getAdditionalInfo() , 'additionalInfo' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['zip'] , $this->maxLength['zip'] , $location->getZip() , 'zip' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['city'] , $this->maxLength['city'] , $location->getCity() , 'city' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['country'] , $this->maxLength['country'] , $location->getCountry() , 'country' , NULL , $isValid ) ;
        if ( $location->getStreetAndNr() != "-" && $location->getStreetAndNr() != '') {
            $validBeforeLNG = $isValid ;

            $custError = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:location.geocode.error' );
            $isValid = $this->stringLengthIsValid($this->minLength['lng'] , $this->maxLength['lng'] , $location->getLng() , 'lng' ,
                null , $isValid , $custError . " LNG= 11.575"  ) ;

            if ( !$isValid && $validBeforeLNG ) {
                $isValid = $this->stringLengthIsValid($this->minLength['lat'] , $this->maxLength['lat'] , $location->getLat() , 'lat' ,
                    null , $isValid , " LAT= 48.137"  ) ;

            } else {
                $isValid = $this->stringLengthIsValid($this->minLength['lat'] , $this->maxLength['lat'] , $location->getLat() , 'lat' ,
                    null , $isValid , $custError . " LAT= 48.137"  ) ;
            }

        }
        $isValid = $this->isHasUnwantedHtmlCodeValue( $location->getDescription() , 'description' , $isValid ) ;

	}


}
