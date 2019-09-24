$(function() {
    productView($('.product-view'));
    cartView();
    cartRender();
    userModel();
    orderForm();
});

function orderForm() {
    $('.order-popup').on('click', function () {
        $.fancybox.open({
            width: "100%",
            margin: [0, 0, 0, 100],
            src: '/order/',
            type: 'ajax',
            opts : {
                margin: [0, 0],
                closeClickOutside : false,
                closeBtn: false,
                smallBtn : false,
                closeTpl: '',
                afterLoad: function(e) {
                    initElements(e.$refs.slider);
                }
            }
        });
        return false;
    });
}

$(function() {
    /*$('.pr-link').on('click', function() {
        let link = $(this);
        let prEl = link.hasClass('product') ? link : link.closest('.product');

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

                let clickOpts = {};

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

function productView(box) {
    $('.product-tabs').tabs({
        historyMode: true
    });

    let viewedProducts = new Products().init();
    viewedProducts.add({
        id: $('[name="product_id"]', $('.type-box', box)).val()
    });

    let product = box;
    let pic = $('.images .pic', product);
    let img = $('img', pic);
    let thumbs = $('.thumb', product);

    thumbs.on('click', function () {
        let el = $(this);
        let img = $('img', pic);

        el.addClass('active').siblings().removeClass('active');
        pic.data('size', el.data('size'))
            .data('taste', el.data('taste'))
            .removeClass('hide');

        img.attr('src', el.attr('href'))
            .data('zoom-image', el.data('zoom'));

        return false;
    });

    toCartForm(product, {
        priceUpdate: function (resp) {
            let counter = $('.std-counter', product).inputCounter();

            if(resp.stock) {
                counter.setMax(resp.stock).setMin(1);
            } else {
                counter.setMax(0).setMin(0);
            }

            $('.price-per-unit span', product).text($.aptero.price(resp.price));
            $('.price-full span', product).text($.aptero.price(resp.price * counter.getCount()));
            $('.price-old span', product).text($.aptero.price(resp.price_old * counter.getCount()));

            if (parseInt(resp.stock)) {
                $('.in-stock', product).css({display: 'block'});
                $('.not-in-stock', product).css({display: 'none'});
            } else {
                $('.in-stock', product).css({display: 'none'});
                $('.not-in-stock', product).css({display: 'block'});
            }
        },
        toCart: function () {
            $('.to-cart', product).addClass('in-cart').removeClass('to-cart').text('В корзине');
        },
        typeChange: function (size_id, taste_id) {
            $('.in-cart', product).addClass('to-cart').removeClass('in-cart').text('В корзину');

            thumbs.each(function () {
                let thumb = $(this);
                let th_size_id = thumb.data('size');
                let th_taste_id = thumb.data('taste');

                if((th_size_id && th_size_id != size_id) || (th_taste_id && th_taste_id != taste_id)) {
                    thumb.addClass('hide');
                } else {
                    thumb.removeClass('hide');
                }
            });

            if((pic.data('taste') && pic.data('taste') != taste_id) || (pic.data('size') && pic.data('size') != size_id)) {
                pic.addClass('hide');
            }

            if(pic.hasClass('hide')) {
                $('.thumb:not(.hide)', product).eq(0).trigger('click');
            }
        }
    });
}

function toCartForm(box, options) {
    if(!box.length) { return; }

    let typeBox = $('.type-box', box);
    let sizeSelect  = $('.js-size-select', typeBox);
    let tasteSelect = $('.js-taste-select', typeBox);
    let countSelect = $('.js-count', typeBox);

    function updatePrice() {
        let data = $.aptero.serializeArray(typeBox);

        $.ajax({
            url: '/catalog/get-price/',
            method: 'post',
            data: data,
            success: options.priceUpdate
        });
    }

    sizeSelect.on('change', function() {
        $.ajax({
            url: '/catalog/get-product-stock/',
            method: 'post',
            data: $.aptero.serializeArray(typeBox),
            success: function(resp) {
                $('option', tasteSelect).each(function() {
                    let el = $(this);
                    let id = el.attr('value');

                    if(parseInt(resp[sizeSelect.val()].taste[id])) {
                        el.addClass('green').removeClass('red');
                    } else {
                        el.addClass('red').removeClass('green');
                    }
                });

                if($('option[value="' + tasteSelect.val() + '"]', tasteSelect).hasClass('red') && $('option.green', tasteSelect).length) {
                    tasteSelect.val($('option.green', tasteSelect).attr('value')).trigger('change');
                } else {
					options.typeChange(sizeSelect.val(), tasteSelect.val());
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

    $.ajax({
        url: '/catalog/get-product-stock/',
        method: 'post',
        data: $.aptero.serializeArray(typeBox),
        success: function (resp) {
            $('option', sizeSelect).each(function () {
                let el = $(this);
                let id = el.attr('value');

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
                margin: [0, 0],
                ajax: {
                    settings: {
                        data: $.aptero.serializeArray(typeBox)
                    }
                }
            }
        });
    });

    $('.js-cart-add', box).on('click', function() {
        let btn = $(this);

        if(btn.hasClass('to-cart')) {
            $.cart.add($.aptero.serializeArray(typeBox));
            options.toCart();
            return false;
        }
    });
}

function cartView() {
    let box = $('.cart-list');
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
        let product = $(this).closest('.product');
        product.fadeOut(200);
        $.cart.del(product.data());
    });
}

function cartRender() {
    $.cart.on('render', function () {
        for (let i in $.cart.cart) {
            let product = $.cart.cart[i];

            let productEl = $('.product' +
                '[data-product_id="' + product.product_id + '"]' +
                '[data-size_id="' + product.size_id + '"]' +
                '[data-taste_id="' + product.taste_id + '"]', '.cart-list');

            $('.sum span', productEl).text($.aptero.price(product.price * product.count));
        }

        let navCart = $('.item.cart', '#header');
        let orderForm = $('.order-form');

        if ($.cart.count) {
            $('.desc', navCart).html($.aptero.price($.cart.sum) + ' <i class="fa fa-rub"></i>');
            $('.counter', navCart).text($.cart.count).fadeIn(200);
            $('.cart-price').text($.aptero.price($.cart.sum));

            if (orderForm.length) {
                let deliveryPrice = 0;

                switch ($('[name="attrs-delivery"]', orderForm).val()) {
                    case 'courier':
                        deliveryPrice = $.cart.delivery.courier;
                        $('.cart-delivery').html(deliveryPrice ? $.aptero.price(deliveryPrice) + ' <i class="fa fa-rub"></i>' : 'бесплатно');
                        break;
                    case 'pickup':
                        deliveryPrice = $.cart.delivery.pickup;
                        $('.cart-delivery').html(deliveryPrice ? $.aptero.price(deliveryPrice) + ' <i class="fa fa-rub"></i>' : 'бесплатно');
                        break;
                    default:
                        $('.cart-delivery').html('не выбрана');
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

function userModel() {
    $('.profile-tabs').tabs();
}