$(function(){
    var module = $('.module', '.site-settings').val();
    var section = $('.section', '.site-settings').val();

    var message = new Message();

    $('#settings-form').formValidator({
        success: function(resp, form){
            form.find('input[name="id"]').val(resp['id']);

            message.setMessage('Настройки сохранены', 'success');
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
});