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
 * Organizer
 */
class Organizer extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * Name of the Organizer, shown in Event lists
     *
     * @var string
     * @validate NotEmpty
     */
    protected $name = '';
    
    /**
     * Please enter a valid Email Address. Needed if you activate the option: send
     * email to organizer on new registrations
     *
     * @var string
     * @validate NotEmpty
     */
    protected $email = '';


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
     * a logo / Image for this organizer
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $images = null;

    /**
     * Files that may be useful for this event
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
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
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category>
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
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Tag>
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
        $this->organizerCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->tags = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

        $this->images = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->teaserImage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference $images
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
     * @param \JVE\JvEvents\Domain\Model\Category $organizerCategory
     * @return void
     */
    public function addOrganizerCategory(\JVE\JvEvents\Domain\Model\Category $organizerCategory)
    {
        $this->organizerCategory->attach($organizerCategory);
    }
    
    /**
     * Removes a Category
     *
     * @param \JVE\JvEvents\Domain\Model\Category $organizerCategoryToRemove The Category to be removed
     * @return void
     */
    public function removeOrganizerCategory(\JVE\JvEvents\Domain\Model\Category $organizerCategoryToRemove)
    {
        $this->organizerCategory->detach($organizerCategoryToRemove);
    }
    
    /**
     * Returns the organizerCategory
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category> $organizerCategory
     */
    public function getOrganizerCategory()
    {
        return $this->organizerCategory;
    }
    
    /**
     * Sets the organizerCategory
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category> $organizerCategory
     * @return void
     */
    public function setOrganizerCategory(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $organizerCategory)
    {
        $this->organizerCategory = $organizerCategory;
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

}