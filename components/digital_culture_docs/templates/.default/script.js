
$(document).ready(function(){
    $('.filterListDoc__tab').on('click', function(){
        let tabId = $(this).attr('data-id');
        $('#activeTab').val(tabId);
    });

    var lists = $('.filterListDoc__list');
    lists.slice(1).hide();
    $('.filterListDoc__tab').each(function( index ) {
        let current = $(this).attr('data-cur');
        let dataId = $(this).attr('data-id');

        if (current === "Y"){
            var activeList = $("[data-doc=\"".concat(dataId, "\"]"));
            lists.hide();
            $(activeList).show(500);
        }
   });
});
