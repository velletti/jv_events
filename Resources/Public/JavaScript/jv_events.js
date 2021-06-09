/**
 * Created by velletti on 29.09.2016.
 * Last Change:
 */
jQuery(document).ready(function() {
	jv_events_init() ;
    if ( $("#jvEventsAjaxMenu").length ) {
        jv_events_init_AjaxMenu();
    }

}) ;

function jv_events_init_AjaxMenu() {
    var eventId = 0;
    var locationId = 0;
    var ajaxCurrentPageUid = parseInt($('meta[name=pageUid]').attr('content'));
    if ( ajaxCurrentPageUid < 1) {
        ajaxCurrentPageUid = 1 ;
    }
    if( $("#jv-events-dataids").length ) {
        if( $("#jv-events-dataids").data ("eventuid") ) {
            eventId = parseInt( $("#jv-events-dataids").data("eventuid"));
        }
        if( $("#jv-events-dataids").data ("locationuid") ) {
            locationId = parseInt( $("#jv-events-dataids").data("locationuid"));
        }
    }
    if ( $("#jvEventsAjaxMenu").length ) {
        $.ajax( {
            url: '/index.php' ,
            data: 'id=' + ajaxCurrentPageUid + '&L=0&tx_jvevents_ajax[event]=' + eventId + '&tx_jvevents_ajax[location]=' +  locationId + '&tx_jvevents_ajax[action]=eventMenu&tx_jvevents_ajax[controller]=Ajax&' ,

            before: function() {
                $('#jvEventsAjaxMenu').addClass('show').addClass('d-block') ;
            } ,
            success: function(response) {
                $('#jvEventsAjaxMenu').removeClass('d-block') ;
                $('#jvEventsAjaxMenu').html( response.html.main) ;

                if ($('#jvEventsAjaxSingleMenu') ) {
                    $('#jvEventsAjaxSingleMenu').addClass('d-block').removeClass('d-none') ;
                    $('#jvEventsAjaxSingleMenu').html( response.html.single) ;
                    if ($('#jv-events-cancelEvent') ) {
                        $(function () {
                            $('#jv-events-cancelEvent').bootstrapToggle();
                        })
                        $('#jv-events-cancelEvent').change(function() {
                            $(this).parent().addClass('blink') ;
                            if( $(this).prop('checked')) {
                                $(this).prop('checked' , '' ) ;
                                $('#jv-events-cancelEvent-info').addClass('fade slow') ;
                                $('#jv-events-cancelEvent-info').addClass('d-none') ;
                                $('#jv-events-cancelEvent-info').removeClass('in') ;
                            } else {
                                $(this).prop('checked' , 'checked' ) ;
                                $('#jv-events-cancelEvent-info').addClass('in') ;
                                $('#jv-events-cancelEvent-info').removeClass('d-none') ;
                                $('#jv-events-cancelEvent-info').removeClass('fade') ;
                            }
                            window.location.href = $("#jv-events-cancelEvent-link").href() ;

                        })
                    }
                }

            },
            error: function(response) {
                $('#jvEventsAjaxMenu').removeClass('show').addClass('d-none') ;
            }
        })

    }
}




//  ############   generic function for everyone: test if a spezific Parameter is in URL and return its value ###########
function jv_events_GetURLParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++) {
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam) {
			return  decodeURIComponent(sParameterName[1]);
		}
	}
}
function jv_events_GetURLnonEventParms( noIdandLang ) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    var ret = '' ;
    for (var i = 0; i < sURLVariables.length; i++) {
        if ( sURLVariables[i].length > 1 ) {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0].substring(0,18) != 'tx_jvevents_events') {
                if(noIdandLang) {
                    if( sParameterName[0] != "id" && sParameterName[0] != "L") {
                        ret = ret + "&" + sParameterName[0] + "=" +  decodeURIComponent(sParameterName[1]);
                    }
                } else {
                    ret = ret + "&" + sParameterName[0] + "=" +  decodeURIComponent(sParameterName[1]);
                }

            }
        }
    }
    return ret ;
}

