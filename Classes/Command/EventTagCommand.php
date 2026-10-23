<?php

declare(strict_types=1);

namespace JVelletti\JvEvents\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

#[AsCommand(
    name: 'jvevents:eventtag',
    description: 'Entfernt einen Tag von allen zukünftigen Events (optional nur für einen Organizer).'
)]
final class EventTagCommand extends Command
{
    private const TABLE_EVENT = 'tx_jvevents_domain_model_event';
    private const TABLE_ORGANIZER = 'tx_jvevents_domain_model_organizer';
    private const TABLE_LOCATION = 'tx_jvevents_domain_model_location';
    private const TABLE_TAG = 'tx_jvevents_domain_model_tag';
    private const TABLE_MM = 'tx_jvevents_event_tag_mm';
    private const CHUNK_SIZE = 50;
    private const DATE_FORMAT = 'd.M.y';
    private const SECONDS_PER_DAY = 86400;

    public function __construct(
        private readonly ConnectionPool $connectionPool
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('tag', 't', InputOption::VALUE_REQUIRED, 'UID des Tags', 10)
            ->addOption('organizer', 'o', InputOption::VALUE_REQUIRED, 'UID des Organizers (optional)')
            ->addOption('maxDays', 'm', InputOption::VALUE_REQUIRED, 'Max. Tage (start_date + maxDays < Location crdate => Skip)', 90)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Nur anzeigen, nichts ändern');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $tagUid = (int)$input->getOption('tag');
        $organizerOption = $input->getOption('organizer');
        $organizerUid = $organizerOption !== null ? (int)$organizerOption : null;
        $maxDays = (int)$input->getOption('maxDays');
        $maxSec = $maxDays * self::SECONDS_PER_DAY;
        $dryRun = (bool)$input->getOption('dry-run');

        if ($tagUid <= 0) {
            $io->error('invalid Tag UID.');
            return Command::INVALID;
        }
        if ($organizerUid !== null && $organizerUid <= 0) {
            $io->error('invalid Organizer Uid, must be > 0');
            return Command::INVALID;
        }
        if ($maxDays < 0) {
            $io->error('invalid value for maxDays, must be >= 0');
            return Command::INVALID;
        }

        $tag = $this->fetchTag($tagUid);
        if ($tag === null) {
            $io->error(sprintf('Tag with UID %d not found.', $tagUid));
            return Command::FAILURE;
        }
        $io->newLine(1);
        $io->title('Event Tag Removal');
        $io->info(sprintf('Tag: [%d] => "%s"', (int)$tag['uid'], (string)$tag['name']));
        if ($organizerUid !== null) {
            $io->info(sprintf('Restricted to Organizer Uid: %d', $organizerUid));
        }
        $io->newLine(1);

        $events = $this->findAffectedEvents($tagUid, $organizerUid);
        if ($events === []) {
            $io->success('no effected Events in the future.');
            return Command::SUCCESS;
        }

        $eventsByOrganizer = $this->groupByOrganizer($events);
        $organizers = $this->fetchOrganizers(array_keys($eventsByOrganizer));

        if ($dryRun) {
            $this->renderOrganizerTable($io, $eventsByOrganizer, $organizers);
        }

        if ($output->isVerbose() || $organizerUid !== null) {
            $this->renderEventsPerOrganizer($io, $eventsByOrganizer, $organizers);
        }

        if ($dryRun) {
            $io->note(sprintf('Dry-Run: %d Event(s) betroffen, keine Änderungen durchgeführt.', count($events)));
            return Command::SUCCESS;
        }

        $io->writeln(sprintf('maxDays: %d (maxSec: %d)', $maxDays, $maxSec));

        $toRemove = [];
        $skipped = 0;
        foreach ($events as $uid => $row) {
            $startDate = (int)$row['start_date'];
            $locationCrdate = (int)($row['location_crdate'] ?? 0);
            $skip = $startDate + $maxSec < $locationCrdate;

            $io->comment(sprintf(
                '[%d] %s %s – Location: %s (%s): %s',
                $uid,
                $this->formatDate($startDate),
                (string)$row['name'],
                (string)($row['location_name'] ?? '-'),
                $this->formatDate($locationCrdate),
                $skip ? 'Skipped' : 'Removed'
            ));

            if ($skip) {
                $skipped++;
                continue;
            }
            $toRemove[] = $uid;
        }

        $removed = $toRemove !== [] ? $this->removeTagFromEvents($tagUid, $toRemove) : 0;

        $io->success(sprintf(
            'Tag %d: %d Event(s) entfernt, %d Event(s) übersprungen (%d MM-Relation(en) gelöscht).',
            $tagUid,
            count($toRemove),
            $skipped,
            $removed
        ));

        return Command::SUCCESS;
    }

