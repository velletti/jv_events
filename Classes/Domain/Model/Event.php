<?php
namespace JVE\JvEvents\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
use \TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Jörg velletti <jVelletti@allplan.com>, Allplan GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Single Event
 */
class Event extends AbstractEntity
{

    /**
     * Select one of the possabilities: link to an internal or external page or
     * default: show event details from this extension
     *
     * @var int
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $eventType = 0;


    /**
     * hidden or not that is the question
     *
     * @var int
     */
    protected $hidden ;

    /**
     * last modification
     *
     * @var int|null
     */
    protected $tstamp ;


    /**
     * last modification in Frontend
     *
     * @var int|null
     */
    protected $lastUpdated ;

    /**
     * last modification in Frontend by feuser ID
     *
     * @var int|null
     */
    protected $lastUpdatedBy ;


    /**
     * hidden or not that is the question
     *
     * @var int
     */
    protected $changeFutureEvents ;

    /**
     * event Viewed in single View (ajax Call requiered)
     *
     * @var int
     */
    protected $viewed = 0;


    /**
     * event master ID : if event is copied, the copies wil get here the ID of the copy master. Further changes can be copied from all entries with same  master id
     *
     * @var int
     */
    protected $masterId = 0;



    /**

     * @var int
     */
    protected $sysLanguageUid ;


    /**
     * default: add allways 1 even if this object does not have a subevent
     *
     * @var int
     */
    protected $subeventCount = 1;


    /**
     * Short Title of this event. Used in listings
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $name = '';

    /**
     * Short Botton to overwrite Button "Termin Details >" for single Event
     *
     * @var string
     */
    protected $eventButtonText = '';


    /**
     * generated from Short Title and startdate of this event. Used as URL
     *
     * @var string
     */
    protected $slug = '';

    /**
     * Short description of this event. Used in listings
     *
     * @var string
     */
    protected $teaser = '';

    /**
     * int Price only inclusive VAT
     *
     * @var double
     */
    protected $price = 0 ;

    /**
     * currency ( $ € , CHF or Pound ) see tca )
     *
     * @var string
     */
    protected $currency  ;

    /**
     * int PriceReduced only in EURO
     *
     * @var double
     */
    protected $priceReduced = 0 ;

    /**
     * int PriceReduced only in EURO
     *
     * @var string
     */
    protected $priceReducedText = '' ;

    /**
     * Full decription of this event. May be formated. Only visible in Detail event
     * View.
     *
     * @var string
     */
    protected $description = '';
    
    /**
     * An image shown together with the event. The first image may be used for listings
     * as teaser Image
     *
     * @var ObjectStorage<FileReference>
     */
    protected $images = null;

	/**
  * Files that may be useful for this event
  *
  * @var FileReference
  */
 protected $teaserImage = null;


	/**
     * Files that may be useful for this event
     *
     * @var ObjectStorage<FileReference>
     */
    protected $files = null;

    /**
     * Files that may be useful for this event
     *
     * @var ObjectStorage<FileReference>
     */
    protected $filesAfterReg = null;

    /**
     * Files that may be useful for this event
     *
     * @var ObjectStorage<FileReference>
     */
    protected $filesAfterEvent = null;

    
    /**
     * Is this an event with out start / Endtime?
     *
     * @var bool
     */
    protected $allDay = false;

    /**
     * creation Date as timestring
     *
     * @var int
     */
    protected $crdate ;


    /**
     * Start Date of this event. Mandatory
     *
     * @var \DateTime
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $startDate = null;

    /**
     * Start Date of this event. replacement for startDate but FE = for frontend Editing
     *
    * @var string
     */
    protected $startDateFE = null;

    /**
     * Start Time of this event. replacement for startTime but FE = for frontend Editing
     *
     * @var string
     */
    protected $startTimeFE = null;


    /**
     * End Time of this event. replacement for endTime but FE = for frontend Editing
     *
     * @var string
     */
    protected $endTimeFE = null;

    /**
     * Start Time of this event
     *
     * @var int
     */
    protected $entryTime = 0;

    /**
     * End Time of this event. replacement for endTime but FE = for frontend Editing
     *
     * @var string
     */
    protected $entryTimeFE = null;

    /**
     * Tag list , comma separated of this event. replacement for tags Object Storage  but FE = for frontend Editing
     *
     * @var string
     */
    protected $tagsFE = null;

    /**
     * Start Time of this event
     *
     * @var int
     */
    protected $startTime = 0;

    /**
     * mark this event as TOP Event (to be able to filter, place special CSS code etc.
     *
     * @var int
     */
    protected $topEvent = 0;

    /**
     *  this event is canceled bt should be still listed to show it in list as CANCELED
     *
     * @var int
     */
    protected $canceled = 0;

    /**
     * Access Start Time of this event (default TYPO3 Field ) -> this does not wrk in TYPO3 10:  int|\DateTime|NULL
     * @var \DateTime|null
     */
    protected $starttime ;

    /**
     * Access End Time of this event (default TYPO3 Field ) -> this does not wrk in TYPO3 10:  int|\DateTime|NULL
     * @var \DateTime|null
     */
    protected $endtime ;

    /**
     * End Date of this event.  var \DateTime|int does  not work in TYPO3 LTS 10
     * @var \DateTime|null

     */
    protected $endDate = null;

    /**
     * End Date of this event. replacement for startDate but FE = for frontend Editing
     *
     * @var string|null
     */
    protected $endDateFE = null;
    
    /**
     * End time of this event
     * @var int
     */
    protected $endTime = 0;
    
    /**
     * this event is visible for the following usergroups / Access Rights
     *
     * @var string
     */
    protected $access = '';
    
    /**
     * Should it be possible to register to this event
     *
     * @var bool
     */
    protected $withRegistration = false;
    
    /**
     * Regsitration is possible until
     *
     * @var \DateTime
     */
    protected $registrationUntil = null;

    /**
     * the Actual Time Needed for is registration Possible
     *
     * @var \DateTime
     */
    protected $actualTime = null;
    
    /**
     * Registration is visible for this usergroups / access rights
     *
     * @var string
     */
    protected $registrationAccess = '';
    
