/**
 * Created by velletti on 29.09.2016.
 * Last Change:
 */
jQuery(document).ready(function() {
	jv_events_init() ;
    jv_events_init_AjaxMenu() ;

	if( $(".jv-events-tags-edit") ){
        jv_events_init_edit_tags() ;
    }

    if ( jQuery("#streetAndNr").length) {
        jQuery("#streetAndNr").on("keydown" , function( event) {
            jQuery("#jvevents-geo-update").removeClass('opacity-2') ;
            jQuery("#jvevents-geo-getpos").removeClass('opacity-1') ;
            jQuery("#jvevents-geo-ok").removeClass('opacity-4') ;
            if ( event.which == 13 ) {
                jQuery("#jvevents-geo-update").click() ;
            }
        })
    }

}) ;

function jv_events_init_AjaxMenu() {
    var eventId = 0;
    var locationId = 0;
    var addOrgId = '';
    var ajaxCurrentPageUid = parseInt($('meta[name=pageUid]').attr('content'));
    if ( ajaxCurrentPageUid < 1) {
        ajaxCurrentPageUid = 1 ;
    }
    if( $("#jv-events-dataids").length ) {
        if( $("#jv-events-dataids").data("eventuid") ) {
            eventId = parseInt( $("#jv-events-dataids").data("eventuid"));
        }
        if( $("#jv-events-dataids").data("locationuid") ) {
            locationId = parseInt( $("#jv-events-dataids").data("locationuid"));
        }
        if( $("#jv-events-dataids").data("orguid") ) {
            addOrgId = '&tx_jvevents_ajax[organizer]=' + parseInt( $("#jv-events-dataids").data("orguid"));
        }
    }
    if ( $(".jv_events_unlink_event").length) {
        $(".jv_events_unlink_event").on('click', function () {
            var eventId = $(this).data("eventuid") ;
            var index = $(this).data("index") ;
            $(this).find('.iconLink').addClass('d-none');
            $(this).find('.iconWait').removeClass('d-none'); ;
            $('.' + index).find('span').addClass('d-none'); ;
            $.ajax({

                url: '/index.php',
                data: 'uid=' + ajaxCurrentPageUid + '&eID=jv_events&L=0&tx_jvevents_ajax[event]=' + eventId + '&tx_jvevents_ajax[action]=eventUnlink&tx_jvevents_ajax[controller]=Ajax&',

                before: function () {
                    spinner.removeClass('d-none');
                },
                success: function (response) {
                    $('.jv_events_unlink_event .iconWait').addClass('d-none');
                    $('.' + index).addClass('d-none'); ;

                },
                error: function (response) {
                    alert("Error " + response.msg ) ;
                }
            });
        });
    }
    if ( $("#jvEventsAjaxMenu").length) {
        $.ajax( {
            url: '/index.php' ,
            data: 'uid=' + ajaxCurrentPageUid + '&tx_jvevents_ajax[returnPid]=' + ajaxCurrentPageUid + '&eID=jv_events&L=0&tx_jvevents_ajax[event]=' + eventId + addOrgId + '&tx_jvevents_ajax[location]=' +  locationId + '&tx_jvevents_ajax[action]=eventMenu&tx_jvevents_ajax[controller]=Ajax&' ,

            before: function() {
                $('#jvEventsAjaxMenu').addClass('show').addClass('d-block') ;
            } ,
            success: function(response) {
                $('#jvEventsAjaxMenu').removeClass('d-block').html( response.html.main) ;

                if ($('#jvEventsAjaxSingleMenu') ) {
                    $('#jvEventsAjaxSingleMenu').addClass('d-block').removeClass('d-none').html( response.html.single) ;
                    if ($('#jv-events-cancelEvent').length ) {

                        $('#jv-events-cancelEvent').bootstrapToggle();

                        $('#jv-events-cancelEvent').on('change' , function() {
                            $(this).parent().addClass('blink') ;
                            if( $(this).prop('checked')) {
                                $(this).prop('checked' , '' ) ;
                                $('#jv-events-cancelEvent-info').addClass('fade slow').addClass('d-none').removeClass('in') ;
                            } else {
                                $(this).prop('checked' , 'checked' ) ;
                                $('#jv-events-cancelEvent-info').addClass('in').removeClass('d-none').removeClass('fade') ;
                            }
                            if ( $("#jv-events-cancelEvent-link").href().length ) {
                                window.location.href = $("#jv-events-cancelEvent-link").href() ;
                            }

                        }) ;
                    }
                }

            },
            error: function(response) {
                $('#jvEventsAjaxMenu').removeClass('show').addClass('d-none') ;
            }
        })

    }
}



