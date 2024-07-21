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
 * Organizer
 */
class Organizer extends AbstractEntity
{

    /**
     * Name of the Organizer, shown in Event lists
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $name = '';

    /**
     * Name Addon of the Organizer, shown in Event lists
     *
     * @var string
     */
    protected $subname = '';

    /**
     * slug Name of the Location
     *
     * @var string
     */
    protected $slug = '';

    /**
     * Please enter a valid Email Address. Needed if you activate the option: send
     * email to organizer on new registrations
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $email = '';

    /**
     * mark this organizer as TOP  (to be able to filter, place special CSS code etc.)
     *
     * @var int
     */
    protected $top = 0;

    /**
     * Please enter one or more valid Email Addresses.
     * Used as CC email address to inform more than one person at the organizer on new registrations
     *
     * @var string
     */
    protected $emailCc = '';

    /**
     * creation Date as timestring
     *
     * @var int
     */
    protected $crdate ;


    /**
     * get Sorting Postion
     *
     * @var int
     */
    protected $sorting ;

    /**

     * @var int
     */
    protected $sysLanguageUid ;

    /**
     * lastmod Date as timestring
     *
     * @var int
     */
    protected $tstamp ;

    /**
     * Email of this organizer. Shown in Emails and as  Contact info in the event
     * details
     *
     * @var string
     */
    protected $phone = '';

    /**
 * Link of this organizer. Shown in Emails and as  Contact info in the event
 * details
 *
 * @var string
 */
    protected $charityLink = '';

    /**
     * Link to a youtube Video
     * details
     *
     * @var string
     */
    protected $youtubeLink = '';

    /**
     * Link of this organizer. Shown in Emails and as  Contact info in the event
     * details
     *
     * @var string
     */
    protected $link = '';


    
    /**
     * if you use SalesForce to store eventsregistrations, we use this uid to store the
     * data. Other SalesForce Users with an other UID may not see this event or the
     * registrations
     *
     * @var string
     */
    protected $salesForceUserId = '';

    /**
     * if you use SalesForce to store Campaigns for eventsregistrations, we use this uid to store the
     * data. NEW
     *
     * @var string
     */
    protected $salesForceUserId2 = '';

    /**
     * if you use SalesForce to store Campaigns for eventsregistrations, we use this uid to store the
     * data. NEW
     *
     * @var string
     */
    protected $salesForceUserOrg = '';

    /**
     * a logo / Image for this organizer
     *
     * @var FileReference
     */
    protected $images = null;

    /**
     * Files that may be useful for this event
     *
     * @var FileReference
     */
    protected $teaserImage = null;

    /**
     * description
     *
     * @var string
     */
    protected $description = '';

    /**
     * registrationInfo
     *
     * @var string
     */
    protected $registrationInfo = '';

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
     * hidden
     *
     * @var integer
     */
    protected $hidden ;
    /**
     * this event was admin Functions visible for the following usergroups / Access Rights
     *
     * @var string
     */
    protected $accessGroups = '';

    /**
     * this event was admin Functions  visible for the following users / Access Rights
     *
     * @var string
     */
    protected $accessUsers = '';


    /**
     * organizerCategory
     *
     * @var ObjectStorage<Category>
     */
    protected $organizerCategory = null;


    /**
     * Tag list , comma separated of this event. replacement for tags Object Storage  but FE = for frontend Editing
     *
     * @var string
     */
    protected $tagsFE = null;

    /**
     * tags
     *
     * @var ObjectStorage<Tag>
     */
    protected $tags = null;

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
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->organizerCategory = new ObjectStorage();
        $this->tags = new ObjectStorage();

