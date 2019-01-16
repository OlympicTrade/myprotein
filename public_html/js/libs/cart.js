var Cart = function(){
    this.cart = [];
    this.price = 0;
    this.count = 0;
    this.sum   = 0;
    this.cookie = 'cart';

    this.init = function() {
        this.on('update', function() {
            this.save();
            this.sync();
        });

        var jsonCart = $.cookie(this.cookie);
        this.cart = jsonCart ? $.parseJSON(jsonCart) : [];
        this.trigger('update');
    };

    this.clear = function() {
        this.cart = [];
        this.trigger('update');
    };

    this.compare = function(data1, data2) {
        return data1.product_id == data2.product_id &&
               data1.taste_id   == data2.taste_id &&
               data1.size_id    == data2.size_id;
    };

    this.getProductInfo = function(data, callback) {
        $.ajax({
            url: '/catalog/get-product-info/',
            data: data,
            success: callback,
            dataType: 'json',
            method: 'post'
        });
    };

    this.add = function(data, options) {
        options = $.extend({
            count   : 'increase'
        }, options);

        var exists = false;
        var newCount = parseInt(data.count);
        var diff = newCount;

        for (var i = 0; i < this.cart.length; i++) {
            if(!this.compare(this.cart[i], data)) {
                continue;
            }

            var cartCount = parseInt(this.cart[i].count);
            cartCount = isNaN(cartCount) || cartCount === undefined ? 1 : cartCount;

            if(options.count == 'increase') {
                data.count = newCount + cartCount;
            } else {
                diff = newCount - cartCount;
            }

            this.cart.splice(i, 1, data);
            exists = true;
        }

        if(!exists) {
            this.cart.push(data);
        }
        
    	if(diff > 0) {
            this.getProductInfo(data, function (resp) {
                gtag('event', 'add_to_cart', {'items': [
                        $.extend(resp, {quantity: diff})
                    ]});
                gtag('event', 'order_event', {'event_category': 'catalog', 'event_action': 'order',
                    'event_label': 'cart_add', 'value': 10});

                window.metrikaEc.push({ecommerce: {
                        add: { products: resp }
                    }});
                getYandexCounter().reachGoal('cart_add');
            });
        } else {
            this.getProductInfo(data, function (resp) {
                gtag('event', 'remove_from_cart', {'items': [
                        $.extend(resp, {quantity: diff * -1})
                    ]});

                gtag('event', 'order_event', {'event_category': 'catalog', 'event_action': 'order',
                    'event_label': 'cart_del', 'value': 10});

                window.metrikaEc.push({ecommerce: {
                        add: { products: resp }
                    }});
                getYandexCounter().reachGoal('cart_del');
            });
        }

        this.trigger('update');
    };

    this.del = function(data) {
        for (var i in this.cart) {
            if(this.compare(this.cart[i], data)) {
            	var quantity = this.cart[i].count;
                this.cart.splice(i, 1);
                this.getProductInfo(data, function (resp) {
	                gtag('event', 'remove_from_cart', {'items': [
	                	$.extend(resp, {quantity: quantity})
	                ]});
                    
	                gtag('event', 'order_event', {'event_category': 'catalog', 'event_action': 'order',
                        'event_label': 'cart_del', 'value': 10});

                    window.metrikaEc.push({ecommerce: {
                        add: { products: resp }
                    }});
                    getYandexCounter().reachGoal('cart_del');
                });
                break;
            }
        }

        this.trigger('update');
    };

    this.ecoomerceCart = function(callback) {
        this.getProductInfo({products: this.cart}, function (resp) {
            callback(resp);
        });
    };

    /*this.order = function(order) {
        this.getProductInfo({products: this.cart}, function (resp) {
            window.metrikaEc.push({purchase: {
                actionField: {
                    id:      order.id,
                    revenue: order.price,
                },
                products: {
                    products: resp
                }
            }});
        });
    };*/

    this.getCart = function() {
        return this.cart;
    };

    this.save = function() {
        var cartJson = null;

        if(this.cart.length) {
            cartJson = JSON.stringify(this.cart);
        }

        $.cookie(this.cookie, cartJson, {expires: 365, path: "/"});
    };

    this.sync = function() {
        var cart = this;

        $.ajax({
            url: '/cart/get-info/',
            success: function(serverCart){
                cart.cart = [];
                for (var i in serverCart.cart) {
                    var product = serverCart.cart[i];
                    cart.cart.push(product)
                }

                cart.price = serverCart.price;
                cart.count = parseInt(serverCart.count);
                cart.sum   = serverCart.price;
                cart.delivery   = serverCart.delivery;
                cart.save();
                cart.trigger('render');
            },
            dataType: 'json'
        });
    };

    this.on = function(event, fn) {
        $(this).on(event, fn);
    };

    this.trigger = function(event) {
        $(this).trigger(event);
    };
};

$.cart = new Cart();
$.cart.init();