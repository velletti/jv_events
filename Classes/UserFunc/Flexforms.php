<?php
/**
 * Created by PhpStorm.
 * User: velletti
 * Date: 21.09.2016
 * Time: 13:39
 */

namespace JVE\JvEvents\UserFunc;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Flexforms {
	public function getSettings() {
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
		$configurationManager = $objectManager->get(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class);
		$settings = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT );

		return $settings ;
	}
	public function selectLayout($PA, $fObj) {
		
		$allSettings = $this->getSettings() ;
		$settings = $allSettings['plugin.']['tx_jvevents_events.']['settings.'];

		$layoutType = $PA['parameters']['layoutType'] ;

		if ( $layoutType == '' ) {
			return '<div class="alert alert-error">Typo Script of this Extension : settings.list.layouts.layout1 .. not set ! </div>' ;
		}
		$layouts = $settings[$layoutType]['layouts.'] ;
		if ( !is_array($layouts)) {
			return '<div class="alert alert-error">Typo Script of this Extension : settings.list.layouts.layout1 .. not an Array ! </div>' ;
		}
		$formField = '<select name="' . $PA['itemFormElName'] . '"';
		$formField .= ' onchange="' . htmlspecialchars(implode('', $PA['fieldChangeFunc'])) . '"';
		$formField .= $PA['onFocus'];
		$formField .= ' >';
		foreach ( $layouts as $key => $layout) {
			$selected = '' ;
			if ( $key == htmlspecialchars($PA['itemFormElValue']) ) {
				$selected = ' selected="selected"' ;
			}
			$formField .= '<option ' . $selected . ' value="' . $key .  '"> ' . $layout . '</option>';
		}
		$formField .= '</select>';
		// $formField .= ' Old Value ' . $PA['itemFormElValue'] ;

		return $formField;
	}

    /** used in event Model TCA to show the Translations of Tags and Categories, but still work with the uid of the default record
     * does not work in 9.5.15 - 20.4.2020 but there is a closed issue / patch
     * https://forge.typo3.org/issues/85142
     * */
    public function TranslateMMvalues($config) {

        if ( $config['row']['uid'] < 1 ) {
            return $config ;
        }
        $plugin = BackendUtility::getRecord("tt_content" , $config['row']['uid'] );
        if ( is_array( $plugin) ) {
            $config['row'] = array_merge( $config['row'] , $plugin ) ;
        } else {
            return $config ;
        }
        if( ! is_array($config ) || $config['row']['sys_language_uid'] < 1  || ! is_array($config['items'])) {
            return $config ;
        }

        // Works only Out Of the Box if label Field and Foreign_table in TCA is set AND  only ONE Field / Table Name is set and not splitted, joined  with a komma
        $table = $config['config']['foreign_table'] ;

        $nameField = trim( $GLOBALS['TCA'][$table]['ctrl']['label'] ) ;

        if( $table . $nameField == '' || strpos( $table . $nameField  , ",") > 0 ) {
            return $config ;
        }

        $newItems = [] ;

        foreach ( $config['items'] as $key => $item ) {
            $uid = $item[1] ;
            $row = $this->getRow($table , $nameField , $config['row']['sys_language_uid'] , $uid ) ;
            if( is_array(  $row ) ) {
                if( $row['uid' ]  <> $uid ) {
                    $config['items'][$key][0] = "[" . $uid . "] " . $config['items'][$key][0] . " (L " . $row['sys_language_uid' ]  . " Uid: " . $row['uid' ] . " - " . $row[ $nameField ]  . ")" ;
                } else {
                    $config['items'][$key][0] = "[" . $uid . "] " . $config['items'][$key][0] . " (only in Lang:" . $row['sys_language_uid' ]  . ")" ;
                }
            } else {
                $row = $this->getRow($table , $nameField , -1 , $uid ) ;
                if( !is_array(  $row ) ) {
                    $uid = false ;
                } else {
                    $config['items'][$key][0] = "[" . $uid . "] " .  $config['items'][$key][0] . " (all languages)" ;
                }
            }
            // remove untranslated Items so olny items for all languages or selected languages are left
            if ( $uid) {
                $newItems[] =  array ( 0 => $config['items'][$key][0] , 1 => $uid ) ;
            }

            // $config['items'][$key][0] = "[" . $config['items'][$key][0] = $config['items'][$key][1] . "] ". $config['items'][$key][0] ;

        }
        $config['items'] = $newItems ;
        return $config ;
    }
    private function getRow($table , $nameField , $lngField , $uid ) {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class);
        $lngField = intval($lngField ) ;
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $connectionPool->getConnectionForTable($table)->createQueryBuilder();
        $queryBuilder->select('pid' , 'l10n_parent', 'sys_language_uid','uid',$nameField) ->from($table) ;
        $expr = $queryBuilder->expr();
        $queryBuilder->where( $expr->eq('sys_language_uid',
            $queryBuilder->createNamedParameter( $lngField , Connection::PARAM_INT))
        ) ;
        if( $lngField > 0 ) {
            $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                $expr->eq('l10n_parent', intval($uid ))  ,
                $queryBuilder->expr()->andX(
                    $expr->eq('l10n_parent', 0 ) ,
                    $expr->eq('uid', intval($uid ) )
                )
            ) )
            ;

        } elseif ($lngField < 0 ) {
            $queryBuilder->andWhere($expr->eq('uid', intval($uid ))) ;
        }


        return  $queryBuilder->execute()->fetch() ;
    }
}