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
     * Email of this organizer. Shown in Emails and as  Contact info in the event
     * details
     *
     * @var string
     */
    protected $phone = '';
    
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
     * description
     *
     * @var string
     */
    protected $description = '';
    
    /**
     * organizerCategory
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category>
     */
    protected $organizerCategory = null;
    
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
        $this->organizerCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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

}