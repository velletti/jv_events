<?php
namespace JVE\JvEvents\Controller;


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
 * TagController
 */
class TagController extends BaseController
{

    /**
     * tagRepository
     *
     * @var \JVE\JvEvents\Domain\Repository\TagRepository
     * @inject
     */
    protected $tagRepository = NULL;

    /**
     * action init
     *
     * @return void
     */
    public function initializeAction()
    {
        if( !$this->request->hasArgument('tag')) {
            // ToDo redirect to error
        }
        parent::initializeAction() ;

    }
    
    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $tags = $this->tagRepository->findAll();
        $this->view->assign('tags', $tags);
    }

}