        $this->images = new ObjectStorage();
        $this->teaserImage = new ObjectStorage();
    }

    /**
     * @param int|null $uid
     * Set Uid
     *
     * @return void
     */
    public function setUid( ?int $uid)
    {
        $this->uid = $uid ;
    }


    /**
     * Returns the name
     *
     * @return string $name
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Sets the name
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSubname(): string
    {
        return $this->subname;
    }

    public function setSubname(string $subname): void
    {
        $this->subname = $subname;
    }


    
    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail(): string
    {
        return $this->email;
    }
    
    /**
     * Sets the email
     *
     * @return void
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmailCc()
    {
        return $this->emailCc;
    }

    /**
     * @param string $emailCc
     */
    public function setEmailCc($emailCc)
    {
        $this->emailCc = $emailCc;
    }



    /**
     * Returns the phone
     *
     * @return string|null  $phone
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    
    /**
     * Sets the phone
     *
     * @return void
     */
    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
    }
    
    /**
     * Returns the salesForceUserId
     *
     * @return string $salesForceUserId
     */
    public function getSalesForceUserId()
    {
        return $this->salesForceUserId;
    }
    
    /**
     * Sets the salesForceUserId
     *
     * @param string $salesForceUserId
     * @return void
     */
    public function setSalesForceUserId($salesForceUserId)
    {
        $this->salesForceUserId = $salesForceUserId;
    }
    
    /**
     * Returns the images
     *
     * @return FileReference $images
     */
    public function getImages()
    {
        return $this->images;
    }
    
    /**
     * Sets the images
     *
     * @return void
     */
    public function setImages(FileReference $images)
    {
        $this->images = $images;
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
     * @return string
     */
    public function getRegistrationInfo()
    {
        return $this->registrationInfo;
    }

    /**
     * @param string $registrationInfo
     */
    public function setRegistrationInfo($registrationInfo)
    {
        $this->registrationInfo = $registrationInfo;
    }
    

    

    /**
     * Adds a Category
     *
     * @return void
     */
    public function addOrganizerCategory(Category $organizerCategory)
    {
        $this->organizerCategory->attach($organizerCategory);
    }
    
    /**
     * Removes a Category
     *
     * @param Category $organizerCategoryToRemove The Category to be removed
     * @return void
     */
    public function removeOrganizerCategory(Category $organizerCategoryToRemove)
    {
        $this->organizerCategory->detach($organizerCategoryToRemove);
    }
    
    /**
     * Returns the organizerCategory
     *
     * @return ObjectStorage<Category> $organizerCategory
     */
    public function getOrganizerCategory()
    {
        return $this->organizerCategory;
    }
    
    /**
     * Sets the organizerCategory
     *
     * @param ObjectStorage<Category> $organizerCategory
     * @return void
     */
    public function setOrganizerCategory(ObjectStorage $organizerCategory)
    {
        $this->organizerCategory = $organizerCategory;
    }

    /**
     * @return string
     */
    public function getAccessGroups()
    {
        return $this->accessGroups;
    }

    /**
     * @param string $accessGroups
     */
    public function setAccessGroups($accessGroups)
    {
        $this->accessGroups = $accessGroups;
    }

    /**
     * @return string
     */
    public function getAccessUsers()
    {
        return $this->accessUsers;
    }

    /**
     * @param string $accessUsers
     */
    public function setAccessUsers($accessUsers)
    {
        $this->accessUsers = $accessUsers;
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
    public function setTeaserImage($teaserImage)
    {
        $this->teaserImage = $teaserImage;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
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
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * @param int $tstamp
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;
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
     * Adds a Tag
     *
     * @return void
     */
    public function addTag(Tag $tag)
    {
        $this->tags->attach($tag);
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
     * @return string
     */
    public function getSalesForceUserId2()
    {
        return $this->salesForceUserId2;
    }

    /**
     * @param string $salesForceUserId2
     */
    public function setSalesForceUserId2($salesForceUserId2)
    {
        $this->salesForceUserId2 = $salesForceUserId2;
    }

    /**
     * @return string
     */
    public function getSalesForceUserOrg()
    {
        return $this->salesForceUserOrg;
    }

    /**
     * @param string $salesForceUserOrg
     */
    public function setSalesForceUserOrg($salesForceUserOrg)
    {
        $this->salesForceUserOrg = $salesForceUserOrg;
    }


    /**
     * @return int
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * @param int $sorting
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
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
     * @return int
     */
    public function getSysLanguageUid()
    {
        //return $this->sysLanguageUid ;
      return $this->_languageUid ;
    }

    /**
     * @return string
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param string $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param string $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
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
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getCharityLink(): string
    {
        return $this->charityLink;
    }

    public function setCharityLink(string $charityLink)
    {
        $this->charityLink = $charityLink;
    }

    /**
     * @return string
     */
    public function getYoutubeLink(): string
    {
        return $this->youtubeLink;
    }

    public function setYoutubeLink(string $youtubeLink)
    {
        $this->youtubeLink = $youtubeLink;
    }

    /**
     * @return int
     */
    public function getTop(): int
    {
        return $this->top;
    }

    public function setTop(int $top): void
    {
        $this->top = $top;
    }




}