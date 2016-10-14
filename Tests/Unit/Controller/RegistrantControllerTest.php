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
 * Test case for class JVE\JvEvents\Controller\RegistrantController.
 *
 * @author Jörg velletti <jVelletti@allplan.com>
 */
class RegistrantControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{

	/**
	 * @var \JVE\JvEvents\Controller\RegistrantController
	 */
	protected $subject = NULL;

	public function setUp()
	{
		$this->subject = $this->getMock('JVE\\JvEvents\\Controller\\RegistrantController', array('redirect', 'forward', 'addFlashMessage'), array(), '', FALSE);
	}

	public function tearDown()
	{
		unset($this->subject);
	}



	/**
	 * @test
	 */
	public function listActionFetchesAllRegistrantsFromRepositoryAndAssignsThemToView()
	{

		$allRegistrants = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array(), array(), '', FALSE);

		$registrantRepository = $this->getMock('JVE\\JvEvents\\Domain\\Repository\\RegistrantRepository', array('findAll'), array(), '', FALSE);
		$registrantRepository->expects($this->once())->method('findAll')->will($this->returnValue($allRegistrants));
		$this->inject($this->subject, 'registrantRepository', $registrantRepository);

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$view->expects($this->once())->method('assign')->with('registrants', $allRegistrants);
		$this->inject($this->subject, 'view', $view);

		$this->subject->listAction();
	}

	/**
	 * @test
	 */
	public function showActionAssignsTheGivenRegistrantToView()
	{
		$registrant = new \JVE\JvEvents\Domain\Model\Registrant();

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$this->inject($this->subject, 'view', $view);
		$view->expects($this->once())->method('assign')->with('registrant', $registrant);

		$this->subject->showAction($registrant);
	}
}