    /**
     * If you want to store registrants user data to Citrix GoTo Webinar, activate this
     * checkbox and enter the Citrix Uid of the Webinar
     *
     * @var bool
     */
    protected $storeInCitrix = false;


    /**
     * If you want to store registrants user data to Citrix GoTo Webinar, activate this
     * checkbox and enter the Citrix Uid of the Webinar
     *
     * @var bool
     */
    protected $storeInHubspot = false;

    /**
     * Autoamtic geneated Salesforce Campagne ID. If enableHubpot is set to 1 in extConf and store_in_hubspot is activated in Event, druing save event hook we create or update a campagign in salesforce
     *
     * @var string
     */
    protected $salesForceCampaignId= '';

    /**
     * enter the Citrix Uid of the Webinar
     *
     * @var string
     */
    protected $citrixUid = '';
    
    /**
     * Will generate a SalesForce Object "Webinar" with this
     *
     * @var bool
     */
    protected $storeInSalesForce = false;
    
    /**
     * If you want to start a marketingProcess (sending emails, reminder and so) after
     * a registration, enter the ID of the configured salesforce marketing Process
     *
     * @var string
     */
    protected $marketingProcessId = '';
    
    /**
     * Important: thsi should be normaly EMPTY. Only enter a diferent SalesForce Record
     * Type if you know what you are doing !
     *
     * @var string
     */
    protected $salesForceRecordType = '';
    
    /**
     * This ID is a read Only Information as it is generated automatically from
     * Salesforce
     *
     * @var string
     */
    protected $salesForceEventId = '';
    
    /**
     * This ID is a read Only Information as it is generated automatically from
     * Salesforce
     *
     * @var string
     */
    protected $salesForceSessionId = null;
    
    /**
     * Enter Number of maximum possible registrations. If you do not want more
     * registrations and want to show, that this event is full: enter any negative
     * Number
	 * If there are availableWaitingSeats, this will be used
     *
     * @var int
     */
    protected $availableSeats = 0;

	/**
	 * Enter Number of maximum possible registrations of a Waiting List. If you do not want more
	 * registrations and want to show, that this event is full: enter any negative
	 * Number
	 * If there are no AvailableSeats, registration will be possible unless also this is set to 0

	 *
	 * @var int
	 */
	protected $availableWaitingSeats = 0;

	/**
     * Number of registrants. Automatically generated!
     *
     * @var int
     */
    protected $registeredSeats = 0;

	/**
     * If you use the option: user must confirm his registration via email OR the
     * organizer must confirm the registraion, this number shows the amount of wainting
     * registrants
     *
     * @var int
     */
    protected $unconfirmedSeats = null;
    
    /**
     * If you want to send an email to the organizer, activate this option
     *
     * @var bool
     */
    protected $notifyOrganizer = false;
    
    /**
     * if you want to notify the registratant, activate this Option
     *
     * @var bool
     */
    protected $notifyRegistrant = false;
    
    /**
     * subjectOrganizer
     *
     * @var string
     */
    protected $subjectOrganizer = '';

	/**
	 * registrationUrl
	 *
	 * @var string
	 */
	protected $registrationUrl = '';

    /**
     * Link to a ypu Tube Video
     * details
     *
     * @var string|null
     */
    protected $youtubeLink = '';


    /**
     * This event is an exception for an recurring event.
     *
     * @var bool
     */
    protected $isRegistrationPossible = false ;

    /**
     * This event is an exception for an recurring event.
     *
     * @var bool
     */
    protected $isNoFreeSeats = false ;




    /**
	 * registrationPid
	 *
	 * @var integer
	 */
	protected $registrationPid = 0 ;

	/**
	 * registrationFormPid
	 *
	 * @var integer
	 */
	protected $registrationFormPid = 0 ;

    /**
     * registrationGender
     *
     * @var integer
     */
    protected $registrationGender= 0 ;

    /**
     * registrationFormPid
     *
     * @var integer
     */
    protected $registrationShowStatus = 0 ;

	/**
     * Email texst send to the organizer
     *
     * @var string
     */
    protected $textOrganizer = '';
    
    /**
     * Email subject that is sent to the registratrant. You can use Placeholders like
     * ###EVENT_NAME### ###SITENAME###
     *
     * @var string
     */
    protected $subjectRegistrant = '';
    
    /**
     * Text of the Email
     * you can use placeholder like ###EVENT_NAME### or ###SITENAME###
     *
     * @var string
     */
    protected $textRegistrant = '';

    /**
     * IntroText of the Email
     *
     *
     * @var string
     */
    protected $introtextRegistrant = '';

    /**
     * IntroText of the Email
     *
     *
     * @var string
     */
    protected $introtextRegistrantConfirmed = '';
    
    /**
     * If the user needs to confirm the registration, activate this option
     *
     * @var bool
     */
    protected $needToConfirm = false;
    
    /**
     * Use this checkbox, if this is a frequently event.
     *
     * @var bool
     */
    protected $isRecurring = false;
    
    /**
     * select Kind of Frequency: Daily, weekly, monthly, yearly
     *
     * @var int
     */
    protected $frequency = 0;
    
    /**
     * Fe. If Weekly, select if every thursday or only every second week.
     *
     * @var int
     */
    protected $freqException = 0;
    
    /**
     * This event is an exception for an recurring event.
     *
     * @var int
     */
    protected $isExceptionFor = 0;
    
    /**
     * organizer
     *
     * @var Organizer
     */
    protected $organizer = null;


    /**
     * Subevent (additional dates )
     *
     * @var ObjectStorage<Subevent>
     */
    #[Cascade(['value' => 'remove'])]
    protected $subevent = null;

    /**
     * location
     *
     * @var Location
     */
    protected $location = null;
    
       /**
     * eventCategory
     *
     * @var ObjectStorage<Category>
     */
    protected $eventCategory = null;
    
    /**
     * tags
     *
     * @var ObjectStorage<Tag>
     */
    protected $tags = null;

	/**
	 * @var string
	 */
	protected $internalurl;

	/**
	 * @var string
	 */
	protected $externalurl;

    /**
     * @var string
     */
    protected $url;


