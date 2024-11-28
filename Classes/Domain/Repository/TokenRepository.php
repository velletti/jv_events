<?php
namespace JVelletti\JvEvents\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class TokenRepository extends Repository
{
    public function findByFilter(array $filter)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setRespectStoragePage(false);

        if (isset($filter['token'])) {
            $query->matching($query->equals('token', $filter['token'] ));
        }

        if (isset($filter['feuser'])) {
            $query->matching($query->equals('feuser', $filter['feuser']));
        }

        if (isset($filter['license'])) {
            $query->matching($query->equals('license', $filter['license']));
        }

        return $query->execute();
    }
}