/**
 * Created by velletti on 29.09.2016.
 * Last Change:
 */
jQuery(document).ready(function() {
	jv_events_init() ;
}) ;
//  ############   generic function for everyone: test if a spezific Parameter is in URL and return its value ###########
function GetURLParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++) {
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam) {
			return  decodeURIComponent(sParameterName[1]);
		}
	}
}

function jv_events_init() {

	// http://nemetschek.local/index.php?id=116&tx_jvevents_events[eventsFilter][categories]=3&tx_jvevents_events[eventsFilter][citys]=4
	// http://nemetschek.local/index.php?id=116&tx_jvevents_events[eventsFilter][categories]=3&tx_jvevents_events[eventsFilter][citys]=4&tx_jvevents_events[eventsFilter][tags]=3&tx_jvevents_events[eventsFilter][months]=03.2017

	jv_events_initOneFilter('categories') ;
	jv_events_initOneFilter('locations') ;
	jv_events_initOneFilter('citys') ;
	jv_events_initOneFilter('tags') ;
	jv_events_initOneFilter('organizers') ;
	jv_events_initOneFilter('months') ;
	if( jQuery('#jv_events_geo').length > 0 ) {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(jv_events_initPosition);
		}
	}

	// jv_events_refreshList();
}
function jv_events_initPosition(position) {

	if( jQuery('#jv_events_geo').length > 0  ) {
		jQuery('#jv_events_geo').data("lng" , position.coords.longitude ) ;
		jQuery('#jv_events_geo').data("lat" , position.coords.latitude ) ;
	}
}

function jv_events_initOneFilter(filterName) {

	if ( jQuery('#jv_events_filter_' + filterName ) ) {
		jQuery('#jv_events_filter_' + filterName ).change(function(i) {
			jv_events_refreshList() ;
		});
		var filterVal = GetURLParameter('tx_jvevents_events[eventsFilter][' + filterName + ']') ;
		if ( filterVal ) {
			jQuery('#jv_events_filter_' + filterName + ' OPTION').each(function(i) {
				if ("'" + jQuery(this).val() + "'" == "'" + filterVal +"'") {
					jQuery(this).prop("selected", true);
				}
			});

		}
	}
}


function jv_events_refreshList(){
	var fMonth= jQuery("#jv_events_filter_months") ;
	var fTag= jQuery("#jv_events_filter_tags") ;
	var fCity= jQuery("#jv_events_filter_citys") ;
	var fCat= jQuery("#jv_events_filter_categories") ;


	jQuery('.tx-jv-events DIV.jv-events-singleEvent').each(function (i) {
		jQuery(this).removeClass('hide') ;

		if( fMonth && fMonth.val().length > 0 ) {
			if( jQuery(this).data("monthuid")  != fMonth.val() ) {
				jQuery(this).addClass('hide') ;
			}
		}
		if( fTag && fTag.val() > 0 ) {
			var fTags = jQuery(this).data("taguids") ;
			if( fTags ) {
				fTags = fTags.split(",") ;
				if( fTags.indexOf( fTag.val() ) < 0 ) {
					jQuery(this).addClass('hide') ;
				}
			} else {
				jQuery(this).addClass('hide') ;
			}

		}
		if( fCity && fCity.val().length > 0 ) {
			if( jQuery(this).data("cityuid")  != fCity.val() ) {
				jQuery(this).addClass('hide') ;
			}
		}

		if( fCat && fCat.val() > 0 ) {
			var fCats = jQuery(this).data("catuids") ;
			if( fCats ) {
				fCats = fCats.split(",") ;
				if( fCats.indexOf( fCat.val() ) < 0 ) {
					jQuery(this).addClass('hide') ;
				}
			} else {
				jQuery(this).addClass('hide') ;
			}
		}
	});
}

function jv_events_submit() {

	var error = false ;
	// Test if all required Fields have input .. first cleanup then test
	jQuery(".jv-events-regform .has-error").each(function() {
		jQuery(this).removeClass('has-error') ;
	}) ;

	jQuery(".jv-events-regform .jv-events-req-TRUE").each(function() {

		switch ( jQuery(this).getType() ) {
			case 'checkbox' :
				if ( ! jQuery(this).prop("checked" ) ) {
					jQuery(this).parent().parent().addClass('has-error') ;
					error = true ;
				}
				break ;

			case 'select' :
				if (  parseInt (jQuery(this).val()) == 0 ) {
					jQuery(this).parent().addClass('has-error') ;
					error = true ;
				}
				break ;

			default:
				if ( jQuery(this).val() == '' ) {
					jQuery(this).parent().addClass('has-error') ;
					error = true ;
				}
				break;

		}


	}) ;
	if( error) {
		jQuery('#jv_events_js_error').removeClass('hidden') ;
		alert( jQuery('#jv_events_js_error DIV.alert').html().trim()  ) ;
	} else {
		var retVal = 1 ;
		if ( typeof 'jv-events-own-script' == 'function' ) {
			// return 0 to stay and show your won error messages
			// return 1 to let this script submit
			retVal = jv-events-own-script()  ;
			// jump out if return val = 2
			if (retVal == 2) {
				return ;
			}
		}
		if (retVal == 1) {
			jQuery(".jv-events-regform").submit();
		}
	}

}


$.fn.getType = function(){ return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase(); }