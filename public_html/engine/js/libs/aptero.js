var fn = {};

fn.loadCss = function(url) {
    $('head').append('<link rel="stylesheet" href="' + url + '" type="text/css">');
};

function dd(data) {
    console.log(data);
}

/* loadingHtml
 Loading animation
 */
fn.loadingHtml = function() {
    return '<div class="std-loading">' +
        '<i class="fa fa-cog fa-spin"></i>' +
        '<div class="label">Загрузка</div>' +
        '</div>';
};

/* toInt
 equal (int) in php/c++/...
 */
fn.int = function(str, def) {
    var int = parseInt(str);
    def = def ? def : 0;
    return isNaN(int) ? def : int;
};

fn.serializeArray = function(box) {
    var data = {};
    $('input, textarea, select', box).each(function() {
        var el = $(this);
        data[el.attr('name')] = el.val();
    });

    return data;
};

/* Price
 10000 -> 10 000
 */
fn.price = function(price) {
    price = new String(price);
    return price.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
};

fn.scrollTo = function(el, duration, options) {
    options = $.extend({
        offsetTop     : -50,
        easing        : 'swing'
    }, options);

    if(!duration) {
        duration = 600;
    }

    $('html, body').animate({
        scrollTop: el.offset().top + options.offsetTop,
    }, duration, options.easing);
};

/* Url */
/*
var url = new Url();
url.setPath('/user/');
url.setParams({id: 5});
url.setHash({test: 'word'});
url.redirect();
*/

var Url = function() {
    this.path = '',
    this.get  = {},
    this.hash = {},

    this.init = function() {
        var url = {};
        var tmp = location.href;

        tmp = tmp.split('?');
        url.path = '/' + tmp[0].replace(/^http:\/\/[a-zA-Z1-9\-\.]*\//, '');

        if(tmp[1]) {
            tmp = tmp[1].split('#');
            url.get = tmp[0];
            url.hash = tmp[1];
        }

        this.path = url.path;

        if(url.get) {
            var getParams = url.get.split('&');
            for(i = 0; i < getParams.length; i++)
            {
                tmp = getParams[i].split('=');
                this.get[tmp[0]] = tmp[1];
            }
        }

        if(url.hash) {
            var hashParams = url.hash.split('&');
            for(i = 0; i < hashParams.length; i++)
            {
                tmp = hashParams[i].split('=');
                this.hash[tmp[0]] = tmp[1];
            }
        }

        return this;
    };

    this.setPath = function(path) {
        this.path = path;
        return this;
    };

    this.setParams = function(params, value) {
        if (typeof params === "object") {
            this.get = $.extend(this.get, params);
        } else {
            this.get[params] = value;
        }

        return this;
    };

    this.getParams = function(key) {
        if(key) {
            return this.get[key];
        } else {
            return this.get;
        }
    };

    this.clearParams = function() {
        this.get = {};
        return this;
    };

    this.setHash = function(params, value) {
        if (typeof params === "object") {
            this.hash = $.extend(this.hash, params);
        } else {
            this.hash[params] = value;
        }

        location.hash = '#' + this.generateHash();

        return this;
    };

    this.getHash = function(key) {
        if(key) {
            return this.hash[key];
        } else {
            return this.hash;
        }
    };

    this.clearHash = function() {
        this.hash = {};
        return this;
    };

    this.generateParams = function() {
        var getParams = '';
        var first = true;

        for (var param in this.get) {
            if(this.get[param]) {
                getParams += (first ? '' : '&') + param + '=' + this.get[param];
                first = false;
            }
        }

        return getParams;
    };

    this.generateHash = function() {
        var hashParams = '';
        var first = true;

        for (var param in this.hash) {
            if(this.hash[param]) {
                hashParams += (first ? '' : '&') + param + '=' + this.hash[param];
                first = false;
            }
        }

        return hashParams;
    };

    this.getUrl = function() {
        var url = this.path;

        if(getParams = this.generateParams()) {
            url += '?' + getParams;
        }

        if(hashParams = this.generateHash()) {
            url += '#' + hashParams;
        }

        return url;
    };

    this.redirect = function() {
        location.href = this.getUrl();
    };
};

$.url = function(){
    var url = new Url();
    url.init();
    return url;
};

/* Tabs */
var Tabs = function() {
    this.el = null;

    this.init = function() {
        var header = this.el.children('.tabs-header');

        var tabs = this;
        header.find('.tab').on('click', function(){
            tabs.setActive($(this));
        });

        var active = header.children('.tab.active');
        if(!active.length) {
            var tabName = $.url().getHash(this.el.attr('data-name'));
            if(tabName) {
                active = header.children('.tab[data-tab="' + tabName +  '"]').addClass('active');
            }
        }
        if(!active.length) {
            active = header.children('.tab:eq(0)').addClass('active');
        }
        tabs.setActive(active);
    };

    this.setActive = function(tab) {
        var header = this.el.children('.tabs-header');
        var body   = this.el.children('.tabs-body');

        header.find('.tab').removeClass('active');
        header.find(tab).addClass('active');

        body.find('.tab').removeClass('active');
        body.find('.tab[data-tab="' + tab.attr('data-tab') + '"]').addClass('active');

        //$.url().setHash(this.el.attr('data-name'), tab.attr('data-tab'));
    };

    this.setElement = function(el) {
        this.el = el;
        return this;
    };
};

$.fn.tabs = function(params){
    $(this).each(function(){
        var tabs = new Tabs();
        tabs.setElement($(this)).init();
    });
};

$.aptero = fn;