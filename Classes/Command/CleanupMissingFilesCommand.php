<?php
namespace JVelletti\JvEvents\Command;

use PDO;
use Symfony\Component\Console\Input\InputOption;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CleanupMissingFilesCommand
 * @author Jörg Velletti <typo3@velletti.de>
 * @package JVelletti\JvEvents\Command
 */
class CleanupMissingFilesCommand extends Command {


    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Updates usergroups of all users .')
            ->setName("Remove sys file references if sysfile entry or file missing -> --user=1234 ")
            ->setHelp('Get list of Options: .' . LF . 'use the --help option.')
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                '--user=1234 (uid of user)'
            )->addOption(
                'dryrun',
                'd',
                InputOption::VALUE_OPTIONAL,
                '--dryrun=1 for just testing'
            ) ;


    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $rows = null;
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $usergroup = 0;
        $musthave = 0;
        $cmd = "";

        if ($input->getOption('user')) {
            $user = (int)$input->getOption('user');
            $io->writeln("Command will only work on data of : " . $user);
        }
        $dryrun = false ;
        if ($input->getOption('dryrun')) {
            $io->writeln("Dry Run active. Will do nothing, just show" );
            $dryrun = true ;
        } else {
            $doit = $io->confirm('Do you want to continue?', false);
            if (!$doit) {
                $io->writeln("Aborted by user");
                return 0;
            }
        }
        $progress = false ;
        $table = "tx_jvmediaconnector_domain_model_media" ;
		$qb = $this->getQueryBuilder($table) ;
        $qb->select("a.uid" , "a.feuser" , "a.sysfile" , "ref.uid_local" , "ref.uid_foreign")->from($table , "a") ;
        $qb->leftJoin("a" , "sys_file_reference" , "ref" , $qb->expr()->eq("ref.uid" , $qb->quoteIdentifier("a.sysfile"))) ;
        if ($user) {
            $qb->where($qb->expr()->eq("feuser" , $qb->createNamedParameter($user , PDO::PARAM_INT))) ;
        }
        $rows = $qb->executeQuery() ;

        $total = $rows->rowCount()  ;
        $io->writeln( $table . " - rowCount: " . $total );
        $io->section("Start:");
        $progress = $io->createProgressBar($total) ;
        $i = 0 ;
        $wrong = 0;
        $repaired = 0;
        $debugOutput = "" ;
        while ( $row = $rows->fetchAssociative()) {
            $progress->advance();
            if( $io->getVerbosity() > 32  ) {

                $io->writeln(str_pad( $i , 5 , " " , STR_PAD_LEFT) . " - Uid:" . $row["uid"] . " - Ref:" . $row["sysfile"]  . " - File:" . $row["uid_local"] . " - A:" . $row["uid_foreign"] ) ;
            }
            if ($row["uid_local"] == 0) {
                $wrong++ ;
                if( $io->getVerbosity() > 16  ) {
                    $io->writeln("No sysfile reference found for uid:" . $row["uid"] . " - File:" . $row["uid_local"] . " - A:" . $row["uid_foreign"]);
                }
                if (!$dryrun) {
                    $qbr = $this->getQueryBuilder($table) ;
                    $qbr->update($table)->where($qbr->expr()->eq("uid" , $qbr->createNamedParameter($row["uid"] , PDO::PARAM_INT)))->set("hidden" , 1)
                        ->executeStatement() ;
                    $repaired++ ;
                }
            } else {
                $qbr = $this->getQueryBuilder("sys_file") ;
                $qbr->select("sys_file.uid" , "sys_file.identifier", "storage.name")
                    ->from("sys_file")
                    ->leftJoin("sys_file" , "sys_file_storage" , "storage" , $qbr->expr()->eq("storage.uid" , $qbr->quoteIdentifier("sys_file.storage")))
                    ->where($qbr->expr()
                        ->eq("sys_file.uid" , $qbr->createNamedParameter($row["uid_local"] , PDO::PARAM_INT))) ;
                $file = $qbr->executeQuery() ;
                if ($file->rowCount() == 0) {
                    $wrong++ ;
                    $io->writeln(" ") ;
                    $io->error("No sysfile found for uid:" . $row["uid"] . " - File ID:" . $row["uid_local"] );
                    if (!$dryrun) {
                        $qbr2 = $this->getQueryBuilder($table) ;
                        $qbr2->update($table)->where($qbr2->expr()->eq("uid" , $qbr2->createNamedParameter($row["uid"] , PDO::PARAM_INT)))->set("hidden" , 1)
                            ->executeStatement() ;
                        $repaired++ ;
                    }
                } else {
                    $sysfile = $file->fetchAssociative() ;
                    if (  strpos( $sysfile["name"] , "fileadmin") == false ) {
                        $fileName = GeneralUtility::getFileAbsFileName("fileadmin" . $sysfile["identifier"]) ;
                    }
                    if ( !$fileName || !file_exists($fileName) ) {
                        $wrong++ ;
                        $io->writeln(" ") ;
                        $io->error("File not found for uid:" . $row["uid"] . " - File ID:" . $row["uid_local"] . " - File:" . $fileName );
                        if (!$dryrun) {
                            $qbr3 = $this->getQueryBuilder($table) ;
                            $qbr3->update($table)->where($qbr3->expr()->eq("uid" , $qbr3->createNamedParameter($row["uid"] , PDO::PARAM_INT)))->set("hidden" , 1)
                                ->executeStatement() ;
                            $qbr4 = $this->getQueryBuilder("sys_file") ;
                            $qbr4->update("sys_file")->where($qbr4->expr()->eq("uid" , $qbr4->createNamedParameter($row["uid_local"] , PDO::PARAM_INT)))->set("missing" , 1)
                                ->executeStatement() ;
                            $repaired++ ;

                        }
                    } else {
                        if( $io->getVerbosity() > 32  ) {
                            $io->writeln("Sys_file: ID: " . $sysfile["uid"]  . " ". $fileName);
                        }
                    }

                }
            }

            $i++ ;
        }
        $io->writeln(" ") ;
        $progress->finish();
        $io->section("Result:");
        $io->writeln("Finished ( Found '" . $wrong . "' Errors and worked on '"   . $repaired . "' of '" . $i . "' records) ");
        $io->writeln("====================================================================") ;
        $io->writeln(" ") ;

        return 0 ;
	}

    /**
     * @param string $table
     * @return QueryBuilder
     */
	private function getQueryBuilder(string $table): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */
        return $connectionPool->getConnectionForTable($table)->createQueryBuilder();
	}



}