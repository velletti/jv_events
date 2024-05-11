<?php
declare(strict_types = 1);

namespace JVelletti\JvEvents\FormEngine\FieldControl;

/**
 * This file is part of the "jv_events" Extension for TYPO3 CMS.
 * and based to 99% on the work of "tt_address" Extension of friendsoftypo3
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
/**
 * Adds a wizard for location selection via map
 */
class EventLocationMapWizard extends AbstractNode
{
    /**
     * @return array
     */
    public function render(): array
    {
        $row = $this->data['databaseRow'];

        $paramArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();
        $title = $this->getLanguageService()->sL('LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:jv_events_model_location.geocoder.title');


        $nameLongitude = $paramArray['itemFormElName'];
        $nameLatitude = str_replace('lng', 'lat', $nameLongitude);
        $nameLatitudeActive = str_replace('data', 'control[active]', $nameLatitude);
        $geoCodeUrl = '';
        $gLat = '48.1135455';  // Me at home as default
        $gLon = '11.4725687';

        $lat = $row['lat'] != '' ? htmlspecialchars(sprintf("%.16f",$row['lat'])) : '';
        $lon = $row['lng'] != '' ? htmlspecialchars(sprintf("%.16f",$row['lng'])) : '';
        if( $row['street_and_nr'] != '' &&  $row['city'] != ''  ) {
            $title = "GeoCoder" ;
        }
        if ($row['lat'] || $row['lng'] == '') {
            // remove all after first slash in address (top, floor ...)
            $address = preg_replace('/^([^\/]*).*$/', '$1', $row['street_and_nr'] ?? '') . ' ';
            $address .= $row['city'] ?? '';
            // if we have at least some address part (saves geocoding calls)
            if ($address) {
                // base url
                $geoCodeUrlBase = 'https://nominatim.openstreetmap.org/search';
                $geoCodeUrlAddress = $address;
                $geoCodeUrlCityOnly = ($row['city'] ?? '');
                // urlparams for nominatim which are fixed.
                $geoCodeUrlQuery = '?format=json&addressdetails=1&limit=1&polygon_svg=1';
                // replace newlines with spaces; remove multiple spaces
                $geoCodeUrl = trim(preg_replace('/\s\s+/', ' ', $geoCodeUrlBase . $geoCodeUrlAddress . $geoCodeUrlQuery));
                $geoCodeUrlShort = trim(preg_replace('/\s\s+/', ' ', $geoCodeUrlBase . $geoCodeUrlCityOnly . $geoCodeUrlQuery));
            }
        }

        $resultArray['iconIdentifier'] = 'jvevents-location-map-wizard';
        $resultArray['title'] = $title ;
        $resultArray['linkAttributes']['class'] = 'locationMapWizard ';
        $resultArray['linkAttributes']['data-label-title'] = $title ;
        $resultArray['linkAttributes']['data-label-close'] = $this->getLanguageService()->sL('LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:jv_events_model_location.geocoder.close');
        $resultArray['linkAttributes']['data-label-import'] = $this->getLanguageService()->sL('LLL:EXT:jv_events/Resources/Private/Language/locallang.xlf:select-position');
        $resultArray['linkAttributes']['data-lat'] = $lat;
        $resultArray['linkAttributes']['data-lon'] = $lon;
        $resultArray['linkAttributes']['data-glat'] = $gLat;
        $resultArray['linkAttributes']['data-glon'] = $gLon;
        $resultArray['linkAttributes']['data-geocodeurl'] = $geoCodeUrl;
        $resultArray['linkAttributes']['data-geocodeurlshort'] = $geoCodeUrlShort;
        $resultArray['linkAttributes']['data-namelat'] = htmlspecialchars($nameLatitude);
        $resultArray['linkAttributes']['data-namelon'] = htmlspecialchars($nameLongitude);
        $resultArray['linkAttributes']['data-namelat-active'] = htmlspecialchars($nameLatitudeActive);
        $resultArray['linkAttributes']['data-tiles'] = htmlspecialchars('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
        $resultArray['linkAttributes']['data-copy'] = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
        $resultArray['stylesheetFiles'][] = 'EXT:jv_events/Resources/Public/Css/leaflet-1-7-1.css';
        $resultArray['stylesheetFiles'][] = 'EXT:jv_events/Resources/Public/Css/leafletBackend.css';

        $resultArray['requireJsModules'][] = JavaScriptModuleInstruction::forRequireJS(
            'JVelletti/JvEvents/leaflet-1-7-1.js'
        )->instance("jvevents-location-map-wizard");
        $resultArray['requireJsModules'][] = JavaScriptModuleInstruction::forRequireJS(
            'JVelletti/JvEvents/LeafletBackend.js'
        )->instance($paramArray['itemFormElName']);

        return $resultArray;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
