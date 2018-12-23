CKEDITOR.editorConfig = function( config ) {
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

    config.removeButtons = 'Source,Save,NewPage,Preview,Print,Templates,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Italic,Underline,Subscript,Superscript,Indent,Outdent,CreateDiv,Blockquote,BidiLtr,BidiRtl,Language,Image,Flash,Table,PageBreak,Iframe,Font,FontSize,TextColor,BGColor,About,Maximize,PasteFromWord,Find,Replace,SelectAll,Scayt,NumberedList,JustifyRight,JustifyBlock,Anchor,Smiley,Styles';
};