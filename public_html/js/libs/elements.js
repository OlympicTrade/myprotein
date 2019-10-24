var InputCounter = function(el, options) {
    var obj = this;

    this.setMax = function(max) {
        this.max = parseInt(max);
        this.counter > max ? this.setCount(max) : this.setCount(this.counter);
        return this;
    };

    this.setMin = function(min) {
        this.min = parseInt(min);
        this.counter < min ? this.setCount(min) : this.setCount(this.counter);
        return this;
    };

    this.getCount = function() {
        return this.counter;
    };

    this.changeTimer = null;
    this.setCount = function(newCount) {
        newCount = parseInt(newCount);
        if(newCount >= this.max) {
            newCount = this.max;
            this.incr.addClass('disabled');
        } else {
            this.incr.removeClass('disabled');
        }

        if(newCount <= this.min) {
            newCount = this.min;
            this.decr.addClass('disabled');
        } else {
            this.decr.removeClass('disabled');
        }

        if(newCount === this.counter)
            return this;

        this.counter = newCount;
        this.input.val(this.counter);

        clearTimeout(this.changeTimer);
        this.changeTimer = setTimeout(function() {
            obj.input.trigger('change');
        }, 250);

        return this;
    };

    this.el = el;
    this.input = $('input', el);
    this.incr  = $('.incr', el);
    this.decr  = $('.decr', el);

    this.counter = parseInt(this.input.val());
    this.min = this.input.attr('min') ? parseInt(this.input.attr('min')) : 1;
    this.max = this.input.attr('max') ? parseInt(this.input.attr('max')) : 999;


    this.incr.on('click', function() {
        obj.setCount(obj.counter + 1);
    });

    this.decr.on('click', function() {
        obj.setCount(obj.counter - 1);
    });

    this.setCount(this.counter)
};
/*
function pluginGenerator(els, className, options) {
    els = $(els);

    let init = function(el) {
        let dataName = 'obj' + className.toLowerCase();
        let sl = el.data(dataName);

        if (sl === undefined || sl === '') {
            sl = eval('new ' + className + '(el, options)');
            el.data(dataName, sl);
        }

        return sl;
    };

    if(els.length === 1) {
        return init(els);
    }

    els.each(function () {
        init($(this));
    });

    return this;
}
*/
$.fn.inputCounter = function (options) {
    var el = $(this);
    var dataName = 'obj-InputCounter'.toLowerCase();
    var sl = el.data(dataName);

    if (sl === undefined || sl === '') {
        sl = new InputCounter($(this), options);
        el.data(dataName, sl);
    }

    return sl;
};
/*
$.fn.inputCounter = function () {
    return pluginGenerator(this, 'InputCounter');
};*/