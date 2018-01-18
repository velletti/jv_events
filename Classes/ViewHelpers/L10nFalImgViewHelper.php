<?php
namespace JVE\JvEvents\ViewHelpers;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

class L10nFalImgViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param \JVE\JvEvents\Domain\Model\Event $event
	 * @param string $tableFieldName
	 * @return mixed
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException
	 */
	public function render($event, $tableFieldName) {

		$allowedTableFieldNames = [
			'teaser_image',
			'files',
			'images'
		];

		if(!in_array($tableFieldName, $allowedTableFieldNames)){
			return null;
		}


		$where = 'hidden=0 AND deleted=0 AND tablenames="tx_jvevents_domain_model_event" AND fieldname="' . $tableFieldName . '" AND uid_foreign=' . (int)$event->_getProperty('_localizedUid');
		$where .= ' ' . \TYPO3\CMS\Backend\Utility\BackendUtility::getWorkspaceWhereClause('sys_file_reference');

		/**
		 * @var $db \TYPO3\CMS\Core\Database\DatabaseConnection
		 * @var $sysPage \TYPO3\CMS\Frontend\Page\PageRepository
		 * @var $obj \TYPO3\CMS\Core\Resource\FileReference
		 */
		$db = $GLOBALS['TYPO3_DB'];
		$sysPage = $GLOBALS['TSFE']->sys_page;


		$records = $db->exec_SELECTgetRows('*', 'sys_file_reference', $where);
		$outArray = array() ;
		foreach ($records as &$r) {
			$sysPage->versionOL('sys_file_reference', $r);
			$fileReferenceData = $db->exec_SELECTgetSingleRow('*', 'sys_file_reference', 'uid=' . $r['uid'] . ' AND deleted=0');

			/** @var \TYPO3\CMS\Core\Resource\FileReference $obj */
			$obj =  GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileReference', $fileReferenceData);

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