/**
 * Module: TYPO3/CMS/JvEvents/ShowEventInFrontend
 *
 * JavaScript to handle data import
 * @exports TYPO3/CMS/JvEvents/ShowEventInFrontend
 */

define(['jquery', 'TYPO3/CMS/Backend/Icons', 'TYPO3/CMS/Backend/FormEngine'], function ($, Icons, FormEngine) {

    'use strict';

    /**
     * @exports TYPO3/CMS/JvEvents/ShowEventInFrontend
     */
    var ShowEventInFrontend = {};


    /**
     * it Will just render a LINK and Open it in new Browser Tab
     */
    ShowEventInFrontend.initializeEvents = function () {

        $('.windowOpenUri').on('click', function (evt) {
            evt.preventDefault();
            window.open(  $(this).attr('data-uri') , '_blank')      ;
        });
    };

    ShowEventInFrontend.initializeEvents();

    return ShowEventInFrontend;
});