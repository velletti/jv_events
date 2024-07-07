<?php
namespace JVelletti\JvEvents\ViewHelpers;

/** This Viewhelper is atm necessary to fetch the localised fal images
 *
 *
 * Example
 * ----------
 *    <n:l10nfalimg news="{newsItem}">
 *        <f:for each="{l10nfalimg}" as="falimage" iteration="i">
 *            <a title="{falimage._file.description}" href="{f:uri.image(src:'{falimage._file.publicUrl}' maxWidth:'1360')}" rel="lightbox[{newsItem.uid}]" data-toggle="lightbox">
 *            <f:image
 *                src="{falimage._file.publicUrl}"
 *                alt="{f:if(condition: '{falimage._file.alternative}', then: '{falimage._file.alternative}', else: '{falimage._file.title}')}"
 *                width="{settings.detail.media.image.width}"
 *                height="{settings.detail.media.image.height}"
 *                maxWidth="{settings.detail.media.image.maxWidth}"
 *                maxHeight="{settings.detail.media.image.maxHeight}"
 *                treatIdAsReference="1"
 *            />
 *            </a>
 *            <p class="news-single-imgcaption">
 *                <f:format.htmlentitiesDecode>{falimage._file.title}</f:format.htmlentitiesDecode>
 *            </p>
 *            <f:comment>
 *            <f:format.html>
 *                falimage.uid              : {falimage.uid}
 *                falimage.identifier       : {falimage._file.identifier}
 *                falimage.public_url       : {falimage._file.publicUrl}
 *                falimage.name             : {falimage._file.name}
 *                falimage.title            : {falimage._file.title}
 *                falimage.alternative      : {falimage._file.alternative}
 *                falimage.description      : {falimage._file.description}
 *                falimage.extension        : {falimage._file.extension}
 *                falimage.type             : {falimage._file.type}
 *                falimage.mimeType         : {falimage._file.mimeType}
 *                falimage.size             : {falimage._file.size}
 *                falimage.creationTime     : {falimage._file.creationTime}
 *                falimage.modificationTime : {falimage._file.modificationTime}
 *            </f:format.html>
 *            </f:comment>
 *        </f:for>
 *    </n:l10nfalimg>
 *
 *
 */
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use JVelletti\JvEvents\Domain\Model\Event;
use TYPO3Fluid\Fluid\Core\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
class L10nFalImgViewHelper extends AbstractViewHelper {


    public function initializeArguments()
    {
        $this->registerArgument('event', Event::class, 'Event', true);
        $this->registerArgument('tableFieldName', 'string', ' the tableFieldName', true  );
        parent::initializeArguments() ;
    }


	/**
  * @return mixed
  * @throws Exception
  */
 public function render() {
        $event = $this->arguments['event'] ;
        $tableFieldName = $this->arguments['tableFieldName'] ;
		$allowedTableFieldNames = [
			'teaser_image',
			'files',
			'images'
		];

		if(!in_array($tableFieldName, $allowedTableFieldNames)){
			return null;
		}
        /**
         * @var $sysPage PageRepository
         * @var $obj FileReference
         */
        $relevantParametersForCachingFromPageArguments = [];
        $pageArguments = $GLOBALS['REQUEST']->getAttribute('routing');
        $queryParams = $pageArguments->getDynamicArguments();
        if (!empty($queryParams) && ($pageArguments->getArguments()['cHash'] ?? false)) {
            $queryParams['id'] = $pageArguments->getPageId();
            $relevantParametersForCachingFromPageArguments = GeneralUtility::makeInstance(CacheHashCalculator::class)->getRelevantParameters(HttpUtility::buildQueryString($queryParams));
        }
        $sysPage = $relevantParametersForCachingFromPageArguments;




        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);

        $connection = $connectionPool->getConnectionForTable('tx_jvchat_room') ;



        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getQueryBuilderForTable('sys_file_reference') ;
        $records = $queryBuilder->select('*' ) ->from('sys_file_reference')
            ->where( $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter("tx_jvevents_domain_model_event" , Connection::PARAM_STR)) )
            ->andWhere( $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter($tableFieldName , Connection::PARAM_STR)) )
            ->andWhere( $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter((int)$event->_getProperty('_localizedUid') , Connection::PARAM_INT )) )
            ->execute()
            ->fetchAll();


		$outArray = [] ;
		foreach ($records as &$r) {
			$sysPage->versionOL('sys_file_reference', $r);
            $queryBuilder2 = $connectionPool->getQueryBuilderForTable('sys_file_reference') ;
            $fileReferenceData = $queryBuilder2->select('*' ) ->from('sys_file_reference')
                ->where( $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($r['uid'] , Connection::PARAM_INT)) )
                ->execute()
                ->fetch();

			/** @var FileReference $obj */
			$obj =  GeneralUtility::makeInstance(FileReference::class, $fileReferenceData);

			// Next line is obsolete! you can not access to non Public Properties of this OBJ
			// $r['_file'] = $obj;

			$r['_path'] = $obj->getPublicUrl();
			$r['_properties'] = $obj->getProperties();
            $outArray[] = $r ;
		}

		$this->templateVariableContainer->add('l10nfalimg', $outArray );
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove('l10nfalimg');

		return $output;

	}

}