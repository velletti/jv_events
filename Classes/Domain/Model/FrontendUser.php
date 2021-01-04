<?php
namespace JVE\JvEvents\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Martin Heigermoser <martin.heigermoser@typovision.de>, typovision* - Agentur für multimediale Kommunikation
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
 * License
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser {


	/**
	 * @var int
	 */
	protected $crdate;


	/**
	 * @var int
	 */
	protected $deleted;

	/**
	 * @var int
	 */
	protected $disable;


    /**
     * @return int
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * @param int $crdate
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return int
     */
    public function getDisable()
    {
        return $this->disable;
    }

    /**
     * @param int $disable
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;
    }






}
