<?php
namespace JVelletti\JvEvents\Domain\Repository;

use JVelletti\JvEvents\Domain\Model\FrontendUser;
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
 * @method findOneByEmail(string $email)
 * @method findOneByUid(int $uid)
 * @method findOneByUsername(string $userName)
 */
# class UserRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {
class FrontendUserRepository extends BaseRepository {

    public function initializeObject() {

        $querysettings =  $this->createQuery()->getQuerySettings() ;
        $querysettings->setIgnoreEnableFields(TRUE) ;
        $querysettings->setRespectStoragePage(FALSE) ;
        $querysettings->setRespectSysLanguage(FALSE) ;

        $this->setDefaultQuerySettings($querysettings);
    }

    /**
     * find the logged in Frontend User and return the Details.
     * @param int $uid
     * @return FrontendUser|object the current user if found, otherwise NULL
     */
    public function findByUid($uid) {
        $constraints = array();
        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings() ;
        $querySettings->setRespectStoragePage(false);
     //   $querySettings->setRespectSysLanguage(FALSE);
     //   $querySettings->setIgnoreEnableFields(true) ;
        $query->setQuerySettings($querySettings) ;
        $constraints[] = $query->equals('uid', intval($uid));
        $query->matching($query->logicalAnd(...$constraints));

        return  $query->execute()->getFirst();

    }

	/**
  * find the logged in Frontend User and return the Details.
  * @return FrontendUser the current user if found, otherwise NULL
  */
 public function findActualUser() {
		$uid = (int)$GLOBALS['TSFE']->fe_user->user['uid'];
		return $this->findByUid($uid);
	}


}

