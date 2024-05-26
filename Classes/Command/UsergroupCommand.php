<?php
namespace JVelletti\JvEvents\Command;

use JVelletti\JvEvents\Utility\SlugUtility;
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
 * Class UpdateSlugCommandController
 * @author Jörg Velletti <typo3@velletti.de>
 * @package JVelletti\JvEvents\Command
 */
class UsergroupCommand extends Command {

    private array $ALLOWED_CMD= [ "add" , "remove" , "count"] ;

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
                InputOption::VALUE_OPTIONAL,
                '--cmd=add (or remove)'
            )
            ->addOption(
                'usergroup',
                'u',
                InputOption::VALUE_OPTIONAL,
                'enter Number of --usergroup=x to add/remove'
            )->addOption(
                'musthave',
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
        if ($input->getOption('usergroup')) {
            $usergroup = (int)$input->getOption('usergroup');
        }
        if ($input->getOption('musthave')) {
            $musthave = (int)$input->getOption('musthave');
        }
        $cmd = "remove";
        if ($input->getOption('cmd')) {
            $cmd = $input->getOption('cmd') ;
        } else {
            $cmd = "count" ;
        }
        if ( !in_array($cmd , $this->ALLOWED_CMD) ) {
            $io->writeln('CMD not allowed : given  '. $cmd );
            return 1 ;
        }

        $maxRows = 999999999999 ;
        if ($input->getOption('rows') ) {
            $maxRows = (int)$input->getOption('rows') ;
            $io->writeln('Max Rows to be updated was set to '. $maxRows );

        }

        if ( $musthave == 0 ) {
            $io->writeln( "ERROR: musthave ($musthave)   not set " );
            return 1 ;
        }
        if ( $usergroup == 0 && $cmd != "count") {
            $io->writeln( "ERROR: usergroup ($usergroup) not set but requierred if command not count" );
            return 1 ;
        }
        if ( $cmd != "count") {
            $io->writeln("Command will '" . $cmd . "' group: '" . $usergroup . "' from fe_users if user has Group: " . $musthave);
        } else {
            $io->writeln("Command will '" . $cmd . "' users with group: '" . $musthave . "' " );
        }
        $progress = false ;
        $table = "fe_users" ;
		$qb = $this->getQueryBuilder($table) ;
        if ( $cmd == "add") {
            $rows = $qb->select("uid" , "usergroup")->from($table)
                ->where($qb->expr()->notInSet("usergroup" , $usergroup ))
                ->andWhere($qb->expr()->inSet("usergroup" , $musthave ))
                ->executeQuery() ;
        } elseif ( $cmd == "remove") {
            $rows = $qb->select("uid" , "usergroup")->from($table)
                ->where($qb->expr()->inSet("usergroup" , $usergroup ))
                ->andWhere($qb->expr()->inSet("usergroup" , $musthave ))
                ->executeQuery() ;
        } elseif ( $cmd == "count") {
            $rows = $qb->select("uid" , "usergroup")->from($table)
                ->where($qb->expr()->inSet("usergroup" , $musthave ))
                ->executeQuery() ;
        }

        $total = $rows->rowCount()  ;
        if( $io->getVerbosity() > 16 ) {
            $io->writeln( $table . " - rowCount: " . $total );
            $progress = $io->createProgressBar($total) ;
        }
        if ( $cmd == "count") {
            return 0;
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
            $io->writeln("====================================================================") ;
            $io->writeln(" ") ;
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