    private function fetchTag(int $tagUid): ?array
    {
        $qb = $this->getQueryBuilder(self::TABLE_TAG);
        $row = $qb
            ->select('uid', 'name')
            ->from(self::TABLE_TAG)
            ->where($qb->expr()->eq('uid', $qb->createNamedParameter($tagUid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        return $row === false ? null : $row;
    }

    /**
     * @return array<int,array<string,mixed>> eventUid => row
     */
    private function findAffectedEvents(int $tagUid, ?int $organizerUid): array
    {
        $qb = $this->getQueryBuilder(self::TABLE_EVENT);
        $qb
            ->select('e.uid', 'e.organizer', 'e.start_date', 'e.name')
            ->addSelect('l.name AS location_name', 'l.crdate AS location_crdate')
            ->from(self::TABLE_EVENT, 'e')
            ->join(
                'e',
                self::TABLE_MM,
                'mm',
                $qb->expr()->eq('mm.uid_local', $qb->quoteIdentifier('e.uid'))
            )
            ->leftJoin(
                'e',
                self::TABLE_LOCATION,
                'l',
                $qb->expr()->eq('l.uid', $qb->quoteIdentifier('e.location'))
            )
            ->where(
                $qb->expr()->eq('mm.uid_foreign', $qb->createNamedParameter($tagUid, Connection::PARAM_INT)),
                $qb->expr()->gt('e.start_date', $qb->createNamedParameter(time(), Connection::PARAM_INT))
            )
            ->orderBy('e.organizer')
            ->addOrderBy('e.start_date')
            ->addOrderBy('e.uid');

        if ($organizerUid !== null) {
            $qb->andWhere(
                $qb->expr()->eq('e.organizer', $qb->createNamedParameter($organizerUid, Connection::PARAM_INT))
            );
        }

        $events = [];
        foreach ($qb->executeQuery()->iterateAssociative() as $row) {
            $events[(int)$row['uid']] = $row;
        }

        return $events;
    }

    /**
     * @param array<int,array<string,mixed>> $events
     * @return array<int,array<int,array<string,mixed>>> organizerUid => [eventUid => row]
     */
    private function groupByOrganizer(array $events): array
    {
        $grouped = [];
        foreach ($events as $uid => $row) {
            $grouped[(int)$row['organizer']][$uid] = $row;
        }
        ksort($grouped);

        return $grouped;
    }

    /**
     * @param int[] $organizerUids
     * @return array<int,array<string,mixed>>
     */
    private function fetchOrganizers(array $organizerUids): array
    {
        $organizerUids = array_values(array_filter($organizerUids, static fn(int $uid): bool => $uid > 0));
        if ($organizerUids === []) {
            return [];
        }

        $qb = $this->getQueryBuilder(self::TABLE_ORGANIZER);
        $rows = $qb
            ->select('uid', 'name', 'email')
            ->from(self::TABLE_ORGANIZER)
            ->where($qb->expr()->in('uid', $qb->createNamedParameter($organizerUids, Connection::PARAM_INT_ARRAY)))
            ->executeQuery()
            ->fetchAllAssociative();

        $organizers = [];
        foreach ($rows as $row) {
            $organizers[(int)$row['uid']] = $row;
        }

        return $organizers;
    }

    private function renderOrganizerTable(SymfonyStyle $io, array $eventsByOrganizer, array $organizers): void
    {
        $tableRows = [];
        foreach ($eventsByOrganizer as $uid => $events) {
            $tableRows[] = [
                $uid,
                (string)($organizers[$uid]['name'] ?? '-'),
                (string)($organizers[$uid]['email'] ?? '-'),
                count($events),
            ];
        }

        $io->table(['UID', 'Name', 'E-Mail', 'Events'], $tableRows);
    }

    private function renderEventsPerOrganizer(SymfonyStyle $io, array $eventsByOrganizer, array $organizers): void
    {
        foreach ($eventsByOrganizer as $organizerUid => $events) {
            $io->section(sprintf(
                'Organizer [%d] %s',
                $organizerUid,
                (string)($organizers[$organizerUid]['name'] ?? '-')
            ));

            $tableRows = [];
            foreach ($events as $row) {
                $tableRows[] = [
                    (int)$row['uid'],
                    $this->formatDate($row['start_date']),
                    (string)$row['name'],
                    (string)($row['location_name'] ?? '-'),
                    $this->formatDate($row['location_crdate'] ?? null),
                ];
            }

            $io->table(['UID', 'Start', 'Name', 'Location', 'Location crdate'], $tableRows);
        }
    }

    private function formatDate(mixed $timestamp): string
    {
        $timestamp = (int)$timestamp;
        return $timestamp > 0 ? date(self::DATE_FORMAT, $timestamp) : '-';
    }

    /**
     * @param int[] $eventUids
     */
    private function removeTagFromEvents(int $tagUid, array $eventUids): int
    {
        $removed = 0;
        $mmConnection = $this->connectionPool->getConnectionForTable(self::TABLE_MM);
        $eventConnection = $this->connectionPool->getConnectionForTable(self::TABLE_EVENT);

        foreach (array_chunk($eventUids, self::CHUNK_SIZE) as $chunk) {
            $mmConnection->beginTransaction();
            try {
                $qb = $mmConnection->createQueryBuilder();
                $removed += $qb
                    ->delete(self::TABLE_MM)
                    ->where(
                        $qb->expr()->eq('uid_foreign', $qb->createNamedParameter($tagUid, Connection::PARAM_INT)),
                        $qb->expr()->in('uid_local', $qb->createNamedParameter($chunk, Connection::PARAM_INT_ARRAY))
                    )
                    ->executeStatement();

                foreach ($chunk as $eventUid) {
                    $countQb = $mmConnection->createQueryBuilder();
                    $count = (int)$countQb
                        ->count('*')
                        ->from(self::TABLE_MM)
                        ->where($countQb->expr()->eq(
                            'uid_local',
                            $countQb->createNamedParameter($eventUid, Connection::PARAM_INT)
                        ))
                        ->executeQuery()
                        ->fetchOne();

                    $eventConnection->update(
                        self::TABLE_EVENT,
                        ['tags' => $count, 'tstamp' => time()],
                        ['uid' => $eventUid],
                        [Connection::PARAM_INT, Connection::PARAM_INT, Connection::PARAM_INT]
                    );
                }

                $mmConnection->commit();
            } catch (\Throwable $e) {
                $mmConnection->rollBack();
                throw $e;
            }
        }

        return $removed;
    }

    private function getQueryBuilder(string $table): QueryBuilder
    {
        $qb = $this->connectionPool->getQueryBuilderForTable($table);
        $qb->getRestrictions()->removeAll()->add(new DeletedRestriction());
        return $qb;
    }
}