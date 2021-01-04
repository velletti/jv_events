<?php
namespace JVE\JvEvents\Domain\Model;


use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;


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
 * Tag
 */
class Tag extends AbstractEntity
{

    /**
     * name
     *
     * @var integer
     */
    protected $sorting ;

    /**
     * name
     *
     * @var string
     */
    protected $name = '';

    /**
     * nocopy
     *
     * @var int
     */
    protected $nocopy = 0;


    /**
     * type 0=Event Category 1=Location Category 2=Organizer Category
     *
     * @var int
     */
    protected $type = 0;

    /**
     * tagCategory
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category>
     */
    protected $tagCategory = NULL;

    /**
     * Initializes all ObjectStorage properties
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->tagCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Adds a Category
     *
     * @param \JVE\JvEvents\Domain\Model\Category $tagCategory
     * @return void
     */
    public function addTagCategory(\JVE\JvEvents\Domain\Model\Category $tagCategory)
    {
        $this->tagCategory->attach($tagCategory);
    }

    /**
     * Removes a Category
     *
     * @param \JVE\JvEvents\Domain\Model\Category $tagCategoryToRemove The Category to be removed
     * @return void
     */
    public function removeTagCategory(\JVE\JvEvents\Domain\Model\Category $tagCategoryToRemove)
    {
        $this->tagCategory->detach($tagCategoryToRemove);
    }

    /**
     * @return int
     */
    public function getLanguageUid(): ?int
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
     * @return int
     */
    public function getSorting(): ?int
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
     * @return int
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }


    /**
     * Returns the tagCategory
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category> $tagCategory
     */
    public function getTagCategory()
    {
        return $this->tagCategory;
    }

    /**
     * Sets the tagCategory
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JVE\JvEvents\Domain\Model\Category> $tagCategory
     * @return void
     */
    public function setTagCategory(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $tagCategory)
    {
        $this->tagCategory = $tagCategory;
    }

    /**
     * @return int
     */
    public function getNocopy(): int
    {
        return $this->nocopy;
    }

    /**
     * @param int $nocopy
     */
    public function setNocopy(int $nocopy)
    {
        $this->nocopy = $nocopy;
    }


}