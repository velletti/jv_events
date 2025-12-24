<?php
namespace JVelletti\JvEvents\Validation\Validator;

class MediaValidator extends BaseValidator {

    /**
     * @var array
     */
    protected $minLength = array(
        'link'				=> 0,

        'name'				=> 3,
        'teaserText'	    => 0,
    );

    /**
	 * @var array
	 */
	protected $maxLength = array(
        'link'				=> 80,

		'name'				=> 60,
		'teaserText'	    => 255,
	);

	/**
	 * Check if $value is valid
	 *
	 * @param \JVelletti\JvEvents\Domain\Model\Location $location
	 * @return void
	 */
	public function isValid(mixed $media): void
    {

        $isValid = true ;

        $isValid = $this->securityChecks( $media->getName() , 'name' , $isValid ) ;

        $isValid = $this->securityChecks( $media->getTeaserText() , 'teaserText' , $isValid ) ;

        $isValid = $this->securityChecks( $media->getLink() , 'link' , $isValid ) ;
        if( $media->getLink() && $media->getLink()  != "-") {
            $isValid = $this->urlIsValid( trim($media->getLink()) , 'link' , NULL , $isValid ) ;
        }

        $isValid = $this->securityChecks( $media->getReleaseDateFE() , 'releaseDateFE' , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['name'] , $this->maxLength['name'] , $media->getName() , 'name' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['teaserText'] , $this->maxLength['teaserText'] , $media->getTeaserText() , 'teaserText' , NULL , $isValid ) ;
        $isValid = $this->isHasUnwantedHtmlCodeValue( $media->getDescription() , 'description' , $isValid ) ;

	}


}
