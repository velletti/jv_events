/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config
    // config.contentsCss = [ './contents.css' , '/typo3conf/ext/jve_template/Resources/Public/Css/Www/rte.min.css' ] ;
	// The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        { name: 'styles', groups: [ 'styles' ] },
        { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
        { name: 'forms', groups: [ 'forms' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
        { name: 'links', groups: [ 'links' ] },
        { name: 'insert', groups: [ 'insert' ] },
        { name: 'colors', groups: [ 'colors' ] },
        { name: 'tools', groups: [ 'tools' ] },
        { name: 'others', groups: [ 'others' ] },
        { name: 'about', groups: [ 'about' ] }
    ];

    // config.removeButtons = 'Source,Save,NewPage,Preview,Print,Templates,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Italic,Underline,Subscript,Superscript,Indent,Outdent,CreateDiv,Blockquote,BidiLtr,BidiRtl,Language,Image,Flash,Table,PageBreak,Iframe,Font,FontSize,TextColor,BGColor,About,Maximize,PasteFromWord,Find,Replace,SelectAll,Scayt,NumberedList,JustifyRight,JustifyBlock,Anchor,Smiley,Styles';


    // Set the most common block elements.
	config.format_tags = 'p;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';
};