function jv_events_init() {

	// http://nemetschek.local/index.php?id=116&tx_jvevents_events[eventsFilter][categories]=3&tx_jvevents_events[eventsFilter][citys]=4
	// http://nemetschek.local/index.php?id=116&tx_jvevents_events[eventsFilter][categories]=3&tx_jvevents_events[eventsFilter][citys]=4&tx_jvevents_events[eventsFilter][tags]=3&tx_jvevents_events[eventsFilter][months]=03.2017
    // https://allplan.local/index.php?id=110&L=1&no_cache=1&tx_jvevents_events[eventsFilter][categories]=4&tx_jvevents_events[eventsFilter][citys]=Ratingen

    $(".js-warning-disabled").hide() ;

    jv_events_initOneFilter('categories') ;
	jv_events_initOneFilter('locations') ;
	jv_events_initOneFilter('citys') ;
	jv_events_initOneFilter('tags') ;
	jv_events_initOneFilter('organizers') ;
	jv_events_initOneFilter('months') ;
	if( jQuery('#jv_events_geo').length > 0 ) {
        if( jQuery('#jv_events_geo').data("askUser" )  == "1") {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(jv_events_initPosition);
            }
        }
	}
    jQuery('#filter-reset-events' ).click(function(i) {
        jv_events_filter_reset() ;
        return false ;
    });

    // Set the fieldsets to the same height
    if($('.filter').length ){
        if( $('body').hasClass('lg') ) {
            $('.filter').each(function(){

                var heightBiggestElement = 0;

                $(this).find('fieldset').each(function(){
                    if($(this).height() > heightBiggestElement) {
                        heightBiggestElement = $(this).height();
                    }
                });
                $(this).find('fieldset').each(function(){
                    $(this).css("min-height" , heightBiggestElement);
                });

            });
        } else {
            if($('.filter fieldset').length ){
                $('.filter fieldset').each(function(){
                    $(this).width('100%') ;
                });
            }
        }


    }

	jv_events_refreshList();
}
function jv_events_initPosition(position) {

	if( jQuery('#jv_events_geo').length > 0  ) {
		jQuery('#jv_events_geo').data("lng" , position.coords.longitude ) ;
		jQuery('#jv_events_geo').data("lat" , position.coords.latitude ) ;
	}
}

function jv_events_initOneFilter(filterName) {

	if ( jQuery('SELECT#jv_events_filter_' + filterName ).length ) {
		jQuery('SELECT#jv_events_filter_' + filterName ).change(function(i) {
			jv_events_refreshList() ;
		});
		var filterVal = jv_events_GetURLParameter('tx_jvevents_events[eventsFilter][' + filterName + ']') ;
		if ( filterVal ) {
		    jQuery('SELECT#jv_events_filter_' + filterName + ' OPTION').each(function(i) {
                if ("'" + jQuery(this).val() + "'" == "'" + filterVal +"'") {
                    jQuery(this).prop("selected", true);
                }
            });
		}
	}
    if ( jQuery('#jv_events_filter_' + filterName + " input[type=checkbox]").length ) {
        jQuery('#jv_events_filter_' + filterName + " input[type=checkbox]").change(function(i) {
            jv_events_refreshList() ;
        });
        var filterVal = jv_events_GetURLParameter('tx_jvevents_events[eventsFilter][' + filterName + ']') ;
        if ( filterVal ) {
            var filterSplit =  filterVal.split(",") ;
            if(filterSplit.length > 1 ) {
                jQuery('#jv_events_filter_' + filterName + ' input[type=checkbox]').each(function(i) {
                    if(  filterSplit.indexOf( jQuery(this).val() ) > -1 ) {
                        jQuery(this).prop("checked", true);
                    }
                });
            } else {
                jQuery('#jv_events_filter_' + filterName + ' input[type=checkbox]').each(function(i) {
                    if ("'" + jQuery(this).val() + "'" == "'" + filterVal +"'") {
                        jQuery(this).prop("checked", true);
                    }
                });
            }
        } else {
            jQuery('#jv_events_filter_' + filterName + ' input[type=checkbox]').each(function(i) {
            //    jQuery(this).prop("checked", true);
            });
        }
    }
}


