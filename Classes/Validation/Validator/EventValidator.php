<?php
namespace JVE\JvEvents\Validation\Validator;

class EventValidator extends BaseValidator {

	/**
	 * @var array
	 */
	protected $maxLength = array(
		'name'				=> 30,
		'link'				=> 80,
	);

    /**
     * @var array
     */
    protected $minLength = array(
        'name'				=> 3,
        'link'				=> -1,
    );

	/**
	 * Check if $value is valid
	 *
	 * @param \JVE\JvEvents\Domain\Model\Event $event
     * @ignorevalidation $event
	 * @return boolean
	 */
	public function isValid($event) {
        $isValid = true ;

        $isValid = $this->securityChecks( $event->getName() , 'name' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getTeaser() , 'teaser' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getStartDateFE() , 'startDateV' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getStartTimeFE() , 'startTimeV' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getEndTimeFE() , 'endTimeV' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getPrice() , 'price' , $isValid ) ;

        $isValid = $this->isNumeric( $event->getEventCategory() , 'eventCategory' , $isValid ) ;

        $isValid = $this->isTagArray(  $event->getTagsFE() , 'tagsFE' , $isValid ) ;

        $isValid = $this->isStringDateValue( $event->getStartDateFE() , 'startDateV' , $isValid ) ;
        $isValid = $this->isStringTimeValue( $event->getStartTimeFE() , 'startTimeV' , $isValid ) ;
        $isValid = $this->isStringTimeValue( $event->getEndTimeFE() , 'endTimeV' , $isValid ) ;

        $isValid = $this->stringLengthIsValid($this->minLength['name'] , $this->maxLength['name'] , $event->getName() , 'name' , NULL , $isValid ) ;

        $isValid = $this->isHasUnwantedHtmlCodeValue( $event->getDescription() , 'description' , $isValid ) ;



		return $isValid;

	}


}
