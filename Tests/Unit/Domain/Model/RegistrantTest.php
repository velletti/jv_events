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
 * Test case for class \JVE\JvEvents\Domain\Model\Registrant.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Jörg velletti <jVelletti@allplan.com>
 */
class RegistrantTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
	/**
	 * @var \JVE\JvEvents\Domain\Model\Registrant
	 */
	protected $subject = NULL;

	public function setUp()
	{
		$this->subject = new \JVE\JvEvents\Domain\Model\Registrant();
	}

	public function tearDown()
	{
		unset($this->subject);
	}



	/**
	 * @test
	 */
	public function getFirstNameReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getFirstName()
		);
	}

	/**
	 * @test
	 */
	public function setFirstNameForStringSetsFirstName()
	{
		$this->subject->setFirstName('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'firstName',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getLastNameReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getLastName()
		);
	}

	/**
	 * @test
	 */
	public function setLastNameForStringSetsLastName()
	{
		$this->subject->setLastName('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'lastName',
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
	public function getGenderReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setGenderForStringSetsGender()
	{
		$this->subject->setGender('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'gender',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getCompanyReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function setCompanyForStringSetsCompany()
	{
		$this->subject->setCompany('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'company',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getDepartmentReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getDepartment()
		);
	}

	/**
	 * @test
	 */
	public function setDepartmentForStringSetsDepartment()
	{
		$this->subject->setDepartment('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'department',
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
	public function getLanguageReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setLanguageForStringSetsLanguage()
	{
		$this->subject->setLanguage('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'language',
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
	public function getAdditionalInfoReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getAdditionalInfo()
		);
	}

	/**
	 * @test
	 */
	public function setAdditionalInfoForStringSetsAdditionalInfo()
	{
		$this->subject->setAdditionalInfo('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'additionalInfo',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getPrivacyReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getPrivacy()
		);
	}

	/**
	 * @test
	 */
	public function setPrivacyForBoolSetsPrivacy()
	{
		$this->subject->setPrivacy(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'privacy',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getNewsletterReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getNewsletter()
		);
	}

	/**
	 * @test
	 */
	public function setNewsletterForBoolSetsNewsletter()
	{
		$this->subject->setNewsletter(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'newsletter',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getCustomerIdReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getCustomerId()
		);
	}

	/**
	 * @test
	 */
	public function setCustomerIdForStringSetsCustomerId()
	{
		$this->subject->setCustomerId('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'customerId',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getProfessionReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getProfession()
		);
	}

	/**
	 * @test
	 */
	public function setProfessionForStringSetsProfession()
	{
		$this->subject->setProfession('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'profession',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getRecallReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getRecall()
		);
	}

	/**
	 * @test
	 */
	public function setRecallForBoolSetsRecall()
	{
		$this->subject->setRecall(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'recall',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getContactIdReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getContactId()
		);
	}

	/**
	 * @test
	 */
	public function setContactIdForStringSetsContactId()
	{
		$this->subject->setContactId('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'contactId',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getUsernameReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getUsername()
		);
	}

	/**
	 * @test
	 */
	public function setUsernameForStringSetsUsername()
	{
		$this->subject->setUsername('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'username',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getMore1ReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getMore1()
		);
	}

	/**
	 * @test
	 */
	public function setMore1ForStringSetsMore1()
	{
		$this->subject->setMore1('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'more1',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getMore2ReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getMore2()
		);
	}

	/**
	 * @test
	 */
	public function setMore2ForStringSetsMore2()
	{
		$this->subject->setMore2('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'more2',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getMore3ReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getMore3()
		);
	}

	/**
	 * @test
	 */
	public function setMore3ForStringSetsMore3()
	{
		$this->subject->setMore3('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'more3',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getMore4ReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getMore4()
		);
	}

	/**
	 * @test
	 */
	public function setMore4ForStringSetsMore4()
	{
		$this->subject->setMore4('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'more4',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getMore5boolReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getMore5bool()
		);
	}

	/**
	 * @test
	 */
	public function setMore5boolForBoolSetsMore5bool()
	{
		$this->subject->setMore5bool(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'more5bool',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getMore6intReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setMore6intForIntSetsMore6int()
	{	}

	/**
	 * @test
	 */
	public function getMore7dateReturnsInitialValueForDateTime()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getMore7date()
		);
	}

	/**
	 * @test
	 */
	public function setMore7dateForDateTimeSetsMore7date()
	{
		$dateTimeFixture = new \DateTime();
		$this->subject->setMore7date($dateTimeFixture);

		$this->assertAttributeEquals(
			$dateTimeFixture,
			'more7date',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getMore8fileReturnsInitialValueForFileReference()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getMore8file()
		);
	}

	/**
	 * @test
	 */
	public function setMore8fileForFileReferenceSetsMore8file()
	{
		$fileReferenceFixture = new \TYPO3\CMS\Extbase\Domain\Model\FileReference();
		$this->subject->setMore8file($fileReferenceFixture);

		$this->assertAttributeEquals(
			$fileReferenceFixture,
			'more8file',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getPasswordReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getPassword()
		);
	}

	/**
	 * @test
	 */
	public function setPasswordForStringSetsPassword()
	{
		$this->subject->setPassword('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'password',
			$this->subject
		);
	}
}