    /**
	 * __construct
	 */
	public function __construct()
	{
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
	}


	/**
     * Returns the name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Returns the organizer
     *
     * @return Organizer $organizer
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }
    
    /**
     * Sets the organizer
     *
     * @param Organizer $organizer
     * @return void
     */
    public function setOrganizer(Organizer $organizer)
    {
        $this->organizer = $organizer;
    }
    
    /**
     * Returns the location
     *
     * @return Location $location
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    /**
     * Sets the location
     *
     * @param Location $location
     * @return void
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;
    }
    
    /**
     * Returns the eventType
     *
     * @return int $eventType
     */
    public function getEventType()
    {
        return $this->eventType;
    }
    
    /**
     * Sets the eventType
     *
     * @param int $eventType
     * @return void
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @return string
     */
    public function getEventButtonText(): string
    {
        return $this->eventButtonText;
    }

    /**
     * @param string $eventButtonText
     */
    public function setEventButtonText(string $eventButtonText): void
    {
        $this->eventButtonText = $eventButtonText;
    }

    /**
     * Returns the teaser
     *
     * @return string $teaser
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * Sets the teaser
     *
     * @param string $teaser
     * @return void
     */
    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;
    }
    
    /**
     * Returns the description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Sets the description
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /**
     * Returns the images
     *
     * @return ObjectStorage<FileReference> $images
     */
    public function getImages()
    {
        return $this->images;
    }
    
    /**
     * Sets the images
     *
     * @param FileReference $images
     * @return void
     */
    public function setImages(FileReference $images)
    {
        $this->images = $images;
    }

	/**
  * Returns the teaserImage
  *
  * @return FileReference $teaserImage
  */
 public function getTeaserImage()
	{
		return $this->teaserImage;
	}

	/**
  * Sets the teaserImage
  *
  * @param FileReference $teaserImage
  * @return void
  */
 public function setTeaserImage(FileReference $teaserImage)
	{
		$this->teaserImage = $teaserImage;
	}

	/**
     * Returns the files
     *
     * @return ObjectStorage<FileReference> $files
     */
    public function getFiles()
    {
        return $this->files;
    }



    /**
     * Sets the files
     *
     * @param FileReference $files
     * @return void
     */
    public function setFiles(FileReference $files)
    {
        $this->files = $files;
    }



    /**
     * Returns the filesAfterReg
     *
     * @return ObjectStorage<FileReference> $files
     */
    public function getFilesAfterReg()
    {
        return $this->filesAfterReg;
    }



    /**
     * Sets the files
     *
     * @param FileReference $files
     * @return void
     */
    public function setFilesAfterReg(FileReference $files)
    {
        $this->filesAfterReg = $files;
    }

    /**
     * Returns the filesAfterEvent
     *
     * @return ObjectStorage<FileReference> $files
     */
    public function getFilesAfterEvent()
    {
        return $this->filesAfterEvent;
    }



    /**
     * Sets the filesAfterEvent
     *
     * @param FileReference $files
     * @return void
     */
    public function setFilesAfter(FileReference $files)
    {
        $this->filesAfterEvent = $files;
    }



	/**
     * Returns the startDate
     *
     * @return \DateTime startDate
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
    
    /**
     * Sets the startDate
     *
     * @param \DateTime $startDate
     * @return void
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
    }
    
    /**
     * Returns the allDay
     *
     * @return bool $allDay
     */
    public function getAllDay()
    {
        return $this->allDay;
    }
    
    /**
     * Sets the allDay
     *
     * @param bool $allDay
     * @return void
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;
    }
    
    /**
     * Returns the boolean state of allDay
     *
     * @return bool
     */
    public function isAllDay()
    {
        return $this->allDay;
    }
    
    /**
     * Returns the startTime
     *
     * @return int
     */
    public function getStartTime(): int
    {
        return $this->startTime;
    }
    
    /**
     * Sets the startTime
     *
     * @param int $startTime
     * @return void
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    public function getStartUTCDateTime() {

        return  $this->startDate->format("Ymd") . "T" . date("His" , $this->startTime );

    }

    public function getEndUTCDateTime() {
        if( $this->endDate ) {
            return  $this->endDate->format("Ymd") . "T" . date("His" , $this->endTime );
        } else {
            if( $this->endTime ) {
                if( $this->endTime > $this->startTime ) {
                    return  $this->startDate->format("Ymd") . "T" . date("His" , $this->endTime );
                }
                return  $this->startDate->modify("+1 day")->format("Ymd") . "T" . date("His" , $this->endTime );
            }
            return $this->getStartUTCDateTime() ;
        }



    }
    
    /**
     * Returns the endDate
     *
     * @return \DateTime $endDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
    
    /**
     * Sets the endDate
     *
     * @param \DateTime|null $endDate
     * @return void
     */
    public function setEndDate(?\DateTime $endDate)
    {
        $this->endDate = $endDate;
    }
    
    /**
     * Returns the endTime
     *
     * @return int $endTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Returns the Access endtime
     *
     * @return \DateTime
     */
    public function getAccessEndtime()
    {
        return $this->endtime;
    }

    /**
     * Returns the Access starttime
     *
     * @return \DateTime
     */

    public function getAccessStarttime()
    {
        return $this->starttime;
    }

    /**
     * sets the Access endtime
     *
     * @param \DateTime $starttime
     * @return void
     */

    public function setAccessStarttime($starttime): \DateTime
    {
        $this->starttime = $starttime;
    }

    /**
     * sets the Access endtime
     *
     * @param \DateTime $endtime
     * @return void
     */
    public function setAccessEndtime($endtime)
    {
        $this->endtime = $endtime;
    }
    /**
     * Sets the endTime
     *
     * @param int $endTime
     * @return void
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }
    
    /**
     * Returns the withRegistration
     *
     * @return bool
     */
    public function getWithRegistration()
    {
        return $this->withRegistration;
    }
    
    /**
     * Sets the withRegistration
     *
     * @param bool $withRegistration
     * @return void
     */
    public function setWithRegistration($withRegistration)
    {
        $this->withRegistration = $withRegistration;
    }
    
    /**
     * Returns the boolean state of withRegistration
     *
     * @return bool
     */
    public function isWithRegistration()
    {
        return $this->withRegistration;
    }

    /**
     * @return float|int
     */
    public function getRegistrationWidthConfirmed() {
        if ( $this->availableSeats > 0){
            return (  $this->registeredSeats  / $this->availableSeats)  * 100 ;
        }
        return 0 ;
    }

    /**
     * @return float|int
     */
    public function getRegistrationWidthConfirmedWithWaiting() {
        $totalSeats = $this->availableWaitingSeats + $this->availableSeats ;
        if ( $totalSeats > 0){
            return (  $this->registeredSeats  / $totalSeats)  * 100 ;
        }
        return 0 ;
    }

    /**
     * @return float|int
     */
    public function getRegistrationWidthWaiting() {
        $totalSeats = $this->availableWaitingSeats + $this->availableSeats ;
        if ( $totalSeats > 0){
            return (  $this->unconfirmedSeats  / $totalSeats)  * 100 ;
        }
        return 0 ;
    }
    
    /**
     * Returns the boolean state of storeInCitrix
     *
     * @return bool
     */
    public function isStoreInCitrix(): bool
    {
        return $this->storeInCitrix;
    }
    
    /**
     * Returns the citrixUid
     *
     * @return string $citrixUid
     */
    public function getCitrixUid()
    {
        return $this->citrixUid;
    }
    
    /**
     * Sets the citrixUid
     *
     * @param string $citrixUid
     * @return void
     */
    public function setCitrixUid($citrixUid)
    {
        $this->citrixUid = $citrixUid;
    }
    
    /**
     * Returns the storeInSalesForce
     *
     * @return bool $storeInSalesForce
     */
    public function getStoreInSalesForce()
    {
        return $this->storeInSalesForce;
    }
    
    /**
     * Sets the storeInSalesForce
     *
     * @param bool $storeInSalesForce
     * @return void
     */
    public function setStoreInSalesForce($storeInSalesForce)
    {
        $this->storeInSalesForce = $storeInSalesForce;
    }
    
    /**
     * Returns the boolean state of storeInSalesForce
     *
     * @return bool
     */
    public function isStoreInSalesForce()
    {
        return $this->storeInSalesForce;
    }
    
    /**
     * Returns the salesForceRecordType
     *
     * @return string $salesForceRecordType
     */
    public function getSalesForceRecordType()
    {
        return $this->salesForceRecordType;
    }
    
    /**
     * Sets the salesForceRecordType
     *
     * @param string $salesForceRecordType
     * @return void
     */
    public function setSalesForceRecordType($salesForceRecordType)
    {
        $this->salesForceRecordType = $salesForceRecordType;
    }
    
    /**
     * Returns the salesForceEventId
     *
     * @return string $salesForceEventId
     */
    public function getSalesForceEventId()
    {
        return $this->salesForceEventId;
    }
    
    /**
     * Sets the salesForceEventId
     *
     * @param string $salesForceEventId
     * @return void
     */
    public function setSalesForceEventId($salesForceEventId)
    {
        $this->salesForceEventId = $salesForceEventId;
    }
    
    /**
     * Returns the salesForceSessionId
     *
     * @return String $salesForceSessionId
     */
    public function getSalesForceSessionId()
    {
        return $this->salesForceSessionId;
    }
    
    /**
     * Sets the salesForceSessionId
     *
     * @param string $salesForceSessionId
     * @return void
     */
    public function setSalesForceSessionId( $salesForceSessionId)
    {
        $this->salesForceSessionId = $salesForceSessionId;
    }
    
    /**
     * Returns the availableSeats
     *
     * @return int $availableSeats
     */
    public function getAvailableSeats()
    {
        return $this->availableSeats;
    }
    
    /**
     * Sets the availableSeats
     *
     * @param int $availableSeats
     * @return void
     */
    public function setAvailableSeats($availableSeats)
    {
        $this->availableSeats = $availableSeats;
    }

	/**
	 * @return int
	 */
	public function getAvailableWaitingSeats() {
		return $this->availableWaitingSeats;
	}

	/**
	 * @param int $availableWaitingSeats
	 */
	public function setAvailableWaitingSeats($availableWaitingSeats) {
		$this->availableWaitingSeats = $availableWaitingSeats;
	}



    /**
     * Returns the registeredSeats
     *
     * @return int $registeredSeats
     */
    public function getRegisteredSeats()
    {
        return $this->registeredSeats;
    }
    
    /**
     * Sets the registeredSeats
     *
     * @param int $registeredSeats
     * @return void
     */
    public function setRegisteredSeats($registeredSeats)
    {
        $this->registeredSeats = $registeredSeats;
    }
    
    /**
     * Returns the unconfirmedSeats
     *
     * @return int $unconfirmedSeats
     */
    public function getUnconfirmedSeats()
    {
        return $this->unconfirmedSeats;
    }
    
    /**
     * Sets the unconfirmedSeats
     *
     * @param int $unconfirmedSeats
     * @return void
     */
    public function setUnconfirmedSeats( $unconfirmedSeats)
    {
        $this->unconfirmedSeats = $unconfirmedSeats;
    }
    
    /**
     * Returns the notifyOrganizer
     *
     * @return bool $notifyOrganizer
     */
    public function getNotifyOrganizer()
    {
        return $this->notifyOrganizer;
    }
    
    /**
     * Sets the notifyOrganizer
     *
     * @param bool $notifyOrganizer
     * @return void
     */
    public function setNotifyOrganizer($notifyOrganizer)
    {
        $this->notifyOrganizer = $notifyOrganizer;
    }
    
    /**
     * Returns the boolean state of notifyOrganiser
     *
     * @return bool
     */
    public function isNotifyOrganizer()
    {
        return $this->notifyOrganizer;
    }
    
    /**
     * Returns the notifyRegistrant
     *
     * @return bool $notifyRegistrant
     */
    public function getNotifyRegistrant()
    {
        return $this->notifyRegistrant;
    }
    
    /**
     * Sets the notifyRegistrant
     *
     * @param bool $notifyRegistrant
     * @return void
     */
    public function setNotifyRegistrant($notifyRegistrant)
    {
        $this->notifyRegistrant = $notifyRegistrant;
    }
    
    /**
     * Returns the boolean state of notifyRegistrant
     *
     * @return bool
     */
    public function isNotifyRegistrant()
    {
        return $this->notifyRegistrant;
    }
    
    /**
     * Returns the subjectOrganizer
     *
     * @return string $subjectOrganizer
     */
    public function getSubjectOrganizer()
    {
        return $this->subjectOrganizer;
    }
    
    /**
     * Sets the subjectOrganizer
     *
     * @param string $subjectOrganizer
     * @return void
     */
    public function setSubjectOrganizer($subjectOrganizer)
    {
        $this->subjectOrganizer = $subjectOrganizer;
    }
    
    /**
     * Returns the textOrganizer
     *
     * @return string $textOrganizer
     */
    public function getTextOrganizer()
    {
        return $this->textOrganizer;
    }
    
    /**
     * Sets the textOrganizer
     *
     * @param string $textOrganizer
     * @return void
     */
    public function setTextOrganizer($textOrganizer)
    {
        $this->textOrganizer = $textOrganizer;
    }
    
    /**
     * Returns the subjectRegistrant
     *
     * @return string $subjectRegistrant
     */
    public function getSubjectRegistrant()
    {
        return $this->subjectRegistrant;
    }
    
    /**
     * Sets the subjectRegistrant
     *
     * @param string $subjectRegistrant
     * @return void
     */
    public function setSubjectRegistrant($subjectRegistrant)
    {
        $this->subjectRegistrant = $subjectRegistrant;
    }

    /**
     * @return string
     */
    public function getIntrotextRegistrant()
    {
        return $this->introtextRegistrant;
    }

    /**
     * @param string $introtextRegistrant
     */
    public function setIntrotextRegistrant($introtextRegistrant)
    {
        $this->introtextRegistrant = $introtextRegistrant;
    }

    /**
     * @return string
     */
    public function getIntrotextRegistrantConfirmed()
    {
        return $this->introtextRegistrantConfirmed;
    }

    /**
     * @param string $introtextRegistrantConfirmed
     */
    public function setIntrotextRegistrantConfirmed($introtextRegistrantConfirmed)
    {
        $this->introtextRegistrantConfirmed = $introtextRegistrantConfirmed;
    }





    /**
     * Returns the storeInCitrix
     *
     * @return bool storeInCitrix
     */
    public function getStoreInCitrix()
    {
        return $this->storeInCitrix;
    }
    
    /**
     * Sets the storeInCitrix
     *
     * @param bool $storeInCitrix
     * @return void
     */
    public function setStoreInCitrix($storeInCitrix)
    {
        $this->storeInCitrix = $storeInCitrix;
    }
    
    /**
     * Returns the textRegistrant
     *
     * @return string textRegistrant
     */
    public function getTextRegistrant()
    {
        return $this->textRegistrant;
    }
    
    /**
     * Sets the textRegistrant
     *
     * @param string $textRegistrant
     * @return void
     */
    public function setTextRegistrant($textRegistrant)
    {
        $this->textRegistrant = $textRegistrant;
    }
    
    /**
     * Returns the registrationUntil
     *
     * @return \DateTime $registrationUntil
     */
    public function getRegistrationUntil()
    {
        return $this->registrationUntil;
    }
    
    /**
     * Sets the registrationUntil
     *
     * @param \DateTime $registrationUntil
     * @return void
     */
    public function setRegistrationUntil(\DateTime $registrationUntil)
    {
        $this->registrationUntil = $registrationUntil;
    }

    /**
     * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->registrant = new ObjectStorage();
        $this->eventCategory = new ObjectStorage();
        $this->tags = new ObjectStorage();

		$this->files = new ObjectStorage();
		$this->images = new ObjectStorage();
		$this->teaserImage = new ObjectStorage();
		$this->subevent = new ObjectStorage();
    }
    

    /**
     * Returns the access
     *
     * @return string $access
     */
    public function getAccess()
    {
        return $this->access;
    }
    
    /**
     * Sets the access
     *
     * @param string $access
     * @return void
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }
    
    /**
     * Returns the registrationAccess
     *
     * @return string $registrationAccess
     */
    public function getRegistrationAccess()
    {
        return $this->registrationAccess;
    }
    
    /**
     * Sets the registrationAccess
     *
     * @param string $registrationAccess
     * @return void
     */
    public function setRegistrationAccess($registrationAccess)
    {
        $this->registrationAccess = $registrationAccess;
    }
    
    /**
     * Returns the needToConfirm
     *
     * @return bool $needToConfirm
     */
    public function getNeedToConfirm()
    {
        return $this->needToConfirm;
    }
    
    /**
     * Sets the needToConfirm
     *
     * @param bool $needToConfirm
     * @return void
     */
    public function setNeedToConfirm($needToConfirm)
    {
        $this->needToConfirm = $needToConfirm;
    }
    
    /**
     * Returns the boolean state of needToConfirm
     *
     * @return bool
     */
    public function isNeedToConfirm()
    {
        return $this->needToConfirm;
    }
    
    /**
     * Returns the marketingProcessId
     *
     * @return string $marketingProcessId
     */
    public function getMarketingProcessId()
    {
        return $this->marketingProcessId;
    }
    
    /**
     * Sets the marketingProcessId
     *
     * @param string $marketingProcessId
     * @return void
     */
    public function setMarketingProcessId($marketingProcessId)
    {
        $this->marketingProcessId = $marketingProcessId;
    }
    
    /**
     * Returns the isRecurring
     *
     * @return bool $isRecurring
     */
    public function getIsRecurring()
    {
        return $this->isRecurring;
    }
    
    /**
     * Sets the isRecurring
     *
     * @param bool $isRecurring
     * @return void
     */
    public function setIsRecurring($isRecurring)
    {
        $this->isRecurring = $isRecurring;
    }
    
    /**
     * Returns the boolean state of isRecurring
     *
     * @return bool
     */
    public function isIsRecurring()
    {
        return $this->isRecurring;
    }
    
    /**
     * Returns the frequency
     *
     * @return int $frequency
     */
    public function getFrequency()
    {
        return $this->frequency;
    }
    
    /**
     * Sets the frequency
     *
     * @param int $frequency
     * @return void
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }
    
    /**
     * Returns the freqException
     *
     * @return int $freqException
     */
    public function getFreqException()
    {
        return $this->freqException;
    }
    
    /**
     * Sets the freqException
     *
     * @param int $freqException
     * @return void
     */
    public function setFreqException($freqException)
    {
        $this->freqException = $freqException;
    }
    
    /**
     * Returns the isExceptionFor
     *
     * @return int $isExceptionFor
     */
    public function getIsExceptionFor()
    {
        return $this->isExceptionFor;
    }
    
    /**
     * Sets the isExceptionFor
     *
     * @param int $isExceptionFor
     * @return void
     */
    public function setIsExceptionFor($isExceptionFor)
    {
        $this->isExceptionFor = $isExceptionFor;
    }
    
    /**
     * Adds a Category
     *
     * @param Category $eventCategory
     * @return void
     */
    public function addEventCategory(Category $eventCategory)
    {
        $this->eventCategory->attach($eventCategory);
    }
    
    /**
     * Removes a Category
     *
     * @param Category $eventCategoryToRemove The Category to be removed
     * @return void
     */
    public function removeEventCategory(Category $eventCategoryToRemove)
    {
        $this->eventCategory->detach($eventCategoryToRemove);
    }
    
    /**
     * Returns the eventCategory
     *
     * @return ObjectStorage<Category> $eventCategory
     */
    public function getEventCategory()
    {
        return $this->eventCategory;
    }
    
    /**
     * Sets the eventCategory
     *
     * @param ObjectStorage<Category> $eventCategory
     * @return void
     */
    public function setEventCategory(ObjectStorage $eventCategory)
    {
        $this->eventCategory = $eventCategory;
    }
    
    /**
     * Adds a Tag
     *
     * @param Tag $tag
     * @return void
     */
    public function addTag(Tag $tag)
    {
        $this->tags->attach($tag);
    }



    /**
     * Removes a FileReference
     *
     * @param FileReference $teaserImage
     * @return void
     */
    public function removeTeaserImage()
    {
        $this->teaserImage = null;
    }


    /**
     * Removes a Tag
     *
     * @param Tag $tagToRemove The Tag to be removed
     * @return void
     */
    public function removeTag(Tag $tagToRemove)
    {
        $this->tags->detach($tagToRemove);
    }
    
    /**
     * Returns the tags
     *
     * @return ObjectStorage<Tag> $tags
     */
    public function getTags()
    {
        return $this->tags;
    }
    
    /**
     * Sets the tags
     *
     * @param ObjectStorage<Tag> $tags
     * @return void
     */
    public function setTags(ObjectStorage $tags)
    {
        $this->tags = $tags;
    }

	/**
	 * Get internal url
	 *
	 * @return string
	 */
	public function getInternalurl()
	{
		return $this->internalurl;
	}

	/**
	 * Set internal url
	 *
	 * @param string $internalUrl internal url
	 * @return void
	 */
	public function setInternalurl($internalUrl)
	{
		$this->internalurl = $internalUrl;
	}

	/**
	 * Get external url
	 *
	 * @return string
	 */
	public function getExternalurl()
	{
		return $this->externalurl;
	}

	/**
	 * Set external url
	 *
	 * @param string $externalUrl external url
	 * @return void
	 */
	public function setExternalurl($externalUrl)
	{
		$this->externalurl = $externalUrl;
	}

	/**
	 * @return string
	 */
	public function getRegistrationUrl() {
		return $this->registrationUrl;
	}

	/**
	 * @param string $registrationUrl
	 */
	public function setRegistrationUrl($registrationUrl) {
		$this->registrationUrl = $registrationUrl;
	}

	/**
	 * @return int
	 */
	public function getRegistrationPid() {
		return $this->registrationPid;
	}

	/**
	 * @param int $registrationPid
	 */
	public function setRegistrationPid($registrationPid) {
		$this->registrationPid = $registrationPid;
	}

	/**
	 * @return int
	 */
	public function getRegistrationFormPid() {
		return $this->registrationFormPid;
	}

	/**
	 * @param int $registrationFormPid
	 */
	public function setRegistrationFormPid($registrationFormPid) {
		$this->registrationFormPid = $registrationFormPid;
	}

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getActualTime() {
        // Needs in Additoonal Configuration this setting to work
        // $GLOBALS['TYPO3_CONF_VARS']['SYS']['oriPhpTimeZone'] = @date_default_timezone_get() ;
        // important if you set $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] NOT TO UTC and in php.ini ist NOT set to UTC
        // if both is set to UTC, every thing is fine.. but this will cause other problems :-)
        //  REASON: in some cases TYPO3 uses date_default_timezone_get() .. in some cases it uses hardcoded UTC
        // and in some cases it reads value from $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone']

        $DTZ = $GLOBALS['TYPO3_CONF_VARS']['SYS']['oriPhpTimeZone'] == '' ? $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] : $GLOBALS['TYPO3_CONF_VARS']['SYS']['oriPhpTimeZone'] ;

        $DTZ = $DTZ == '' ? @date_default_timezone_get() : $DTZ ;
        $DTZ = $DTZ == '' ? 'UTC' : $DTZ ;

        return new \DateTime('now' , new \DateTimeZone($DTZ) ) ;
    }


    /** + +++ special Helper functions for the model  */

    /**
     * @return boolean
     */
    public function isIsRegistrationConfigured()
    {

        // first Text if internal or external registration is set.
        if ($this->withRegistration ) {
            // Internal Registration Process : check $this->availableSeats  configured and PID set

            if (($this->availableWaitingSeats  + $this->availableSeats)  > 0  && $this->registrationFormPid > 0 ) {

                return TRUE;
            }
        } else {
            if ( $this->registrationUrl ) {
                return TRUE  ;
            }
        }
        return FALSE ;
    }

    /**
     * @return boolean
     */
    public function isIsRegistrationPossible()
    {

        // first Text if internal or external registration is set.
        if ($this->withRegistration ) {
            // Internal Registration Process : check $this->availableSeats  Seats against Registered
            if (($this->registeredSeats + $this->unconfirmedSeats +1) > ($this->availableSeats + $this->availableWaitingSeats)  ) {
                //  echo "<br>Line: " . __LINE__ . " : " . " File: " . __FILE__ . '<br>$this->registeredSeats + $this->unconfirmedSeats +1) > ($this->availableSeats + $this->availableWaitingSeat <hr>';

                return FALSE;
            }
            if (($this->unconfirmedSeats + 1) > ($this->availableSeats) && ( $this->availableSeats > 0 )) {
                //  echo "<br>Line: " . __LINE__ . " : " . " File: " . __FILE__ . '<br>$this->unconfirmedSeats + 1) > ($this->availableSeats) && ( $this->availableSeats > 0  <hr>';

                return FALSE;
            }
        } else {
            if (! $this->registrationUrl ) {

                //  echo "<br>Line: " . __LINE__ . " : " . " File: " . __FILE__ . '<br>! $this->withRegistration and $this->registrationUrl ! set <hr>' ;

                  return false ;
            }
        }

        $now = $this->getActualTime() ;
        $nowDateString = $now->format("Y-m-d-H-i-s") ;
        if ( $this->registrationUntil ) {
            $regDateString = $this->registrationUntil->format("Y-m-d-H-i-s") ;
            if( $regDateString < $nowDateString && $this->registrationUntil->getTimestamp() > 1 ) {
                return false ;
            }
            if( $this->startDate < $now && $this->registrationUntil->getTimestamp() < 1 ) {
                return false ;
            }
        }

        if ( ! $this->mustLoginRights()  ) {
            return false ;
        }
        // access rights are NOT part of this check .. see mustLoginRights() and hasAccessRights() ..
        return true ;
    }

    /** + +++ special Helper functions for the model  */

    /**
     * @return boolean
     */
    public function isIsNoFreeSeats()
    {

        // only Check if is with internal registration and we have free seats
        if ($this->withRegistration ) {
            // Internal Registration Process : check $this->availableSeats  Seats against Registered
            if (($this->registeredSeats + $this->unconfirmedSeats +1) > ($this->availableSeats + $this->availableWaitingSeats)  ) {
                  // echo "<br>Line: " . __LINE__ . " : " . " File: " . __FILE__ . '<br>$this->registeredSeats + $this->unconfirmedSeats +1) > ($this->availableSeats + $this->availableWaitingSeat <hr>';
                 return TRUE;
            }
            if (($this->unconfirmedSeats + 1) > ($this->availableSeats) && ( $this->availableSeats > 0 )) {
                //  echo "<br>Line: " . __LINE__ . " : " . " File: " . __FILE__ . '<br>$this->unconfirmedSeats + 1) > ($this->availableSeats) && ( $this->availableSeats > 0  <hr>';
                return TRUE;
            }
        }
        // ok: we have free seats or it is eyternal registration so we do not need or can not show the No Free Seats warning
        return false ;
    }

    /**
     * @return boolean
     */
    public function isOnlyWaitinglist()
    {

        // only Check if is with internal registration and we have free seats
        if ($this->withRegistration ) {
            // Internal Registration Process : check $this->availableSeats  Seats against Registered
            if (($this->registeredSeats  +1) > ($this->availableSeats )  ) {
                // echo "<br>Line: " . __LINE__ . " : " . " File: " . __FILE__ . '<br>$this->registeredSeats + $this->unconfirmedSeats +1) > ($this->availableSeats + $this->availableWaitingSeat <hr>';
                return TRUE;
            }
        }
        // ok: we have free seats or it is eyternal registration so we do not need or can not show the No Free Seats warning
        return false ;
    }

	/**
	 * @return boolean
	 */
	public function mustLoginRights()
	{
		// ToDo : Check if registration_access is set and isLoginUser
		return true ;
	}
	/**
	 * @return boolean
	 */
	public function hasAccessRights()
	{
		// ToDo : Check registration_access againstUsersGroups
		return true ;
	}

    /**
     * @return int
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * @param int $crdate
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * @return int
     */
    public function getLanguageUid()
    {
        return $this->_languageUid;
    }


    /**
     * @return int
     */
    public function getLocalizedUid()
    {
        return $this->_localizedUid;
    }

    /**
     * @param int $localizedUid
     */
    public function setLocalizedUid($localizedUid)
    {
        $this->_localizedUid = $localizedUid;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * Adds a Subevent
     *
     * @param Subevent $subevent
     * @return void
     */
    public function addSubevent(Subevent $subevent)
    {
        $this->subevent->attach($subevent);
    }

    /**
     * Removes a Subevent
     *
     * @param Subevent $subevent ToRemove : The subevent  to be removed
     * @return void
     */
    public function removeSubevent(Subevent $subevent )
    {
        $this->subevent->detach($subevent);
    }

    /**
     * Returns the Subevent
     *
     * @return ObjectStorage<Subevent> $subevent
     */
    public function getSubevent()
    {
        return $this->subevent;
    }

    /**
     * Sets the Subevent
     *
     * @param ObjectStorage<Subevent> $subevent
     * @return void
     */
    public function setSubevent(ObjectStorage $subevent)
    {
        $this->subevent = $subevent;
    }

    /**
     * @return int Count of days + 2
     */
    public function getSubeventCount()
    {
        return  intval($this->getSubevent()->count() ) + 1;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getStartDateFE()
    {
        return $this->startDateFE;
    }
    /**
     * @return string
     */
    public function getEndDateFE()
    {
        return $this->endDateFE;
    }
    /**
     * @param string $endDateFE
     */
    public function setEndDateFE($endDateFE)
    {
        $this->endDateFE = $endDateFE;
    }

    /**
     * @param string $startDateFE
     */
    public function setStartDateFE($startDateFE)
    {
        $this->startDateFE = $startDateFE;
    }


    /**
     * @return string
     */
    public function getStartTimeFE()
    {
        return $this->startTimeFE;
    }

    /**
     * @param string $startTimeFE
     */
    public function setStartTimeFE($startTimeFE)
    {
        $this->startTimeFE = $startTimeFE;
    }

    /**
     * @return string
     */
    public function getEndTimeFE()
    {
        return $this->endTimeFE;
    }

    /**
     * @param string $endTimeFE
     */
    public function setEndTimeFE($endTimeFE)
    {
        $this->endTimeFE = $endTimeFE;
    }

    /**
     * @return int
     */
    public function getEntryTime()
    {
        return $this->entryTime;
    }

    /**
     * @param int $entryTime
     */
    public function setEntryTime($entryTime)
    {
        $this->entryTime = $entryTime;
    }

    /**
     * @return string
     */
    public function getEntryTimeFE()
    {
        return $this->entryTimeFE;
    }

    /**
     * @param string $entryTimeFE
     */
    public function setEntryTimeFE($entryTimeFE)
    {
        $this->entryTimeFE = $entryTimeFE;
    }





    /**
     * @return int
     */
    public function getSysLanguageUid()
    {
        return $this->sysLanguageUid;
    }

    /**
     * @param int $sysLanguageUid
     */
    public function setSysLanguageUid($sysLanguageUid)
    {
        $this->sysLanguageUid = $sysLanguageUid;
        $this->_languageUid = $sysLanguageUid;
    }

    /**
     * @return string
     */
    public function getTagsFE()
    {
        return $this->tagsFE;
    }

    /**
     * @param string $tagsFE
     */
    public function setTagsFE($tagsFE)
    {
        $this->tagsFE = $tagsFE;
    }

    /**
     * @return int
     */
    public function getTopEvent()
    {
        return $this->topEvent;
    }

    /**
     * @param int $topEvent
     */
    public function setTopEvent($topEvent)
    {
        $this->topEvent = $topEvent;
    }

    /**
     * @return int
     */
    public function getCanceled()
    {
        return $this->canceled;
    }

    /**
     * @param int $canceled
     */
    public function setCanceled($canceled)
    {
        $this->canceled = $canceled;
    }

    /**
     * @return int
     */
    public function getViewed()
    {
        return $this->viewed;
    }

    /**
     * @param int $viewed
     */
    public function setViewed($viewed)
    {
        $this->viewed = $viewed;
    }


    /**
     */
    public function increaseViewed()
    {
        $this->viewed ++;
    }

    /**
     * @return float
     */
    public function getPriceReduced()
    {
        return $this->priceReduced;
    }

    /**
     * @param float $priceReduced
     */
    public function setPriceReduced($priceReduced)
    {
        $this->priceReduced = $priceReduced;
    }

    /**
     * @return string
     */
    public function getPriceReducedText()
    {
        return $this->priceReducedText;
    }

    /**
     * @param string $priceReducedText
     */
    public function setPriceReducedText($priceReducedText)
    {
        $this->priceReducedText = $priceReducedText;
    }

    /**
     * @return bool
     */
    public function getStoreInHubspot()
    {
        return $this->storeInHubspot;
    }

    /**
     * @param bool $storeInHubspot
     */
    public function setStoreInHubspot($storeInHubspot)
    {
        $this->storeInHubspot = $storeInHubspot;
    }

    /**
     * @return string
     */
    public function getSalesForceCampaignId()
    {
        return $this->salesForceCampaignId;
    }

    /**
     * @param string $salesForceCampaignId
     */
    public function setSalesForceCampaignId($salesForceCampaignId)
    {
        $this->salesForceCampaignId = $salesForceCampaignId;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param int $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return int|null
     */
    public function getTstamp(): ?int
    {
        return $this->tstamp;
    }

    /**
     * @param int $tstamp
     */
    public function setTstamp(int $tstamp): void
    {
        $this->tstamp = $tstamp;
    }

    /**
     * @return int|null
     */
    public function getLastUpdated(): ?int
    {
        return $this->lastUpdated;
    }

    /**
     * @param int|null $lastUpdated
     */
    public function setLastUpdated(?int $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * @return int|null
     */
    public function getLastUpdatedBy(): ?int
    {
        return $this->lastUpdatedBy;
    }

    /**
     * @param int|null $lastUpdatedBy
     */
    public function setLastUpdatedBy(?int $lastUpdatedBy): void
    {
        $this->lastUpdatedBy = $lastUpdatedBy;
    }



    public function getDaysSinceLastMod() {
        return round( ( time() - $this->lastUpdated) / (3600*24) , 0 ) ;
    }
    public function getHoursSinceLastMod() {
        return round( ( time() - $this->lastUpdated) / (3600) , 0 ) ;
    }


    /**
     * @return int
     */
    public function getMasterId()
    {
        return $this->masterId;
    }

    /**
     * @param int $masterId
     */
    public function setMasterId($masterId)
    {
        $this->masterId = $masterId;
    }

    /**
     * @return int
     */
    public function getChangeFutureEvents()
    {
        return $this->changeFutureEvents;
    }

    /**
     * @param int $changeFutureEvents
     */
    public function setChangeFutureEvents($changeFutureEvents)
    {
        $this->changeFutureEvents = $changeFutureEvents;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return int
     */
    public function getRegistrationGender(): int
    {
        return $this->registrationGender;
    }

    /**
     * @param int $registrationGender
     */
    public function setRegistrationGender(int $registrationGender)
    {
        $this->registrationGender = $registrationGender;
    }

    /**
     * @return int
     */
    public function getRegistrationShowStatus(): int
    {
        return $this->registrationShowStatus;
    }

    /**
     * @param mixed $registrationShowStatus
     */
    public function setRegistrationShowStatus($registrationShowStatus)
    {
        if( is_null($registrationShowStatus)) {
            $registrationShowStatus = 0 ;
        }
        $this->registrationShowStatus = $registrationShowStatus;
    }

    /**
     * @return string|null
     */
    public function getYoutubeLink(): ?string
    {
        return $this->youtubeLink;
    }

    /**
     * @param string|null $youtubeLink
     */
    public function setYoutubeLink(?string $youtubeLink): void
    {
        $this->youtubeLink = $youtubeLink;
    }








}