function Message()
{
    this.msg = null;
    this.msgBox = $('.message-box');
    this.type = null;

    this.setLoading = function () {
        var msg = this.initMessage();
        this.setType('loading');

        msg.empty().append('<i class="fa fa-spin fa-refresh loading"></i> Загрузка');

        msg.animate({opacity: 1, top: 0}, 600);
    };
    this.setMessage = function(text, type) {
        var msg = this.initMessage();
        this.setType(type);
        var messageObj = this;

        msg.empty().append(text + ' <i class="fa fa-times close"></i>');
        msg.find('i').on('click', function(){
            messageObj.delMessage();
        });

        msg.animate({opacity: 1, top: 0}, 600);
    };
    this.delMessage = function() {
        if(!this.msg) {
            return;
        }

        var msg = this.msg;
        msg.fadeOut(200, function(){
            msg.remove();
        });
        this.type = null;
        this.msg = null;

    };
    this.setType = function(type) {
        if(!this.msg || type == this.type) {
            return;
        }

        var newType = type ? type : 'default';
        var oldType = this.type;
        var msg = this.msg;

        if(!oldType) {
            msg.addClass(newType);
        } else {
            msg.addClass(newType, 300, function() {
                msg.removeClass(oldType);
            });
        }

        this.type = newType;
    };
    this.initMessage = function() {
        if(!this.msg) {
            this.msg = $('<div/>').addClass('message');
            this.msgBox.prepend(this.msg);
        }

        return this.msg;
    };
}
