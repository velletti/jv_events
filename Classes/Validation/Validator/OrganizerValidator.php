<?php
namespace JVelletti\JvEvents\Validation\Validator;

class OrganizerValidator extends BaseValidator {

	/**
	 * @var array
	 */
	protected $maxLength = array(
		'email'				=> 80,
		'name'				=> 40,
		'phone'				=> 30,
		'link'				=> 120,
		'charityLink'		=> 240,
		'youtubeLink'		=> 120,
	);

    /**
     * @var array
     */
    protected $minLength = array(
        'email'				=> 8,
        'name'				=> 3,
        'phone'				=> 8,
        'link'				=> -1,
        'charityLink'		=> -1,
        'youtubeLink'		=> -1,
    );

	/**
	 * Check if $value is valid
	 *
	 * @param \JVelletti\JvEvents\Domain\Model\Organizer $organizer
	 * @return void
	 */
	public function isValid(mixed $organizer):void
    {

        $isValid = true ;
        $isValid = $this->securityChecks( $organizer->getEmail() , 'email' , $isValid ) ;
        $isValid = $this->securityChecks( $organizer->getName() , 'name' , $isValid ) ;
        $isValid = $this->securityChecks( $organizer->getPhone() , 'phone' , $isValid ) ;
        $isValid = $this->securityChecks( $organizer->getLink() , 'link' , $isValid ) ;
        $isValid = $this->securityChecks( $organizer->getCharityLink() , 'charityLink' , $isValid ) ;
        $isValid = $this->securityChecks( $organizer->getYoutubeLink() , 'youtubeLink' , $isValid ) ;

        $isValid = $this->emailIsValid( trim($organizer->getEmail()) , 'email' , false , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['name'] , $this->maxLength['name'] , $organizer->getName() , 'name' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['phone'] , $this->maxLength['phone'] , $organizer->getPhone() , 'phone' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['link'] , $this->maxLength['link'] , $organizer->getLink() , 'link' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['charityLink'] , $this->maxLength['charityLink'] , $organizer->getCharityLink() , 'charityLink' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['youtubeLink'] , $this->maxLength['youtubeLink'] , $organizer->getYoutubeLink() , 'youtubeLink' , NULL , $isValid ) ;

        if( $organizer->getLink() ) {
            $isValid = $this->urlIsValid( trim($organizer->getLink()) , 'link' , NULL , $isValid ) ;
        }
        if( $organizer->getCharityLink() ) {
            $isValid = $this->urlIsValid( trim($organizer->getCharityLink()) , 'charityLink' , NULL , $isValid ) ;
        }
        if( $organizer->getYoutubeLink() ) {
            $isValid = $this->urlIsValid( trim($organizer->getYoutubeLink()) , 'youtubeLink' , NULL , $isValid ) ;
            $isValid = $this->youtubeIsValid( trim($organizer->getYoutubeLink()) , 'youtubeLink' , NULL , $isValid ) ;
        }
        $isValid = $this->isHasUnwantedHtmlCodeValue( $organizer->getDescription() , 'description' , $isValid ) ;

	}


}
