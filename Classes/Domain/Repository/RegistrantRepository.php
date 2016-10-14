<?php
namespace JVE\JvEvents\Domain\Repository;

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
 * The repository for Registrants
 */
class RegistrantRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface
	 */
	protected $defaultQuerySettings = null;

	protected $respectStoragePage = false;

	protected $respectSysLanguage = false;

	/**
	 * @param string $fingerprint
	 *
	 * @return array|bool|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function getByFingerprint($fingerprint = '') {
		if ($fingerprint == '' ) {
			return false ;
		}
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		// $query->getQuerySettings()->setIgnoreEnableFields(TRUE) ;

		return $query->matching( $query->equals("fingerprint", $fingerprint) )->execute() ;
	}


	/**
	 * @param string $email
	 * @param uid $event
	 * @return array
	 */
	public function findByFilter($email = '', $event = 0, $pid = 0 , $settings) {
		$query = $this->createQuery();
		$constraints = array();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->getQuerySettings()->setIgnoreEnableFields(TRUE) ;


		if($email <> '' ) {
			$constraints[] = $query->equals("email", $email);
		}
		if($event > 0 ) {
			$constraints[] = $query->equals("event", $event);
		}
		if($pid > 0 ) {
			$constraints[] = $query->equals("pid", $pid);
		}
		if(count($constraints) > 0 ) {
			$query->matching($query->logicalAnd($constraints));
		}

		$query->setLimit(1);
		$result = $query->execute();
		if ($settings['debug']  == 2 ) {
			$GLOBALS['TYPO3_DB']->debugOutput = 2;
			$GLOBALS['TYPO3_DB']->explainOutput = true;
			$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
			$result->toArray();
			die;
		}
		return $result;
	}
}