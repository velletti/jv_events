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
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService()
    {
        /** @var \TYPO3\CMS\Core\Localization\LanguageService $lang */
        $lang = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\LanguageService::class) ;
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
        $this->javascriptCode = '<script async defer src="https://maps.googleapis.com/maps/api/js?key=' . $googleApiKey . '&callback=initGeoCoderMap"></script>';


	}


	/**
	 * Inline Js, attention: we need to write TYPO3.jQuery  here instead of jquery...
	 * initGeoCoderMap: Resources/Public/JavaScript/googleGeocoderBackend.js
	 * @param array $addressData
	 * @param integer $uid
	 * @param string $jQueryName
     * @param array $formfieldIds
     * @param string $updateFunction
	 * @return string
	 */
	protected function getInlineJs($addressData = false , $uid = 0 , $jQueryName = "TYPO3.jQuery" , $formfieldIds= false , $updateFunction='' )
    {

        $js = '<script type="text/javascript">';

        if ($addressData) {
            // get AddressData variables from incomming array add Function to write back to fields
            $js .= $this->inlineJsFromAddressData($addressData, $jQueryName, $updateFunction);
        } else {
            if ($formfieldIds) {
                if (array_key_exists("input", $formfieldIds)) {
                    $js .= $this->inlineJsFromLatLng($formfieldIds, $jQueryName, $updateFunction);
                } else {
                    // get AddressData variables from incomming array add Function to write back to fields
                    $js .= $this->inlineJsFromFormfieldIds($formfieldIds, $jQueryName, $updateFunction);
                }

            } else {
                // get AddressData variables from parent.window and add Function to write back to that window
                $js .= $this->inlineJsFromParentWindow($uid, $jQueryName, $updateFunction);
            }

        }
        $updateFunctionCode = '';
        if ($updateFunction) {
            // $updateFunction should look like: "jv_events_refreshList();"
            $updateFunctionName = explode("(", $updateFunction);
            $updateFunctionCode = 'if (typeof ' . $updateFunctionName[0] . ' === "function") {' . "\n" .
                $updateFunction . "\n"
                . '}' . "\n" . "\n";
        }
        // $updateFunctionCode = '' ;
        // We need this here and not in a file, because we don't need this in the head
        $js .= '
            if (typeof initAddress !== "undefined") {
		       var address = initAddress ; 
		    } else {
		       var address = concatAddress();
		    }
            // console.log(address);
            
            var map = null;
            var geocoder = null;
            var bounds = null;
            var marker = null;
            
			
			function getCookies(name) {
                var v = document.cookie.match("(^|;) ?" + name + "=([^;]*)(;|$)");
                return v ? v[2] : null;
            }

            // Done by API-call
			/**
			 * Creates the google map
			 * Called by: https://maps.googleapis.com/maps/api/geocode/output?[parameters]
			 */
			function initGeoCoderMap() {
			
				// Set the center of the map (value not important, because we bound the markers to set the center of the map)
				var myLatLng = {lat: 11.4712, lng: 48.1148};
				 if (typeof initZoom !== "undefined") {
                    map = new google.maps.Map(document.getElementById(\'map\'), {
                        zoom: initZoom ,
                        streetViewControl: false,
                        mapTypeControl: false,
                        draggableCursor: "default" ,
                        center: myLatLng
                    });
				} else {
                    // Create the map
                    map = new google.maps.Map(document.getElementById(\'map\'), {
                        zoom: 8,
                        streetViewControl: false,
                        mapTypeControl: false,
                        draggableCursor: "default" ,
                        center: myLatLng
                    });
				}
				map.addListener( \'zoom_changed\', function() {
                    if(document.getElementById("zoom")) {
                        document.getElementById("zoom").value = map.getZoom() ;
                    }
                    updatePosition() ;
                });
				map.addListener( \'click\', function(e) {
				    marker.setPosition( e.latLng ) ;
				    map.panTo(marker.getPosition()) ;
                    var zoom = map.getZoom();
                    if ( zoom < 9 ) {
                      zoom = 9 ;
                    }
                  
                    map.setZoom( zoom ) ;
                    updatePosition() ;
                    
                });
                map.addListener( \'dblclick\', function(e) {
				    marker.setPosition( e.latLng ) ;
				    map.panTo(marker.getPosition()) ;
                    var zoom = map.getZoom();
                    if( jQuery("#jv_events_geo").length ) {
                       if ( jQuery("#jv_events_geo").data( "doubleclickzoom" ) ) {
                          zoom = jQuery("#jv_events_geo").data( "doubleclickzoom" ) 
                       }
                    }
                    if ( zoom < 9 ) {
                      zoom = 9 ;
                    }
                    
                  
                    map.setZoom( zoom ) ;
                    updatePosition() ;
                    
                });
			
				// Google geocoder
				geocoder = new google.maps.Geocoder();
			
				// Bounding for centering the map
				bounds = new google.maps.LatLngBounds();
			
		        // console.log(address);	
				// Find the address 
				
				    findAddress({"address": address});
				    // Put the address into the search field
				' . $jQueryName . '("#geosearch input#geosearchbox").val(address);
			
				
				
				
			}
			
			function updatePosition() {
				if(document.getElementById("lat")) {
					document.getElementById("lat").value =  marker.getPosition().lat() ;
				}
				if(document.getElementById("lng")) {
					document.getElementById("lng").value =  marker.getPosition().lng() ;
				}

				if(document.getElementById("jvevents-geo-ok")) {
					document.getElementById("jvevents-geo-ok").style.opacity = "1" ;
				}
				if(document.getElementById("jvevents-geo-update")) {
					document.getElementById("jvevents-geo-update").style.opacity = ".8" ;
				}
		
                if ( getCookies("tx_cookies_accepted") == "1") {
                   // Set cookie for 365 days
                    var d = new Date();
                    d.setTime(d.getTime() + ( 24*60*60*1000 * 365));
                    var expires = "expires=" + d.toUTCString();
                    document.cookie = "tx_events_lat=" + marker.getPosition().lat() + "; " + expires + ";path=/";
                    document.cookie = "tx_events_lng=" + marker.getPosition().lng() + "; " + expires + ";path=/";
                }
                ' . $updateFunctionCode .
            '
			}
			function concatAddress() {
                address = "";
                address += addressAddress;
                var notFullAddress = true ;
                if (addressZip && addressZip != ""){
                    address += ", ";
                    address += addressZip;
                    address += ", ";
                    notFullAddress = false ;
                }
                 if (addressCity && addressCity != ""){
                    address += addressCity;
                    address += " ";
                    notFullAddress = false ;
                }
                if (addressCountry && addressCountry != ""){
                    address += ", ";
                    address += addressCountry;
                    notFullAddress = false ;
                }
                
                if ( getCookies("tx_events_lat") && getCookies("tx_events_lng")  && notFullAddress ) {
                //   return getCookies("tx_events_lat") + "," + getCookies("tx_events_lng") ;
                }
                
                // All addressData (without zip) have to be entered, if not, Google will find nothing and an error message is shown
                var parts = address.split(",") ;
                if( parts.length < 1  ){
                    if( jQuery("#streetAndNr").length && jQuery("#streetAndNr").data("default-city") ) {
                        address += ", " + jQuery("#streetAndNr").data("default-city") ; 
                    }
                    if( parts.length < 2  ){
                        if( jQuery("#streetAndNr").length && jQuery("#streetAndNr").data("default-cntry") ) {
                            address += ", " + jQuery("#streetAndNr").data("default-cntry") ; 
                        }
                        address = "";
                    }
                }
                if ( address == "" ) {
                    if(document.getElementById("lat") && document.getElementById("lng") ) {
					   address = document.getElementById("lat").value + "," +document.getElementById("lng").value;
				    }
				}
				
			    return address ;
			}
			/**
			 * Finds the address
			 */
			function findAddress(address){
			    	
				// Hide error-message (maybe there is one shown)
				document.cookie = "tx_events_lat=\'\'; -1000;path=/";
				document.cookie = "tx_events_lng=\'\'; -1000;path=/";
				' . $jQueryName . '("#geosearcherrormessage").hide();
				
				if ( !geocoder ) {
				     // retryIntervalId = setInterval(function () { initGeoCoderMap() }, 1000);
				     initGeoCoderMap();
				} else {
                    
                   // refreshIntervalId = setInterval(function () { updateMapTimer(map) }, 300);
                    
                    geocoder.geocode(address, function(results, status) {
                
                        if (status == google.maps.GeocoderStatus.OK) {
                            // console.log(results);
                
                            /**
                             * Create marker at searched position
                             */
                            
                            var draggable = false ;
                            
                            if(document.getElementById("lat")) {
                                draggable = true ;
                                address.address += "\n\n  Click to update Position  \nDouble Click to update and zoom in" ;
                            } 
                            // console.log( "geometrie Location") ;
                            // console.debug( results[0].geometry.location ) ;
                            if( marker ) {
                                marker.setPosition(results[0].geometry.location) ;
                            } else {
                                marker = new google.maps.Marker({
                                        position: results[0].geometry.location,
                                        map: map,
                                        zIndex: 99999,
                                        title: address.address,
                                        draggable: draggable
                                    });
                            }    
                            if(draggable ) {
                                marker.addListener("drag", updatePosition);
                            }
                              marker.addListener("click", () => {
                                map.setZoom(9);
                                map.panTo(marker.getPosition()) ;
                              });
                              
                              marker.addListener("dblclick", () => {
                                let doubleCLickZoom = 9 ;  
                                if( jQuery("#jv_events_geo").length ) {
                                   if ( jQuery("#jv_events_geo").data( "doubleclickzoom" ) ) {
                                      doubleCLickZoom = jQuery("#jv_events_geo").data( "doubleclickzoom" ) 
                                   }
                                }
                                map.setZoom(doubleCLickZoom);
                                map.panTo(marker.getPosition()) ;
                              });
                            
                            map.addListener("zoom_changed" , updateCity ) ;
                            
                            function updateCity() {
                                if( jQuery("SELECT#jv_events_filter_citys") ) {
                                    jQuery("SELECT#jv_events_filter_citys").val("") ;
                                }
                            }
                
                            function updatePosition() {
                                if(document.getElementById("lat")) {
                                    document.getElementById("lat").value = marker.getPosition().lat() ;
                                }
                                if(document.getElementById("lng")) {
                                    document.getElementById("lng").value = marker.getPosition().lng() ;
                                }
                                if(document.getElementById("jvevents-geo-ok")) {
                                    document.getElementById("jvevents-geo-ok").style.opacity = "1" ;
                                }
                                if(document.getElementById("jvevents-geo-update")) {
                                    document.getElementById("jvevents-geo-update").style.opacity = ".8" ;
                                }
                                if ( getCookies("tx_cookies_accepted") == "1") {
                                   // Set cookie for 365 days
                                    var d = new Date();
                                    d.setTime(d.getTime() + ( 24*60*60*1000 * 365));
                                    var expires = "expires=" + d.toUTCString();
                                    document.cookie = "tx_events_lat=" + marker.getPosition().lat() + "; " + expires + ";path=/";
                                    document.cookie = "tx_events_lng=" + marker.getPosition().lng() + "; " + expires + ";path=/";
                                }
                                ' . $updateFunctionCode . ' 
                                map.panTo(marker.getPosition()) ;
                                if( jQuery("SELECT#jv_events_filter_citys") ) {
                                    jQuery("SELECT#jv_events_filter_citys").val("") ;
                                }
                            }
                            function getCookies(name) {
                                var v = document.cookie.match("(^|;) ?" + name + "=([^;]*)(;|$)");
                                return v ? v[2] : null;
                            }
                            /**
                             * Center map on marker and zoom if needed
                             */
                            map.panTo(results[0].geometry.location);
                            if ( typeof initZoom !== "undefined") {
                                map.setZoom(initZoom);
                            } else {
                                map.setZoom(15);
                            }
                
                            /**
                             * Update fields longitude and latitude
                             */
                              geoCoderFieldUpdate( results[0].geometry.location ) ;
                              
                
                        } else {
                            showErrorMessage("' . $this->getLanguageService()->getLL('geocoding.error.geocodingNotSuccessful.part1') . ' " + status + " ' . $this->getLanguageService()->getLL('geocoding.error.geocodingNotSuccessful.part2') . '");
                        }
                
                    });
                }
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
			function updateMarker(address) {
			    if( ! address.includes(",")) {
                   address = address + ",DE" ;
                }
                if ( map && map.getZoom() < 10 ) {
                    initZoom = 10 ;
                } else {
                   initZoom = map.getZoom() ;
                }
                findAddress({address: address}) ;
               
			    
			}
            function updateMapTimer(map) {
                clearInterval(refreshIntervalId);
                var center = map.getCenter() ;
                google.maps.event.trigger(map, \'resize\');
                map.setCenter(center) ;
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
            
            function updateMapTimer(map) {
                clearInterval(refreshIntervalId);
                var center = map.getCenter() ;
                google.maps.event.trigger(map, \'resize\');
                map.setCenter(center) ;
            }

            /**
		    * updates the fields in search Form 
		    */
		    /**
		    * updates the fields in search Form if available
		    */
			function geoCoderFieldUpdate( location ) {
			    if ( getCookies("tx_cookies_accepted") == "1") {
			        // Set cookie for 365 days
                    var d = new Date();
                    d.setTime(d.getTime() + ( 24*60*60*1000 * 365));
                    var expires = "expires=" + d.toUTCString();
                    document.cookie = "tx_events_lat=" + location.lat() + "; " + expires + ";path=/";
                    document.cookie = "tx_events_lng=" + location.lng() + "; " + expires + ";path=/";
                }
			    ' . $jQueryName . '("'. $formfieldIds['return']['lat'] . '").val( roundDataToNumber(location.lat() , 11) );
				' . $jQueryName . '("'. $formfieldIds['return']['lng'] . '").val( roundDataToNumber(location.lng() , 11) );
				
				' . $updateFunctionCode
               .'
			}
			function getCookies(name) {
                var v = document.cookie.match("(^|;) ?" + name + "=([^;]*)(;|$)");
                return v ? v[2] : null;
            }
	    ' ;

    }


    /**
     * @param array $formfieldIds array of Selectors for jQuery
     * @param string $jQueryName
     * @return string
     */
    public function inlineJsFromLatLng($formfieldIds , $jQueryName , $updateFunction='') {
        $updateFunctionCode = '' ;
        if( $updateFunction ) {
            // $updateFunction should look like: "jv_events_refreshList();"
            $updateFunctionName = explode("(" , $updateFunction) ;
            $updateFunctionCode = 'if (typeof ' . $updateFunctionName[0] . ' === "function") {' . "\n" .
                $updateFunction . "\n"
                .'}'  . "\n"  . "\n" ;
        }

        return '
	        var initAddress = "' . $formfieldIds['input']['address'] . '"; 
	        var initZoom = ' . $formfieldIds['input']['zoom'] . '; 
            
            function updateMapTimer(map) {
                clearInterval(refreshIntervalId);
                var center = map.getCenter() ;
                google.maps.event.trigger(map, \'resize\');
                map.setCenter(center) ;
            }

            /**
		    * updates the fields in search Form 
		    */
		    /**
		    * updates the fields in search Form if available
		    */
			function geoCoderFieldUpdate( location ) {
			    if ( getCookies("tx_cookies_accepted") == "1") {
			        // Set cookie for 365 days
                    var d = new Date();
                    d.setTime(d.getTime() + ( 24*60*60*1000 * 365));
                    var expires = "expires=" + d.toUTCString();
                    document.cookie = "tx_events_lat=" + location.lat() + "; " + expires + ";path=/";
                    document.cookie = "tx_events_lng=" + location.lng() + "; " + expires + ";path=/";
                }
			    ' . $jQueryName . '("'. $formfieldIds['return']['lat'] . '").val( roundDataToNumber(location.lat() , 11) );
				' . $jQueryName . '("'. $formfieldIds['return']['lng'] . '").val( roundDataToNumber(location.lng() , 11) );
				' . $updateFunctionCode
            .'
			}
			function getCookies(name) {
                var v = document.cookie.match("(^|;) ?" + name + "=([^;]*)(;|$)");
                return v ? v[2] : null;
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
        
            function updateMapTimer(map) {
                clearInterval(refreshIntervalId);
                var center = map.getCenter() ;
                google.maps.event.trigger(map, \'resize\');
                map.setCenter(center) ;
            }

		
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
				// initGeoCoderMap();

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
