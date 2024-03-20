
BX.ready(function () {
    var popup = BX.PopupWindowManager.create('popup-message', BX('element'), {
        content: 'Напоминаем Вам о прохождении «Ежедневного тестирования». Пожалуйста,'+
            'перейдите по ссылке (войдите в систему дистанционного обучения, если у '+
            'Вас не сохранились данные при первом входе) и нажмите кнопку «Начать».'+
            'После указанных действий ответьте на представленный вопрос.'+
            'Спасибо за участие в тестировании!»',
        width: 600, // ширина окна
        height: 300, // высота окна
        padding: 10,
        zIndex: 10000000000, // z-index
        closeIcon: {
            // объект со стилями для иконки закрытия, при null - иконки не будет
            opacity: 1
        },
        titleBar: 'Уважаемые коллеги!',
        closeByEsc: true, // закрытие окна по esc
        darkMode: false, // окно будет светлым или темным
        autoHide: false, // закрытие при клике вне окна
        draggable: true, // можно двигать или нет
        resizable: true, // можно ресайзить
        min_height: 100, // минимальная высота окна
        min_width: 100, // минимальная ширина окна
        lightShadow: true, // использовать светлую тень у окна
        angle: false, // появится уголок
        overlay: {
            // объект со стилями фона
            backgroundColor: 'black',
            opacity: 800
        },
        buttons: [
            new BX.PopupWindowButton({
                text: 'Пройти тестирование',
                id: 'save-btn',
                className: 'ui-btn ui-btn-success',
                events: {
                    click: function() {
                        ClosePopup();
                    }
                }
            }),
            new BX.PopupWindowButton({
                text: 'Закрыт', // текст кнопки
                id: 'cancel-btn', // идентификатор
                className: 'ui-btn ui-btn-light-border', // доп. классы
                events: {
                    click: function() {
                        ClosePopup();
                    }
                }
            }),
        ],

        events: {
            onPopupShow: function() {
                // Событие при показе окна
            },
            onPopupClose: function() {
                CloseEvent();
            }
        }
    });
    popup.show();

    function ClosePopup(){
        popup.close();
    }

    function CloseEvent()
    {
        /*BX.ajax.runComponentAction("rns:reminder", "reminded", {
            mode: "class",
            data: {
            }
        }).then(function (response) {
            console.log(response);
        });*/

        BX.ajax.runAction('rns:testreminder.api.remindcontroller.reminded', {
            getParameters: {
            }
        }).then((response) => {
            console.log(response)
        });
    }
});