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
class DataAttribViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper   {

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
		$this->registerArgument('event', '\JVE\JvEvents\Domain\Model\Event', 'Single Event', false);
        parent::initializeArguments() ;
	}



	/**
	 * Render a special sign if the field is required
	 *
	 * @return string
	 */
	public function render() {
        $event = $this->arguments['event'] ;
        if( !is_object ( $event )) {
            return " " ;
        }
        $orgId = '' ;
        $orgName = '' ;
	    if( is_object ( $event->getOrganizer() )) {
            $orgId  = $event->getOrganizer()->getUid()  ;
            $orgName  = urlencode( trim($event->getOrganizer()->getName()))  ;
        }

        $result = 'data-eventuid="' . $event->getUid() . '" data-eventpid="' .  $event->getPid()
            .   '" data-orguid="' .  $orgId  . '"  data-orgname="' .  $orgName  . '" '
            . 'data-monthuid="' . date("m.Y" , $event->getStartDate()->getTimestamp() ) . '" '
            . 'data-dayuid="' . date("d.m.Y" , $event->getStartDate()->getTimestamp() )  . '" '
        ;

	    $locCity = '' ;
        if( is_object ( $event->getLocation() )) {
            $locCity  = urlencode( trim( $event->getLocation()->getCity() )) ;
            $result .= 'data-longitude="' . $event->getLocation()->getLng() . '" ' ;
            $result .= 'data-latitude="' . $event->getLocation()->getLat() . '" ' ;
        }
        $result .= 'data-cityuid="' . strip_tags($locCity ) . '" ' ;
        // data-catuids data-taguids
        $catUids = '' ;
        if( is_object($event->getEventCategory() )  ) {
            $uidArray = $event->getEventCategory()->toArray() ;

            if( is_array( $uidArray  )) {
                /** @var \JVE\JvEvents\Domain\Model\Category $cat */
                foreach ( $uidArray as $cat ) {
                    if ( strlen( $catUids ) > 0  ) {
                        $catUids .= "," ;
                    }
                    $catUids .= $cat->getUid()  ;
                }
            }
        }
        $result .= 'data-catuids="' . $catUids . '" ' ;

        $tagUids = '' ;
        if( is_object($event->getTags() )  ) {
            $uidArray = $event->getTags()->toArray() ;

            if( is_array( $uidArray  )) {
                /** @var \JVE\JvEvents\Domain\Model\Category $cat */
                foreach ( $uidArray as $cat ) {
                    if ( strlen( $tagUids ) > 0  ) {
                        $tagUids .= "," ;
                    }
                    $tagUids .= $cat->getUid()  ;
                }
            }
        }
        $result .= 'data-taguids="' . $tagUids . '" ' ;

// <div class="jv-events-singleEvent" data-monthuid="08.2018" data1-cityuid="M%C3%BCnchen" data-uid="&quot;4&quot;">
		return $result ;
	}
}
