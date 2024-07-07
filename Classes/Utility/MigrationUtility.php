<?php
namespace JVelletti\JvEvents\Utility;


class MigrationUtility
{

	/**
	 * Gets current PageId
	 * If not set returns null
	 *
	 * @param mixed $obj
	 * @return mixed|null
	 */
	public static function getPageId($obj=false)
    {
        $pageId = 0 ;

        if ( $obj && method_exists( $obj ,"request")) {
            if ( $obj && method_exists( $obj->request ,"getAttribute")) {
                $obj->request->getAttribute('routing');
                $pageId = (int)$pageArguments->getPageId();
            }
        } else {
            if( isset($GLOBALS['TYPO3_REQUEST'])) {
                $pageArguments = $GLOBALS['TYPO3_REQUEST']->getAttribute('routing');
                $pageId = (int)$pageArguments->getPageId();
            }
        }
        if ($pageId == 0 ) {
            if( isset($_GET['Id'])) {
                $pageId = (int)$_GET['Id'];
            }
        }
	    return $pageId;

	}


}