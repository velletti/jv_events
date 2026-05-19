<?php

namespace JVelletti\JvEvents\ViewHelpers\Format;

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class DateViewHelper extends AbstractViewHelper
{

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('date', 'mixed', 'DateTimeInterface or string');
        $this->registerArgument('format', 'string', 'Format string', false, '');
        $this->registerArgument('base', 'mixed', 'Base time', false, null);
    }

    public function render(): string
    {
        $this->escapeChildren = false;
        $format = $this->arguments['format'];
        $date = $this->arguments['date'];
        $base = $this->arguments['base'] ?? time();

        if (is_string($base)) {
            $base = trim($base);
        }

        if ($format === '') {
            $format = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?: 'Y-m-d';
        }

        if ($date === null) {
            $date = $this->renderChildren();
            if ($date === null || $date == $format) {
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
                $baseTimestamp = $base instanceof \DateTimeInterface
                    ? $base->format('U')
                    : strtotime((MathUtility::canBeInterpretedAsInteger($base) ? '@' : '') . $base);

                $dateTimestamp = strtotime(
                    (MathUtility::canBeInterpretedAsInteger($date) ? '@' : '') . $date,
                    (int)$baseTimestamp
                );

                $date = new \DateTime('@' . $dateTimestamp);
                $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            } catch (\Exception $e) {
                return '';
            }
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, (int)$date->format('U'));
        }

        return $date->format($format);
    }
}