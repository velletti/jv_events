<?php
namespace JVE\JvEvents\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class Geocoder
 * @package JVE\JvEvents\Utility
 */

class GeocoderUtility {

    /**
     * @var string
     */
    public  $javascriptCode ;

    /**
     * array
     */
    public $styleSheet ;
	/**
	 * Geocoder constructor
	 */

    /**
     * Returns an instance of LanguageService
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        /** @var LanguageService $lang */
        $lang = GeneralUtility::makeInstance(\TYPO3\CMS\Lang\LanguageService::class) ;
        if (TYPO3_MODE === 'BE') {
            $lng = $GLOBALS['BE_USER']->uc['lang'] ;
        } else {
            $lng = $GLOBALS['TSFE']->config['config']['language'] ;
        }
        if ( $lng == '' ) { $lng = "en" ;}
        $lang->init($lng) ;

        return $lang ;
    }

	public function __construct() {

        $this->init();
	}

	/**
	 * Some initializing
	 */
    public function init(){

        $this->getLanguageService()->includeLLFile('EXT:jv_events/Resources/Private/Language/locallang_be.xlf');

		$googleApiKey = \JVE\JvEvents\Utility\EmConfigurationUtility::getGoogleApiKey();
        $this->javascriptCode = '<script async defer src="https://maps.googleapis.com/maps/api/js?key=' . $googleApiKey . '&callback=initMap"></script>';


	}


	/**
	 * Inline Js, attention: we need to write TYPO3.jQuery  here instead of jquery...
	 * initMap: Resources/Public/JavaScript/googleGeocoderBackend.js
	 * @param array $addressData
	 * @param integer $uid
	 * @param string $jQueryName
     * @param array $formfieldIds
     * @param string $updateFunction
	 * @return string
	 */
	protected function getInlineJs($addressData = false , $uid = 0 , $jQueryName = "TYPO3.jQuery" , $formfieldIds= false , $updateFunction='' ){

		$js = '<script type="text/javascript">';

        if ( $addressData ) {
            // get AddressData variables from incomming array add Function to write back to fields
            $js.= $this->inlineJsFromAddressData($addressData , $jQueryName ,  $updateFunction ) ;
        } else {
            if ( $formfieldIds ) {
                // get AddressData variables from incomming array add Function to write back to fields
                $js.= $this->inlineJsFromFormfieldIds($formfieldIds , $jQueryName  ,  $updateFunction ) ;
            } else {
                // get AddressData variables from parent.window and add Function to write back to that window
                $js.= $this->inlineJsFromParentWindow($uid , $jQueryName  ,  $updateFunction  ) ;

            }

        }
        $updateFunctionCode = '' ;
        if( $updateFunction ) {
            // $updateFunction should look like: "jv_events_refreshList();"
            $updateFunctionName = explode("(" , $updateFunction) ;
            $updateFunctionCode = 'if (typeof ' . $updateFunctionName[0] . ' === "function") {' . "\n" .
                $updateFunction . "\n"
            .'}'  . "\n"  . "\n" ;
        }
       // $updateFunctionCode = '' ;
		// We need this here and not in a file, because we don't need this in the head
		$js.= '
		
		    var address = concatAddress();
            // console.log(address);

            
            var map = null;
            var geocoder = null;
            var bounds = null;
            var marker = null;
            
			
            // Done by API-call
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
		
		        // console.log(address);	
				// Find the address
				findAddress({"address": address});
				
				// Put the address into the search field
				
				' . $jQueryName . '("#geosearch input#geosearchbox").val(address);
			
			}
			
			function concatAddress() {
                address = "";
                address += addressAddress;
                
                if (addressZip && addressZip != ""){
                    address += ", ";
                    address += addressZip;
                    address += " ";
                }
                address += addressCity;
                if (addressCountry && addressCountry != ""){
                    address += ", ";
                    address += addressCountry;
                }
                
                // All addressData (without zip) have to be entered, if not, Google will find nothing and an error message is shown
                var parts = address.split(",") ;
                if( parts.length < 2  ){
                    address = "";
                }
			    return address ;
			}
			/**
			 * Finds the address
			 */
			function findAddress(address){
			
				// Hide error-message (maybe there is one shown)
				
				' . $jQueryName . '("#geosearcherrormessage").hide();
			
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
						  geoCoderFieldUpdate( results[0].geometry.location ) ;
						
			
					} else {
						showErrorMessage("' . $this->getLanguageService()->getLL('geocoding.error.geocodingNotSuccessful.part1') . ' " + status + " ' . $this->getLanguageService()->getLL('geocoding.error.geocodingNotSuccessful.part2') . '");
					}
			
				});
				// update eventlist if needed 
				
				'

            . $updateFunctionCode .
				'
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
			 * Shows an errormessage
			 */
			function showErrorMessage(message){
				' . $jQueryName . '("#geosearcherrormessage").show();
				' . $jQueryName . '("#geosearcherrormessage div").text("' . $this->getLanguageService()->getLL('geocoding.error') . ': " + message);
			}

		';

		$js.= '</script>';

		return $js;

	}

    /**
     * @param array $formfieldIds array of Selectors for jQuery
     * @param string $jQueryName
     * @return string
     */
	public function inlineJsFromFormfieldIds($formfieldIds , $jQueryName , $updateFunction='') {
        $updateFunctionCode = '' ;
        if( $updateFunction ) {
            // $updateFunction should look like: "jv_events_refreshList();"
            $updateFunctionName = explode("(" , $updateFunction) ;
            $updateFunctionCode = 'if (typeof ' . $updateFunctionName[0] . ' === "function") {' . "\n" .
                $updateFunction . "\n"
                .'}'  . "\n"  . "\n" ;
        }

	    return '
	        var addressAddress = ' . $jQueryName . '("' . $formfieldIds['address'] . '").val() ; 
            var addressZip = '     . $jQueryName . '("' . $formfieldIds['zip'] . '").val() ; 
            var addressCity = '    . $jQueryName . '("' . $formfieldIds['city'] . '").val() ; 
            var addressCountry = ' . $jQueryName . '("' . $formfieldIds['country'] . '").val() ;  
	        
	        function updateAddressFromFields() {
                addressAddress = ' . $jQueryName . '("' . $formfieldIds['address'] . '").val() ; 
                addressZip = '     . $jQueryName . '("' . $formfieldIds['zip'] . '").val() ; 
                addressCity = '    . $jQueryName . '("' . $formfieldIds['city'] . '").val() ; 
                addressCountry = ' . $jQueryName . '("' . $formfieldIds['country'] . '").val() ;   
                address = concatAddress(); 
                return address ;
                
            }
            
            /**
		    * updates the fields in search Form 
		    */
		    /**
		    * updates the fields in search Form if available
		    */
			function geoCoderFieldUpdate( location ) {
			    ' . $jQueryName . '("'. $formfieldIds['return']['lat'] . '").val( roundDataToNumber(location.lat() , 11) );
				' . $jQueryName . '("'. $formfieldIds['return']['lng'] . '").val( roundDataToNumber(location.lng() , 11) );
				' . $updateFunctionCode
               .'
			}
	    ' ;

    }

    /**
     * @param array $addressData
     * @param string $jQueryName
     * @param string $updateFunction
     * @return string
     */

    public function inlineJsFromAddressData($addressData , $jQueryName , $updateFunction='') {
        $updateFunctionCode = '' ;
        if( $updateFunction ) {
            // $updateFunction should look like: "jv_events_refreshList();"
            $updateFunctionName = explode("(" , $updateFunction) ;
            $updateFunctionCode = 'if (typeof ' . $updateFunctionName[0] . ' === "function") {' . "\n" .
                $updateFunction . "\n"
                .'}'  . "\n"  . "\n" ;
        }
        // AddressData from parent.window
        return '
			
            var addressAddress = "' .$addressData['address'] . '" ;
            var addressZip = "' . $addressData['zip'] . '" ;
            var addressCity = "' . $addressData['city'] . '";
            var addressCountry = "' . $addressData['country'] . '";
        
            

		
			/**
			 * Inserts latitude and longitude in parent window
			 */
			function insertValuesInParent(){
                // Visible fields
                ' .$jQueryName . '("' . $addressData['return']['lat'] .'").val(roundDataToNumber(' . $jQueryName . '("#geosearchlat").val(),11));
                ' .$jQueryName . '("' . $addressData['return']['lng'] .'").val(roundDataToNumber(' . $jQueryName . '("#geosearchlng").val(),11));
                // Hidden fields
                
                windowOrModalclose();
			}
			/**
		    * updates the fields in search Form if available
		    */
			function geoCoderFieldUpdate( location ) {
			    ' . $jQueryName . '("#geosearchlat").val( location.lat() );
				' . $jQueryName . '("#geosearchlng").val( location.lng() );
				' . $updateFunctionCode
            .'
			}
			function windowOrModalclose() {
			   ' .$jQueryName . '("#geoSearchModal").removeClass("in").css("display" , "none") ;
			}
		';
    }

    /**
     * @param int $uid
     * @param string $jQueryName
     * @param string $updateFunction
     * @return string
     */
	public function inlineJsFromParentWindow($uid , $jQueryName , $updateFunction='' ) {

            // AddressData from parent.window
            return '

			// ' . $jQueryName . '(document).ready(function(){
			// });

			if (parent.window.opener){
			
				var addressAddress = parent.window.opener.' . $jQueryName . '("[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $uid . '][street_and_nr]\']").val();
				var addressZip = parent.window.opener.' . $jQueryName . '("[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $uid . '][zip]\']").val();
				var addressCity = parent.window.opener.' . $jQueryName . '("[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $uid . '][city]\']").val();
				// Selectbox => static_countries
				var addressCountry = parent.window.opener.' . $jQueryName . '("select[name=\'data[tx_jvevents_domain_model_location][' . $uid . '][country]\'] option:selected").text();
				// Try to get the value from input-field (static_info_tables is not installed)
				if(addressCountry == ""){
					addressCountry = parent.window.opener.' . $jQueryName . '("[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $uid . '][country]\']").val();
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
		    /**
		    * updates the fields in search Form 
		    */
		    function geoCoderFieldUpdate( location ) {
			    ' . $jQueryName . '("#geosearchlat").val( location.lat() );
				' . $jQueryName . '("#geosearchlng").val( location.lng() );
			}
			
			/**
			 * Inserts latitude and longitude in parent window
			 */
			function insertValuesInParent(){
				if (parent.window.opener)	{
					// Visible fields
					parent.window.opener.' . $jQueryName . '("input[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $uid . '][lat]\']").val(roundDataToNumber(' . $jQueryName . '("#geosearchlat").val(),11));
					parent.window.opener.' . $jQueryName . '("input[data-formengine-input-name=\'data[tx_jvevents_domain_model_location][' . $uid . '][lng]\']").val(roundDataToNumber(' . $jQueryName . '("#geosearchlng").val(),11));
					// Hidden fields
					parent.window.opener.' . $jQueryName . '("input[name=\'data[tx_jvevents_domain_model_location][' . $uid . '][lat]\']").val(roundDataToNumber(' . $jQueryName . '("#geosearchlat").val(),11));
					parent.window.opener.' . $jQueryName . '("input[name=\'data[tx_jvevents_domain_model_location][' . $uid . '][lng]\']").val(roundDataToNumber(' . $jQueryName . '("#geosearchlng").val(),11));
					window.close();
				} else {
					showErrorMessage("' . $this->getLanguageService()->getLL('geocoding.error.referenceToParentWindow') . '");
				}
			}
			function windowOrModalclose() {
			    window.close();
			}
		';
    }

	/**
	 * Main function - handles the output
     * @param array $addressData
     *
     * @param integer $uid
     * @param string $jQueryName
     * @param array $formfieldIds
     * @param string $updateFunction
	 * @return string
	 */
	public function main($addressData = false , $uid = 0 , $jQueryName = "TYPO3.jQuery" , $formfieldIds=false , $updateFunction=''){


		$content = $this->getInlineJs($addressData , $uid , $jQueryName , $formfieldIds , $updateFunction );

		if ( ! $formfieldIds ) {

            $content .='
    
                <div id="geosearcherrormessage" class="alert alert-danger">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>
                    <div></div>
                </div>
    
    
                <form id="geosearch" name="geosearch" class="form-inline">
                
                  <div class="form-group ">
                    <label for="geosearchbox">' . $this->getLanguageService()->getLL('geocoding.form.geosearch.label.searchFor') . ':</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="geosearchbox" value="">
                        <input class="input-group-addon btn btn-default" type="button" name="submit" value="' . $this->getLanguageService()->getLL('geocoding.form.geosearch.label.find') . '" onclick="findAddress({\'address\': ' . $jQueryName . '(\'#geosearchbox\').val()})" />
                      </div>			
                  </div>			
                  
    
                </form>			
    
    
                <form id="transferGeoData" name="transferGeoData" class="form-inline">
                
                  <div class="form-group">
                    <label for="geosearchlat">' . $this->getLanguageService()->getLL('geocoding.form.transferGeoData.label.latitude') . ':</label>
                    <input type="text" class="form-control" id="geosearchlat" readonly>
                  </div>			
                
                  <div class="form-group">
                    <label for="geosearchlng">' . $this->getLanguageService()->getLL('geocoding.form.transferGeoData.label.longitude') . ':</label>
                    <input type="text" class="form-control" id="geosearchlng" readonly>
                  </div>
                  
                  <input class="btn btn-primary" type="button" name="submit" value="' . $this->getLanguageService()->getLL('geocoding.form.transferGeoData.label.submit') . '" onclick="insertValuesInParent();" />
                  <input class="btn btn-danger" type="button" name="cancel" value="' . $this->getLanguageService()->getLL('geocoding.form.transferGeoData.label.cancel') . '" onclick="windowOrModalclose();" />
    
                </form>
                
                
                <div id="map" style="height:500px;"></div>
            ';

        }

        return $content ;
	}

}
