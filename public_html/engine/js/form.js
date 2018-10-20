$(function(){
    initTextEditor();
    initFileManager();
    initPropsList();
    initAttrsList();
    initImagesList();
    initFilesList();
    initCoolectionsList();
    initElements();
});

function renderElement(type, options) {
    var html = '';

    function atributes(attrs) {
        var str = '';

        if(attrs === undefined) {
            return str;
        }

        attrs.forEach(function(attr) {
            html += ' ' + attr.key + '="' + attr.val + '"';
        });

        return str;
    }

    switch (type) {
        case 'text':
            var value = options.value !== undefined ? ' value="' + options.value + '"' : '';
            var placeholder = options.placeholder !== undefined ? ' placeholder="' + options.placeholder + '"' : '';

            html += '<input type="text" class="' + options.class + '"' + value + placeholder + atributes(options.attrs) +'>';
            break;
        case 'select':
            html += '<select class="' + options.class + '"' + atributes(options.attrs) +'>';

            if(options.empty !== undefined) {
                html += '<option value="">' + options.empty + '</option>';
            }

            options.options.forEach(function(option) {
                switch (typeof(option)) {
                    case 'object':
                        html += '<option value="' + option.id + '">' + option.name + '</option>';
                        break;
                    default:
                        html += '<option value="' + option + '">' + option + '</option>';
                        break;
                }
            });

            html += '</select>';
            break;
    }

    return html;
}

function initElements() {
    $('.std-counter').each(function() {
        var el = $(this);
        var input = $('input', el);

        $('.incr', el).on('click', function() {
            var count = parseInt(input.val()) + 1;
            var max = input.attr('max') ? parseInt(input.attr('max')) : 999;
            if(count > max) {
                return false;
            }

            input.val(count);
        });

        $('.decr', el).on('click', function() {
            var count = parseInt(input.val()) - 1;
            var min = input.attr('min') ? parseInt(input.attr('min')) : 1;
            if(count < min) {
                return false;
            }

            input.val(count);
        });

        var timer = null;
        $('.incr, .decr', el).on('click', function () {
            if(timer) clearTimeout(timer);

            setTimeout(function() {
                input.trigger('change');
            }, 200);
        });
    });
}

function initFileManager() {
    CKFinder.customConfig = function( config ) {
        config.language = 'ru';
        config.basePath = '../';
    };
}

function initCoolectionsList() {
    $('.collection-list').each(function(){
        var collectionBox = $(this);
        var name = collectionBox.data('name');

        $('.add', collectionBox).on('click', function(){
            var html = '<tr>';

            $('input[type="text"], select', $('.form', collectionBox)).each(function() {
                var el = $(this).clone()
                    .attr('name', name + '[add][' + $(this).data('name') + '][]')
                    .val($(this).val());

                html += '<td>' + el.prop('outerHTML') + '</td>';
            });

            html +=
                '<td>' +
                '<input type="hidden" name="price-collection[id][]" value="">' +
                '<span class="btn btn-blue del"><i class="fa fa-trash"></i></span>' +
                '</td>';

            html += '</tr>';

            html = $(html);

            $('input[type="text"], select', $('.form', collectionBox)).each(function() {
                html.find('[data-name="' + $(this).data('name') + '"]').val($(this).val());
            });

            $('.list', collectionBox).append(html);
        });

        $('.list', collectionBox).on('click', '.del', function(){
            $(this).closest('tr').remove();
        });
    });
}

function initFilesList() {
    $('.file-form').each(function(){
        var form = $(this);

        $('.del-file', form).on('click', function() {
            form.text('Файл отмечен на удаление.');
            form.append('<input type="hidden" value="on" name="' + $(this).data('name') + '">');
        })
    });
}

function initImagesList() {
    $('.images-list').each(function(){
        var imagesBox = $(this);
        var form = $('.form', imagesBox);
        var list = $('.list', imagesBox);
        var name = imagesBox.data('name');

        $('.edit', form).click(function(){
            var row = $(this).closest('.row').find('input').prop('disabled', false);
        });

        $('.add', form).click(function(){
            var filePath = $('[data-name="path"]', form);
            if(!filePath.val()) {
                return false;
            }

            var img = $('<div class="img"></div>');

            img.append('<span class="delete"><i class="fa fa-times-circle"></i></span>');
            img.append('<img src="' + filePath.val() + '">');


            $('input, select', form).each(function () {
                var propName = $(this).data('name');
                if(!propName) { return; }

                img.append('<input type="hidden" name="' + name + '[add][' + propName +'][]" value="' + $(this).val() + '">');
            });

            list.append(img);
        });

        imagesBox.on('click', '.delete', function() {
            var img = $(this).closest('.img');
            var id = $(this).data('id');

            img.fadeOut(200);

            if(!id) {
                img.remove();
                return;
            }

            var name = imagesBox.attr('data-name');
            list.append('<input type="hidden" name="' + name + '[del][]" value="' + id + '">');
        });
    });
}

