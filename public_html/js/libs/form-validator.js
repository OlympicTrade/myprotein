var FormValidator = function () {
    this.form = null;
    this.options = {
        autoValidate: false,
        before:  false,
        after:   false,
        fail:    false,
        success: false,
    };

    this.setElement = function (form) {
        this.form = form;
        return this;
    };

    this.init = function (options) {
        this.options = $.extend(this.options, options);

        $('input, select, textarea', this.form).on('change, keyup', function () {
            var parent = $(this).parent();
            parent.removeClass('invalid');
            parent.find('.errors').remove();
        });

        return this;
    };

    this.renderErrors = function(errors) {
        if (!errors) {
            return '';
        }

        var html = '<div class="errors">';

        for (key in errors) {
            html += '<div class="error">' + errors[key] + '</li>';
        }

        html += '</div>';

        return html;
    };

    this.checkErrors = function(form, options) {
        options = $.extend({
            requestType: 'submit',
            element: null,
            async: true
        }, options);

        var result = false;
        var data = form.serializeArray();
        var async = options.async;
        data.push({
            name: 'requestType',
            value: options.requestType
        });

        var formV = this;

        $.ajax({
            type: "POST",
            async: async,
            url: form.attr('action'),
            data: data,
            dataType: "json",
            success: function (resp) {
                if (!resp['errors'] || !Object.keys(resp['errors']).length) {
                    form.find('.errors').remove();
                    form.find('.invalid').removeClass('invalid');
                    result = resp ? resp : true;
                    return;
                }

                form.find('c').empty();

                $('.form-errors', form).empty();
                if (resp['errors']['all']) {
                    console.log(resp['errors']['all']);
                    $('.form-errors', form).append(formV.renderErrors(resp['errors']['all']));
                }

                form.find('input[type!="submit"], select, textarea').each(function () {
                    var input = $(this);
                    var inputBox = $(this).parent();
                    var errorsBox = inputBox.find('.errors');
                    var errors = resp['errors'][input.attr('name')];

                    if (!errors) {
                        errorsBox.remove();
                        inputBox.removeClass('invalid');
                    }

                    if (options.element) {
                        if (options.element.attr('name') == input.attr('name')) {
                            errorsBox.remove();
                            if (resp['errors'][input.attr('name')]) {
                                inputBox.addClass('invalid');
                                inputBox.append(formV.renderErrors(errors));
                            } else {
                                inputBox.removeClass('invalid');
                            }
                        }

                        return;
                    }

                    errorsBox.remove();

                    if (errors) {
                        inputBox.addClass('invalid');
                        inputBox.append(formV.renderErrors(errors));
                    } else {
                        inputBox.removeClass('invalid');
                    }
                });
            }
        });

        return result;
    };

    this.formValidate = function () {
        return checkErrors($(this), {requestType: 'validate', async: false});
    };

    this.submit = function () {
        var options = this.options;
        var form    = this.form;

        var formV = this;

        this.form.on('submit', function () {
            if (options.before) {
                options.before(form);
            }

            if (result = formV.checkErrors(form, {requestType: 'submit', async: false})) {
                if (options.success) {
                    options.success(result, form);
                }
            } else {
                if (options.fail) {
                    options.fail(result, form);
                }
            }

            if (options.after) {
                options.after(result, form);
            }

            return false;
        });

        return this;
    };
};

$.fn.formSubmit = function (options) {
    $(this).each(function () {
        var form = new FormValidator();
        form.setElement($(this)).init(options).submit();
    });
};
