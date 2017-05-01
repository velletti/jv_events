<?php
namespace JVE\JvEvents\Domain\Model;

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
class Event extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * Select one of the possabilities: link to an internal or external page or
     * default: show event details from this extension
     *
     * @var int
     * @validate NotEmpty
     */
    protected $eventType = 0;
    
    /**
     * Short Title of this event. Used in listings
     *
     * @var string
     * @validate NotEmpty
     */
    protected $name = '';
    
    /**
     * Short description of this event. Used in listings
     *
     * @var string
     */
    protected $teaser = '';
    
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
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $images = null;

	/**
	 * Files that may be useful for this event
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
	 */
	protected $teaserImage = null;


	/**
     * Files that may be useful for this event
     *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $files = null;
    
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
     * @validate NotEmpty
     */
    protected $startDate = null;
    
    /**
     * Start Time of this event
     *
     * @var int
     */
    protected $startTime = 0;
    
    /**
     * End Date of this event. Mandatory
     *
     * @var \DateTime

     */
    protected $endDate = null;
    
    /**
     * End time of this event
     *
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
     * This event is an exception for an recurring event.
     *
     * @var bool
     */
    protected $isRegistrationPossible = false ;

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
     * @var \JVE\JvEvents\Domain\Model\Organizer
     * @lazy
     */
    protected $organizer = null;
    
    /**
     * location
     *
     * @var \JVE\JvEvents\Domain\Model\Location
     * @lazy
     */
    protected $location = null;
    
    /**
     * registrant
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Registrant>
     * @cascade remove
     * @lazy
     */
    protected $registrant = null;
    
    /**
     * eventCategory
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category>
     */
    protected $eventCategory = null;
    
    /**
     * tags
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Tag>
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
     * @return \JVE\JvEvents\Domain\Model\Organizer $organizer
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }
    
    /**
     * Sets the organizer
     *
     * @param \JVE\JvEvents\Domain\Model\Organizer $organizer
     * @return void
     */
    public function setOrganizer(\JVE\JvEvents\Domain\Model\Organizer $organizer)
    {
        $this->organizer = $organizer;
    }
    
    /**
     * Returns the location
     *
     * @return \JVE\JvEvents\Domain\Model\Location $location
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    /**
     * Sets the location
     *
     * @param \JVE\JvEvents\Domain\Model\Location $location
     * @return void
     */
    public function setLocation(\JVE\JvEvents\Domain\Model\Location $location)
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
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference> $images
     */
    public function getImages()
    {
        return $this->images;
    }
    
    /**
     * Sets the images
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $images
     * @return void
     */
    public function setImages(\TYPO3\CMS\Extbase\Domain\Model\FileReference $images)
    {
        $this->images = $images;
    }

	/**
	 * Returns the teaserImage
	 *
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $teaserImage
	 */
	public function getTeaserImage()
	{
		return $this->teaserImage;
	}

	/**
	 * Sets the teaserImage
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $teaserImage
	 * @return void
	 */
	public function setTeaserImage(\TYPO3\CMS\Extbase\Domain\Model\FileReference $teaserImage)
	{
		$this->teaserImage = $teaserImage;
	}

	/**
	 * Returns the files
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference> $files
	 */
	public function getFiles()
	{
		return $this->files;
	}

	/**
	 * Sets the files
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $files
	 * @return void
	 */
	public function setFiles(\TYPO3\CMS\Extbase\Domain\Model\FileReference $files)
	{
		$this->files = $files;
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
     * @return int $startTime
     */
    public function getStartTime()
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
     * @param \DateTime $endDate
     * @return void
     */
    public function setEndDate(\DateTime $endDate)
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
     * @return bool $withRegistration
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
     * Returns the boolean state of storeInCitrix
     *
     * @return bool
     */
    public function isStoreInCitrix()
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
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->registrant = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->eventCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->tags = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

		$this->files = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->images = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->teaserImage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }
    
    /**
     * Adds a Registrant
     *
     * @param \JVE\JvEvents\Domain\Model\Registrant $registrant
     * @return void
     */
    public function addRegistrant(\JVE\JvEvents\Domain\Model\Registrant $registrant)
    {
        $this->registrant->attach($registrant);
    }
    
    /**
     * Removes a Registrant
     *
     * @param \JVE\JvEvents\Domain\Model\Registrant $registrantToRemove The Registrant to be removed
     * @return void
     */
    public function removeRegistrant(\JVE\JvEvents\Domain\Model\Registrant $registrantToRemove)
    {
        $this->registrant->detach($registrantToRemove);
    }
    
    /**
     * Returns the registrant
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Registrant> $registrant
     */
    public function getRegistrant()
    {
        return $this->registrant;
    }
    
    /**
     * Sets the registrant
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Registrant> $registrant
     * @return void
     */
    public function setRegistrant(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $registrant)
    {
        $this->registrant = $registrant;
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
     * @param \JVE\JvEvents\Domain\Model\Category $eventCategory
     * @return void
     */
    public function addEventCategory(\JVE\JvEvents\Domain\Model\Category $eventCategory)
    {
        $this->eventCategory->attach($eventCategory);
    }
    
    /**
     * Removes a Category
     *
     * @param \JVE\JvEvents\Domain\Model\Category $eventCategoryToRemove The Category to be removed
     * @return void
     */
    public function removeEventCategory(\JVE\JvEvents\Domain\Model\Category $eventCategoryToRemove)
    {
        $this->eventCategory->detach($eventCategoryToRemove);
    }
    
    /**
     * Returns the eventCategory
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category> $eventCategory
     */
    public function getEventCategory()
    {
        return $this->eventCategory;
    }
    
    /**
     * Sets the eventCategory
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category> $eventCategory
     * @return void
     */
    public function setEventCategory(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $eventCategory)
    {
        $this->eventCategory = $eventCategory;
    }
    
    /**
     * Adds a Tag
     *
     * @param \JVE\JvEvents\Domain\Model\Tag $tag
     * @return void
     */
    public function addTag(\JVE\JvEvents\Domain\Model\Tag $tag)
    {
        $this->tags->attach($tag);
    }
    
    /**
     * Removes a Tag
     *
     * @param \JVE\JvEvents\Domain\Model\Tag $tagToRemove The Tag to be removed
     * @return void
     */
    public function removeTag(\JVE\JvEvents\Domain\Model\Tag $tagToRemove)
    {
        $this->tags->detach($tagToRemove);
    }
    
    /**
     * Returns the tags
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Tag> $tags
     */
    public function getTags()
    {
        return $this->tags;
    }
    
    /**
     * Sets the tags
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Tag> $tags
     * @return void
     */
    public function setTags(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $tags)
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



    /** + +++ special Helper functions for the model  */

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
        // Check Dates :
        $now = new \DateTime('now') ;

        if( $this->registrationUntil < $now && $this->registrationUntil > 1 ) {
            //  echo "<br>Line: " . __LINE__ . " : " . " File: " . __FILE__ . '<br>$this->registrationUntil < $now " . $this->registrationUntil . "<" . $now . "<hr>";

            return false ;
        }
        if( $this->startDate < $now && $this->registrationUntil < 1 ) {
            //  echo "<br>Line: " . __LINE__ . " : " . " File: " . __FILE__ . '<br>$this->startDate < $now " . $this->startDate . "<" . $now . "<hr>";

            return false ;
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
    public function isNoFreeSeats()
    {

        // only Check if is with internal registration and we have free seats
        if ($this->withRegistration ) {
            // Internal Registration Process : check $this->availableSeats  Seats against Registered
            if (($this->registeredSeats + $this->unconfirmedSeats +1) > ($this->availableSeats + $this->availableWaitingSeats)  ) {
                //  echo "<br>Line: " . __LINE__ . " : " . " File: " . __FILE__ . '<br>$this->registeredSeats + $this->unconfirmedSeats +1) > ($this->availableSeats + $this->availableWaitingSeat <hr>';
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





}