$(function() {
    productsList();
    productView($('.product-view'));
    cartView();
    cartRender();
    userModel();
    orderForm();
});

function orderForm() {
    $('.order-popup').on('click', function () {
        $.fancybox.open({
            src: '/order/',
            type: 'ajax',
            opts : {
                closeClickOutside : false,
                closeBtn: false,
                smallBtn : false,
                closeTpl: '',
                afterLoad: function(e) {
                    initElements(box);
                }
            }
        });
        return false;
    });
}

$(function() {
    /*$('.pr-link').on('click', function() {
        var link = $(this);
        var prEl = link.hasClass('product') ? link : link.closest('.product');

        $.ajax({
            url: '/catalog/get-product-info/',
            data: {
                product_id: prEl.data('id')
            },
            success: function (prInfo) {
                ga('ec:addProduct', {
                    id:         prInfo.id,
                    name:       prInfo.name,
                    category:   prInfo.catalog,
                    brand:      prInfo.brand,
                    variant:    prInfo.variant
                });

                var clickOpts = {};

                if(prEl.data('list')) { clickOpts.list = prEl.data('list') }

                ga('ec:setAction', 'click', clickOpts);

                ga('send', 'event', 'pr-link', 'click', 'EC', {
                    hitCallback: function() {
                        location.href = link.attr('href');
                    }
                });
            },
            dataType: 'json',
            method: 'post'
        });

        return false;
    });*/
});

function productsList() {
    var container = $('.container');
    if(!container.length) { return; }

   /* $('.products-popup').on('click', function () {

    });

    $('.products-popup').fancybox({
        type: 'ajax',
        padding: 0,
        margin: [20, 10 , 20, 10],
    });*/

    var sidebar = $('.sidebar', container);
    var widgets = $('.widget.catalog', sidebar);

    sidebar.css({height: container.innerHeight()});

    var sidebarTop = sidebar.offset().top - $('#nav').innerHeight();
    var sidebarBot = sidebarTop + sidebar.innerHeight() - widgets.innerHeight();
    var widgetsH = widgets.innerHeight();

    $(window).on('scroll', function () {
        var scrollTop = $(this).scrollTop();

        if(scrollTop > sidebarBot) {
            widgets.css({top: 'auto', bottom: 0});
        } else if(scrollTop < sidebarTop) {
            widgets.css({top: 0, bottom: 'auto'});
        } else {
            widgets.css({top: scrollTop - sidebarTop, bottom: 'auto'});
        }
    });
}

function productView(box) {
    $('.product-tabs .tabs').tabs({
        historyMode: true,
        disablePushState: false
    });

    var viewedProducts = new Products().init();
    viewedProducts.add({
        id: $('[name="product_id"]', $('.type-box', product)).val()
    });

    var product = $('.product', box);
    var images  = $('.images', product);
    var pic     = $('.pic', images);
    var thumbs  = $('.thumb', images);

    thumbs.on('click', function () {
        var el = $(this);
        var img = $('img', pic);

        el.addClass('active').siblings().removeClass('active');
        pic
            .attr('href', el.attr('href'))
            .attr('data-size', el.data('size'))
            .attr('data-taste', el.data('taste'))
            .removeClass('hide');

        img.attr('src', el.data('m'))
            .data('hr', el.attr('href'));

        return false;
    });

    toCartForm(product, {
        priceUpdate: function (resp) {
            let counter = $('.counter .std-counter', product).inputCounter();

            if(resp.stock) {
                counter.setMax(resp.stock).setMin(1);
            } else {
                counter.setMax(0).setMin(0);
            }

            $('.price-per-unit span', product).text($.aptero.price(resp.price));
            $('.price-full span', product).text($.aptero.price(resp.price * counter.getCount()));
            $('.price-old span', product).text($.aptero.price(resp.price_old * counter.getCount()));

            if (parseInt(resp.stock)) {
                $('.stock-i', product).css({display: 'block'});
                $('.stock-o', product).css({display: 'none'});
            } else {
                $('.stock-i', product).css({display: 'none'});
                $('.stock-o', product).css({display: 'block'});
            }
        },
        toCart: function () {
            $('.to-cart', product).addClass('in-cart').removeClass('to-cart').text('В корзине');
        },
        typeChange: function (size_id, taste_id) {
            $('.in-cart', product).addClass('to-cart').removeClass('in-cart').text('В корзину');

            thumbs.each(function () {
                var thumb = $(this);
                var th_size_id = thumb.data('size');
                var th_taste_id = thumb.data('taste');

                if((th_size_id && th_size_id != size_id) || (th_taste_id && th_taste_id != taste_id)) {
                    thumb.addClass('hide').attr('rel', null);
                } else {
                    thumb.removeClass('hide').attr('rel', 'product-gl');
                }
            });

            if(!$('.thumb.active').length || $('.thumb.active.hide', images).length) {
                $('.thumb:not(.hide)', images).eq(0).trigger('click');
            }

            if($('.thumb:not(.hide)', images).length > 1) {
                $('.thumbs', images).removeClass('hide');
            } else {
                $('.thumbs', images).addClass('hide');
            }
        }
    });
}