function initPropsList() {
    $('.props-list').each(function(){
        var propsBox = $(this);
        var name = propsBox.attr('data-name');

        propsBox.find('.edit').click(function(){
            var row = $(this).closest('.row').find('input').prop('disabled', false);
        });

        propsBox.find('.remove').click(function(){
            var row = $(this).closest('.row');
            var id = $(this).attr('data-id');

            if(id) {
                propsBox.find('.list').append('<input type="hidden" name="' + name + '[delete]" value="' + id + '">');
            }

            row.remove();
        });

        propsBox.find('.add').click(function(){
            var name = propsBox.attr('data-name');
            propsBox.find('.list').append(
                '<div class="row"><input type="text" name="' + name + '[]"> <div class="btn btn-blue remove"><i class="fa fa-trash-o"></i></div></div>'
            );
            propsBox.find('.remove').click(function(){
                $(this).closest('.row').remove();
            });
        });
    });
}

function initAttrsList() {
    $('.attrs-list').each(function(){
        var propsBox = $(this);
        var name = propsBox.attr('data-name');

        propsBox.find('.edit').click(function(){
            var row = $(this).closest('.row').find('input').prop('disabled', false);
        });

        propsBox.find('.remove').click(function(){
            var row = $(this).closest('.row');
            var id = $(this).attr('data-id');

            $('input[data-filed="val"]', row).val('');
            row.css('display', 'none');
        });

        propsBox.find('.add').click(function(){
            var name = propsBox.attr('data-name');
            propsBox.find('.list').append(
                '<div class="row">' +
                    '<input type="text" name="' + name + '[keys][]" placeholder="Свойство"> ' +
                    '<input type="text" name="' + name + '[vals][]" placeholder="Значение">' +
                    ' <div class="btn btn-blue remove"><i class="fa fa-trash-o"></i></div>' +
                '</div>'
            );
            propsBox.find('.remove').click(function(){
                $(this).closest('.row').remove();
            });
        });
    });
}

//CKEditor
var editors = [];

function initTextEditor() {
    //var finder = new CKFinder();

    $('textarea.editor').each(function(i){
        var textarea = $(this);
        var editor = CKEDITOR.replace(this, {
            language: 'ru',
            toolbar: [
                { name: 'document', groups: ['mode', 'document', 'doctools'], items: ['Source']},
                { name: 'clipboard', groups: ['clipboard', 'undo'], items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']},
                //{ name: 'spellcheck', items: [ 'jQuerySpellChecker' ]},
                { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'], items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
                { name: 'links', items: ['Link', 'Unlink', 'Anchor' ]},
                { name: 'insert', items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe']},
                '/',
                { name: 'styles', items: ['Format']},
                { name: 'basicstyles', groups: ['basicstyles', 'cleanup'], items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']},
                { name: 'colors', items: ['TextColor', 'BGColor']},
                { name: 'tools', items: ['Maximize']},
                { name: 'others', items: ['-']}
            ],
            //contentsCss: '/admin/css/jquery/spellchecker.css'
        });

        CKFinder.setupCKEditor(editor, '/js/admin/ckfinder/');

        editor.textarea = textarea;

        editors.push(editor);
    });

    /*CKEDITOR.editorConfig = function(config) {
        config.extraPlugins = 'jqueryspellchecker';
    };*/
}

function updateEditors() {
    $.each(editors, function(value, i) {
        var editor = editors[value];
        if (editor) {
            editor.textarea.val(editor.getData());
        }
    });
}

function removeTextEditor() {
    if (editors.length > 0) {
        $.each(editors, function(value, i) {
            var editor = editors[value];
            if (editor) {
                editor.destroy();
                editor = null;
            }
        });
    }
}

//CKFinder
function showFileManager(setFunctionData)
{
    var finder = new CKFinder();
    finder.selectActionFunction = setFileField;
    finder.selectActionData     = setFunctionData;
    finder.popup();
}

function setFileField(fileUrl, data) {
    $(data['selectActionData']).parent().find('input[type="text"]').val(fileUrl);
    $(data['selectActionData']).parent().find('input[type="text"]').attr('href', fileUrl);
    $(data['selectActionData']).closest('.image-form').find('.pic-box').attr('href', fileUrl);
    $(data['selectActionData']).closest('.image-form').find('.pic-box img').attr('src', fileUrl);
}