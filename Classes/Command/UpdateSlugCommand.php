<?php
namespace JVE\JvEvents\Command;

use JVE\JvEvents\Utility\SlugUtility;
use PDO;
use Symfony\Component\Console\Input\InputOption;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateSlugCommandController
 * @author Jörg Velletti <typo3@velletti.de>
 * @package JVE\JvEvents\Command
 */
class UpdateSlugCommand extends Command {

    /**
     * @var array
     */
    private $allowedTables = [] ;

    /**
     * @var array
     */
    private $extConf = [] ;



    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Updates the Slugs of the Configured database.')
            ->setHelp('Get list of Options: .' . LF . 'use the --help option.')
            ->addArgument(
                'table',
                InputArgument::OPTIONAL,
                'enter name of Table that has a slug. default is: tx_jvevents_domain_model_event'
            )
            ->addOption(
                'rows',
                'r',
                InputOption::VALUE_OPTIONAL,
                'number of rows to be updates must be integer' )
            ->addOption(
                'field',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Name of the slug field in database Table. default is: slug' );
        ;
        // toDo: make this configurable somehow
        $this->allowedTables[] = "tx_jvevents_domain_model_event" ;
        $this->allowedTables[] = "tx_jvevents_domain_model_location" ;
        $this->allowedTables[] = "tx_jvevents_domain_model_organizer" ;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['jv_events']);

        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $maxRows = 999999999999 ;
        if ($input->getOption('rows') ) {
            $maxRows = (int)$input->getOption('rows') ;
            $io->writeln('max Rows to be updated was set to '. $maxRows );

        }
       // Bootstrap::initializeBackendAuthentication();
        $table = $this->allowedTables[0] ;

        if ($input->getArgument('table')) {
            $table = $input->getArgument('table');

        }
        $field = 'slug' ;
        if ($input->getOption('field')) {
            $field = $input->getOption('field');

        }

        if( in_array( $table , $this->allowedTables) ) {
            $this->updateCommand($io , $table, $field , $maxRows  ) ;
        } else {
            $io->writeln('Entered Tablename . $table . " that has a field slug to be updated. Only tableNames that are configured are allowed');
            $io->writeln(var_export( $this->allowedTables , true )) ;
        }
            // ...



    }


	public function updateCommand(SymfonyStyle $io , $table , $slugField , $maxRows  ){

        if( !$table ) { return ; }
		$rows = $this->getQueryBuilder($table)->select("*")->from($table)->execute() ;
        $total = $rows->rowCount()  ;
        if( $total < $maxRows ) {
            $maxRows = $total ;

        }
        if( $io->getVerbosity() > 16 ) {
            $io->writeln( $table . " - rowCount: " . $total );
            $progress = $io->createProgressBar($total) ;
        }
        $i = 0 ;
        $debugOutput = "" ;
        while ( $row = $rows->fetch()) {
            $singleRow = $this->mapRow($table, $row , $slugField) ;
            $slug = SlugUtility::getSlug( $table, $slugField , $singleRow  )  ;
            if( $io->getVerbosity() > 16 ) {
                $progress->advance();
            }
            if( $slug != $singleRow[$slugField]) {
                $i++ ;
                if( $io->getVerbosity()  > 128 ) {
                    $debugOutput .= PHP_EOL . " Update: " . $singleRow['uid'] . " - " . $singleRow[$slugField] . " => " . $slug ;
                }
            }
            $this->setSlug( $table , $singleRow['uid']  , $slugField , $slug ) ;
            if( $i >= $maxRows ) {
                break ;
            }
        }

        if( $io->getVerbosity() > 16 ) {
            $progress->finish();

            if( $io->getVerbosity()  > 128 ) {
                $io->writeln($debugOutput);
            }
            $io->writeln(" ") ;
            $io->writeln("Finished ( " . $table . " updated: "   . $i . "/" . $total .  " records) ");
        }
	}

	/**
	 * @return QueryBuilder
	 */
	private function getQueryBuilder($table){
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        /** @var QueryBuilder $queryBuilder */
        return $connectionPool->getConnectionForTable($table)->createQueryBuilder();
	}

	private function mapRow($table , $row , $slugField ) {
	    $return = array() ;

        $return['pid'] =   $row['pid'] ? $row['pid'] : 0  ;
        $return['parentpid'] =  1 ;
        $return['uid'] =  $row['uid'] ? $row['uid'] : 0  ;



	    switch ($table) {
            case "tx_jvevents_domain_model_event":

                $return['name'] =  $row['name'] ;
                $return['parentpid'] =  1 ;
                $return['sys_language_uid'] = -1 ;

                $slugGenerationDateFormat = "d-m-Y" ;
                if( is_array( $this->extConf) and array_key_exists( "slugGenerationDateFormat" , $this->extConf )) {
                    $slugGenerationDateFormat =  $this->extConf['slugGenerationDateFormat'] ;
                }

                $return['start_date'] =   date( $slugGenerationDateFormat , $row['start_date'] ) ;
                $return[$slugField] =   $row[$slugField]?  $row[$slugField] : $row['name'] . "-" . $row['start_date'] ;
                break ;
            default:
                $return['name'] =  $row['name'] ;
                if(array_key_exists('parentpid' , $row)) {
                    $return['parentpid'] =  $row['parentpid']  ;
                } else {
                    $return['parentpid'] =  1 ;
                }
                $return['sys_language_uid'] =   $row['sys_language_uid'] ;
                $return['start_date'] =   date( "d-m-Y" , $row['start_date'] ) ;
                $return[$slugField] =   $row[$slugField]?  $row[$slugField] : $row['name'] . "-" . $row['start_date'] ;
                break ;
        }
        return $return ;
    }

    /**
     * @return QueryBuilder
     */
    private function setSlug($table , $uid , $slugField , $slug){
        $qb = $this->getQueryBuilder($table) ;
        $qb->update($table)->set($slugField , $slug)
            ->where($qb->expr()->eq("uid" , $qb->createNamedParameter($uid , PDO::PARAM_INT)))
            ->execute() ;

    }


}