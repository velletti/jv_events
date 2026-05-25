<?php
namespace JVelletti\JvEvents\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
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
class Location extends AbstractEntity
{

    /**
     * Name of the Location
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
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
     * Additional Info to find the location
     *
     * @var string
     */
    protected $additionalInfo = '';
    
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
     */
    #[Validate(['validator' => 'NotEmpty'])]
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
     * lastmod Date as timestring
     *
     * @var int
     */
    protected $tstamp ;


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
     * phone Number for this location
     *
     * @var string
     */
    protected $organizerName = '';
    
    /**
     * Details to this location
     *
     * @var string
     */
    protected $description = '';

    /**
     * Files that may be useful for this event
     *
     * @var FileReference
     */
    protected $teaserImage = null;

    /**
     * Organizer Id of this Location
     *
     * @var Organizer|null
     */
    protected $organizer = null;
    
    /**
     * Organizer Id of this Location
     *
     * @var ObjectStorage<Category>
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
    public function setName($name): void
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
    public function setStreetAndNr($streetAndNr): void
    {
        $this->streetAndNr = $streetAndNr;
    }

    public function getAdditionalInfo(): string
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(string $additionalInfo): void
    {
        $this->additionalInfo = $additionalInfo;
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
    public function setZip($zip): void
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
     * Returns the city but URL Endocded
     *
     * @return string $city
     */
    public function getCityEncoded()
    {
        return urlencode( $this->city );
    }

    /**
     * Returns the Address  URL Endocded
     *
     * @return string $city
     */
    public function getAddressEncoded()
    {
        $address = preg_replace('/^([^\/]*).*$/', '$1', $this->streetAndNr ?? '') ;
        $address .= ($this->zip ) ? (' ' . $this->zip ) : '' ;
        $address .= ($this->city ) ? (' ' . $this->city ) : '' ;
        return urlencode( $address  );
    }
    
    /**
     * Sets the city
     *
     * @param string $city
     * @return void
     */
    public function setCity($city): void
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
    public function setCountry($country): void
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
    public function setLng($lng): void
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
    public function setLat($lat): void
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
    public function setLink($link): void
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
    public function setEmail($email): void
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
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * Returns the organizerName
     *
     * @return string $organizerName
     */
    public function getOrganizerName():string
    {
        return ($this->organizerName ??'');
    }

    /**
     * Sets the organizerName
     *
     * @param string|null $organizerName
     * @return void
     */
    public function setOrganizerName(?string $organizerName):void
    {
        $this->organizerName = ($organizerName ?? '');
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
    public function setDescription($description): void
    {
        $this->description = $description;
    }
    
    /**
     * Returns the organizer
     *
     * @return ?Organizer $organizer
     */
    public function getOrganizer()
    {
        if( is_object ($this->organizer)) {
            return $this->organizer;
        }
        return null ;
    }
    
    /**
     * Sets the organizer
     *
     * @return void
     */
    public function setOrganizer(?Organizer $organizer): void
    {
        if( is_object( $organizer )) {
            $this->organizer = $organizer;
        } else {
            $this->organizer = null ;
        }
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
        $this->locationCategory = new ObjectStorage();
    }
    
    /**
     * Adds a Category
     *
     * @return void
     */
    public function addLocationCategory(Category $locationCategory): void
    {
        $this->locationCategory->attach($locationCategory);
    }
    
    /**
     * Removes a Category
     *
     * @param Category $locationCategoryToRemove The Category to be removed
     * @return void
     */
    public function removeLocationCategory(Category $locationCategoryToRemove): void
    {
        $this->locationCategory->detach($locationCategoryToRemove);
    }
    
    /**
     * Returns the locationCategory
     *
     * @return ObjectStorage<Category> $locationCategory
     */
    public function getLocationCategory()
    {
        return $this->locationCategory;
    }
    
    /**
     * Sets the locationCategory
     *
     * @param ObjectStorage<Category> $locationCategory
     * @return void
     */
    public function setLocationCategory(ObjectStorage $locationCategory): void
    {
        $this->locationCategory = $locationCategory;
    }

    /**
     * @return FileReference
     */
    public function getTeaserImage()
    {
        return $this->teaserImage;
    }

    /**
     * @param FileReference $teaserImage
     */
    public function setTeaserImage($teaserImage): void
    {
        $this->teaserImage = $teaserImage;
    }

    /**
     * @param int $uid
     */
    public function setUid($uid): void
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
    public function setLanguageUid($languageUid): void
    {
        $this->_languageUid = $languageUid;
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
    public function setLatestEvent($latestEvent): void
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
    public function setDefaultLocation($defaultLocation): void
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

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return int
     */
    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function setTstamp(int $tstamp): void
    {
        $this->tstamp = $tstamp;
    }



}