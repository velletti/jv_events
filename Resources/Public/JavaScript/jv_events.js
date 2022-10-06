/**
 * Created by velletti on 29.09.2016.
 * Last Change: 23.6.2021
 */
//$(window).on("load", function(){
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
                        if ( !$(this).hasClass("fieldsetbox5")) {
                            heightBiggestElement = $(this).height() ;
                        }
                    }
                });
                $(this).find('fieldset').each(function(){
                    $(this).css("min-height" , heightBiggestElement);
                    if ( $('.tx-jv-events .filter').hasClass("filterType6")) {
                        $(this).css("height", heightBiggestElement);
                    }
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
    // the included map javascript will do the refesh, when map is loaded.
    // if we refresh the eventslist before map is ready  will not work
    if( ! jQuery("#map").length  ) {
        jv_events_refreshList();
    }
}
function jv_events_initPosition(position) {

    if( jQuery('#jv_events_geo').length > 0  ) {
        jQuery('#jv_events_geo').data("lng" , position.coords.longitude ) ;
        jQuery('#jv_events_geo').data("lat" , position.coords.latitude ) ;
    }
}

function jv_events_initOneFilter(filterName) {

    if ( jQuery('SELECT#jv_events_filter_' + filterName ).length ) {
        // console.log( "Select " + filterName + " found") ;

        var filterVal = jv_events_GetURLParameter('tx_jvevents_events[eventsFilter][' + filterName + ']') ;
        if ( filterVal ) {
            jQuery('SELECT#jv_events_filter_' + filterName + ' OPTION').each(function(i) {
                if ("'" + jQuery(this).val() + "'" == "'" + filterVal +"'") {
                    jQuery(this).prop("selected", true);
                }
            });
        }
        jQuery('SELECT#jv_events_filter_' + filterName ).change(function(i) {
            jv_events_refreshList() ;
            if( filterName == "citys" && jQuery("#map").length && jQuery(this).val().length > 1 ){
                let eventInCity = jQuery('[data-cityuid="' + jQuery(this).val() + '"]') ;
                if( eventInCity && eventInCity.data("address") ) {
                    updateMarker( eventInCity.data("address")) ;
                } else {
                    updateMarker(jQuery(this).val()) ;
                }

            }
        });
    }
    if ( jQuery('#jv_events_filter_' + filterName + " input[type=checkbox]").length ) {
     //   console.log( "INPUT  " + filterName + " found") ;
        var filterVal = jv_events_GetURLParameter('tx_jvevents_events[eventsFilter][' + filterName + ']') ;
        //   console.log( "filterVal = " + filterVal + " ") ;
        if ( filterVal ) {
            var filterSplit =  filterVal.split(",") ;
            if(filterSplit.length > 1 ) {
                //         console.log( "filterSplit.length  > 1 " ) ;
                jQuery('#jv_events_filter_' + filterName + ' input[type=checkbox]').each(function(i) {
                    if(  filterSplit.indexOf( jQuery(this).val() ) > -1 ) {
                        jQuery(this).prop("checked", true);
                    }
                });
            } else {
                //        console.log( "filterSplit.length  <= 1 " ) ;
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
        jQuery('#jv_events_filter_' + filterName + " input[type=checkbox]").change(function(i) {
            jv_events_refreshList() ;
        });
    }
}


function jv_events_refreshList(){
    var fMonth= jQuery("SELECT#jv_events_filter_months") ;
    var fTag= jQuery("SELECT#jv_events_filter_tags") ;
    var fCity= jQuery("SELECT#jv_events_filter_citys") ;
    var fCat= jQuery("SELECT#jv_events_filter_categories") ;
    var fOrg= jQuery("SELECT#jv_events_filter_organizers") ;

    var iZoom= jQuery("INPUT#zoom") ;
    var userLat = false ;
    var userLng= false ;
    var maxDist = false ;
    if( iZoom && jQuery( iZoom ).val() > 0 ) {




        if( jQuery('#lat').length > 0  && jQuery('#lat').val() > 5 ) {
            userLat = jQuery('#lat').val();
        }

        if( jQuery('#lng').length > 0  && jQuery('#lng').val() > 5 ) {
            userLng = jQuery('#lng').val();
        }
        if( userLat  && userLng ) {
            switch (jQuery( iZoom ).val())
            {
                case "15":
                case "14":
                    maxDist = 10;
                    break;
                case "13":
                case "12":
                case "11":
                    maxDist = 20;
                    break;
                case "10":
                    maxDist = 30;
                    break;
                case "9":
                    maxDist = 60;
                    break;
                case "8":
                    maxDist = 100;
                    break;
                case "7":
                    maxDist = 200;
                    break;
                case "6":
                    maxDist = 300;
                    break;
                case "5":
                    maxDist = 500;
                    break;
                default:
                    maxDist = 9999;
            }
        //    console.log( "Lat:" + userLat + " Lng: " + userLng + " maxDist:" +  maxDist ) ;

        }
    }



    var cCats= jQuery("#jv_events_filter_categories INPUT[type=checkbox]") ;

    var cTags= jQuery(".jv_events_filter_tag_check") ;
    var cTagChecked = false ;
    if( jQuery( ".filterType7").length ) {
        var cTagChecked1 = false  ;
        var cTags1= jQuery(".fieldsetbox1 .jv_events_filter_tag_check") ;
        if( cTags1.length ) {

            jQuery( cTags1 ).each( function() {
                if ( jQuery(this).prop("checked") ) {
                    cTagChecked1 = true ;
                    cTagChecked = true ;
                    jQuery("#toggle-accordion-1").prop("checked" , true ) ;
                    return false ;
                }

            }) ;
        }
        var cTagChecked2 = false  ;
        var cTags2= jQuery(".fieldsetbox2 .jv_events_filter_tag_check") ;
        if( cTags2.length ) {
            jQuery( cTags2 ).each( function() {
                if ( jQuery(this).prop("checked") ) {
                    cTagChecked2 = true ;
                    cTagChecked = true ;
                    jQuery("#toggle-accordion-2").prop("checked" , true ) ;
                    return false ;
                }

            }) ;
        }
        var cTagChecked3= false  ;
        var cTags3= jQuery(".fieldsetbox3 .jv_events_filter_tag_check") ;
        if( cTags3.length ) {
            jQuery( cTags3 ).each( function() {
                if ( jQuery(this).prop("checked") ) {
                    cTagChecked3 = true ;
                    cTagChecked = true ;
                    jQuery("#toggle-accordion-3").prop("checked" , true ) ;
                    return false ;
                }

            }) ;
        }
        var cTagChecked4= false  ;
        var cTags4= jQuery(".fieldsetbox4 .jv_events_filter_tag_check") ;
        if( cTags4.length ) {
            jQuery( cTags4 ).each( function() {
                if ( jQuery(this).prop("checked") ) {
                    cTagChecked4 = true ;
                    cTagChecked = true ;
                    jQuery("#toggle-accordion-4").prop("checked" , true ) ;
                    return false ;
                }

            }) ;
        }
    } else {

        jQuery( cTags ).each( function() {
            if ( jQuery(this).prop("checked") ) {
             //   console.log("found one tag checked: " + jQuery(this).val() )
                cTagChecked = true ;
                return false ;
            }

        }) ;
    }
    var cCatChecked = false ;

    jQuery( cCats ).each( function() {
        if ( jQuery(this).prop("checked") ) {
            //     console.log("found one category checked: " + jQuery(this).val() )
            if(  jQuery("#toggle-accordion-cat").length ) {
                jQuery("#toggle-accordion-cat").prop("checked" , true ) ;
            }

            cCatChecked = true ;
            return false ;
        }

    }) ;




    var filterIsActive = false ;
    var needTohide = false ;
    let resultcountEvents = 0 ;
    var dist = 0 ;
    jQuery('.tx-jv-events DIV.jv-events-singleEvent').each(function (i) {
        // console.log( " ************* event **************** UID: " + jQuery(this).data("eventuid")  ) ;

        jQuery(this).removeClass('hide') ;
        if( fMonth && fMonth.val() && fMonth.val().length > 0 ) {
            if( jQuery(this).data("monthuid")  != fMonth.val() ) {
                jQuery(this).addClass('hide').addClass('hidden-by-fMonth') ;
            }
            if(  jQuery("#toggle-accordion-6").length ) {
                jQuery("#toggle-accordion-6").prop("checked" , true ) ;
            }

        }


        if( fTag && fTag.val() > 0 && !jQuery(this).hasClass('hide')) {
            var fTags = jQuery(this).data("taguids") ;
            if( fTags ) {
                fTags = fTags.split(",") ;
                if( fTags.indexOf( fTag.val() ) < 0 ) {
                    jQuery(this).addClass('hide').addClass('hidden-by-fTag') ;
                }
            } else {
                jQuery(this).addClass('hide').addClass('hidden-by-fTag') ;
            }

        }
        if( fCity && fCity.length > 0 && !jQuery(this).hasClass('hide')) {
            if(  fCity.val().length > 0 ) {
                if( (jQuery(this).data("cityuid")) && decodeURI (jQuery(this).data("cityuid")) != (fCity.val()) && ( parseInt( jQuery(this).data("longitude") ) != 0  )  ) {
                    jQuery(this).addClass('hide').addClass('hidden-by-fCity') ;
                }
                if(  jQuery("#toggle-accordion-8").length ) {
                    jQuery("#toggle-accordion-8").prop("checked" , true ) ;
                }
            }
        }

        if( fCat && fCat.val() > 0 && !jQuery(this).hasClass('hide')) {
            var fCats = jQuery(this).data("catuids") ;
            if( fCats ) {
                fCats = fCats.split(",") ;
                if( fCats.indexOf( fCat.val() ) < 0 ) {
                    jQuery(this).addClass('hide').addClass('hidden-by-fCat') ;
                }
            } else {
                jQuery(this).addClass('hide').addClass('hidden-by-fCat') ;
            }
        }
        if( fOrg && !jQuery(this).hasClass('hide') ) {

            if ( ($("#jv_events_filter_tags").hasClass( "filterType6") || $("#jv_events_filter_tags").hasClass( "filterType7")) && fOrg.val() ) {
               //  console.log( "filterType6: forg: " + ( fOrg.val()) + " <> " + decodeURI(jQuery(this).data("orgname")) ) ;
                if( (jQuery(this).data("orgname")) && decodeURI (jQuery(this).data("orgname")) !== (fOrg.val()) ) {
                    jQuery(this).addClass('hide').addClass('hidden-by-fOrg') ;
                }
                if(  jQuery("#toggle-accordion-7").length ) {
                    jQuery("#toggle-accordion-7").prop("checked" , true ) ;
                }
            } else {
                if( fOrg.val() > 0 && parseInt( jQuery(this).data("orguid"))   !== parseInt( fOrg.val()) ) {
                    jQuery(this).addClass('hide').addClass('hidden-by-fOrg') ;
                }
            }


        }

        if( cTagChecked === true && !jQuery(this).hasClass('hide')) {
            var sTags = jQuery(this).data("taguids") ;
            // console.log( " sTags (Tag Ids of Event) : " + sTags ) ;

            if( sTags ) {
                sTags = "," + sTags + "," ;



                // with filter  type 7 (on left side) we always combined search, but only between sections
                if( jQuery( ".filterType7").length ) {
                    var needTohide1 = false ;
                    if( cTagChecked1 === true ) {
                        needTohide1 = true ;
                        jQuery( cTags1 ).each( function() {
                            if ( jQuery(this).prop("checked") ) {
                                if( sTags.indexOf( "," + jQuery(this).val() + ","  ) > -1 ) {
                                    needTohide1 = false ;
                                    return false ;
                                }
                            }
                        }) ;
                    }
                    var needTohide2 = false ;
                    if( cTagChecked2 === true  ) {
                        needTohide2 = true ;
                        jQuery( cTags2 ).each( function() {
                            if ( jQuery(this).prop("checked") ) {
                                if( sTags.indexOf( "," + jQuery(this).val() + ","  ) > -1 ) {
                                    needTohide2 = false ;
                                    return false ;
                                }
                            }
                        }) ;
                    }
                    var needTohide3 = false ;
                    if( cTagChecked3 === true  ) {
                        needTohide3 = true ;
                        jQuery( cTags3 ).each( function() {
                            if ( jQuery(this).prop("checked") ) {
                                if( sTags.indexOf( "," + jQuery(this).val() + ","  ) > -1 ) {
                                    needTohide3 = false ;
                                    return false ;
                                }
                            }
                        }) ;
                    }
                    var needTohide4 = false ;
                    if( cTagChecked4 === true  ) {
                        needTohide4 = true ;
                        jQuery( cTags4 ).each( function() {
                            if ( jQuery(this).prop("checked") ) {
                                if( sTags.indexOf( "," + jQuery(this).val() + ","  ) > -1 ) {
                                    needTohide4 = false ;
                                    return false ;
                                }
                            }
                        }) ;
                    }
                    if( needTohide1 || needTohide2 || needTohide3 || needTohide4 ) {
                        var needTohide = true ;
                    }


                } else {
                    var needTohide = true ;
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
                }



                if( needTohide ) {
                    jQuery(this).addClass('hide').addClass('hidden-by-cTag') ;
                }
            } else {
                jQuery(this).addClass('hide').addClass('hidden-by-cTag') ;
            }
        }

        if( cCatChecked === true && !jQuery(this).hasClass('hide')) {
            var sCats = jQuery(this).data("catuids") ;

            if( sCats ) {
                sCats = "," + sCats + "," ;
                // console.log( " sCats of Event : " + sCats ) ;
                let needTohide = true ;
                jQuery( cCats ).each( function() {
                     // console.log("FormField: " + jQuery(this).val() + " " +  jQuery(this).prop("checked") ) ;
                    if ( jQuery(this).prop("checked") ) {
                          // console.log( "position of cat ID " + jQuery(this).val()  + " = " + sCats.indexOf( "," + jQuery(this).val() + "," ) ) ;
                        if( sCats.indexOf( "," + jQuery(this).val() + ","  ) > -1 ) {
                            // console.log( "No need to hide") ;
                            needTohide = false ;
                            return false ;
                        }
                    }

                }) ;
                // console.log( "needTohide " + needTohide ) ;
                if( needTohide === true ) {
                    jQuery(this).addClass('hide').addClass('hidden-by-cCat') ;
                }
            } else {
                jQuery(this).addClass('hide').addClass('hidden-by-cCat') ;
            }
        }
        if ( maxDist  && !jQuery(this).hasClass('hide') ) {
            dist = PythagorasEquirectangular( userLat , userLng , jQuery(this).data("latitude") , jQuery(this).data("longitude") ) ;
            if ( dist > maxDist  ) {
                // console.log( jQuery(this).data("eventuid") + ": MaxDist " + maxDist + " > dist: " + dist ) ;
                jQuery(this).addClass('hide').addClass('hidden-by-maxDist')  ;
            }
        }


        if ( jQuery(this).hasClass('hide')) {
            filterIsActive = true ;
        } else {
            resultcountEvents ++ ;
        }
    });

    jQuery( "#filter-resultcount-events").html( resultcountEvents ) ;

    if ( filterIsActive ) {
        jQuery( "#filter-events A").addClass('hide') ;
        jQuery( "#filter-reset-events").removeClass('hide') ;
        jQuery( "#filter-result-hint-events").removeClass('hide') ;

    } else {
        jQuery( "#filter-events A").removeClass('hide') ;
        jQuery( "#filter-result-hint-events").addClass('hide') ;

        //   jQuery( "#filter-reset-events").addClass('hide') ;
    }

    // now change also the URL in the Browser to be able to copy the URL !!!
    urlFilter = "" ;
    if( fOrg ) {
        if ( $("#jv_events_filter_tags").hasClass( "filterType6") && fOrg.val() ) {
            urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][organizers]=" + fOrg.val() ;
        } else {
            if(  fOrg.val() > 0 ) {
                urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][organizers]=" + fOrg.val() ;
            }
        }
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

    if( fCity && fCity.val() != '' && fCity.val() !== 'undefined'  ) {
        urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][citys]=" + fCity.val() ;
    }
    if( fMonth && fMonth.val() != '' && fMonth.val() !== 'undefined'  ) {
        urlFilter = urlFilter + "&tx_jvevents_events[eventsFilter][months]=" + fMonth.val() ;
    }

    jv_events_pushUrl( urlFilter ) ;


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


function Deg2Rad( deg ) {
    return deg * Math.PI / 180;
}

function PythagorasEquirectangular( lat1, lon1, lat2, lon2 ) {

    if( lat2 > 0 && lon2 > 0 ) {
        lat1 = Deg2Rad(lat1);
        lat2 = Deg2Rad(lat2);
        lon1 = Deg2Rad(lon1);
        lon2 = Deg2Rad(lon2);
        var R = 6371; // km
        var x = (lon2-lon1) * Math.cos((lat1+lat2)/2);
        var y = (lat2-lat1);
        var d = Math.sqrt(x*x + y*y) * R;
        return d;
    } else {
        return 0;
    }

}


$.fn.getType = function(){ return this[0].tagName == "INPUT" ? this[0].type.toLowerCase() : this[0].tagName.toLowerCase(); };


