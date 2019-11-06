<?php
namespace JVE\JvEvents\Domain\Repository;
/***************************************************************
 * Copyright notice
 *
 * (c) 2011-13 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A repository for static info tables country
 */
class StaticCountryRepository extends \JVE\JvEvents\Domain\Repository\BaseRepository {
	/**
	 * Find all countries despecting the storage page
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findAll() {
		$query = $this->createQuery();
		$query
			->getQuerySettings()
			->setRespectStoragePage(FALSE);

		return $query->execute();
	}


    /**
     * Find countries by iso2 codes despection the storage page
     * @param array $cnIso2
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
	public function findByCnIso2(array $cnIso2) {

		$query = $this->createQuery();
		$query
			->getQuerySettings()
			->setRespectStoragePage(FALSE);

		$query->matching($query->in('cn_iso_2', $cnIso2));

		return $query->execute();
	}
}