function jv_events_init_edit_tags() {
    var jvEventsNewTags = '' ;
    var jvEventsTagsSum = 0 ;

    $(".jv-events-tags-edit").each(function() {
        if ($(this).prop("checked")) {
            jvEventsNewTags =  $(this).val() + ","  + jvEventsNewTags ;
            $(this).parent().addClass('event-checked') ;
            jvEventsTagsSum++ ;
        }
    }) ;

    $("#jv-events-tags-sum").html(jvEventsTagsSum) ;

    $(".jv-events-cats-div INPUT").each(function() {
        if ($(this).prop("checked")) {
            $(this).parent().addClass("event-checked") ;
        } else {
            $(this).parent().removeClass("event-checked") ;
        }
    }) ;

    $(".jv-events-cats-div-div ").on("click" , function () {
        $(this).find("INPUT").prop("checked" , true ) ;

        jv_events_refreshTags() ;

        $(".jv-events-cats-div INPUT").each(function() {
            if ($(this).prop("checked")) {
                $(this).parent().addClass("event-checked") ;
            } else {
                $(this).parent().removeClass("event-checked") ;
            }
        }) ;

    }) ;


    jv_events_refreshTags() ;




    $(".jv-events-tags-div").on("click" , function () {

        var thisCheck =  $(this).find(".jv-events-tags-edit") ;

        if ($(thisCheck).prop("checked")) {
            $(thisCheck).prop("checked", false);
            $(this).removeClass("event-checked");
        } else {
            $(thisCheck).prop("checked" , true ) ;
            $(this).addClass("event-checked");
        }
        jv_events_refreshTags() ;

    }) ;



    $("#lat").on("click" , function () {
        $("#geoSearchModal").css("display" , "block") ;
    });

    if( $('.clockpicker').length ) {
        $('.clockpicker').clockpicker();
    }

}
function jv_events_refreshTags() {
    var clickedCat =  $(".jv-events-cats-div-div INPUT:checked").val() ;

    if (clickedCat ) {
        var parentCats = '' ;
        $(".jv-events-tags-div").each(function() {
            parentCats           = $(this).data("parent")  ;
            parentCatArray = parentCats.split(",") ;
            var thisCheck =  $(this).find(".jv-events-tags-edit") ;
            if( $.inArray( clickedCat ,  parentCatArray) > -1 || ! parentCats ) {
                $(this).removeClass("d-none");
            } else {
                if ($(thisCheck).prop("checked")) {
                    $(thisCheck).prop("checked", false);
                    $(this).removeClass("event-checked");
                }
                $(this).addClass("d-none");
            }
        }) ;
    }
    var jvEventsNewTags = '' ;
    var jvEventsTagsSum = 0 ;

    $(".jv-events-tags-edit").each(function() {
        if ($(this).prop("checked")) {
            jvEventsNewTags =  $(this).val() + ","  + jvEventsNewTags ;
            jvEventsTagsSum++ ;
        }
    }) ;
    $("#jv-events-tags-sum").html(jvEventsTagsSum) ;
    if ( ( parseInt(jvEventsTagsSum ) + 1 )  > parseInt($("#jv-events-tags-sum-max").html( ))) {
        $(".jv-events-tags-edit").each(function() {
            if ( !$(this).prop("checked")) {
                $(this).parent().addClass("d-none");
            }
        }) ;
    }
    $("#jv-events-tagsFE").val(jvEventsNewTags ) ;
}