function toCartForm(box, options) {
    if(!box.length) { return; }

    var typeBox = $('.type-box', box);
    var sizeSelect  = $('[name="size_id"]', typeBox);
    var tasteSelect = $('[name="taste_id"]', typeBox);
    var countSelect = $('[name="count"]', typeBox);

    function updatePrice() {
        var data = $.aptero.serializeArray(typeBox);

        $.ajax({
            url: '/catalog/get-price/',
            method: 'post',
            data: data,
            success: options.priceUpdate
        });
    }

    var tasteOptions = $('option', tasteSelect);
    if(tasteOptions.length == 1 && tasteOptions.eq(0).text() == '') {
        tasteSelect.closest('.row').css({display: 'none'});
    }

    sizeSelect.on('change', function() {
        options.typeChange();

        $.ajax({
            url: '/catalog/get-product-stock/',
            method: 'post',
            data: $.aptero.serializeArray(typeBox),
            success: function(resp) {
                $('option', tasteSelect).each(function() {
                    var el = $(this);
                    var id = el.attr('value');

                    if(parseInt(resp[sizeSelect.val()].taste[id])) {
                        el.addClass('green').removeClass('red');
                    } else {
                        el.addClass('red').removeClass('green');
                    }
                });

                if($('option[value="' + tasteSelect.val() + '"]', tasteSelect).hasClass('red') && $('option.green', tasteSelect).length) {
                    tasteSelect.val($('option.green', tasteSelect).attr('value')).trigger('change');
                } else {
                    updatePrice();
                }
            }
        });
    });

    tasteSelect.on('change', function() {
        options.typeChange(sizeSelect.val(), tasteSelect.val());
        updatePrice();
    });

    countSelect.on('change', function() {
        updatePrice();
    });

    tasteSelect.trigger('change');

    $.ajax({
        url: '/catalog/get-product-stock/',
        method: 'post',
        data: $.aptero.serializeArray(typeBox),
        success: function (resp) {
            $('option', sizeSelect).each(function () {
                var el = $(this);
                var id = el.attr('value');

                if (parseInt(resp[id].stock)) {
                    el.addClass('green').removeClass('red');
                } else {
                    el.addClass('red').removeClass('green');
                }
            });

            if($('option[value="' + sizeSelect.val() + '"]', sizeSelect).hasClass('red')) {
                if($('option.green', sizeSelect).length) {
                    sizeSelect.val($('option.green', sizeSelect).attr('value'));
                }
            }

            sizeSelect.trigger('change');
        }
    });

    $('.js-product-request', box).on('click', function() {
        $.fancybox.open({
            src: '/order/product-request/',
            type: 'ajax',
            opts: {
                ajax: {
                    settings: {
                        data: $.aptero.serializeArray(typeBox)
                    }
                }
            }
        });
    });

    $('.js-cart-add', box).on('click', function() {
        var btn = $(this);

        if(btn.hasClass('to-cart')) {
            $.cart.add($.aptero.serializeArray(typeBox));
            options.toCart();
            return false;
        }
    });
}

