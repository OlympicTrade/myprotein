class InputCounter {
    obj = this;

    constructor(el, options) {
        this.el = el;
        this.input = $('input', el);
        this.incr  = $('.incr', el);
        this.decr  = $('.decr', el);

        this.counter = parseInt(this.input.val());
        this.min = this.input.attr('min') ? parseInt(this.input.attr('min')) : 1;
        this.max = this.input.attr('max') ? parseInt(this.input.attr('max')) : 999;

        let obj = this;

        this.incr.on('click', function() {
            obj.setCount(obj.counter + 1);
        });

        this.decr.on('click', function() {
            obj.setCount(obj.counter - 1);
        });

        this.setCount(this.counter)
    }

    setMax(max) {
        this.max = parseInt(max);
        this.counter > max ? this.setCount(max) : this.setCount(this.counter);
        return this;
    };

    setMin(min) {
        this.min = parseInt(min);
        this.counter < min ? this.setCount(min) : this.setCount(this.counter);
        return this;
    };

    getCount() {
        return this.counter;
    }

    changeTimer = null;
    setCount(newCount) {
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

        let obj = this;
        clearTimeout(this.changeTimer);
        this.changeTimer = setTimeout(function() {
            obj.input.trigger('change');
        }, 250);

        return this;
    };
}

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

$.fn.inputCounter = function () {
    return pluginGenerator(this, 'InputCounter');
};