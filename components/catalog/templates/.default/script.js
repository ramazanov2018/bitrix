$( document ).ready(function() {
    $( "#form_reset" ).on( "click", function(event) {
        event.preventDefault();
        window.location.href = $(this).data('href');
    } );

    $(document).on('click', '.show__more__catalog', function() {
        var pageContent = $('.catalogPage__content');
        var targetContainer = $('.catalogPage__cards'),
            url = $('.show__more__catalog').attr('data-url');

        if (url !== undefined) {
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'html',
                success: function (data) {
                    $('.show__more__catalog').remove();
                    var elements = $(data).find('.catalogCard'),
                        pagination = $(data).find('.show__more__catalog');
                    $(elements).each(function () {
                        $(this).hide();
                    });
                    targetContainer.append(elements);
                    pageContent.append(pagination);
                    var hiddenElements = $('.catalogPage__cards').find('.catalogCard');
                    hiddenElements.show(700, 'linear');

                }
            });
        }
    })

    $(document).on('click', '.catalogSort', function() {
        window.location.href = $(this).data('href');
    })

    $(document).on('click', '.filter-field-clear', function() {
        var FieldLowerId = $(this).data('clear-lower');
        var FieldId = '#' + $(this).data('clear');
        console.log(FieldLowerId);
        if (FieldLowerId === "range-rating-lower"){
            $("#range-rating .noUi-handle-lower").attr("aria-valuenow", 0)
            $("#range-rating .noUi-handle-upper").attr("aria-valuenow", 100)
        }

        if (FieldLowerId === "range-percent-lower"){
            $("#range-percent .noUi-handle-lower").attr("aria-valuenow", 0)
            $("#range-percent .noUi-handle-upper").attr("aria-valuenow", 100)
        }

        $(FieldId).val('');
        $('#setFilter').click();
    })

    $( "#setFilter" ).on( "click", function(event) {
        let ratingLower = $("#range-rating .noUi-handle-lower").attr("aria-valuenow");
        let ratingUpper = $("#range-rating .noUi-handle-upper").attr("aria-valuenow");
        let percentLower = $("#range-percent .noUi-handle-lower").attr("aria-valuenow");
        let percentUpper = $("#range-percent .noUi-handle-upper").attr("aria-valuenow");

        $('#range-rating-lower').val(ratingLower);
        $('#range-rating-upper').val(ratingUpper);
        $('#range-percent-lower').val(percentLower);
        $('#range-percent-upper').val(percentUpper);
    } );


});