<?php
namespace JVE\JvEvents\Tests\Unit\Controller;
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
 * Test case for class JVE\JvEvents\Controller\OrganizerController.
 *
 * @author Jörg velletti <jVelletti@allplan.com>
 */
class OrganizerControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{

	/**
	 * @var \JVE\JvEvents\Controller\OrganizerController
	 */
	protected $subject = NULL;

	public function setUp()
	{
		$this->subject = $this->getMock('JVE\\JvEvents\\Controller\\OrganizerController', array('redirect', 'forward', 'addFlashMessage'), array(), '', FALSE);
	}

	public function tearDown()
	{
		unset($this->subject);
	}



	/**
	 * @test
	 */
	public function listActionFetchesAllOrganizersFromRepositoryAndAssignsThemToView()
	{

		$allOrganizers = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array(), array(), '', FALSE);

		$organizerRepository = $this->getMock('JVE\\JvEvents\\Domain\\Repository\\OrganizerRepository', array('findAll'), array(), '', FALSE);
		$organizerRepository->expects($this->once())->method('findAll')->will($this->returnValue($allOrganizers));
		$this->inject($this->subject, 'organizerRepository', $organizerRepository);

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$view->expects($this->once())->method('assign')->with('organizers', $allOrganizers);
		$this->inject($this->subject, 'view', $view);

		$this->subject->listAction();
	}

	/**
	 * @test
	 */
	public function showActionAssignsTheGivenOrganizerToView()
	{
		$organizer = new \JVE\JvEvents\Domain\Model\Organizer();

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$this->inject($this->subject, 'view', $view);
		$view->expects($this->once())->method('assign')->with('organizer', $organizer);

		$this->subject->showAction($organizer);
	}

	/**
	 * @test
	 */
	public function createActionAddsTheGivenOrganizerToOrganizerRepository()
	{
		$organizer = new \JVE\JvEvents\Domain\Model\Organizer();

		$organizerRepository = $this->getMock('JVE\\JvEvents\\Domain\\Repository\\OrganizerRepository', array('add'), array(), '', FALSE);
		$organizerRepository->expects($this->once())->method('add')->with($organizer);
		$this->inject($this->subject, 'organizerRepository', $organizerRepository);

		$this->subject->createAction($organizer);
	}

	/**
	 * @test
	 */
	public function editActionAssignsTheGivenOrganizerToView()
	{
		$organizer = new \JVE\JvEvents\Domain\Model\Organizer();

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$this->inject($this->subject, 'view', $view);
		$view->expects($this->once())->method('assign')->with('organizer', $organizer);

		$this->subject->editAction($organizer);
	}


	/**
	 * @test
	 */
	public function updateActionUpdatesTheGivenOrganizerInOrganizerRepository()
	{
		$organizer = new \JVE\JvEvents\Domain\Model\Organizer();

		$organizerRepository = $this->getMock('JVE\\JvEvents\\Domain\\Repository\\OrganizerRepository', array('update'), array(), '', FALSE);
		$organizerRepository->expects($this->once())->method('update')->with($organizer);
		$this->inject($this->subject, 'organizerRepository', $organizerRepository);

		$this->subject->updateAction($organizer);
	}

	/**
	 * @test
	 */
	public function deleteActionRemovesTheGivenOrganizerFromOrganizerRepository()
	{
		$organizer = new \JVE\JvEvents\Domain\Model\Organizer();

		$organizerRepository = $this->getMock('JVE\\JvEvents\\Domain\\Repository\\OrganizerRepository', array('remove'), array(), '', FALSE);
		$organizerRepository->expects($this->once())->method('remove')->with($organizer);
		$this->inject($this->subject, 'organizerRepository', $organizerRepository);

		$this->subject->deleteAction($organizer);
	}
}
