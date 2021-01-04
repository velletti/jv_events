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
 * Single Subevent
 */
class Subevent extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {


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
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $startDate = null;
    
    /**
     * Start Time of this event
     *
     * @var int
     */
    protected $startTime = 0;

    /**
     * Access Start Time of this event (default TYP3 Field )
     * @var int|\DateTime|null
     */
    protected $starttime ;

    /**
     * Access End Time of this event (default TYP3 Field )
     *
     * @var \DateTime
     */
    protected $endtime ;

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
     * parent event
     *
     * @var int
     */
    protected $event ;



    /**
	 * __construct
	 */
	public function __construct()
	{
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
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
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }


    /**
     * @return int
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param int $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }



}