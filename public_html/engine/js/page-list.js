$(function() {
    var module = $('.module', '.site-settings').val();
    var section = $('.section', '.site-settings').val();
    
    var filter = $('#list-filter');

    $('select', filter).on('change', function () {
        filter.submit();
    });
});

function dataTableAction(tableId, module, section){
    var table = $('.table-list[data-id="' + tableId + '"]');


    var removeForm = $('.popup-delete', table);
    removeForm.fancybox({
        closeBtn: false,
        minHeight: 30
    });

    $('.yes', removeForm).click(function(){
        $.fancybox.close();
        var id = $(this).attr('data-id');

        $.ajax({
            url: '/admin/' + module + '/' + section + '/delete/',
            data: {
                id: id
            },
            success: function(resp) {
                $('.rowset .tb-row[data-id="' + id + '"]', table).fadeOut(200);
            },
            dataType: 'json',
            type: 'post'
        });

        return false;
    });

    $('.no', removeForm).click(function(){
        $.fancybox.close();
    });

    $('.tbl-btn-remove', table).click(function(){
        $('.yes', removeForm).attr('data-id', $(this).attr('data-id'));
        removeForm.eq(0).trigger("click");
    });

    $('.tbl-btn-remove', table).click(function(){
        $('#popup-delete .btn-remove').attr('data-id', $(this).attr('data-id'));
    });

    $('.btn-submit').on('click', function() {
        $(this).closest('form').submit();
        return false;
    });

    $('input, select', '.list-form').on('change', function() {
        $(this).closest('form').submit();
    });
}