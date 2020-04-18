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
 * Location
 */
class Location extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * Name of the Location
     *
     * @var string
     * @validate NotEmpty
     */
    protected $name = '';

    /**
     * slug Name of the Location
     *
     * @var string
     */
    protected $slug = '';
    /**
     * Address and Number of the location
     *
     * @var string
     */
    protected $streetAndNr = '';
    
    /**
     * Zip / Postal Code
     *
     * @var string
     */
    protected $zip = '';
    
    /**
     * Name of the City
     *
     * @var string
     * @validate NotEmpty
     */
    protected $city = '';
    
    /**
     * Country 3 Digits Iso Code
     *
     * @var string
     */
    protected $country = '';
    
    /**
     * Longitude of this location use the Wizard to set this
     *
     * @var string
     */
    protected $lng = '';
    
    /**
     * Latitude of this location use the Wizard to set this
     *
     * @var string
     */
    protected $lat = '';

    /**
     * latest Event  of any event in this location. Calculated
     *
     * @var \DateTime
     */
    protected $latestEvent = null;

    /**
     * URL should start with http://
     *
     * @var string
     */
    protected $link = '';
    
    /**
     * valid Email Address. if set, organizer notifications will be sent to this email
     *
     * @var string
     */
    protected $email = '';
    
    /**
     * phone Number for this location
     *
     * @var string
     */
    protected $phone = '';
    
    /**
     * Details to this location
     *
     * @var string
     */
    protected $description = '';

    /**
     * Files that may be useful for this event
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $teaserImage = null;

    /**
     * Organizer Id of this Location
     *
     * @var \JVE\JvEvents\Domain\Model\Organizer
     */
    protected $organizer = null;
    
    /**
     * Organizer Id of this Location
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category>
     */
    protected $locationCategory = null;

    /**
     *
     *
     * @var bool
     */
    protected $defaultLocation = false ;

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
     * Returns the streetAndNr
     *
     * @return string $streetAndNr
     */
    public function getStreetAndNr()
    {
        return $this->streetAndNr;
    }
    
    /**
     * Sets the streetAndNr
     *
     * @param string $streetAndNr
     * @return void
     */
    public function setStreetAndNr($streetAndNr)
    {
        $this->streetAndNr = $streetAndNr;
    }
    
    /**
     * Returns the zip
     *
     * @return string $zip
     */
    public function getZip()
    {
        return $this->zip;
    }
    
    /**
     * Sets the zip
     *
     * @param string $zip
     * @return void
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }
    
    /**
     * Returns the city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }
    
    /**
     * Sets the city
     *
     * @param string $city
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }
    
    /**
     * Returns the country
     *
     * @return string $country
     */
    public function getCountry()
    {
        return $this->country;
    }
    
    /**
     * Sets the country
     *
     * @param string $country
     * @return void
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }
    
    /**
     * Returns the lng
     *
     * @return string $lng
     */
    public function getLng()
    {
        return $this->lng;
    }
    
    /**
     * Sets the lng
     *
     * @param string $lng
     * @return void
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }
    
    /**
     * Returns the lat
     *
     * @return string $lat
     */
    public function getLat()
    {
        return $this->lat;
    }
    
    /**
     * Sets the lat
     *
     * @param string $lat
     * @return void
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }
    
    /**
     * Returns the link
     *
     * @return string $link
     */
    public function getLink()
    {
        return $this->link;
    }
    
    /**
     * Sets the link
     *
     * @param string $link
     * @return void
     */
    public function setLink($link)
    {
        $this->link = $link;
    }
    
    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
    
    /**
     * Returns the phone
     *
     * @return string $phone
     */
    public function getPhone()
    {
        return $this->phone;
    }
    
    /**
     * Sets the phone
     *
     * @param string $phone
     * @return void
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
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
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
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
        $this->locationCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }
    
    /**
     * Adds a Category
     *
     * @param \JVE\JvEvents\Domain\Model\Category $locationCategory
     * @return void
     */
    public function addLocationCategory(\JVE\JvEvents\Domain\Model\Category $locationCategory)
    {
        $this->locationCategory->attach($locationCategory);
    }
    
    /**
     * Removes a Category
     *
     * @param \JVE\JvEvents\Domain\Model\Category $locationCategoryToRemove The Category to be removed
     * @return void
     */
    public function removeLocationCategory(\JVE\JvEvents\Domain\Model\Category $locationCategoryToRemove)
    {
        $this->locationCategory->detach($locationCategoryToRemove);
    }
    
    /**
     * Returns the locationCategory
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category> $locationCategory
     */
    public function getLocationCategory()
    {
        return $this->locationCategory;
    }
    
    /**
     * Sets the locationCategory
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category> $locationCategory
     * @return void
     */
    public function setLocationCategory(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $locationCategory)
    {
        $this->locationCategory = $locationCategory;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    public function getTeaserImage()
    {
        return $this->teaserImage;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $teaserImage
     */
    public function setTeaserImage($teaserImage)
    {
        $this->teaserImage = $teaserImage;
    }

    /**
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return int
     */
    public function getLanguageUid()
    {
        return $this->_languageUid;
    }

    /**
     * @param int $languageUid
     */
    public function setLanguageUid($languageUid)
    {
        $this->_languageUid = $languageUid;
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
     * @return \DateTime
     */
    public function getLatestEvent()
    {
        return $this->latestEvent;
    }

    /**
     * @param \DateTime $latestEvent
     */
    public function setLatestEvent($latestEvent)
    {
        if( $latestEvent > $this->latestEvent ) {
            $this->latestEvent = $latestEvent;
        }
    }

    /**
     * @return bool
     */
    public function isDefaultLocation()
    {
        return $this->defaultLocation;
    }

    /**
     * @param bool $defaultLocation
     */
    public function setDefaultLocation($defaultLocation)
    {
        $this->defaultLocation = $defaultLocation;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }



}