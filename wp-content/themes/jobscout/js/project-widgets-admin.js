/* Native WordPress image selection and visual text editing, including AJAX widget forms. */
jQuery(function ($) {
    $(document).on('click', '.jsc-select-media', function () {
        const control = $(this).closest('.jsc-media-control');
        const multiple = $(this).attr('data-multiple') === '1';
        const frame = wp.media({title: multiple ? 'Choose company photos' : 'Choose section image', state: 'library', library: {type: 'image'}, multiple, button: {text: 'Use selected images'}});
        frame.on('open', function () {
            control.find('.jsc-media-value').val().split(',').filter(Boolean).forEach(id => frame.state().get('selection').add(wp.media.attachment(id)));
        });
        frame.on('select', function () {
            const items = frame.state().get('selection').toJSON();
            control.find('.jsc-media-value').val(items.map(item => item.id).join(',')).trigger('change');
            const preview = control.find('.jsc-media-preview').empty();
            items.forEach(item => $('<img>', {src: item.sizes && item.sizes.thumbnail ? item.sizes.thumbnail.url : item.url, alt: item.alt || '', style: 'max-width:100px;height:70px;object-fit:cover;margin:4px'}).appendTo(preview));
        });
        frame.open();
    });
    $(document).on('click', '.jsc-remove-media', function () {
        const control = $(this).closest('.jsc-media-control');
        control.find('.jsc-media-value').val('').trigger('change');
        control.find('.jsc-media-preview').empty();
    });
    $(document).on('click', '.jsc-edit-rich', function () {
        const textarea = $(this).siblings('textarea')[0];
        if (!textarea || !textarea.id || textarea.id.includes('__i__')) return;
        wp.editor.remove(textarea.id);
        wp.editor.initialize(textarea.id, {tinymce: {wpautop: true, toolbar1: 'formatselect,bold,italic,bullist,numlist,link,unlink,undo,redo', setup: editor => editor.on('change keyup', () => { editor.save(); $(textarea).trigger('change'); })}, quicktags: false, mediaButtons: false});
        $(this).hide();
    });
    $(document).on('click', '.widget-control-save', function () { if (window.tinymce) tinymce.triggerSave(); });
});
