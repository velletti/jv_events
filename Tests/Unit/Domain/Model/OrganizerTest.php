<?php

namespace JVE\JvEvents\Tests\Unit\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Jörg velletti <jVelletti@allplan.com>, Allplan GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class \JVE\JvEvents\Domain\Model\Organizer.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Jörg velletti <jVelletti@allplan.com>
 */
class OrganizerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
	/**
	 * @var \JVE\JvEvents\Domain\Model\Organizer
	 */
	protected $subject = NULL;

	public function setUp()
	{
		$this->subject = new \JVE\JvEvents\Domain\Model\Organizer();
	}

	public function tearDown()
	{
		unset($this->subject);
	}



	/**
	 * @test
	 */
	public function getNameReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getName()
		);
	}

	/**
	 * @test
	 */
	public function setNameForStringSetsName()
	{
		$this->subject->setName('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'name',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getEmailReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getEmail()
		);
	}

	/**
	 * @test
	 */
	public function setEmailForStringSetsEmail()
	{
		$this->subject->setEmail('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'email',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getPhoneReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getPhone()
		);
	}

	/**
	 * @test
	 */
	public function setPhoneForStringSetsPhone()
	{
		$this->subject->setPhone('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'phone',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getSalesForceUserIdReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getSalesForceUserId()
		);
	}

	/**
	 * @test
	 */
	public function setSalesForceUserIdForStringSetsSalesForceUserId()
	{
		$this->subject->setSalesForceUserId('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'salesForceUserId',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getImagesReturnsInitialValueForFileReference()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getImages()
		);
	}

	/**
	 * @test
	 */
	public function setImagesForFileReferenceSetsImages()
	{
		$fileReferenceFixture = new \TYPO3\CMS\Extbase\Domain\Model\FileReference();
		$this->subject->setImages($fileReferenceFixture);

		$this->assertAttributeEquals(
			$fileReferenceFixture,
			'images',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionForStringSetsDescription()
	{
		$this->subject->setDescription('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'description',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getOrganizerCategoryReturnsInitialValueForCategory()
	{
		$newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->subject->getOrganizerCategory()
		);
	}

	/**
	 * @test
	 */
	public function setOrganizerCategoryForObjectStorageContainingCategorySetsOrganizerCategory()
	{
		$organizerCategory = new \JVE\JvEvents\Domain\Model\Category();
		$objectStorageHoldingExactlyOneOrganizerCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$objectStorageHoldingExactlyOneOrganizerCategory->attach($organizerCategory);
		$this->subject->setOrganizerCategory($objectStorageHoldingExactlyOneOrganizerCategory);

		$this->assertAttributeEquals(
			$objectStorageHoldingExactlyOneOrganizerCategory,
			'organizerCategory',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function addOrganizerCategoryToObjectStorageHoldingOrganizerCategory()
	{
		$organizerCategory = new \JVE\JvEvents\Domain\Model\Category();
		$organizerCategoryObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('attach'), array(), '', FALSE);
		$organizerCategoryObjectStorageMock->expects($this->once())->method('attach')->with($this->equalTo($organizerCategory));
		$this->inject($this->subject, 'organizerCategory', $organizerCategoryObjectStorageMock);

		$this->subject->addOrganizerCategory($organizerCategory);
	}

	/**
	 * @test
	 */
	public function removeOrganizerCategoryFromObjectStorageHoldingOrganizerCategory()
	{
		$organizerCategory = new \JVE\JvEvents\Domain\Model\Category();
		$organizerCategoryObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('detach'), array(), '', FALSE);
		$organizerCategoryObjectStorageMock->expects($this->once())->method('detach')->with($this->equalTo($organizerCategory));
		$this->inject($this->subject, 'organizerCategory', $organizerCategoryObjectStorageMock);

		$this->subject->removeOrganizerCategory($organizerCategory);

	}
}
