var fn = {};

function dd(data) {
    console.log(data);
}

fn.loadCss = function(url) {
    $('head').append('<link rel="stylesheet" href="' + url + '" type="text/css">');
};

/* Loading animation */
fn.loadingHtml = function(box) {
    var html =
        '<div class="loading">' +
            '<i class="fa fa-circle-o-notch fa-spin"></i>' +
        '</div>'
    
    box.empty().html(html);
};

/* cpp (int) */
fn.int = function(str, def) {
    var int = parseInt(str);
    def = def ? def : 0;
    return isNaN(int) ? def : int;
};

fn.serializeArray = function(box) {
    var data = {};
    $('input, textarea, select', box).each(function() {
        var el = $(this);
        var name = el.attr('name');
        
        if(!name) { return; }

        if(el.attr('type') == 'checkbox') {
            if(el.is(':checked')) {
                data[name] = data[name] || [];
                data[name].push(el.val());
            }

            return;
        }

        data[name] = el.val();
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

/* Url
 var url = new Url();
 url.setPath('/user/');
 url.setParams({id: 5});
 url.setHash({test: 'word'});
 url.redirect();
 */
var Url = function () {
    this.path = '',
        this.get = {},
        this.hash = {},

        this.init = function () {
            var url = {};

            var tmp = location.href.split('?');
            if (tmp[1]) {
                tmp = tmp[1].split('#');
                url.get = tmp[0];
                url.hash = tmp[1];
            }

            url.hash = location.hash.substr(1);
            url.path = '/' + tmp[0].replace(/^http:\/\/[a-zA-Z1-9\-\.]*\//, '');

            this.path = url.path;

            if (url.get) {
                var getParams = url.get.split('&');
                for (i = 0; i < getParams.length; i++) {
                    var tmp = getParams[i].split('=');
                    this.get[tmp[0]] = tmp[1];
                }
            }

            if (url.hash) {
                var hashParams = url.hash.split('&');
                for (i = 0; i < hashParams.length; i++) {
                    var tmp = hashParams[i].split('=');
                    this.hash[tmp[0]] = tmp[1];
                }
            }

            return this;
        };

    this.setPath = function (path) {
        this.path = path;
        return this;
    };

    this.setParams = function (params, value) {
        if (typeof params === "object") {
            this.get = $.extend(this.get, params);
        } else {
            this.get[params] = value;
        }

        return this;
    };

    this.getParams = function (key) {
        if (key) {
            return this.get[key];
        } else {
            return this.get;
        }
    };

    this.clearParams = function () {
        this.get = {};
        return this;
    };

    this.setHash = function (params, value) {
        if (typeof params === "object") {
            this.hash = $.extend(this.hash, params);
        } else {
            this.hash[params] = value;
        }

        location.hash = '#' + this.generateHash();

        return this;
    };

    this.getHash = function (key) {
        if (key) {
            return this.hash[key];
        } else {
            return this.hash;
        }
    };

    this.clearHash = function () {
        this.hash = {};
        return this;
    };

    this.generateParams = function () {
        var getParams = '';
        var first = true;

        for (var param in this.get) {
            if (this.get[param]) {
                getParams += (first ? '' : '&') + param + '=' + this.get[param];
                first = false;
            }
        }

        return getParams;
    };

    this.generateHash = function () {
        var hashParams = '';
        var first = true;

        for (var param in this.hash) {
            if (this.hash[param]) {
                hashParams += (first ? '' : '&') + param + '=' + this.hash[param];
                first = false;
            }
        }

        return hashParams;
    };

    this.getUrl = function () {
        var url = this.path;

        if (getParams = this.generateParams()) {
            url += '?' + getParams;
        }

        if (hashParams = this.generateHash()) {
            url += '#' + hashParams;
        }

        return url;
    };

    this.redirect = function () {
        location.href = this.getUrl();
    };
};

fn.url = function () {
    return new Url();
};

/* Tabs */
var Tabs = function () {
    this.el = null;
    this.options = {
        historyMode: false,
        afterLoad:   false
    };

    this.init = function (options) {
        this.options = $.extend(this.options, options);

        var header = this.el.children('.tabs-header');

        this.el.find('.tab').each(function() {
            var tab = $(this);
            if(!tab.data('tab')) {
                tab.attr('data-tab', (parseInt(tab.index() + 1)));
            }
        });

        var tabs = this;
        header.find('.tab').on('click', function () {
            var tab = $(this);
            if(tab.prop("tagName") != 'A' || tab.data('load')) {
                tabs.setActive(tab);
                return false;
            }
        });

        var active = header.children('.tab.active');
        if (!active.length) {
            var tabName = fn.url().getHash(this.el.attr('data-name'));
            if (tabName) {
                active = header.children('.tab[data-tab="' + tabName + '"]').addClass('active');
            }
        }
        if (!active.length) {
            active = header.children('.tab:eq(0)').addClass('active');
        }
        tabs.setActive(active);
    };

    this.setActive = function (tab) {
        var header = this.el.children('.tabs-header');
        var body = this.el.children('.tabs-body');

        var headerTab = header.find(tab);
        var bodyTab = body.find('.tab[data-tab="' + tab.attr('data-tab') + '"]');

        if(!bodyTab.length) {
            bodyTab = $('<div class="tab"></div>');
            bodyTab.attr('data-tab', tab.attr('data-tab'));
            bodyTab.appendTo(body);
        }

        if(headerTab.hasClass('active') && !bodyTab.is(':empty')) {
            bodyTab.addClass('active');
            return;
        }

        headerTab.addClass('active').siblings().removeClass('active');
        bodyTab.addClass('active').siblings().removeClass('active');

        var options = this.options;

        if (headerTab.attr('data-load')) {

            var url = headerTab.attr('data-load');

            if(this.options.historyMode) {
                var disablePushState = this.options.disablePushState;

                $.ajax({
                    url: url,
                    dataType: 'json',
                    success: function(resp) {
                        bodyTab.html(resp.html);
                        if(!disablePushState) {
                            History.replaceState({}, resp.meta.title, url);
                        }

                        if(options.afterLoad) {
                            options.afterLoad();
                        }
                    }
                });
            } else {
                bodyTab.load(url, function () {
                    headerTab.attr('data-load', null);
                });
            }
        }

        if (this.el.attr('data-name')) {
            fn.url().setHash(this.el.attr('data-name'), headerTab.attr('data-tab'));
        }
    };

    this.setElement = function (el) {
        this.el = el;
        return this;
    };
};

$.fn.tabs = function (options) {
    $(this).each(function () {
        var tabs = new Tabs();
        tabs.setElement($(this)).init(options);
    });
};

$.aptero = fn;
