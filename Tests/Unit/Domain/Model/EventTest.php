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
 * Test case for class \JVE\JvEvents\Domain\Model\Event.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @author Jörg velletti <jVelletti@allplan.com>
 */
class EventTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
	/**
	 * @var \JVE\JvEvents\Domain\Model\Event
	 */
	protected $subject = NULL;

	public function setUp()
	{
		$this->subject = new \JVE\JvEvents\Domain\Model\Event();
	}

	public function tearDown()
	{
		unset($this->subject);
	}



	/**
	 * @test
	 */
	public function getEventTypeReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setEventTypeForIntSetsEventType()
	{	}

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
	public function getTeaserReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function setTeaserForStringSetsTeaser()
	{
		$this->subject->setTeaser('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'teaser',
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
	public function getFilesReturnsInitialValueForFileReference()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getFiles()
		);
	}

	/**
	 * @test
	 */
	public function setFilesForFileReferenceSetsFiles()
	{
		$fileReferenceFixture = new \TYPO3\CMS\Extbase\Domain\Model\FileReference();
		$this->subject->setFiles($fileReferenceFixture);

		$this->assertAttributeEquals(
			$fileReferenceFixture,
			'files',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getAllDayReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getAllDay()
		);
	}

	/**
	 * @test
	 */
	public function setAllDayForBoolSetsAllDay()
	{
		$this->subject->setAllDay(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'allDay',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getStartDateReturnsInitialValueForDateTime()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getStartDate()
		);
	}

	/**
	 * @test
	 */
	public function setStartDateForDateTimeSetsStartDate()
	{
		$dateTimeFixture = new \DateTime();
		$this->subject->setStartDate($dateTimeFixture);

		$this->assertAttributeEquals(
			$dateTimeFixture,
			'startDate',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getStartTimeReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setStartTimeForIntSetsStartTime()
	{	}

	/**
	 * @test
	 */
	public function getEndDateReturnsInitialValueForDateTime()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getEndDate()
		);
	}

	/**
	 * @test
	 */
	public function setEndDateForDateTimeSetsEndDate()
	{
		$dateTimeFixture = new \DateTime();
		$this->subject->setEndDate($dateTimeFixture);

		$this->assertAttributeEquals(
			$dateTimeFixture,
			'endDate',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getEndTimeReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setEndTimeForIntSetsEndTime()
	{	}

	/**
	 * @test
	 */
	public function getAccessReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getAccess()
		);
	}

	/**
	 * @test
	 */
	public function setAccessForStringSetsAccess()
	{
		$this->subject->setAccess('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'access',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getWithRegistrationReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getWithRegistration()
		);
	}

	/**
	 * @test
	 */
	public function setWithRegistrationForBoolSetsWithRegistration()
	{
		$this->subject->setWithRegistration(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'withRegistration',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationUntilReturnsInitialValueForDateTime()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getRegistrationUntil()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationUntilForDateTimeSetsRegistrationUntil()
	{
		$dateTimeFixture = new \DateTime();
		$this->subject->setRegistrationUntil($dateTimeFixture);

		$this->assertAttributeEquals(
			$dateTimeFixture,
			'registrationUntil',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationAccessReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getRegistrationAccess()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationAccessForStringSetsRegistrationAccess()
	{
		$this->subject->setRegistrationAccess('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'registrationAccess',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getStoreInCitrixReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getStoreInCitrix()
		);
	}

	/**
	 * @test
	 */
	public function setStoreInCitrixForBoolSetsStoreInCitrix()
	{
		$this->subject->setStoreInCitrix(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'storeInCitrix',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getCitrixUidReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getCitrixUid()
		);
	}

	/**
	 * @test
	 */
	public function setCitrixUidForStringSetsCitrixUid()
	{
		$this->subject->setCitrixUid('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'citrixUid',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getStoreInSalesForceReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getStoreInSalesForce()
		);
	}

	/**
	 * @test
	 */
	public function setStoreInSalesForceForBoolSetsStoreInSalesForce()
	{
		$this->subject->setStoreInSalesForce(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'storeInSalesForce',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getMarketingProcessIdReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getMarketingProcessId()
		);
	}

	/**
	 * @test
	 */
	public function setMarketingProcessIdForStringSetsMarketingProcessId()
	{
		$this->subject->setMarketingProcessId('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'marketingProcessId',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getSalesForceRecordTypeReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getSalesForceRecordType()
		);
	}

	/**
	 * @test
	 */
	public function setSalesForceRecordTypeForStringSetsSalesForceRecordType()
	{
		$this->subject->setSalesForceRecordType('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'salesForceRecordType',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getSalesForceEventIdReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getSalesForceEventId()
		);
	}

	/**
	 * @test
	 */
	public function setSalesForceEventIdForStringSetsSalesForceEventId()
	{
		$this->subject->setSalesForceEventId('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'salesForceEventId',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getSalesForceSessionIdReturnsInitialValueForDateTime()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getSalesForceSessionId()
		);
	}

	/**
	 * @test
	 */
	public function setSalesForceSessionIdForDateTimeSetsSalesForceSessionId()
	{
		$dateTimeFixture = new \DateTime();
		$this->subject->setSalesForceSessionId($dateTimeFixture);

		$this->assertAttributeEquals(
			$dateTimeFixture,
			'salesForceSessionId',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getAvailableSeatsReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setAvailableSeatsForIntSetsAvailableSeats()
	{	}

	/**
	 * @test
	 */
	public function getRegisteredSeatsReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setRegisteredSeatsForIntSetsRegisteredSeats()
	{	}

	/**
	 * @test
	 */
	public function getUnconfirmedSeatsReturnsInitialValueForDateTime()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getUnconfirmedSeats()
		);
	}

	/**
	 * @test
	 */
	public function setUnconfirmedSeatsForDateTimeSetsUnconfirmedSeats()
	{
		$dateTimeFixture = new \DateTime();
		$this->subject->setUnconfirmedSeats($dateTimeFixture);

		$this->assertAttributeEquals(
			$dateTimeFixture,
			'unconfirmedSeats',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getNotifyOrganiserReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getNotifyOrganiser()
		);
	}

	/**
	 * @test
	 */
	public function setNotifyOrganiserForBoolSetsNotifyOrganiser()
	{
		$this->subject->setNotifyOrganiser(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'notifyOrganiser',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getNotifyRegistrantReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getNotifyRegistrant()
		);
	}

	/**
	 * @test
	 */
	public function setNotifyRegistrantForBoolSetsNotifyRegistrant()
	{
		$this->subject->setNotifyRegistrant(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'notifyRegistrant',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getSubjectOrganizerReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getSubjectOrganizer()
		);
	}

	/**
	 * @test
	 */
	public function setSubjectOrganizerForStringSetsSubjectOrganizer()
	{
		$this->subject->setSubjectOrganizer('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'subjectOrganizer',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getTextOrganizerReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getTextOrganizer()
		);
	}

	/**
	 * @test
	 */
	public function setTextOrganizerForStringSetsTextOrganizer()
	{
		$this->subject->setTextOrganizer('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'textOrganizer',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getSubjectRegistrantReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getSubjectRegistrant()
		);
	}

	/**
	 * @test
	 */
	public function setSubjectRegistrantForStringSetsSubjectRegistrant()
	{
		$this->subject->setSubjectRegistrant('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'subjectRegistrant',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getTextRegistrantReturnsInitialValueForString()
	{
		$this->assertSame(
			'',
			$this->subject->getTextRegistrant()
		);
	}

	/**
	 * @test
	 */
	public function setTextRegistrantForStringSetsTextRegistrant()
	{
		$this->subject->setTextRegistrant('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'textRegistrant',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getNeedToConfirmReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getNeedToConfirm()
		);
	}

	/**
	 * @test
	 */
	public function setNeedToConfirmForBoolSetsNeedToConfirm()
	{
		$this->subject->setNeedToConfirm(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'needToConfirm',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getIsRecurringReturnsInitialValueForBool()
	{
		$this->assertSame(
			FALSE,
			$this->subject->getIsRecurring()
		);
	}

	/**
	 * @test
	 */
	public function setIsRecurringForBoolSetsIsRecurring()
	{
		$this->subject->setIsRecurring(TRUE);

		$this->assertAttributeEquals(
			TRUE,
			'isRecurring',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getFrequencyReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setFrequencyForIntSetsFrequency()
	{	}

	/**
	 * @test
	 */
	public function getFreqExceptionReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setFreqExceptionForIntSetsFreqException()
	{	}

	/**
	 * @test
	 */
	public function getIsExceptionForReturnsInitialValueForInt()
	{	}

	/**
	 * @test
	 */
	public function setIsExceptionForForIntSetsIsExceptionFor()
	{	}

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
	public function getLocationReturnsInitialValueForLocation()
	{
		$this->assertEquals(
			NULL,
			$this->subject->getLocation()
		);
	}

	/**
	 * @test
	 */
	public function setLocationForLocationSetsLocation()
	{
		$locationFixture = new \JVE\JvEvents\Domain\Model\Location();
		$this->subject->setLocation($locationFixture);

		$this->assertAttributeEquals(
			$locationFixture,
			'location',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getRegistrantReturnsInitialValueForRegistrant()
	{
		$newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->subject->getRegistrant()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrantForObjectStorageContainingRegistrantSetsRegistrant()
	{
		$registrant = new \JVE\JvEvents\Domain\Model\Registrant();
		$objectStorageHoldingExactlyOneRegistrant = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$objectStorageHoldingExactlyOneRegistrant->attach($registrant);
		$this->subject->setRegistrant($objectStorageHoldingExactlyOneRegistrant);

		$this->assertAttributeEquals(
			$objectStorageHoldingExactlyOneRegistrant,
			'registrant',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function addRegistrantToObjectStorageHoldingRegistrant()
	{
		$registrant = new \JVE\JvEvents\Domain\Model\Registrant();
		$registrantObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('attach'), array(), '', FALSE);
		$registrantObjectStorageMock->expects($this->once())->method('attach')->with($this->equalTo($registrant));
		$this->inject($this->subject, 'registrant', $registrantObjectStorageMock);

		$this->subject->addRegistrant($registrant);
	}

	/**
	 * @test
	 */
	public function removeRegistrantFromObjectStorageHoldingRegistrant()
	{
		$registrant = new \JVE\JvEvents\Domain\Model\Registrant();
		$registrantObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('detach'), array(), '', FALSE);
		$registrantObjectStorageMock->expects($this->once())->method('detach')->with($this->equalTo($registrant));
		$this->inject($this->subject, 'registrant', $registrantObjectStorageMock);

		$this->subject->removeRegistrant($registrant);

	}

	/**
	 * @test
	 */
	public function getEventCategoryReturnsInitialValueForCategory()
	{
		$newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->subject->getEventCategory()
		);
	}

	/**
	 * @test
	 */
	public function setEventCategoryForObjectStorageContainingCategorySetsEventCategory()
	{
		$eventCategory = new \JVE\JvEvents\Domain\Model\Category();
		$objectStorageHoldingExactlyOneEventCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$objectStorageHoldingExactlyOneEventCategory->attach($eventCategory);
		$this->subject->setEventCategory($objectStorageHoldingExactlyOneEventCategory);

		$this->assertAttributeEquals(
			$objectStorageHoldingExactlyOneEventCategory,
			'eventCategory',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function addEventCategoryToObjectStorageHoldingEventCategory()
	{
		$eventCategory = new \JVE\JvEvents\Domain\Model\Category();
		$eventCategoryObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('attach'), array(), '', FALSE);
		$eventCategoryObjectStorageMock->expects($this->once())->method('attach')->with($this->equalTo($eventCategory));
		$this->inject($this->subject, 'eventCategory', $eventCategoryObjectStorageMock);

		$this->subject->addEventCategory($eventCategory);
	}

	/**
	 * @test
	 */
	public function removeEventCategoryFromObjectStorageHoldingEventCategory()
	{
		$eventCategory = new \JVE\JvEvents\Domain\Model\Category();
		$eventCategoryObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('detach'), array(), '', FALSE);
		$eventCategoryObjectStorageMock->expects($this->once())->method('detach')->with($this->equalTo($eventCategory));
		$this->inject($this->subject, 'eventCategory', $eventCategoryObjectStorageMock);

		$this->subject->removeEventCategory($eventCategory);

	}

	/**
	 * @test
	 */
	public function getTagsReturnsInitialValueForTag()
	{
		$newObjectStorage = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->subject->getTags()
		);
	}

	/**
	 * @test
	 */
	public function setTagsForObjectStorageContainingTagSetsTags()
	{
		$tag = new \JVE\JvEvents\Domain\Model\Tag();
		$objectStorageHoldingExactlyOneTags = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$objectStorageHoldingExactlyOneTags->attach($tag);
		$this->subject->setTags($objectStorageHoldingExactlyOneTags);

		$this->assertAttributeEquals(
			$objectStorageHoldingExactlyOneTags,
			'tags',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function addTagToObjectStorageHoldingTags()
	{
		$tag = new \JVE\JvEvents\Domain\Model\Tag();
		$tagsObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('attach'), array(), '', FALSE);
		$tagsObjectStorageMock->expects($this->once())->method('attach')->with($this->equalTo($tag));
		$this->inject($this->subject, 'tags', $tagsObjectStorageMock);

		$this->subject->addTag($tag);
	}

	/**
	 * @test
	 */
	public function removeTagFromObjectStorageHoldingTags()
	{
		$tag = new \JVE\JvEvents\Domain\Model\Tag();
		$tagsObjectStorageMock = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array('detach'), array(), '', FALSE);
		$tagsObjectStorageMock->expects($this->once())->method('detach')->with($this->equalTo($tag));
		$this->inject($this->subject, 'tags', $tagsObjectStorageMock);

		$this->subject->removeTag($tag);

	}
}
