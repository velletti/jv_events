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
 * Test case for class \JVE\JvEvents\Domain\Model\Location.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Jörg velletti <jVelletti@allplan.com>
 */
class LocationTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
	/**
	 * @var \JVE\JvEvents\Domain\Model\Location
	 */
	protected $subject = NULL;

	public function setUp()
	{
		$this->subject = new \JVE\JvEvents\Domain\Model\Location();
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
	public function getStreetAndNrReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getStreetAndNr()
		);
	}

	/**
	 * @test
	 */
	public function setStreetAndNrForStringSetsStreetAndNr()
	{
		$this->subject->setStreetAndNr('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'streetAndNr',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getZipReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setZipForStringSetsZip()
	{
		$this->subject->setZip('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'zip',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getCityReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getCity()
		);
	}

	/**
	 * @test
	 */
	public function setCityForStringSetsCity()
	{
		$this->subject->setCity('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'city',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getCountryReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setCountryForStringSetsCountry()
	{
		$this->subject->setCountry('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'country',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getLngReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getLng()
		);
	}

	/**
	 * @test
	 */
	public function setLngForStringSetsLng()
	{
		$this->subject->setLng('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'lng',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getLatReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getLat()
		);
	}

	/**
	 * @test
	 */
	public function setLatForStringSetsLat()
	{
		$this->subject->setLat('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'lat',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getLinkReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getLink()
		);
	}

	/**
	 * @test
	 */
	public function setLinkForStringSetsLink()
	{
		$this->subject->setLink('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'link',
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
	public function getOrganizerReturnsInitialValueForOrganizer()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getOrganizer()
		);
	}

	/**
	 * @test
	 */
	public function setOrganizerForOrganizerSetsOrganizer()
	{
		$organizerFixture = new \JVE\JvEvents\Domain\Model\Organizer();
		$this->subject->setOrganizer($organizerFixture);

		$this->assertAttributeEquals(
			$organizerFixture,
			'organizer',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getLocationCategoryReturnsInitialValueForCategory()
	{
		$newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->subject->getLocationCategory()
		);
	}

	/**
	 * @test
	 */
	public function setLocationCategoryForObjectStorageContainingCategorySetsLocationCategory()
	{
		$locationCategory = new \JVE\JvEvents\Domain\Model\Category();
		$objectStorageHoldingExactlyOneLocationCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$objectStorageHoldingExactlyOneLocationCategory->attach($locationCategory);
		$this->subject->setLocationCategory($objectStorageHoldingExactlyOneLocationCategory);

		$this->assertAttributeEquals(
			$objectStorageHoldingExactlyOneLocationCategory,
			'locationCategory',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function addLocationCategoryToObjectStorageHoldingLocationCategory()
	{
		$locationCategory = new \JVE\JvEvents\Domain\Model\Category();
		$locationCategoryObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('attach'), array(), '', FALSE);
		$locationCategoryObjectStorageMock->expects($this->once())->method('attach')->with($this->equalTo($locationCategory));
		$this->inject($this->subject, 'locationCategory', $locationCategoryObjectStorageMock);

		$this->subject->addLocationCategory($locationCategory);
	}

	/**
	 * @test
	 */
	public function removeLocationCategoryFromObjectStorageHoldingLocationCategory()
	{
		$locationCategory = new \JVE\JvEvents\Domain\Model\Category();
		$locationCategoryObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('detach'), array(), '', FALSE);
		$locationCategoryObjectStorageMock->expects($this->once())->method('detach')->with($this->equalTo($locationCategory));
		$this->inject($this->subject, 'locationCategory', $locationCategoryObjectStorageMock);

		$this->subject->removeLocationCategory($locationCategory);

	}
}
