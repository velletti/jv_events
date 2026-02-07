<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace JVelletti\JvEvents\Domain\Model;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;


/**
 * A Frontend User Group
 *
 */
class FrontendUserGroup extends AbstractEntity
{
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var ObjectStorage<FrontendUserGroup>
     */
    protected $subgroup;

    /**
     * Constructs a new Frontend User Group
     *
     * @param string $title
     */
    public function __construct($title = '')
    {
        $this->setTitle($title);
        $this->subgroup = new ObjectStorage();
    }

    /**
     * Sets the title value
     *
     * @param string|null $title
     */
    public function setTitle(?string $title = null)
    {
        if ($title === null && $this->uid > 0) {
            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
            /** @var QueryBuilder $queryBuilder */
            $qb =  $connectionPool->getConnectionForTable('fe_groups')->createQueryBuilder();
            $title = $qb->select('title')
                ->from('fe_groups')
                ->where(
                    $qb->expr()->eq('uid', $qb->createNamedParameter($this->uid, \PDO::PARAM_INT))
                )
                ->execute()
                ->fetchColumn();
            if ($title === false) {
                switch ($this->uid) {
                   case 2:
                      $title = 'Organizer Default';
                      break;
                   case 5:
                      $title = 'Banner Request allowed';
                      break;
                   case 6:
                      $title = 'Registration internal allowed';
                      break;
                   case 7:
                      $title = 'Image Upload is allowed';
                      break;
                   case 7:
                      $title = 'youTubeLinkAllowed';
                      break;

                   default:
                          $title = $this->uid . ' - lost groupname';
               }
            }

        }
        $this->title = $title;
    }

    /**
     * Returns the title value
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the description value
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the description value
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the subgroups. Keep in mind that the property is called "subgroup"
     * although it can hold several subgroups.
     *
     * @param ObjectStorage<FrontendUserGroup> $subgroup An object storage containing the subgroups to add
     */
    public function setSubgroup(ObjectStorage $subgroup)
    {
        $this->subgroup = $subgroup;
    }

    /**
     * Adds a subgroup to the frontend user
     */
    public function addSubgroup(FrontendUserGroup $subgroup)
    {
        $this->subgroup->attach($subgroup);
    }

    /**
     * Removes a subgroup from the frontend user group
     */
    public function removeSubgroup(FrontendUserGroup $subgroup)
    {
        $this->subgroup->detach($subgroup);
    }

    /**
     * Returns the subgroups. Keep in mind that the property is called "subgroup"
     * although it can hold several subgroups.
     *
     * @return ObjectStorage<FrontendUserGroup> An object storage containing the subgroups
     */
    public function getSubgroup()
    {
        return $this->subgroup;
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }
}
