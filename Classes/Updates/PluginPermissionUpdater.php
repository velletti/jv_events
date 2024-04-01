<?php

declare(strict_types=1);

/*
 * This file is part of the "news" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace JVelletti\JvEvents\Updates;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
#[UpgradeWizard('jveEvents_PluginPermissionUpdater')]
class PluginPermissionUpdater implements UpgradeWizardInterface
{
    public function getIdentifier(): string
    {
        return 'jveEvents_PluginPermissionUpdater';
    }

    public function getTitle(): string
    {
        return 'EXT:JvEvents: Migrate plugin permissions';
    }

    public function getDescription(): string
    {
        $description = 'This update wizard updates all permissions and allows **all** news plugins instead of the previous single plugin.';
        $description .= ' Count of affected groups: ' . count($this->getMigrationRecords());
        return $description;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function updateNecessary(): bool
    {
        return $this->checkIfWizardIsRequired();
    }

    public function executeUpdate(): bool
    {
        return $this->performMigration();
    }

    public function checkIfWizardIsRequired(): bool
    {
        return count($this->getMigrationRecords()) > 0;
    }

    public function performMigration(): bool
    {
        $records = $this->getMigrationRecords();

        foreach ($records as $record) {
            $this->updateRow($record);
        }

        return true;
    }

    protected function getMigrationRecords(): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('be_groups');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('uid', 'explicit_allowdeny')
            ->from('be_groups')
            ->where(
                $queryBuilder->expr()->like(
                    'explicit_allowdeny',
                    $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards('tt_content:list_type:news_pi1') . '%')
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function updateRow(array $row): void
    {
        $default = '' ;
        $pluginArray = \JVelletti\JvEvents\Utility\PluginUtility::getPluginArray() ;

        foreach ( $pluginArray as $plugin ) {
            $default .= "tt_content:CType:" . str_replace('_', '', 'jv_events') . '_' . strtolower($plugin['name']) . ",";
        }
        $default = rtrim ( "," , $default ) ;

        $searchReplace = [
            'tt_content:list_type:jvevents_events:ALLOW' => $default,
            'tt_content:list_type:jvevents_events:DENY' => '',
            'tt_content:list_type:jvevents_events' => $default,
        ];

        $newList = str_replace(array_keys($searchReplace), array_values($searchReplace), $row['explicit_allowdeny']);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_groups');
        $queryBuilder->update('be_groups')
            ->set('explicit_allowdeny', $newList)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($row['uid'], Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }
}
