<?php
namespace JVE\JvEvents\ViewHelpers;
/***************************************************************
 * Copyright notice
 *
 * (c) 2011-13 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Viewhelper to render a selectbox with values
 * in given steps from start to end value
 *
 * <code title="Usage">
 * {namespace register=\JVE\JvEvents\\iewHelpers}
 * <register:form.required fieldName="'username"/>
 * </code>
 */
class OpenStreetmapGeocoderViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper   {

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Needed as child node's output can return a DateTime object which can't be escaped
     *
     * @var bool
     */
    protected $escapeChildren = false;

    public function initializeArguments() {
        $this->registerArgument('location', \JVE\JvEvents\Domain\Model\Location::class, 'Single location', false , NULL);
        $this->registerArgument('formfields', 'array', 'Field Array', false , NULL );
        $this->registerArgument('updateFunction', 'string', 'Name of javaScript function that should run after Update Map', false , '' );
        parent::initializeArguments() ;
    }



    /**
     * Render a special sign if the field is required
     * looks as if this is unused and all replqced with f:assrt!!!
     *
     * @return string
     */
    public function render() {

        $return =  '<script type="text/javascript" src="/typo3conf/ext/jv_events/Resources/Public/JavaScript/leaflet-1-7-1.js"></script>' . PHP_EOL  ;
        $return .= '<script type="text/javascript" src="/typo3conf/ext/jv_events/Resources/Public/JavaScript/tango/LeafletFeGeoCoder.js"></script>' . PHP_EOL ;
        $return .= '<link rel="stylesheet" media="all" type="text/css" href="/typo3conf/ext/jv_events/Resources/Public/Css/leaflet-1-7-1.css" />' . PHP_EOL ;
        $return .= '<link rel="stylesheet" media="all" type="text/css" href="/typo3conf/ext/jv_events/Resources/Public/Css/tango/leafletFrontend.css" />' . PHP_EOL ;
        return $return ;
    }
}
