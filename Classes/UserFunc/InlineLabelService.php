<?php
namespace JVelletti\JvEvents\UserFunc;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Resource\Exception\InvalidUidException;
use TYPO3\CMS\Core\Resource\Index\MetaDataRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * User file inline label service
 */
class InlineLabelService
{
    /**
     * Get the user function label for the file_reference table
     *
     * @param array $params
     */
    public function getInlineLabel(array &$params)
    {
        if ( isset($params['options']['tx_jvevents_domain_model_subevent']) && is_array($params['options']['tx_jvevents_domain_model_subevent']) ) {
            foreach ( $params['options']['tx_jvevents_domain_model_subevent'] as $field => $format ) {
                $params['title'] .= date( $format ,  intval($params['row'][$field] )) ;

            }
            if ( $params['row']['start_time'] > $params['row']['end_time'] ) {
                $params['title'] .= " << Error in Start/endtime !" ;
            }
            return;
        }

        // Else Nothing to do give back th UID
        $params['title'] = $params['row']['uid'];
        return;

    }
}
