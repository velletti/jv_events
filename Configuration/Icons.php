<?php

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

// Provide icon for page tree, list view, ... :
return array_map(static fn (string $source) => ['provider' => SvgIconProvider::class, 'source' => $source], [
    'jvevents-plugin' => 'EXT:jv_events/Resources/Public/Icons/jvevents-plugin.svg',
    'jvevents-location-map-wizard' => 'EXT:jv_events/Resources/Public/Icons/actions-geo.svg',
]);