function jv_events_refreshList(){
	var fMonth= jQuery("SELECT#jv_events_filter_months") ;
	var fTag= jQuery("SELECT#jv_events_filter_tags") ;
	var fCity= jQuery("SELECT#jv_events_filter_citys") ;
	var fCat= jQuery("SELECT#jv_events_filter_categories") ;
	var fOrg= jQuery("SELECT#jv_events_filter_organizers") ;
    var cCats= jQuery("#jv_events_filter_categories INPUT[type=checkbox]") ;
    var cTags= jQuery("#jv_events_filter_tags INPUT[type=checkbox]") ;

    var cTagChecked = false ;
    jQuery( cTags ).each( function() {
        if ( jQuery(this).prop("checked") ) {
            cTagChecked = true ;
            return false ;
        }

    }) ;

    var cCatChecked = false ;
    jQuery( cCats ).each( function() {
        if ( jQuery(this).prop("checked") ) {
            cCatChecked = true ;
            return false ;
        }

    }) ;
    var filterIsActive = false ;
    var needTohide = false ;
	jQuery('.tx-jv-events DIV.jv-events-singleEvent').each(function (i) {
       // console.log( " ************* event **************** UID: " + jQuery(this).data("eventuid")  ) ;
		jQuery(this).removeClass('hide') ;

		if( fMonth && fMonth.val() && fMonth.val().length > 0 ) {
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
		if( fCity && fCity.length > 0 ) {
		    if(  fCity.val().length > 0 ) {
                if( (jQuery(this).data("cityuid")) && decodeURI (jQuery(this).data("cityuid")) != (fCity.val()) && ( parseInt( jQuery(this).data("longitude") ) != 0  )  ) {
                    jQuery(this).addClass('hide') ;
                }
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
        if( fOrg && fOrg.val() > 0 ) {
            if( parseInt( jQuery(this).data("orguid"))   !== parseInt( fOrg.val()) ) {
                jQuery(this).addClass('hide') ;
            }
        }

        if( cTagChecked  ) {
            var sTags = jQuery(this).data("taguids") ;
            // console.log( " sTags (Tag Ids of Event) : " + sTags ) ;

            if( sTags ) {
                sTags = "," + sTags + "," ;
                needTohide = true ;
                var combineTags =  jQuery('#jv_events_filter_tags').data('combinetags') ;
                //console.log( "Combine Tags: " +  combineTags  ) ;
				jQuery( cTags ).each( function() {
                   // console.log( "Filter Tag: " + jQuery(this).val() + " checked ? : " + jQuery(this).prop("checked") ) ;

					if ( jQuery(this).prop("checked") ) {
                        // console.log( "position of " + jQuery(this).val() + " in string " + sTags + " = " + sTags.indexOf( "," + jQuery(this).val()   ) ) ;
						if( sTags.indexOf( "," + jQuery(this).val() + ","  ) > -1 ) {
                            needTohide = false ;
                           // console.log(" if All Tags must fit (combineTags = " + combineTags + "): we can not exit , we need to stay in loop and check all " ) ;
                            if ( combineTags != "1") {
                                return false ;
                            }
                        } else {
                            // console.log(" if All Tags must fit (combineTags = " + combineTags + "): we will exit and hide event " ) ;
                            //
                            if ( combineTags == "1") {
                                needTohide = true ;
                                return false ;
                            }
                        }
					}

				}) ;


                if( needTohide ) {
                    jQuery(this).addClass('hide') ;
                }
            } else {
                jQuery(this).addClass('hide') ;
            }
        }

        if( cCatChecked ) {
            var sCats = jQuery(this).data("catuids") ;
            // console.log( " sCats : " + sCats ) ;
            if( sCats ) {
                sCats = "," + sCats + "," ;
                needTohide = true ;
                jQuery( cCats ).each( function() {
					// console.log( jQuery(this).prop("checked") ) ;
                    if ( jQuery(this).prop("checked") ) {
                      //  console.log( "position: " + sCats.indexOf( jQuery(this).val()  ) ) ;
                        if( sCats.indexOf( "," + jQuery(this).val() + ","  ) > -1 ) {
                            needTohide = false ;
                            return false ;
                        }
                    }

                }) ;
                if( needTohide ) {
                    jQuery(this).addClass('hide') ;
                }
            } else {
                jQuery(this).addClass('hide') ;
            }
        }
        if ( jQuery(this).hasClass('hide')) {
            filterIsActive = true ;
		}
	});


	if ( filterIsActive ) {
		jQuery( "#filter-events A").addClass('hide') ;
		jQuery( "#filter-reset-events").removeClass('hide') ;
		jQuery( "#filter-result-hint-events").removeClass('hide') ;


        // now change also the URL in the Browser to be able to copy the URL !!!
        urlFilter = "" ;
        if( fOrg && fOrg.val() > 0 ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][organizers]=" + fOrg.val() ;
        }

        if( cCatChecked ) {

            var catUrlFilter = '' ;
            jQuery( cCats ).each( function() {
                if ( jQuery(this).prop("checked") ) {
                    catUrlFilter = catUrlFilter + jQuery(this).val() +","  ;
                }
            }) ;
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][categories]=" +  catUrlFilter ;
        }
        if( cTagChecked ) {

            var tagUrlFilter = '' ;
            jQuery( cTags ).each( function() {
                if ( jQuery(this).prop("checked") ) {
                    tagUrlFilter = tagUrlFilter + jQuery(this).val() +","  ;
                }
            }) ;
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][tags]=" +  tagUrlFilter ;
        }


        if( fCat && fCat.val() > 0 ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][categories]=" + fCat.val() ;
        }

        if( fTag && fTag.val() > 0 ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][tags]=" + fTag.val() ;
        }

        if( fCity && fCity.val() != ''  ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][citys]=" + fCity.val() ;
        }
        if( fMonth && fMonth.val() != ''  ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][months]=" + fMonth.val() ;
        }

        jv_events_pushUrl( urlFilter ) ;


	} else {
        jQuery( "#filter-events A").removeClass('hide') ;
        jQuery( "#filter-reset-events").addClass('hide') ;
        jQuery( "#filter-result-hint-events").addClass('hide') ;
        jv_events_pushUrl( '' ) ;

	}


}
function jv_events_pushUrl( urlFilter ) {
    if( jQuery(".tx-jv-events .filter").length > 0 || jQuery(".tx-jv-events .jv_events_filter").length > 0 ) {

        var urlFilterBase = jQuery('meta[name=realUrlPath]').attr('content')  ;
        var stateObj = { Event: "Filter" };
        var addChar = "?" ;
        if ( urlFilterBase.indexOf(addChar) > 0 ) {
            addChar = "&" ;
        }
        if ( urlFilterBase.length > 0 ) {
            var moreParams = jv_events_GetURLnonEventParms( true) ;
            window.history.pushState(stateObj, "Filter", window.location.protocol + "//" + window.location.hostname + urlFilterBase + addChar + urlFilter + moreParams);
        } else {
            var moreParams = jv_events_GetURLnonEventParms(false) ;
            window.history.pushState(stateObj, "Filter", window.location.protocol + "//" + window.location.hostname +  window.location.pathname + addChar + urlFilter + moreParams);
        }
    }
}
function jv_events_filter_reset() {
    var fMonth= jQuery("SELECT#jv_events_filter_months") ;
    if( fMonth) {
    	jQuery( fMonth).val("") ;
	}
    var fTag= jQuery("SELECT#jv_events_filter_tags") ;
    if( fTag) {
        jQuery( fTag).val("") ;
    }

    var fCity= jQuery("SELECT#jv_events_filter_citys") ;
    if( fCity) {
        jQuery( fCity).val("") ;
    }

    var fCat= jQuery("SELECT#jv_events_filter_categories") ;
    if( fCat) {
        jQuery( fCat).val("") ;
    }

    var fOrg= jQuery("SELECT#jv_events_filter_organizers") ;
    if( fOrg) {
        jQuery( fOrg).val("") ;
    }

    var cCats= jQuery("#jv_events_filter_categories INPUT[type=checkbox]") ;
    if( cCats) {
        jQuery( cCats).each( function() {
        	jQuery(this).prop("checked" , false ) ;
		}) ;
    }
    var cTags= jQuery("#jv_events_filter_tags INPUT[type=checkbox]") ;
    if( cTags) {
        jQuery( cTags).each( function() {
            jQuery(this).prop("checked" , false ) ;
        }) ;
    }
    if(jQuery('#filter-reset-events').length){
		jQuery('#filter-reset-events').addClass('hide');
	}
	if(jQuery('#filter-result-hint-events').length){
		jQuery('#filter-result-hint-events').addClass('hide');
	}


    jQuery('.tx-jv-events DIV.jv-events-singleEvent').each(function (i) {
        jQuery(this).removeClass('hide');
    });

    jv_events_pushUrl( '' ) ;

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
            if (typeof showSpinner == 'function')
            {
                showSpinner();
            }
			jQuery(".jv-events-regform").submit();
		}
	}

}


$.fn.getType = function(){ return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase(); };

