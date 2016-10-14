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
 * Test case for class JVE\JvEvents\Controller\TagController.
 *
 * @author Jörg velletti <jVelletti@allplan.com>
 */
class TagControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{

	/**
	 * @var \JVE\JvEvents\Controller\TagController
	 */
	protected $subject = NULL;

	public function setUp()
	{
		$this->subject = $this->getMock('JVE\\JvEvents\\Controller\\TagController', array('redirect', 'forward', 'addFlashMessage'), array(), '', FALSE);
	}

	public function tearDown()
	{
		unset($this->subject);
	}



	/**
	 * @test
	 */
	public function listActionFetchesAllTagsFromRepositoryAndAssignsThemToView()
	{

		$allTags = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array(), array(), '', FALSE);

		$tagRepository = $this->getMock('JVE\\JvEvents\\Domain\\Repository\\TagRepository', array('findAll'), array(), '', FALSE);
		$tagRepository->expects($this->once())->method('findAll')->will($this->returnValue($allTags));
		$this->inject($this->subject, 'tagRepository', $tagRepository);

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$view->expects($this->once())->method('assign')->with('tags', $allTags);
		$this->inject($this->subject, 'view', $view);

		$this->subject->listAction();
	}
}
