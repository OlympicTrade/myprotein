(function($) {
    var defaults;

    var options;

    function checkErrors(form, requestType, verifiableElement) {
        var result = false;
        var data = form.serializeArray();
        var async = (requestType == 'validate' ? true : false);

        data.push({
            name: 'requestType',
            value: requestType
        });

        $.ajax({
            type: "POST",
            async: async,
            url: form.attr('action'),
            data: data,
            dataType: "json",
            success: function(resp) {
                if(!resp['errors']) {
                    form.find('.errors').remove();
                    result = resp ? resp : true;
                    return;
                }

                form.find('.form-msg').empty();

                if(resp['errors']['all']) {
                    form.find('.form-msg').append(renderErrors(resp['errors']['all']));
                }

                form.find('input[type!="hidden"][type!="submit"], select, textarea').each(function() {
                    var errorsBox = $(this).parent().find('.errors');

                    if(!resp['errors'][$(this).attr('name')]) {
                        errorsBox.remove();
                    }

                    if(verifiableElement) {
                        if(verifiableElement.attr('name') == $(this).attr('name')) {
                            errorsBox.remove();
                            $(this).parent().append(renderErrors(resp['errors'][$(this).attr('name')]));
                        }

                        return;
                    }

                    errorsBox.remove();
                    $(this).parent().append(renderErrors(resp['errors'][$(this).attr('name')]));
                });
            }
        });

        return result;
    }

    function renderErrors(errors) {
        var html = '<div class="errors">';

        for(key in errors) {
            html += '<div class="error">' + errors[key] + '</li>';
        }

        html += '</div>';

        return html;
    }

    $.fn.formValidator = function(params){
        var options = $.extend({}, defaults, options, params);

        var form = this;

        form.submit(function(event){
            if(options.before) {
                options.before(form);
            }

            if(result = checkErrors(form, 'submit', null)) {
                if(options.success) {
                    options.success(result, form);
                }
            } else {
                if(options.fail) {
                    options.fail(result, form);
                }
            }

            if(options.after) {
                options.after(result, form);
            }

            event.stopPropagation();
            return false;
        });

        return this;
    };
})(jQuery);