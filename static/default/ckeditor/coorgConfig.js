CKEDITOR.editorConfig = function( config )
{
    config.toolbar = 'Full';

    config.toolbar_Full =
    [
        ['Styles', '-', 'Bold','Italic', 'Underline','Strike', '-', 'NumberedList','BulletedList', '-', 'Link', 'Unlink', 'Image', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', 'SelectAll']
    ];
    
     config.toolbar_Lite =
    [
        ['Bold','Italic', 'Underline','Strike', '-', 'NumberedList','BulletedList', '-', 'Link', 'Unlink']
    ];
    
    config.resize_enabled = false;
    config.toolbarCanCollapse = false;
    config.stylesSet = 'coorg:coorgStyles.js';
    config.removePlugins = 'elementspath,scayt';
    config.disableNativeSpellchecker = false;
    
    // If we choose to enable scayt again, we have to set our own language...
    // config.scayt_sLang = COORGLANG;
};

