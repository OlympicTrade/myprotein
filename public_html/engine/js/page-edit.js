$(function(){
    var module = $('.module', '.site-settings').val();
    var section = $('.section', '.site-settings').val();

    $('.btn-remove-ck').fancybox({
        closeBtn: false,
        minHeight: 30
    });

    $('.btn-remove').click(function(){
        $.ajax({
            url: '/admin/' + module + '/' + section + '/delete/',
            data: {
                id: $(this).attr('data-id')
            },
            success: function(resp) {
                $.url().setPath('/admin/' + module + '/' + section + '/').redirect();
            },
            dataType: 'json',
            type: 'post'
        });
    });

    var message = new Message();

    $('#edit-form').formValidator({
        success: function(resp, form){
            form.find('input[name="id"]').val(resp['id']);

            var url = $.url();
            url.setPath(('/admin/' + module + '/' + section + '/edit/?id=' + resp['id']).toLowerCase());
            url.redirect();
        },
        fail: function(resp, form){
            message.setMessage('Форма заполнена с ошибками', 'error');
        },
        before: function(form) {
            message.setLoading();
            updateEditors();
        },
        after: function(form) {
            message = new Message();
        }
    });

    $('.btn-submit', '.edit-form').on('click', function(event) {
        $('#edit-form').submit();
        return false;
    });
});