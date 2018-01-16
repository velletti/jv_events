<?php
namespace JVE\JvEvents\Wizard;

use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Geocoder
 * See examples in: typo3/sysext/backend/Classes/Controller/Wizard/
 * @author Peter Benke <pbenke@allplan.com>
 * @package JVE\JvEvents\Wizard
 */

class Geocoder extends \TYPO3\CMS\Backend\Controller\Wizard\AbstractWizardController {

	/**
	 * Document template object
	 * @var DocumentTemplate
	 */
	public $doc;

	/**
	 * @var string
	 */
	public $content;

	/**
	 * Geocoder constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->getLanguageService()->includeLLFile('EXT:jv_events/Resources/Private/Language/locallang_be.xlf');
		$this->init();
	}

	/**
	 * Some initializing
	 */
	protected function init(){

		$this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);

		$googleApiKey = \JVE\JvEvents\Utility\EmConfigurationUtility::getGoogleApiKey();
		$this->doc->JScode = '<script async defer src="https://maps.googleapis.com/maps/api/js?key=' . $googleApiKey . '&callback=initMap"></script>';
		$this->doc->addStyleSheet('The Google Geocoder','../typo3conf/ext/jv_events/Resources/Public/Css/geocoder.css');

	}

	/**
	 * Gets address data from DB (by GET params P)
	 * @return array
	 */
	protected function getAddressDataFromDb(){

		// Parameters
		$parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('P');

		// print_r($parameters);

		$addressData = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord($parameters['table'], $parameters['uid']);

		// If it is a new, unsaved record, we get the following parameter for uid:
		// e.g.: [uid] => NEW57f654657625f780378440
		// So set at least the uid, so we can read the fields inside the parent window
		if(empty($addressData) && preg_match('/^NEW/', $parameters['uid'])){

			unset($addressData);
			$addressData = array(
				'uid' => $parameters['uid']
			);

		}

		return $addressData;

	}


	/**
	 * Inline Js, attention: we need to write TYPO3.jQuery  here instead of jquery...
	 * initMap: Resources/Public/JavaScript/googleGeocoderBackend.js
	 * @param $addressData
	 * @return string
	 */
	protected function getInlineJs($addressData){

		$js = '';

		$js.= '<script type="text/javascript">';

		// AddressData from DB
		/*
		$js.='
			var address = "";
			address += "' . $addressData['address'] . '";
			address += ", ";
			address += "' . $addressData['zip'] . ' ' . $addressData['city'] . '";
			address += ", ";
			address += "' . $addressData['countryString'] . '";
		';
		*/

		// AddressData from parent.window
		$js.='

			// TYPO3.jQuery(document).ready(function(){
			// });

			if (parent.window.opener){
			
				var addressAddress = parent.window.opener.TYPO3.jQuery("[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $addressData['uid'] . '][street_and_nr]\']").val();
				var addressZip = parent.window.opener.TYPO3.jQuery("[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $addressData['uid'] . '][zip]\']").val();
				var addressCity = parent.window.opener.TYPO3.jQuery("[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $addressData['uid'] . '][city]\']").val();
				// Selectbox => static_countries
				var addressCountry = parent.window.opener.TYPO3.jQuery("select[name=\'data[tx_jvevents_domain_model_location][' . $addressData['uid'] . '][country]\'] option:selected").text();
				// Try to get the value from input-field (static_info_tables is not installed)
				if(addressCountry == ""){
					addressCountry = parent.window.opener.TYPO3.jQuery("[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $addressData['uid'] . '][country]\']").val();
				}
			
				var address = "";
				address += addressAddress;
				address += ", ";
				if (addressZip != ""){
					address += addressZip;
					address += " ";
				}
				address += addressCity;
				address += ", ";
				address += addressCountry;
				
				// All addressData (without zip) have to be entered, if not, Google will find nothing and an error message is shown
				if(addressAddress == "" || addressCity == "" || addressCountry == ""){
					address = "";
				}
				
				// console.log(address);

				// Done by API-call
				// initMap();

			}else{
				showErrorMessage("' . $this->getLanguageService()->getLL('geocoding.error.referenceToParentWindow') . '");
			}
		
		';

		// We need this here and not in a file, because we don't need this in the head
		$js.= '
		
			var map = null;
			var geocoder = null;
			var bounds = null;
			var marker = null;

			/**
			 * Creates the google map
			 * Called by: https://maps.googleapis.com/maps/api/geocode/output?[parameters]
			 */
			function initMap() {
			
				// Set the center of the map (value not important, because we bound the markers to set the center of the map)
				var myLatLng = {lat: -25.363, lng: 131.044};
			
				// Create the map
				map = new google.maps.Map(document.getElementById(\'map\'), {
					zoom: 8,
					center: myLatLng
				});
			
				// Google geocoder
				geocoder = new google.maps.Geocoder();
			
				// Bounding for centering the map
				bounds = new google.maps.LatLngBounds();
			
				// Marker
				marker = new google.maps.Marker({map: map});
			
				// Find the address
				findAddress({"address": address});
				
				// Put the address into the search field
				TYPO3.jQuery("#geosearch input#search").val(address);
			
			}
			
			/**
			 * Finds the address
			 */
			function findAddress(address){
			
				// Hide error-message (maybe there is one shown)
				TYPO3.jQuery("#errormessage").hide();
			
				geocoder.geocode(address, function(results, status) {
			
					if (status == google.maps.GeocoderStatus.OK) {
			
						// console.log(results);
			
						/**
						 * Create marker at searched position
						 */
						marker.setPosition(results[0].geometry.location);
						marker.setTitle(address.address);
			
						/**
						 * Center map on marker and zoom if needed
						 */
						map.panTo(results[0].geometry.location);
						map.setZoom(15);
			
						/**
						 * Update fields longitude and latitude
						 */
						TYPO3.jQuery("#lat").val(results[0].geometry.location.lat());
						TYPO3.jQuery("#lng").val(results[0].geometry.location.lng());
			
					} else {
						showErrorMessage("' . $this->getLanguageService()->getLL('geocoding.error.geocodingNotSuccessful.part1') . ' " + status + " ' . $this->getLanguageService()->getLL('geocoding.error.geocodingNotSuccessful.part2') . '");
					}
			
				});
				
			}
			
			/**
			 * Rounds the number
			 */
			function roundDataToNumber(value, ndec){
				var n = 10;
				for(var i = 1; i < ndec; i++){
					n *=10;
				}
				return ((Math.round(value * n) / n).toFixed(ndec)).toString();
			}			
			
			/**
			 * Inserts latitude and longitude in parent window
			 */
			function insertValuesInParentWindow(){
				if (parent.window.opener)	{
					// Visible fields
					parent.window.opener.TYPO3.jQuery("input[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $addressData['uid'] . '][lat]\']").val(roundDataToNumber(TYPO3.jQuery("#lat").val(),11));
					parent.window.opener.TYPO3.jQuery("input[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $addressData['uid'] . '][lng]\']").val(roundDataToNumber(TYPO3.jQuery("#lng").val(),11));
					// Hidden fields
					parent.window.opener.TYPO3.jQuery("input[name=\'data[tx_jvevents_domain_model_location][' . $addressData['uid'] . '][lat]\']").val(roundDataToNumber(TYPO3.jQuery("#lat").val(),11));
					parent.window.opener.TYPO3.jQuery("input[name=\'data[tx_jvevents_domain_model_location][' . $addressData['uid'] . '][lng]\']").val(roundDataToNumber(TYPO3.jQuery("#lng").val(),11));
					window.close();
				} else {
					showErrorMessage("' . $this->getLanguageService()->getLL('geocoding.error.referenceToParentWindow') . '");
				}
			}
			
			/**
			 * Shows an errormessage
			 */
			function showErrorMessage(message){
				TYPO3.jQuery("#errormessage").show();
				TYPO3.jQuery("#errormessage div").text("' . $this->getLanguageService()->getLL('geocoding.error') . ': " + message);
			}

		';

		$js.= '</script>';

		return $js;

	}


	/**
	 * Main function - handles the output
	 * @return void
	 */
	protected function main(){

		$addressData = $this->getAddressDataFromDb();
		$this->content .= $this->getInlineJs($addressData);

		$this->content .='

			<div id="errormessage" class="alert alert-danger">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				<div></div>
			</div>


			<form id="geosearch" name="geosearch" class="form-inline">
			
			  <div class="form-group">
				<label for="search">' . $this->getLanguageService()->getLL('geocoding.form.geosearch.label.searchFor') . ':</label>
				<input type="text" class="form-control" id="search" value="">
			  </div>			
			  
			  <input class="btn btn-default" type="button" name="submit" value="' . $this->getLanguageService()->getLL('geocoding.form.geosearch.label.find') . '" onclick="findAddress({\'address\': TYPO3.jQuery(\'#search\').val()})" />

			</form>			


			<form id="transferGeoData" name="transferGeoData" class="form-inline">
			
			  <div class="form-group">
				<label for="lat">' . $this->getLanguageService()->getLL('geocoding.form.transferGeoData.label.latitude') . ':</label>
				<input type="text" class="form-control" id="lat" readonly>
			  </div>			
			
			  <div class="form-group">
				<label for="lng">' . $this->getLanguageService()->getLL('geocoding.form.transferGeoData.label.longitude') . ':</label>
				<input type="text" class="form-control" id="lng" readonly>
			  </div>
			  
			  <input class="btn btn-primary" type="button" name="submit" value="' . $this->getLanguageService()->getLL('geocoding.form.transferGeoData.label.submit') . '" onclick="insertValuesInParentWindow();" />
			  <input class="btn btn-danger" type="button" name="cancel" value="' . $this->getLanguageService()->getLL('geocoding.form.transferGeoData.label.cancel') . '" onclick="window.close();" />

			</form>
			
			
			<div id="map" style="height:500px;"></div>
		';

	}


	/**
	 * Injects the request object for the current request or subrequest
	 * Calles by Configuration/Backend/Routes.php
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface $response
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function mainAction(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response){

		$this->content = '';
		$this->content .= $this->doc->startPage($this->getLanguageService()->getLL('geocoding.page.title'));
		$this->content .= $this->doc->header($this->getLanguageService()->getLL('geocoding.page.headline'));
		$this->main();
		$this->content .= $this->doc->endPage();
		$response->getBody()->write($this->content);
		return $response;

	}

}
