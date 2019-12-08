$(function() {
    initList();
    initSearch();
});

function initList() {
    $('.std-list').each(function() {
        var list = $(this);

        list.on('click', '.item', function() {
            var item = $(this);

            popup(getUrl(list.data('module'), list.data('section'),'edit', {id: item.data('id')}))
            /*$.fancybox.open({
                src: getUrl(list.data('module'), list.data('section'),'edit', {id: item.data('id')}),
                type: 'ajax',
                opts: {
                    afterLoad: function(e, slide) {
                        initElements(e.$refs.slider);
                    }
                }
            });*/
        });
    });
}

function popup(url) {
    $.fancybox.open({
        src: url,
        type: 'ajax',
        opts: {
            afterLoad: function(e, slide) {
                initElements(e.$refs.slider);
            }
        }
    });
}

function initElements() {

}

function getUrl(module, section, action, params) {
    var url = new Url();

    url.setPath('/admin/mobile/' + module + '/' + section + '/' + action + '/');
    url.setParams(params);

    return url.getUrl();
}


function initSearch() {
    var search = $('#search');
    var input = $('input[name="search"]', search);

    $('select', search).on('change', function () {
        search.submit();
    });

    $.widget("custom.catcomplete", $.ui.autocomplete, {
        _create: function() {
            this._super();
            this.widget().menu("option", "items", ".ac-item");
            $('.add-to-cart').menu("option", "disabled", true);
        },
        _renderItem: function(ul, item) {
            var li = $('<li></li>');
            li.addClass('ac-item');
            li.append('<a href="#">' + item.label + '</a>')

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
                url: search.attr('action'),
                type: "get",
                dataType: "json",
                data: search.serializeArray(),
                success: function(data) {
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            if(ui.item.url) {
                popup(ui.item.url);
                //location.href = ui.item.url;
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