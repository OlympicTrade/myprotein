$(function() {
    initWindow();
    initTooltip();
    initPopups();
    iniDatepicker($('body'));
    
    $('.tabs-class').tabs();

    var sidebar = $('#sidebar');

    var h1 = $('h1 .actions');

    h1.find('.filter').unbind('click').bind('click', function(){
        $('.table-filter').stop().slideToggle(200);
    });

    $('.table-filter').find('.close').bind('click', function(){
        $(this).closest('.table-filter').slideUp(200);
    });

    $('label').each(function(){
        if($(this).children('input[type="checkbox"]').length) {
            initCheckbox($(this));
        }
    });

    initRadio($('form'));

    $('.menu .open .submenu', '#sidebar').css('display', 'block');
    $('.menu li > a', '#sidebar').click(function(){
        var li = $(this).parent();
        var submenu = li.children('.submenu');

        if(submenu.length) {
            li.toggleClass('open');
            submenu.slideToggle(200);
            return false;
        }
    });
});

function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).text()).select();
    document.execCommand("copy");
    $temp.remove();
}

function initPopups() {
    if($.fn.fancybox) {
        $(".popup-form").fancybox({
            type: 'ajax',
            padding: 0
        });

        $(".popup-image").fancybox({
            padding: 0
        });
    }
}

function initRadio(form) {
    form.find('input[type="radio"]').each(function(){
        var label = $(this).parent();
        var input = $(this);

        label.addClass('radio');

        if(input.is(':checked')) {
            label.addClass('checked');
        }

        label.click(function(){
            var inputs = form.find('input[name="' + input.attr("name") + '"]').removeClass('checked');
            inputs.parent().removeClass('checked');
            inputs.removeAttr('checked');

            label.addClass('checked');
            input.attr('checked', true);
            input.prop('checked', true);
        });
    });
}

function initCheckbox(el) {
    el.addClass('checkbox');
    var input = el.children('input');

    if(input.is(':checked')) {
        el.addClass('checked');
    }

    el.click(function(){
        if(input.is(':checked')) {
            el.addClass('checked');
        } else {
            el.removeClass('checked');
        }
    });
}

function initWindow() {
    //do something
}

function initTooltip() {
    $(document).tooltip({track: true});
    $(document).tooltip({
        items: '.tooltip-icon',
        track: true,
        content: function() {
            return $(this).parent().find('.tooltip-desc').html();
        }
    });
}

function iniDatepicker(box) {
    if(!$.datepicker) {
        return;
    }

    var dOptions = {
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
        dateFormat: 'yy-mm-dd',
        firstDay: 1,
        initStatus: '',
        isRTL: false
    };

    $('.datepicker', box).each(function () {
        var el = $(this);
        var options = dOptions;

        if(el.data('date')) {
            options.dateFormat = el.data('date');
        }

        $('.datepicker', box).datepicker(options);
    });
}