function cartView() {
    var box = $('.cart-list');
    if (!box.length) { return; }

    $('.js-cart-count', box).each(function () {
        let el = $(this);

        el.on('change', function () {
            let counter = el.inputCounter();
            let product = el.closest('.product');

            if(counter.getCount() > 0) {
                $.cart.add({
                    product_id: product.data('product_id'),
                    size_id: product.data('size_id'),
                    taste_id: product.data('taste_id'),
                    count: counter.getCount()
                }, {count: 'replace'});
            }
        });
    });

    box.on('click', '.js-cart-del', function() {
        var product = $(this).closest('.product');
        product.fadeOut(200);
        $.cart.del({
            product_id: product.data('product_id'),
            size_id: product.data('size_id'),
            taste_id: product.data('taste_id')
        });
    });

    $.cart.on('update', function () {
        $.ajax({
            url: '/delivery/delivery-notice/',
            method: 'post',
            success: function (resp) {
                $('.delivery-notice', box).replaceWith(resp.html);
            }
        });
    });
}

function cartRender() {
    $.cart.on('render', function () {
        for (var i in $.cart.cart) {
            var product = $.cart.cart[i];

            var productEl = $('.product' +
                '[data-product_id="' + product.product_id + '"]' +
                '[data-size_id="' + product.size_id + '"]' +
                '[data-taste_id="' + product.taste_id + '"]', '.cart-list');

            $('.sum span', productEl).text($.aptero.price(product.price * product.count));
        }

        var navCart = $('.item.cart', '#nav');
        var orderForm = $('.order-form');

        if ($.cart.count) {
            $('.desc', navCart).html($.aptero.price($.cart.sum) + ' <i class="fa fa-rub"></i>');
            $('.counter', navCart).text($.cart.count).fadeIn(200);
            $('.cart-price').text($.aptero.price($.cart.sum));

            var index = $('[name="attrs-index"]', orderForm);
            index.on('keyup', function () {
                if(index.val().length == 6) {
                    calcPostPrice(index);
                }
            });

            if (orderForm.length) {
                var deliveryPrice = 0;

                switch ($('[name="attrs-delivery"]', orderForm).val()) {
                    case 'courier':
                        deliveryPrice = $.cart.delivery.courier;
                        $('.cart-delivery', orderForm).html(deliveryPrice ? $.aptero.price(deliveryPrice) + ' <i class="fa fa-rub"></i>' : 'бесплатно');
                        break;
                    case 'pickup':
                        deliveryPrice = $.cart.delivery.pickup;
                        $('.cart-delivery', orderForm).html(deliveryPrice ? $.aptero.price(deliveryPrice) + ' <i class="fa fa-rub"></i>' : 'бесплатно');
                        break;
                    case 'post':
                        calcPostPrice(index);
                        break;
                    default:
                        $('.cart-delivery', orderForm).html('не выбрана');
                        break;
                }

                $('.cart-sum-price', orderForm).text($.aptero.price($.cart.sum + parseInt(deliveryPrice)));

                $('.cart-error').css({display: 'none'});
                $('.order-btn').css({display: 'block'});
            }
        } else {
            $('.desc', navCart).text('Пока пуста');
            $('.counter', navCart).fadeOut(200);
            $('.cart-list').html('<div class="empty">Ваша корзина пуста</div>');
        }
    });
}

function calcPostPrice(index) {
    $.ajax({
        url: '/delivery/rupost-calc/',
        data: { index: index.val() },
        method: 'post',
        success: function (resp) {
            $('.cart-delivery').html(resp.price ? $.aptero.price(resp.price) + ' <i class="fa fa-rub"></i>' : 'Укажите индекс');
        }
    });
}

function userModel() {
    $('.profile-tabs').tabs();
}