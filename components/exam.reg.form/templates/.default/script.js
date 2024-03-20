BX.ready(function () {
    //клик Регион
    $( "select[name=regionExam]").change(function() {
        let selectedRegion = $(this).val();

        $.ajax({
            url: '/local/ajax/nica/regExam.php',
            data:
                {
                    clickRegion: selectedRegion
                },
            method: 'POST',
            dataType: 'HTML',
            success: function(result){
                if(result) {
                    $('select[name=placeExam]').html(result);
                }
            }
        })
    })
    //клик Место проведения, Вариант экзамена
    $("select[name=placeExam], select[name=typeExam]").change(function() {
        const selected = $(this).attr("name");
        let select2;

        if(selected == "placeExam"){
            select2 = $("select[name=typeExam]").val();
        }else {
            select2 = $("select[name=placeExam]").val();
        }

        if(select2){
            $.ajax({
                url: '/local/ajax/nica/regExam.php',
                data:
                    {
                        clickPlace: $("select[name=placeExam]").val(),
                        clickType: $("select[name=typeExam]").val(),
                    },
                method: 'POST',
                dataType: 'HTML',
                success: function(result){
                    if(result) {
                        $('select[id=\'examForm-date\']').html(result);
                        $('select[id=\'examForm-date\']').prop('disabled', false);

                        $('select[id=\'examForm-time\']').html("<option></option>");
                        $('select[id=\'examForm-time\']').prop('disabled', false);
                    }
                    else {
                        $('select[id=\'examForm-date\']').html("").prop('disabled', true);
                        $('select[id=\'examForm-time\']').html("").prop('disabled', true);
                    }
                }
            })
        }
    })
    //клик Дата экзамена
    $("select[id=\'examForm-date\']").change(function() {
        const dateExam = $(this).find('option:selected').text();
        const placeExam = $('select[name=placeExam]').val();
        const typeExam = $('select[name=typeExam]').val();

        if(dateExam){
            $.ajax({
                url: '/local/ajax/nica/regExam.php',
                data:
                    {
                        clickDate: dateExam,
                        placeExam: placeExam,
                        typeExam: typeExam,
                    },
                method: 'POST',
                dataType: 'HTML',
                success: function(result){
                    if(result) {
                        $('select[id=\'examForm-time\']').html(result);
                    }
                }
            })
        }
    })
    //клик Регистрация
    $('button[name=exam_register_button]').on('click', function(e){
        e.preventDefault();
        const scheduleId = $("select[id=\'examForm-time\'] option:selected").attr('data-schedule');
        const timeExam = $("select[id=\'examForm-time\'] option:selected").attr('data-xmlid');

        $.ajax({
            url: '/local/ajax/nica/regExam.php',
            data:
                {
                    formData: $('#regExam-form').serializeArray(),
                    scheduleId: scheduleId,
                    timeExam: timeExam,
                },
            method: 'POST',
            dataType: 'HTML',
            success: function(result){
                if(result == "ок") {
                    $('#regExam-res').css("color", "green");
                    $('#regExam-res').html("Вы успешно зарегистрированы на экзамен!");
                    window.location.href = '/personal';
                }
                else {
                    $('#regExam-res').html(result);
                }
            }
        })
    })

    /*
        BX.ajax.runComponentAction("rns:exam.reg.form", "addForm", {
            mode: "class",
            data: {
                "regData": $('#reg-form').serializeArray()
            }
        }).then(function (response) {
            // обработка ответа
        });*/
})