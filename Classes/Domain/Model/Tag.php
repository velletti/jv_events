<?php
namespace JVelletti\JvEvents\Domain\Model;


use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
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
     * visibility of tag in Filter o Event details
     *
     * @var int
     */
    protected $visibility = 0;


    /**
     * type 0=Event Category 1=Location Category 2=Organizer Category
     *
     * @var int
     */
    protected $type = 0;

    /**
     * tagCategory
     *
     * @var ObjectStorage<Category>
     */
    protected $tagCategory = NULL;

    /**
     * Initializes all ObjectStorage properties
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->tagCategory = new ObjectStorage();
    }

    /**
     * Returns the name
     *
     * @return string $name
     */
    public function getName( )
    {
        return $this->name;
    }

    /**
     * Returns the Last Part of the name after:
     * Level: Beginner
     *
     * you will get "Beginner"
     *
     * @return string $name
     */
    public function getNameAfterColon( )
    {
        $posColon = strrpos( $this->name , ":" ) ;
        if ( $posColon > 0 )   {
            return  trim( substr( $this->name , $posColon+1 , 999)) ;
        }
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
     * Adds a Category
     *
     * @return void
     */
    public function addTagCategory(Category $tagCategory)
    {
        $this->tagCategory->attach($tagCategory);
    }

    /**
     * Removes a Category
     *
     * @param Category $tagCategoryToRemove The Category to be removed
     * @return void
     */
    public function removeTagCategory(Category $tagCategoryToRemove)
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
     * @return ObjectStorage<Category> $tagCategory
     */
    public function getTagCategory()
    {
        return $this->tagCategory;
    }

    /**
     * Sets the tagCategory
     *
     * @param ObjectStorage<Category> $tagCategory
     * @return void
     */
    public function setTagCategory(ObjectStorage $tagCategory)
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

    public function setNocopy(int $nocopy)
    {
        $this->nocopy = $nocopy;
    }

    /**
     * @return int
     */
    public function getVisibility(): int
    {
        return $this->visibility;
    }

    public function setVisibility(int $visibility): void
    {
        $this->visibility = $visibility;
    }




}