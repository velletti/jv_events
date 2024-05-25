<?php
namespace JVE\JvEvents\Command;

use JVelletti\JvEvents\Utility\SlugUtility;
use PDO;
use Symfony\Component\Console\Input\InputOption;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateSlugCommandController
 * @author Jörg Velletti <typo3@velletti.de>
 * @package JVelletti\JvEvents\Command
 */
class UsergroupCommand extends Command {



    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Updates usergroups of all users .')
            ->setName("Updates usergroups of all users -> --cmd=add --usergroup=3 -musthave=7 ")
            ->setHelp('Get list of Options: .' . LF . 'use the --help option.')
            ->addOption(
                'cmd',
                'c',
                InputOption::VALUE_REQUIRED,
                '--cmd=add (or remove)'
            )
            ->addOption(
                'ug',
                'u',
                InputOption::VALUE_REQUIRED,
                'enter Number of --usergroup=x to add/remove'
            )->addOption(
                'mh',
                "m" ,
                InputOption::VALUE_REQUIRED,
                'enter Number of --musthave=y that user must have'
            )->addOption(
                'rows',
                'r',
                InputOption::VALUE_OPTIONAL,
                'number of rows to be updates must be integer' );


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

        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        if ($input->getOption('ug')) {
            $usergroup = (int)$input->getOption('ug');
        } elseif ($input->getOption('u')) {
            $usergroup = (int)$input->getOption('u');
        }
        if ($input->getOption('mh')) {
            $musthave = (int)$input->getOption('mh');
        } elseif ($input->getOption('m')) {
            $musthave = (int)$input->getOption('m');
        }
        $cmd = "remove";
        if ($input->getOption('cmd')) {
            $cmd = ($input->getOption('cmd') == "add") ? "add" : "remove";
        } elseif ($input->getOption('c')) {
            $cmd = ($input->getOption('c') == "a") ? "add" : "remove";
        }
        $maxRows = 999999999999 ;
        if ($input->getOption('rows') ) {
            $maxRows = (int)$input->getOption('rows') ;
            $io->writeln('max Rows to be updated was set to '. $maxRows );

        }

        $io->writeln( "Command will '" . $cmd . "' group: '" .  $usergroup . "' from fe_users if user has Group: " . $musthave );
        if ( $musthave == 0 || $usergroup == 0 ) {
            $io->writeln( "musthave ($musthave)  or usergroup ($usergroup) not set " );
            return 1 ;
        }
        $progress = false ;
        $table = "fe_users" ;
		$qb = $this->getQueryBuilder($table) ;
        if ( $cmd == "add") {
            $rows = $qb->select("uid" , "usergroup")->from($table)
                ->where($qb->expr()->notInSet("usergroup" , $usergroup ))
                ->andWhere($qb->expr()->inSet("usergroup" , $musthave ))
                ->executeQuery() ;
        } else {
            $rows = $qb->select("uid" , "usergroup")->from($table)
                ->where($qb->expr()->inSet("usergroup" , $usergroup ))
                ->andWhere($qb->expr()->inSet("usergroup" , $musthave ))
                ->executeQuery() ;
        }

        $total = $rows->rowCount()  ;
        if( $io->getVerbosity() > 16 ) {
            $io->writeln( $table . " - rowCount: " . $total );
            $progress = $io->createProgressBar($total) ;
        }

        $i = 0 ;
        $debugOutput = "" ;
        while ( $row = $rows->fetchAssociative()) {
            if( $io->getVerbosity() > 16 ) {
                $progress->advance();
            }
            if ( $cmd == "add") {
                $newgroup = $row['usergroup'] . "," . $usergroup ;
            } else {
                // Remove the specified value from the array
                $usergroups = array_diff( GeneralUtility::trimExplode( "," , $row['usergroup'] ) , [$usergroup]);

                // Join the array elements back into a comma-separated string
                $newgroup = implode(',', $usergroups);
            }
            $i++ ;
            $this->setUsergroup( $table , $row['uid']  ,  $newgroup ) ;
            if( $i >= $maxRows ) {
                break ;
            }
        }

        if( $io->getVerbosity() > 16 ) {
            // @extensionScannerIgnoreLine
            if( $i >= $maxRows ) {
                $progress->finish();
            }

            $io->writeln(" ") ;
            $io->writeln("Finished ( " . $table . " updated: "   . $i . "/" . $total .  " records) ");
        }
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


    /**
     * @param $table
     * @param $uid
     * @param $slugField
     * @param $slug
     */
    private function setUsergroup($table , $uid ,  $usergroup)
    {
        $qb = $this->getQueryBuilder($table) ;
        $qb->update($table)->set( 'usergroup' , $usergroup)->where($qb->expr()->eq("uid" , $qb->createNamedParameter($uid , PDO::PARAM_INT)))->executeStatement() ;

    }


}