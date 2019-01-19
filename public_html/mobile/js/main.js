var $document = $(window);

$(function() {
    initPopups();
    initElements($('body'));
    initNav();
    initDatepicker();
    initAutocomplete();
    initMetric();
});

function initMetric() {
    var url = $.aptero.url();
    url.init();

    $.ajax({
        url: '/metrics/init/',
        method: 'post',
        data: {
            query: url.getParams()
        }
    });
}

function initNav() {
    //Menu
    var header = $('#header');
    var nav = $('.nav', header);
    var menu = $('.menu', nav);

    $(window).on('resize', function() {
        $('#container').css({minHeight: $document.height()});
    }());

    $('.anchor').on('click', function () {
        var scrollTo = $($(this).attr('href')).offset().top - 100;

        $('html, body').animate({scrollTop: scrollTo}, 300);

        return false;
    });

    var flags = $('.flags', header);
    flags.on('click', function () {
        closeMenu();
    });

    $('.flag', flags).on('click', function () {
        if($(this).hasClass('active')) return;

        var lang = $(this).data('lang');
        $.cookie('lang', lang, {expires: 365, path: "/"});
        location.reload();
    });

    var openMenu = function () {
        nav.addClass('open');
        $('.outer', nav).css({width: '100%'});
    };

    var timer;
    var closeMenu = function () {
        nav.removeClass('open');
        clearTimeout(timer);
        timer = setTimeout(function () {
            $('.outer', nav).css({width: '0'});
        }, 300);
    };

    $('.trigger', nav).on('click', function () {
        closeSearch();
        if(nav.hasClass('open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    /*header.children('.item').on('click', function(){
        closeMenu();
    });*/

    $('.outer', nav).touchwipe({
        wipeLeft: function() {
            closeMenu();
        }
    });

    $('.outer .shadow', nav).on('click', function(){
        closeMenu();
    });

    $(window).on('resize', function() {
        var outer = $('.outer', nav);
        var box = $('.box', outer);
        outer.css({
            height: $document.height() - $('#header').height()
        });

        $('.menu', nav).css('height', box.innerHeight() - box.children('.search').height() - 75 - 10);
    }());

    var menuH = 0;
    $('.nav ul', header).each(function () {
        var ul = $(this);
        if(ul.outerHeight() > menuH) {
            menuH = ul.outerHeight();
        }
    });

    $('a, span', menu).on('click', function (e) {
        var li = $(this).closest('li');
        e.stopPropagation();

        if(li.hasClass('back')) {
            li.parent().closest('li')
                .removeClass('open')
                .parent()
                .removeClass('open');
            return false;
        }

        if(li.children('ul').length) {
            li.addClass('open')
                .parent()
                .addClass('open');
            return false;
        }

        return true;
    });

    //Search
    var search = $('#search');

    var openSearch = function () {
        search.addClass('open');
    };

    var closeSearch = function () {
        search.removeClass('open');
    };

    $(header).children('.search').on('click', function () {
        closeMenu();
        if(search.hasClass('open')) {
            closeSearch();
        } else {
            openSearch();
        }
    });

    function stars(stars) {
        var html =
            '<div class="stars">';
        var starFilling;
        var starClass = '';

        for(var i = 0; i <= 4; i++) {
            starFilling = stars - i;

            if(starFilling >= 0.6) {
                starClass = ' class="full"';
            } else if (starFilling >= 0.1) {
                starClass = ' class="half"';
            } else {
                starClass = '';
            }

            html += '<div' + starClass + '></div> ';
        }

        html +=
            '</div>';

        return html;
    }

    $('.search-ac', '#search').on('keyup', function() {
        if($(this).val() === '') {
            $('.list', '#search').empty();
            return;
        }

        $.ajax({
            url: '/catalog/search/',
            data: {
                query: $(this).val()
            },
            success: function (resp) {
                render(resp);
            }
        });
    });

    var render = function (items) {
        var html = '';

        $.each(items, function (key, item) {
            switch (item.type) {
                case 'product':
                    html +=
                        '<a href="' + item.url + '" class="item ac-product">' +
                            '<div>' +
                                '<div class="pic"><img src="' + item.img + '"></div>' +
                                '<div class="info">' +
                                    '<div class="title">' + item.label + '</div>' +
                                    '<div class="desc">' + item.desc + '</div>' +
                                    '<div>' + stars(item.stars) + '</div>' +
                                    '<span class="price"><span>от</span> ' + $.aptero.price(item.price) + ' <i class="fa fa-rub"></i></span> ' +
                                '</div>' +
                            '</div>' +
                        '</a>';
                    break;
                case 'category':
                    html +=
                        '<a class="item ac-category" href="' + item.url + '">' + item.label + '</a>';
                    break;
            }
        });

        $('.list', '#search').html(html);
    }
}


function initElements(box) {
    $('.popup-gallery', box).lightGallery({
        thumbnail:true,
        animateThumb: true,
        download: false,
        closable: true
    });

    $('.select-group', box).each(function () {
        var group = $(this);
        var vals = $('span', group);
        var input = $('input', group);

        var setActive = function (val) {
            if(input.val() == val) { return; }

            input.val(val).trigger('change');
            $('span[data-value="' + val + '"]', group)
                .addClass('selected')
                .siblings()
                .removeClass('selected');
        };

        vals.on('click', function () {
            setActive($(this).data('value'));
        });

        var initVal = input.val() ? input.val() : vals.eq(0).data('value');
        input.val('');
        setActive(initVal);
    });

    $('.std-counter', box).each(function() {
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

function initPopups() {
    $('body').on('click', '.popup', function() {
        var el = $(this);

        $.fancybox.open({
            src: el.attr('href'),
            type: 'ajax',
            opts: {
                ajax: {
                    settings: {
                        data: el.data()
                    }
                },
                afterLoad: function(e) {
                    initElements(e.$refs.slider);
                }
            }
        });

        return false;
    });
}

function initAutocomplete() {
    var input = $('.search .query');
    var url   = '/catalog/search/';

    function stars(stars) {
        var html =
            '<div class="stars">';

        for($i = 0; $i <= 4; $i++) {
            $starFilling = stars - $i;

            if($starFilling >= 0.6) {
                $class = ' class="full"';
            } else if ($starFilling >= 0.1) {
                $class = ' class="half"';
            } else {
                $class = '';
            }

            html += '<div' + $class + '></div> ';
        }

        html +=
            '</div>';

        return html;
    }

    $.widget("custom.catcomplete", $.ui.autocomplete, {
        _create: function() {
            this._super();
            this.widget().menu("option", "items", ".ac-item");
            $('.add-to-cart').menu("option", "disabled", true);
        },
        _renderItem: function(ul, item) {
            var li = $('<li></li>');
            li.addClass('ac-item');

            switch(item.type) {
                case 'title':
                    //li.addClass('ac-title').removeClass('ac-item').text(item.label);
                    break;
                case 'hr':
                    li.addClass('ac-hr').removeClass('ac-item').text(item.label);
                    break;
                case 'clear':
                    li.addClass('ac-clear').removeClass('ac-item').text(item.label);
                    break;
                case 'show-all':
                    li.addClass('ac-show-all').removeClass('ac-item');
                    li.append(
                        '<span>Показать еще</span>'
                    );
                    li.on('click', function() {
                        location.href = url + '?query=' + input.val();
                    });
                    break;
                case 'category':
                    li.addClass('ac-category');
                    li.append('<a href="' + item.url + '">' + item.label + '</a>');
                    break;
                case 'brand':
                    li.addClass('ac-brand');
                    li.append('<a href="' + item.url + '">' + item.label + '</a>');
                    break;
                case 'product':
                    li.addClass('ac-product');
                    li.append(
                        '<div class="pr-box">' +
                        '<div>' +
                        '<a href="' + item.url + '" class="pic"><img src="' + item.img + '"></a>' +
                        '<div class="info">' +
                        '<a href="' + item.url + '" class="title">' + item.label + '</a>' +
                        '<div>' +
                        stars(item.stars) +
                        '<span class="reviews">' + item.reviews + '</span>' +
                        '</div>' +
                        '<span class="price"><span>от</span> ' + $.aptero.price(item.price) + ' <i class="fa fa-rub"></i></span> ' +
                            /*'<div class="order-box">' +
                             '<a href="/order/cart-form/?pid=' + item.id + '" class="btn s add-to-cart popup">В корзину</a>' +
                             '</div>' +*/
                        '</div>' +
                        '</div>' +
                        '</div>'
                    );
                    break;
                default:
                    li.append('<a href="#">' + item.label + '</a>')
            }

            if(item.hide) {
                li.addClass('hide').removeClass('ac-item');;
            }

            return li.appendTo(ul)
        }
    });

    var pos = {my: "left top", at: "left bottom"};

    input.catcomplete({
        position: pos,
        source: function(request, response) {
            $.ajax({
                url: url,
                type: "get",
                dataType: "json",
                data: {
                    query: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            if(ui.item.url) {
                location.href = ui.item.url;
            }
        },
        open: function(event, ui) {
            $('.order-box .js-to-cart', '.ac-product').on('click', function(e) {
                var el = $(this);

                cart.add({
                    id:    el.data('id'),
                    count: 1
                });

                e.stopPropagation();
                return false;
            });
        },
        lookup           : 'res',
        maxHeight        : 300,
        width            : 630,
        zIndex           : 9999,
        deferRequestBy   : 300,
        params           : {limit: 10},
    });
}

function initDatepicker() {
    $.config.datepicker = {
        clearText: 'Очистить',
        clearStatus: '',
        closeText: 'Закрыть',
        closeStatus: '',
        prevText: '',
        prevStatus: '',
        nextText: '',
        nextStatus: '',
        currentText: 'Сегодня',
        currentStatus: '',
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь', 'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн', 'Июл','Авг','Сен','Окт','Ноя','Дек'],
        monthStatus: '',
        yearStatus: '',
        weekHeader: 'Не',
        weekStatus: '',
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        dayStatus: 'DD',
        dateStatus: 'D, M d',
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        initStatus: '',
        isRTL: false,
        minDate: +1,
        maxDate: +30,
    };

    $('.datepicker').datepicker($.config.datepicker);
}

var mapsArr = [];

function setPickupMap(options) {
    var map = null;

    $.each(mapsArr, function (key, val) {
        if(val.id == options.id) {
            val.map.destroy();
            mapsArr.splice(key, 1);
        }
    });

    $.getScript(libs.libYandexMaps, function() {
        var data = options.pointsData ? options.pointsData : {};
        var url = options.url ? options.url : '/delivery/points-map-data/';

        $.ajax({
            url: url,
            method: 'post',
            data: data,
            success: function (resp) {
                options = $.extend(options, {
                    center: resp.center,
                    points: resp.points,
                });

                initMap(options);
            }
        });
    });

    var initMap = function (options) {
        if(!options.center.lat || !options.center.lon) {
            return;
        }

        ymaps.ready(function() {
            map = new ymaps.Map(options.id, {
                center: [options.center.lat, options.center.lon],
                controls: [],
                zoom: (options.zoom ? options.zoom : 11)
            });

            var clusterer = new ymaps.Clusterer({
                preset: 'twirl#invertedBlueClusterIcons',
                clusterDisableClickZoom: false,
            });

            var markers = [];
            options.points.forEach(function(point) {
                var marker = new ymaps.Placemark([point.lat, point.lon], {
                    balloonContent: point.desc
                }, {
                    preset: "islands#blackHomeIcon",
                });

                marker.events.add('balloonopen', function (e) {
                    initElements($('.marker-box'));
                });

                markers.push(marker);
                map.geoObjects.add(marker);
            });

            /*clusterer.add(markers);
            map.geoObjects.add(clusterer);*/

            if(options.zoom !== undefined && options.zoom == false) {
                map.behaviors.disable('scrollZoom');
            }

            map.controls.add('zoomControl', { top: 10, left: 5 });

            if(options.onInit) {
                options.onInit();
            }

            mapsArr.push({
                id: options.id,
                map: map
            });
        });
    };

    return map;
}