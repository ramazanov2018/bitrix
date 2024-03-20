BX.ready(function () {
    function AppDescBlocsHide()
    {
        $('.popup-linksApp__item__desc').addClass('block-hidden')
    }

    function AppInfoIconsDisable()
    {
        $('.popup-linksApp-item__info').removeClass('infoEnable');
        $('.popup-linksApp-item__info').addClass('infoDisable')
    }

    function TabContentsHide()
    {
        $('.popup-linksApp__tabContent').addClass('block-hidden')
    }

    function TabsDisactive()
    {
        $('.AppBtn-default[data-tab_id]').addClass('AppBtn-default__disact')
    }

    //Описание приложения
    $('.popup-linksApp-item__info').on('click', function () {
        AppInfoIconsDisable();
        AppDescBlocsHide();
        $(this).removeClass('infoDisable');
        $(this).addClass('infoEnable');

        let app_id = this.dataset.app_id;
        let el = '.popup-linksApp__item__desc[data-app_id="'+app_id+'"]';
        $(el).removeClass('block-hidden')

    })

    //переключение по табам
    $('.AppBtn-default[data-tab_id]').on('click', function () {
        let tab_id = this.dataset.tab_id;
        TabContentsHide();
        TabsDisactive();
        AppInfoIconsDisable();
        AppDescBlocsHide();
        $(this).removeClass('AppBtn-default__disact');
        $(this).addClass('AppBtn-default__act');
        let el = '.popup-linksApp__tabContent[data-tab_id="'+tab_id+'"]';
        $(el).removeClass('block-hidden')

    })
    //Закрытие окошки доп описания приложений
    $('.linksApp__item__desc-close-icon').add('.linksApp__item__desc-close-btn').on('click', function () {
        AppDescBlocsHide();
        AppInfoIconsDisable();
    })

    //Скролл табов вправо
    $('#btn-next_tab').on('click', function () {
        console.log(3444);
        let element = document.querySelector('#tabsElement');
        slide(element,200,200)
    })

    //Скролл табов влево
    $('#btn-preview_tab').on('click', function () {
        console.log(3444);
        let element = document.querySelector('#tabsElement');
        slide(element,-200,200)
    })

    //анимация при скролле табов
    function slide (slider,step,period) {
        console.log(step);
        const startTime = Date.now()
        const startLeft = slider.scrollLeft
        const render = () => {
            const dt = Date.now() - startTime
            if(dt < period){
                slider.scrollLeft = startLeft + step * dt / period
                requestAnimationFrame(render)
            }
        }
        requestAnimationFrame(render)
    }

    //Событие на открытие приложения
    $('.js_app_link[data-link_id]').mousedown(function () {
        if( event.which === 3) {
            $(this).bind("contextmenu", function(e) {
                e.preventDefault();
            });
        }

        if( event.which === 1 || event.which === 2) {
            let self = this;
            BX.ajax.runComponentAction("rns:applications.list", "executing", {
                mode: "class",
                data: {linkId:self.dataset.link_id}
            }).then(function (response) {
                console.log(response);
            });
        }
    });

    $('.popup-linksApp-item__favorite').on('click', function () {
        let id = this.dataset.app_id;
        BX.ajax.runComponentAction("rns:applications.list", "favorite", {
            mode: "class",
            data: {appId: id}
        }).then(function (response) {
            console.log(response);
            if (response.status === "success" && response.data.result) {
                if (response.data.result === 'add') {
                    $('.popup-linksApp-item__favorite[data-app_id=' + id + ']').addClass('active');
                } else if (response.data.result === 'delete') {
                    $('.popup-linksApp-item__favorite[data-app_id=' + id + ']').removeClass('active');
                }
            }
        });
    });
})
