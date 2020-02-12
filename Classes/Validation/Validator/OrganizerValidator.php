<?php
namespace JVE\JvEvents\Validation\Validator;

class OrganizerValidator extends BaseValidator {

	/**
	 * @var array
	 */
	protected $maxLength = array(
		'email'				=> 80,
		'name'				=> 40,
		'phone'				=> 30,
		'link'				=> 80,
	);

    /**
     * @var array
     */
    protected $minLength = array(
        'email'				=> 8,
        'name'				=> 3,
        'phone'				=> 8,
        'link'				=> -1,
    );

	/**
	 * Check if $value is valid
	 *
	 * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
	 * @return boolean
	 */
	public function isValid($organizer) {

        $isValid = true ;
        $isValid = $this->securityChecks( $organizer->getEmail() , 'email' , $isValid ) ;
        $isValid = $this->securityChecks( $organizer->getName() , 'name' , $isValid ) ;
        $isValid = $this->securityChecks( $organizer->getPhone() , 'phone' , $isValid ) ;
        $isValid = $this->securityChecks( $organizer->getPhone() , 'link' , $isValid ) ;

        $isValid = $this->emailIsValid( trim($organizer->getEmail()) , 'email' , false , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['name'] , $this->maxLength['name'] , $organizer->getName() , 'name' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['phone'] , $this->maxLength['phone'] , $organizer->getPhone() , 'phone' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['link'] , $this->maxLength['link'] , $organizer->getLink() , 'link' , NULL , $isValid ) ;

        if( $organizer->getLink() ) {
            $isValid = $this->urlIsValid( trim($organizer->getLink()) , 'link' , NULL , $isValid ) ;
        }
        $isValid = $this->isHasUnwantedHtmlCodeValue( $organizer->getDescription() , 'description' , $isValid ) ;


		return $isValid;

	}


}
