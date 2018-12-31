/**
 * Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */


if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
    CKEDITOR.tools.enableHtml5Elements( document );

// The trick to keep the editor in the sample quite small
// unless user specified own height.
CKEDITOR.config.height = 300;
CKEDITOR.config.width = 'auto';
CKEDITOR.config.contentsCss = [ '/typo3conf/ext/jve_template/Resources/Public/Css/Www/rte.min.css' ,  './contents.css' ] ;
CKEDITOR.config.customConfig =  '/typo3conf/ext/jv_events/Resources/Public/Javascript/ckeditor_config.js'   ;

var initSample = ( function() {
    var FIELDID = 'editor';
    return function() {
        CKEDITOR.replace( FIELDID );

    };

} )();

