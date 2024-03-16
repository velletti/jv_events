<?php
namespace JVelletti\JvEvents\Validation\Validator;

class EventValidator extends BaseValidator {

	/**
	 * @var array
	 */
	protected $maxLength = array(
		'name'				=> 60,
		'link'				=> 80,
		'teaser'			=> 200,
		'introtextRegistrant'			=> 1000,
		'introtextRegistrantConfirmed'			=> 1000,
		'youtubeLink'			=> 120,
		'registrationUrl'		=> 120,
	);

    /**
     * @var array
     */
    protected $minLength = array(
        'introtextRegistrant'			=> 0,
        'introtextRegistrantConfirmed'			=> 0,
        'teaser'			=> 5,
        'name'				=> 3,
        'link'				=> -1,
        'youtubeLink'				=> -1,
        'registrationUrl'				=> -1,
    );

	/**
	 * Check if $value is valid
	 *
	 * @param \JVelletti\JvEvents\Domain\Model\Event $event
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
	 * @return void
	 */
	public function isValid(mixed $event):void
    {
        $isValid = true ;

        $isValid = $this->securityChecks( $event->getName() , 'name' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getTeaser() , 'teaser' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getIntrotextRegistrant() , 'introtextRegistrant' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getIntrotextRegistrantConfirmed() , 'introtextRegistrantConfirmed' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getStartDateFE() , 'startDateFE' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getEndDateFE() , 'endDateFE' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getStartTimeFE() , 'startTimeFE' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getEndTimeFE() , 'endTimeFE' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getEntryTimeFE() , 'entryTimeFE' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getPrice() , 'price' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getPriceReduced() , 'priceReduced' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getYoutubeLink() , 'youtubeLink' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getRegistrationUrl() , 'registrationUrl' , $isValid ) ;
        $isValid = $this->securityChecks( $event->getPriceReducedText() , 'priceReducedText' , $isValid ) ;

        $isValid = $this->isNumeric( $event->getEventCategory() , 'eventCategory' , $isValid , "No Category selected") ;


        $eventArray = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST("tx_jvevents_events") ;
        $eventCatUid = intval($eventArray['event']['eventCategory']) ;
        $isValid = $this->isNumeric( $eventCatUid , 'eventCategory' , $isValid , "No Category selected") ;

        if( intval( $event->getPrice()) != 0  ) {
            $isValid = $this->isNumeric( $event->getPrice() , 'price' , $isValid ) ;
        }

        if ( $event->getPriceReduced() > 0 ) {
            $isValid = $this->stringLengthIsValid(3 , 200 , $event->getPriceReducedText() , 'priceReducedText' , NULL , $isValid ) ;
        }


        $isValid = $this->isTagArray(  $event->getTagsFE() , 'tagsFE' , $isValid ) ;
        $isValid = $this->isStringDateValue( $event->getStartDateFE() , 'startDateFE' , $isValid ) ;
        if ( $event->getAllDay() ) {
            $isValid = $this->isStringDateValue( $event->getEndDateFE() , 'endDateFE' , $isValid ) ;
        } else {
            $isValid = $this->isStringTimeValue( $event->getStartTimeFE() , 'startTimeFE' , $isValid ) ;
            if ( trim( strlen($event->getEndTimeFE()) > 0 )) {
                $isValid = $this->isStringTimeValue( $event->getEndTimeFE() , 'endTimeFE' , $isValid ) ;
            }
            if ( trim( strlen($event->getEntryTimeFE()) > 0 )) {
                $isValid = $this->isStringTimeValue($event->getEntryTimeFE(), 'entryTimeFE', $isValid);
            }
        }


        $isValid = $this->stringLengthIsValid($this->minLength['name'] , $this->maxLength['name'] , $event->getName() , 'name' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['teaser'] , $this->maxLength['teaser'] , $event->getTeaser() , 'teaser' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['introtextRegistrant'] , $this->maxLength['introtextRegistrant'] , $event->getIntrotextRegistrant() , 'introtextRegistrant' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['introtextRegistrantConfirmed'] , $this->maxLength['introtextRegistrantConfirmed'] , $event->getIntrotextRegistrantConfirmed() , 'introtextRegistrantConfirmed' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['youtubeLink'] , $this->maxLength['youtubeLink'] , $event->getYoutubeLink() , 'youtubeLink' , NULL , $isValid ) ;
        $isValid = $this->stringLengthIsValid($this->minLength['registrationUrl'] , $this->maxLength['registrationUrl'] , $event->getRegistrationUrl() , 'registrationUrl' , NULL , $isValid ) ;

        if( $event->getRegistrationUrl() ) {
            $isValid = $this->urlIsValid( trim($event->getRegistrationUrl()) , 'registrationUrl' , NULL , $isValid ) ;
        }
        if( $event->getYoutubeLink() ) {
            $isValid = $this->urlIsValid( trim($event->getYoutubeLink()) , 'youtubeLink' , NULL , $isValid ) ;
            $isValid = $this->youtubeIsValid( trim($event->getYoutubeLink()) , 'youtubeLink' , NULL , $isValid ) ;
        }

        $isValid = $this->isHasUnwantedHtmlCodeValue( $event->getDescription() , 'description' , $isValid ) ;

	}


}