function jv_events_askPosition() {
   // if( jQuery('#jv_events_geo').data("askuser" )  == "1" && location.protocol == "https:") {
   //    console.log("jv_events_askPosition") ;

    if( location.protocol == "https:") {
     //    console.log("location.protocol == \"https:\" ") ;
        if (navigator.geolocation) {
         //   console.log("navigator.geolocation ") ;
            navigator.geolocation.getCurrentPosition(jv_events_initPosition , jv_events_errorPosition);
           // jQuery("#geosearch input#geosearchbox").val('');
           // jQuery("#streetAndNr").val('');


        } else {
            jQuery('#jv_events_geo_disp_sub').removeClass("d-none") ;
            jQuery('#jv_events_geo_disp_spinner').addClass("d-none") ;
        }
    }
    jQuery("#jvevents-geo-getpos").click() ;
    jQuery("#jvevents-geo-update").addClass('opacity-2') ;
    jQuery("#jvevents-geo-getpos").addClass('opacity-1') ;
    jQuery("#jvevents-geo-ok").addClass('opacity-4') ;


}

//  ############   generic function for everyone: test if a spezific Parameter is in URL and return its value ###########
function jv_events_GetURLParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	if ( sPageURL.indexOf("%5B") > 0 || sPageURL.indexOf("%5D") > 0  ) {
        sPageURL = decodeURI(sPageURL) ;
    }
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++) {
		var sParameterName = sURLVariables[i].split('=');
		if (sParameterName[0] == sParam || sParameterName[0] == "amp;" + sParam  ) {
			return  decodeURIComponent(sParameterName[1]);
		}
	}
}
function jv_events_GetURLnonEventParms( noIdandLang ) {
    var sPageURL = window.location.search.substring(1);
    if ( sPageURL.indexOf("%5B") > 0 || sPageURL.indexOf("%5D") > 0  ) {
        sPageURL = decodeURI(sPageURL) ;
    }
    var sURLVariables = sPageURL.split('&');
    var ret = '' ;
    for (var i = 0; i < sURLVariables.length; i++) {
        // console.log( "i: " + i + " = " + sURLVariables[i] ) ;
        if ( sURLVariables[i].length > 1 ) {
            var sParameterName = sURLVariables[i].split('=');
            // console.log(  sParameterName[0] + " = " + sParameterName[1] ) ;
            if (sParameterName[0].substring(0,18) != 'tx_jvevents_events') {
                if(noIdandLang) {
                    if( sParameterName[0] != "id" && sParameterName[0] != "L") {
                        ret = ret + "&" + sParameterName[0] + "=" +  decodeURIComponent(sParameterName[1]);
                    }
                } else {
                    ret = ret + "&" + sParameterName[0] + "=" +  decodeURIComponent(sParameterName[1]);
                }
            } else {
                // console.log(  " is events Param , check if Overrule " ) ;
                if (sParameterName[0].indexOf('overruleFilter') > 0 ) {
                    // console.log(  " add  Overrule : " + sParameterName[0]  ) ;
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

	/* jv_events_initOneFilter('months') ; */
	if( jQuery('#jv_events_geo').length > 0 ) {
	   // console.log("#jv_events_geo').length > 0 ") ;
        if( jQuery('#jv_events_geo').data("askuser" )  == "1" && document.cookie.indexOf("positionAllowed=") >= 0 && location.protocol == "https:") {
          //  console.log("#jv_events_geo askuser  == 1  ") ;
            if (navigator.geolocation) {
            //    console.log("navigator.geolocation ") ;
                navigator.geolocation.getCurrentPosition(jv_events_initPosition , jv_events_errorPosition);

            } else {
                jQuery('#jv_events_geo_disp_sub').removeClass("d-none") ;
                jQuery('#jv_events_geo_disp_spinner').addClass("d-none") ;
            }
        } else {
            jQuery('#jv_events_geo_disp_sub').removeClass("d-none") ;
            jQuery('#jv_events_geo_disp_spinner').addClass("d-none") ;
        }

    } else {
        jQuery('#jv_events_geo_disp_sub').removeClass("d-none") ;
        jQuery('#jv_events_geo_disp_spinner').addClass("d-none") ;
    }

    jv_events_initOneFilter('distance') ;
    jQuery('#filter-reset-events' ).click(function(i) {
        $(this).preventDefault() ;
        jv_events_filter_reset() ;
        return false ;
    });
    jQuery('#overruleFilterStartDate' ).change(function(i) {
        jv_events_reloadList() ;
    });
    if( ! jQuery('#jv_events_filter_citys' ).length && ! jQuery('#jv_events_filter_organizers' ).length ) {
        jQuery( 'legend.jv_events_filter_more').addClass('d-none') ;
    }
    if( jQuery('#overruleFilterStartDate' ).length && jQuery('.jv_events_list_browser_button' ).length ) {

        jQuery(".jv_events_list_browser_button").on("click" , function () {
            jQuery('#overruleFilterStartDate' ).val( jQuery(this).data('date')) ;
            jv_events_reloadList() ;
        });


    }


    $('#jv-events-filter-sub').on('resize' , function () {
        if( $("#jv-events-filter-sub").hasClass('show')) {
            $("#jv_events_geo_disp").addClass("d-none") ;
        } else {
            $("#jv_events_geo_disp").removeClass("d-none") ;
        }
    });

	jv_events_refreshList();
}
function jv_events_reloadList() {
    if ( jQuery("#overruleFilterStartDate") ) {
        var temp =  window.location.href.split("#")  ;
        if ( Array.isArray(temp) && temp.length > 1) {
            var host = temp[0].split("?");
        } else {
            var host = window.location.href.split("?");
        }
        if ( Array.isArray(host) && host.length > 1 ) {
            var arr = host[1].split("&");

            var baseHref = jQuery('#jv-events-filter-baseUrl') ;
            // console.log( "baseHref") ;
            // console.log( baseHref) ;
            // console.log( baseHref.attr('href')) ;

            if ( baseHref.length && baseHref.attr('href') != undefined ) {
                var newQuery = baseHref.attr('href') + "?" ;
            } else {
                var newQuery = host[0] + "?" ;
            }


            var param = ''
            for (var i = 0; i < arr.length; i++) {
                param = arr[i].substr( 0, 999 ) ;
                if ( param.substr(0,4 ) == "amp;") {
                    param = arr[i].substr( 4, 999 ) ;
                }
                if ( param.substr(0,5) == "chash" ) {
                    break ;
                }
                if ( param.substr(0,27) != "tx_jvevents_events[overruleFilter][startDate]=" ) {
                    newQuery += "&" + param ;
                }
            }
        } else {
            var newQuery = window.location.href + "?" ;
        }

        newQuery += "&tx_jvevents_events[overruleFilter][startDate]=" + jQuery("#overruleFilterStartDate").val() ;
        var cHash = newQuery.hashCode() ;
        // console.log( newQuery) ;
        window.location.href =  newQuery + "&cHash=" + cHash ;
    }


}



String.prototype.hashCode = function() {
    var hash = 0, i, chr;
    if (this.length === 0) return hash;
    for (i = 0; i < this.length; i++) {
        chr   = this.charCodeAt(i);
        hash  = ((hash << 5) - hash) + chr;
        hash |= 0; // Convert to 32bit integer
    }
    return hash;
};

function jv_events_errorPosition() {
    jQuery('#jv_events_geo_disp_sub').removeClass("d-none") ;
    jQuery('#jv_events_geo_disp_spinner').addClass("d-none") ;
}


function jv_events_initPosition(position) {
   // console.log( "init Position done, now store to fields") ;
	if( jQuery('#jv_events_geo').length > 0  || jQuery('#lat').length > 0 ) {
        showSpinner() ;
	    if( position ) {
            if( position.coords ) {
               // console.log( "Position has coords") ;
                if( position.coords.longitude ) {
                    jQuery('#jv_events_geo').data("lng" , position.coords.longitude ) ;

                    jQuery('#jv_events_geo').data("lat" , position.coords.latitude ) ;

                    if( jQuery('#lat').length > 0  ) {
                        jQuery('#lat').val(position.coords.latitude);
                    }
                    if( jQuery('#lng').length > 0  ) {
                        jQuery('#lng').val(position.coords.longitude);
                    }
                    jQuery("#geosearch input#geosearchbox").val(position.coords.latitude + "," + position.coords.longitude);
                    jQuery("#streetAndNr").val(position.coords.latitude + "," + position.coords.longitude);

                    // Set cookie for 365 days
                    var d = new Date();
                    d.setTime(d.getTime() + ( 24*60*60*1000 * 365));
                    var expires = 'expires=' + d.toUTCString();

                    if ( getCookie('tx_cookies_accepted') == "1") {
                        document.cookie = 'tx_events_lat=' + position.coords.latitude + "; " + expires + ';path=/';
                        document.cookie = 'tx_events_lng=' + position.coords.longitude + "; " + expires + ';path=/';
                    }



                    if( map ) {
                    //    console.log( "Map Object Found, place marker to coords") ;
                        myPosition = new google.maps.LatLng(position.coords.latitude , position.coords.longitude);
                      //  console.debug(position.coords)
                        map.panTo(myPosition) ;
                     //   map.setCenter( position.coords) ;

                        if( marker ) {
                            marker.setPosition(myPosition) ;
                            hideSpinner() ;
                        }
                    } else {
                        hideSpinner() ;
                    }


                    if( jQuery('#jv_events_geo').data("allowed" ) == "0" ) {
                        jv_events_refreshList() ;
                        jQuery('#jv_events_geo').data("allowed" , 1 ) ;
                    }
                    jQuery('#jv_events_geo_disp BUTTON').attr( "title" , "Lng: " + position.coords.longitude.toFixed(6) + " / Lat: " +  position.coords.latitude.toFixed(6) )

                    jQuery('#jv_events_geo_disp BUTTON').removeClass('btn-outline-secondary').addClass('btn-outline-primary')
                }
            } else {
                hideSpinner();
            }
        } else {
            hideSpinner() ;
        }

        jQuery('#jv_events_geo_disp_sub').removeClass("d-none") ;
        jQuery('#jv_events_geo_disp_spinner').addClass("d-none") ;
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
            // default No Tag is selected
            jQuery('#jv_events_filter_' + filterName + ' input[type=checkbox]').each(function(i) {
                jQuery(this).prop("checked", false);
            });
        }
    }
}


function jv_events_refreshList(){
	/* var fMonth= jQuery("SELECT#jv_events_filter_months") ; */
    var fMonth=false ;
	var fTag= jQuery("SELECT#jv_events_filter_tags") ;
	var fCity= jQuery("SELECT#jv_events_filter_citys") ;
	var fCat= jQuery("SELECT#jv_events_filter_categories") ;
	var fOrg= jQuery("SELECT#jv_events_filter_organizers") ;
	var fDist= jQuery("SELECT#jv_events_filter_distance") ;
	var maxDist = 99999
    if ( fDist && fDist.val() && fDist.val().length > 0 &&  fDist.val() < 10000 ) {
        maxDist = fDist.val() ;
    }
    var cCats= jQuery("#jv_events_filter_categories INPUT[type=checkbox]") ;
    var cTags= jQuery("#jv_events_filter_tags INPUT[type=checkbox]") ;
    /* var startDate= jQuery("#overruleFilterStartDate") ; */

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
    var lastDay = false ;
    var needTohideDay = true ;
    var streetNrString = false ;
    if ( jQuery('#jv_events_geo').length ) {
        var userLat = getCookie('tx_events_lat' ) ;
        if ( userLat )   {
            jQuery('#jv_events_geo').data("lat" , userLat)
            streetNrString = userLat + "," ;
        } else {
            userLat = jQuery('#jv_events_geo').data("lat") ;
        }
        var userLng = getCookie('tx_events_lng' ) ;
        if ( userLng )   {
            jQuery('#jv_events_geo').data("lng" , userLng)
            streetNrString = streetNrString + userLng ;
        } else {
            userLng = jQuery('#jv_events_geo').data("lng") ;
        }


        if( jQuery('#lat').length > 0  && jQuery('#lat').val() > 5 ) {
            userLat = jQuery('#lat').val();

        }
        if( jQuery('#lng').length > 0  && jQuery('#lng').val() > 5 ) {
            userLng = jQuery('#lng').val();
        }
        if ( !streetNrString === false && jQuery('#streetAndNr').length > 0 ) {
            jQuery('#streetAndNr').val(streetNrString) ;
        }
    }


	jQuery('.tx-jv-events DIV.jv-events-row').each(function (i) {
     //   console.log( " ************* single row **************** UID: " + jQuery(this).data("orguid")  ) ;

        var dist = PythagorasEquirectangular( userLat , userLng , jQuery(this).data("latitude") , jQuery(this).data("longitude") ) ;
        jQuery(this).find(".jv_events_dist").html(dist.toFixed(2) + " km") ;

		jQuery(this).removeClass('d-none') ;
        if( jQuery(this).hasClass("jvevents-newDay")) {
            if( lastDay && needTohideDay ) {
                jQuery(lastDay).addClass('d-none') ;
            }
            needTohideDay = true ;
            lastDay = this ;

        } else {
            if ( dist > maxDist  ) {
                jQuery(this).addClass('d-none') ;
            }
            if( fMonth && fMonth.val() && fMonth.val().length > 0 ) {
                if( jQuery(this).data("monthuid") && jQuery(this).data("monthuid")  !== fMonth.val() ) {
                    jQuery(this).addClass('d-none') ;
                }
            }

            if( fTag && fTag.val() > 0 ) {
                var fTags = jQuery(this).data("taguids") ;
                if( fTags ) {
                    fTags = fTags.split(",") ;
                    if( fTags.indexOf( fTag.val() ) < 0 ) {
                        jQuery(this).addClass('d-none') ;
                    }
                }

            }
            if( fCity && fCity.length > 0 ) {
                if(  fCity.val().length > 0 ) {
                    if( jQuery(this).data("cityuid") && decodeURI (jQuery(this).data("cityuid"))  !== fCity.val() ) {
                        jQuery(this).addClass('d-none') ;
                    }
                }
            }

            if( fCat && fCat.val() > 0 ) {
                var fCats = jQuery(this).data("catuids") ;
                if( fCats ) {
                    fCats = fCats.split(",") ;
                    if( fCats.indexOf( fCat.val() ) < 0 ) {
                        jQuery(this).addClass('d-none') ;
                    }
                }
            }
            if( fOrg && fOrg.val() > 0 ) {
    //            console.log( " data-orguid : " + jQuery(this).data("orguid")  + " Filter: " + fOrg.val() ) ;
                if( jQuery(this).data("orguid") && parseInt( jQuery(this).data("orguid"))  !== parseInt( fOrg.val()) ) {
                    jQuery(this).addClass('d-none') ;
                }
            }

            if( cTagChecked  ) {
                var sTags = jQuery(this).data("taguids") ;
            //     console.log( " sTags : " + sTags ) ;

                if( sTags ) {
                    sTags = "," + sTags + "," ;
                    needTohide = true ;
                    jQuery( cTags ).each( function() {
                    //    console.log( "Tag: " + jQuery(this).val() + "checked ? : " + jQuery(this).prop("checked") ) ;
                        if ( jQuery(this).prop("checked") ) {
                    //        console.log( "position of " + jQuery(this).val() + " in string " + sTags + " = " + sTags.indexOf( "," + jQuery(this).val()   ) ) ;
                            if( sTags.indexOf( "," + jQuery(this).val() + ","  ) > -1 ) {
                                needTohide = false ;
                                return false ;
                            }
                        }

                    }) ;


                    if( needTohide ) {
                        jQuery(this).addClass('d-none') ;
                    }
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
                        jQuery(this).addClass('d-none') ;
                    }
                }
            }
            if ( jQuery(this).hasClass('d-none')) {
                filterIsActive = true ;
              //  console.log(" Event is hidden:" + jQuery(this).data("eventuid")) ;
            } else {
                needTohideDay = false ;
            }
        }

	});
    if( lastDay && needTohideDay ) {
        jQuery(lastDay).addClass('d-none') ;
    }


	if ( filterIsActive ) {
		jQuery( "#filter-events BUTTON .jv-events-filter-sub-text").addClass('d-none') ;
		// jQuery( "#filter-events BUTTON").addClass('rotate-180') ;
		jQuery( "#filter-organizer BUTTON .jv-events-filter-sub-text").addClass('d-none') ;
        jQuery( "#filter-organizer BUTTON SVG").addClass('rotate-180') ;
		jQuery( "#filter-reset-events").removeClass('d-none') ;
		jQuery( "#filter-result-hint-events").removeClass('d-none') ;


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
        if( fDist && parseInt( fDist.val()) !=  parseInt(fDist.data('default') )) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][distance]=" + fDist.val() ;
        }

        if( fTag && fTag.val() > 0 ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][tags]=" + fTag.val() ;
        }

        if( fCity && fCity.val() != ''  && fCity.val() != undefined ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][citys]=" + fCity.val() ;
        }
        if( fMonth && fMonth.val() != 'undefined'  ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][months]=" + fMonth.val() ;
        }
      /*  if( startDate.length && startDate.val() != 'undefined' ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][overruleStartdate]=" + startDate.val() ;
        }
    */
        jv_events_pushUrl( urlFilter ) ;


	} else {
        jQuery( "#filter-events BUTTON .jv-events-filter-sub-text").removeClass('d-none') ;
        jQuery( "#filter-organizer BUTTON .jv-events-filter-sub-text").removeClass('d-none') ;
        jQuery( "#filter-events BUTTON SVG").removeClass('rotate-180') ;
        jQuery( "#filter-organizer BUTTON SVG").removeClass('rotate-180') ;
        jQuery( "#filter-reset-events").addClass('d-none') ;
        jQuery( "#filter-result-hint-events").addClass('d-none') ;
      //  jv_events_pushUrl( '' ) ;

	}


}
function getCookie(name) {
    var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
    return v ? v[2] : null;
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
    var fDist= jQuery("SELECT#jv_events_filter_distance") ;
    if( fDist) {
        var fDistDefault = jQuery( fDist).data("default");
        jQuery( fDist).val( fDistDefault ) ;
    }
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
		jQuery('#filter-reset-events').addClass('d-none');
	}
	if(jQuery('#filter-result-hint-events').length){
		jQuery('#filter-result-hint-events').addClass('d-none');
	}


    jQuery('.tx-jv-events DIV.jv-events-singleEvent').each(function (i) {
        jQuery(this).removeClass('d-none');
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
			jQuery(".jv-events-regform").submit();
		}
	}

}

function Deg2Rad( deg ) {
    return deg * Math.PI / 180;
}

function PythagorasEquirectangular( lat1, lon1, lat2, lon2 ) {
    lat1 = Deg2Rad(lat1);
    lat2 = Deg2Rad(lat2);
    lon1 = Deg2Rad(lon1);
    lon2 = Deg2Rad(lon2);
    var R = 6371; // km
    var x = (lon2-lon1) * Math.cos((lat1+lat2)/2);
    var y = (lat2-lat1);
    var d = Math.sqrt(x*x + y*y) * R;
    return d;
}


$.fn.getType = function(){ return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase(); };


