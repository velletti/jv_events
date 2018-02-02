<?php
namespace JVE\JvEvents\ViewHelpers\Format;

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

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Formats an object implementing \DateTimeInterface.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <f:format.date>{dateObject}</f:format.date>
 * </code>
 * <output>
 * 1980-12-13
 * (depending on the current date)
 * </output>
 *
 * <code title="Custom date format">
 * <f:format.date format="H:i">{dateObject}</f:format.date>
 * </code>
 * <output>
 * 01:23
 * (depending on the current time)
 * </output>
 *
 * <code title="Custom date format with Time/zone">
 * <f:format.date format="H:i" timeZone="Europe/Berlin">{dateObject}</f:format.date>
 * </code>
 * <output>
 * 00:23
 * (depending on the current time. UTC or see http://php.net/manual/en/timezones.php)
 * </output>
 *
 * <code title="Relative date with given time">
 * <f:format.date format="Y" base="{dateObject}">-1 year</f:format.date>
 * </code>
 * <output>
 * 2016
 * (assuming dateObject is in 2017)
 * </output>
 *
 * <code title="strtotime string">
 * <f:format.date format="d.m.Y - H:i:s">+1 week 2 days 4 hours 2 seconds</f:format.date>
 * </code>
 * <output>
 * 13.12.1980 - 21:03:42
 * (depending on the current time, see http://www.php.net/manual/en/function.strtotime.php)
 * </output>
 *
 * <code title="Localized dates using strftime date format">
 * <f:format.date format="%d. %B %Y">{dateObject}</f:format.date>
 * </code>
 * <output>
 * 13. Dezember 1980
 * (depending on the current date and defined locale. In the example you see the 1980-12-13 in a german locale)
 * </output>
 *
 * <code title="Inline notation">
 * {f:format.date(date: dateObject)}
 * </code>
 * <output>
 * 1980-12-13
 * (depending on the value of {dateObject})
 * </output>
 *
 * <code title="Inline notation (2nd variant)">
 * {dateObject -> f:format.date()}
 * </code>
 * <output>
 * 1980-12-13
 * (depending on the value of {dateObject})
 * </output>
 */
class DateViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * Needed as child node's output can return a DateTime object which can't be escaped
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
     //   $this->registerArgument('date', 'mixed', 'Either an object implementing DateTimeInterface or a string that is accepted by DateTime constructor');
        $this->registerArgument('format', 'string', 'Format String which is taken to format the Date/Time', false, '');
        $this->registerArgument('base', 'mixed', 'A base time (an object implementing DateTimeInterface or a string) used if $date is a relative date specification. Defaults to current time.');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     * @throws Exception
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $format = $arguments['format'];
        $base = $arguments['base'] === null ? time() : $arguments['base'];
        if (is_string($base)) {
            $base = trim($base);
        }

        if ($format === '') {
            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: 'Y-m-d';
        }

        $date = $renderChildrenClosure();
        if ($date === null) {
            return '';
        }

        if (is_string($date)) {
            $date = trim($date);
        }

        if ($date === '') {
            $date = 'now';
        }

        if (!$date instanceof \DateTimeInterface) {
            try {
                $base = $base instanceof \DateTimeInterface ? $base->format('U') : strtotime((MathUtility::canBeInterpretedAsInteger($base) ? '@' : '') . $base);
                $dateTimestamp = strtotime((MathUtility::canBeInterpretedAsInteger($date) ? '@' : '') . $date, $base);
                $date = new \DateTime('@' . $dateTimestamp);
                $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            } catch (\Exception $exception) {
                throw new Exception('"' . $date . '" could not be parsed by \DateTime constructor: ' . $exception->getMessage(), 1241722579);
            }
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $date->format('U'));
        }
        return $date->format($format);
    }



    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     *
     * @return string
     * @throws Exception
     */
    public static function renderStaticOLD(array $arguments, \Closure $renderChildrenClosure, \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext)
    {
        $date = $arguments['date'];
        $format = $arguments['format'];
        $base = $arguments['base'] === null ? time() : $arguments['base'];

        $timeZone = $arguments['timeZone'] === null ? trim($GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'])  : $arguments['timeZone'];
        if( ! in_array($timeZone, timezone_identifiers_list()) ) {
            $timeZone = date_default_timezone_get() ;
        }
        if( ! in_array($timeZone, timezone_identifiers_list()) ) {
            $timeZone = 'UTC' ;
        }

        if (is_string($base)) {
            $base = trim($base);
        }

        if ($format === '') {
            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: 'Y-m-d';
        }

        if ($date === null) {
            $date = $renderChildrenClosure();
            if ($date === null) {
                return '';
            }
        }

        if (is_string($date)) {
            $date = trim($date);
        }

        if ($date === '') {
            $date = 'now';
        }

        if (!$date instanceof \DateTimeInterface) {
            try {
                $base = $base instanceof \DateTimeInterface ? $base->format('U') : strtotime((MathUtility::canBeInterpretedAsInteger($base) ? '@' : '') . $base);
                $dateTimestamp = strtotime((MathUtility::canBeInterpretedAsInteger($date) ? '@' : '') . $date, $base);
                $date = new \DateTime('@' . $dateTimestamp);
                $date->setTimezone(new \DateTimeZone($timeZone));
            } catch (\Exception $exception) {
                throw new Exception('"' . $date . '" could not be parsed by \DateTime constructor: ' . $exception->getMessage(), 1241722579);
            }
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $date->format('U'));
        } else {
            return $date->format($format);
        }
    }
}