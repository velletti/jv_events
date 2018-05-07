<?php

namespace JVE\JvEvents\Utility ;

/**
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

class ShowAsJsonArrayUtility
{

    /**
     * @param array $output
     */
    static function show($output) {
        $jsonOutput = json_encode($output);
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Length: ' . strlen($jsonOutput));
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Transfer-Encoding: 8bit');

        $callbackId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP("callback");
        if ( $callbackId == '' ) {
            echo $jsonOutput;
        } else {
            echo $callbackId . "(" . $jsonOutput . ")";
        }

        die();
    }


}
