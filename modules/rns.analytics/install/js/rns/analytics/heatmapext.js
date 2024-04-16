let HeatMapExt = {
    apiUrl: null,
    userId: null,
    vid: null,
    oPopup: null,
    time: null,
    ShowMenu: "N",
    dynamicUrlSeparator:'dynamic_url',
    dynamicUrlValues:'',
    init: function () {
        let me = this;
        let url = window.location.href;
        let urlObj = new URL(url)
        let urlPathName = urlObj.pathname;
        let params = [];
        let dynamicUrlValues = this.dynamicUrlValues.split(';')
        //Dynamic url
        for (let i = 0; i < dynamicUrlValues.length; i++){
            let item = dynamicUrlValues[i].split(':');
            if(item[0] === urlPathName){
                params = item[1].split(',')
                let urlParam = '';
                for (let k = 0; k < params.length; k++){
                    let param = params[k].trim();;
                    let paramValue = urlObj.searchParams.get(param);
                    if(paramValue !== null){
                        urlParam += param +'='+ paramValue +'&';
                    }
                }
                urlParam = urlParam.slice(0, -1);
                if(urlParam.length > 0){
                    url = urlObj.origin+urlObj.pathname+this.dynamicUrlSeparator+btoa(urlParam)+this.dynamicUrlSeparator+urlObj.search
                }
            }
        }
        BX.ajax.post(
            me.apiUrl + '/addVisit',
            {
                'url': url,
                'user': this.userId,
                'title': document.title,
                'time': this.time,
                'vid': this.vid
            }
        );
        BX.bindDelegate(
            document.body,
            'click',
            {},
            function (e) {
                var element = e.target;
                var path = me.getDomPath(element);
                var url = element.baseURI;

                if (element.className === 'heatmap-canvas') {
                    return
                }

                BX.ajax.post(
                    me.apiUrl + '/addClick',
                    {
                        'url': url,
                        'path': path,
                        'user': me.userId,
                        'vid': me.vid,
                        'time':me.time,
                    }
                );
            }
        );

        BX.ajax.runComponentAction(
            "rns:mapanalitics",
            "getForm",
            {
                mode: "class"
            }
        ).then(function (response) {
            if (response.status === "success") {
                let div = document.createElement('div');
                div.innerHTML = response.data.html;
                let scripts = [];
                let scriptsInDiv = div.getElementsByTagName('script');

                for(let index = scriptsInDiv.length - 1; index >= 0; index--){
                    let script = scriptsInDiv[index];
                    if( script.type !== 'text/html' ){
                        let parent = script.parentElement;
                        let data = (script.text || script.textContent || script.innerHTML || "" );
                        let newScript = document.createElement('script');
                        newScript.appendChild(document.createTextNode(data));
                        newScript.type = 'text/javascript';
                        scripts.push(newScript);
                        parent.removeChild(script);
                    }
                }

                me.oPopup = BX.PopupWindowManager.create(
                    "heat-map__popup",
                    BX("element"),
                    {
                        //
                        // ширина окна
                        //
                        width: 500,
                        min_width: 500,
                        //
                        // высота окна
                        //
                        height: 427,
                        min_height: 427,
                        //
                        // z-index
                        //
                        zIndex: 100,
                        //
                        // иконка закрытия окна
                        //
                        closeIcon: {
                            opacity: 0.6,
                        },
                        closeByEsc: true,
                        darkMode: false,
                        autoHide: true,
                        draggable: true,
                        resizable: false,
                        lightShadow: true,
                        angle: false,
                        overlay: {
                            backgroundColor: "black",
                            opacity: 500,
                        },
                        //
                        // заголовок
                        //
                        titleBar: 'Выберите сотрудников и отчетный период',
                        //
                        // контент
                        //
                        content: BX.create({
                            tag: 'form',
                            props: {
                                id: 'heartMap__form',
                                action: ''
                            },
                            children: [
                                BX.create({
                                    tag: 'div',
                                    props:{
                                        id: 'HMError',
                                    }
                                }),
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        className: 'heartMap__form-label-container',
                                    },
                                    children: [
                                        BX.create({
                                            tag: 'label',
                                            props: {
                                                className: 'heartMap__form-label'
                                            },
                                            text: 'Сотрудники:*',
                                        }),
                                    ],
                                }),
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        id: 'HMUsersSelectContainer',
                                        className: 'heartMap_workers-content'
                                    },
                                    html: div.innerHTML,
                                }),
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        className: 'heartMap__form-label-container with-top-margin',
                                    },
                                    children: [
                                        BX.create({
                                            tag: 'label',
                                            props: {
                                                className: 'heartMap__form-label'
                                            },
                                            text: 'Отчетный период:',
                                        }),
                                    ],
                                }),
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        className: 'ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100'
                                    },
                                    children: [
                                        BX.create({
                                            tag: 'div',
                                            props: {
                                                className: 'ui-ctl-after ui-ctl-icon-angle'
                                            }
                                        }),
                                        BX.create({
                                            tag: 'select',
                                            props: {
                                                id: 'heartMap__select',
                                                className: 'ui-ctl-element',
                                                name: 'map_time'
                                            },
                                            children: [
                                                BX.create({
                                                    tag: 'option',
                                                    props: {
                                                        value: 'thisMonth'
                                                    },
                                                    text: 'Этот месяц'
                                                }),
                                                BX.create({
                                                    tag: 'option',
                                                    props: {
                                                        value: 'lastMonth'
                                                    },
                                                    text: 'Прошлый месяц'
                                                }),
                                                BX.create({
                                                    tag: 'option',
                                                    props: {
                                                        value: 'thisWeek'
                                                    },
                                                    text: 'Эта неделя'
                                                }),
                                                BX.create({
                                                    tag: 'option',
                                                    props: {
                                                        value: 'lastWeek'
                                                    },
                                                    text: 'Прошлая неделя'
                                                }),
                                                BX.create({
                                                    tag: 'option',
                                                    props: {
                                                        value: 'last'
                                                    },
                                                    text: 'За последние'
                                                }),
                                                BX.create({
                                                    tag: 'option',
                                                    props: {
                                                        value: 'before'
                                                    },
                                                    text: 'Позже'
                                                }),
                                                BX.create({
                                                    tag: 'option',
                                                    props: {
                                                        value: 'after'
                                                    },
                                                    text: 'Раньше'
                                                }),
                                                BX.create({
                                                    tag: 'option',
                                                    props: {
                                                        value: 'interval'
                                                    },
                                                    text: 'Интервал'
                                                }),
                                                BX.create({
                                                    tag: 'option',
                                                    props: {
                                                        value: 'allTime'
                                                    },
                                                    text: 'За все время'
                                                }),
                                            ],
                                        }),
                                    ]
                                }),
                                BX.create({
                                    tag: 'div',
                                    props: {
                                        id: 'heartMap__interval-wrapper',
                                        className: 'heartMap__form-row-container'
                                    },
                                    children: [
                                        BX.create({
                                            tag: 'div',
                                            props: {
                                                className: 'heartMap__form-block-50 left'
                                            },
                                            children: [
                                                BX.create({
                                                    tag: 'div',
                                                    props: {
                                                        className: 'heartMap__form-label-container with-top-margin',
                                                    },
                                                    children: [
                                                        BX.create({
                                                            tag: 'label',
                                                            props: {
                                                                className: 'heartMap__form-label'
                                                            },
                                                            text: 'От:*',
                                                        }),
                                                    ],
                                                }),
                                                BX.create({
                                                    tag: 'div',
                                                    props: {
                                                        className: 'ui-ctl ui-ctl-textbox',
                                                    },
                                                    children: [
                                                        BX.create({
                                                            tag: 'input',
                                                            props: {
                                                                id: 'HMFromDate',
                                                                name: 'fromDate',
                                                                className: 'ui-ctl-element',
                                                                value: '',
                                                            },
                                                            events: {
                                                                click: function() { BX.calendar({bHideTime: false, node: this, field: this, bTime: true}) },
                                                            },
                                                        }),
                                                    ],
                                                })
                                            ]
                                        }),

                                        BX.create({
                                            tag: 'div',
                                            props: {
                                                className: 'heartMap__form-block-50 center'
                                            },
                                            children: [
                                                BX.create({
                                                    tag: 'div',
                                                    props: {
                                                        className: 'heartMap__form-label-container with-top-margin',
                                                    },
                                                    children: [
                                                        BX.create({
                                                            tag: 'label',
                                                            props: {
                                                                className: 'heartMap__form-label'
                                                            },
                                                            text: 'Дней:*',
                                                        }),
                                                    ],
                                                }),
                                                BX.create({
                                                    tag: 'div',
                                                    props: {
                                                        className: 'ui-ctl ui-ctl-textbox',
                                                    },
                                                    children: [
                                                        BX.create({
                                                            tag: 'input',
                                                            props: {
                                                                id: 'HMLastDays',
                                                                name: 'LastDays',
                                                                className: 'ui-ctl-element',
                                                                value: '',
                                                            },
                                                        }),
                                                    ],
                                                })
                                            ]
                                        }),


                                        BX.create({
                                            tag: 'div',
                                            props: {
                                                className: 'heartMap__form-block-50 right'
                                            },
                                            children: [
                                                BX.create({
                                                    tag: 'div',
                                                    props: {
                                                        className: 'heartMap__form-label-container with-top-margin',
                                                    },
                                                    children: [
                                                        BX.create({
                                                            tag: 'label',
                                                            props: {
                                                                className: 'heartMap__form-label'
                                                            },
                                                            text: 'До:*',
                                                        }),
                                                    ],
                                                }),
                                                BX.create({
                                                    tag: 'div',
                                                    props: {
                                                        className: 'ui-ctl ui-ctl-textbox',
                                                    },
                                                    children: [
                                                        BX.create({
                                                            tag: 'input',
                                                            props: {
                                                                id: 'HMAfterDate',
                                                                name: 'toDate',
                                                                className: 'ui-ctl-element',
                                                                value: '',
                                                            },
                                                            events: {
                                                                click: function() { BX.calendar({bHideTime: false, node: this, field: this, bTime: true}) },
                                                            },
                                                        }),
                                                    ],
                                                })
                                            ]
                                        })
                                    ]
                                }),
                                BX.create({
                                    tag: 'input',
                                    props: {
                                        className: 'heartMap_submit',
                                        type: 'submit',
                                        value: 'Построить диаграмму'
                                    }
                                })
                            ]
                        }),
                        buttons: [
                            new BX.PopupWindowButton({
                                text: "Построить диаграмму",
                                className: "ui-btn ui-btn-primary",
                                events: {
                                    click: function () {
                                        $('.heartMap_submit').trigger('click');
                                    }
                                }
                            }),
                            new BX.PopupWindowButton({
                                text: "Отмена",
                                className: "ui-btn ui-btn-danger",
                                events: {
                                    click: function () {
                                        this.popupWindow.close();
                                    }
                                }
                            })
                        ],
                    }
                );

                setTimeout(() => {
                    BX.ajax.processScripts(response.data.assets.js, true);

                    for(let script of scripts){
                        document.body.appendChild(script);
                    }
                });

                me.initForm();
            }
        }, function (reject) {
            console.log(reject);
        });

        $('.user-block').one('click', function () {
            let menuItem = BX.create('span', {
                attrs: {
                    className: 'menu-popup-item menu-popup-no-icon'
                },
                events: {
                    click: function () {
                        me.oPopup.show();
                    }
                },
                children: [
                    BX.create('span', {
                        attrs: {
                            className: 'menu-popup-item-text'
                        },
                        text: 'Аналитика'
                    })
                ]
            });
            let menuItemNew = BX.create('div', {
                attrs: {
                    className: 'ui-qr-popupcomponentmaker__content--section'
                },
                events: {
                    click: function () {
                        me.oPopup.show();
                    }
                },
                children: [
                    BX.create('div', {
                        attrs: {
                            className: 'ui-qr-popupcomponentmaker__content--section-item',
                            style: 'background-color: rgb(250, 250, 250)'
                        },
                        children: [
                            BX.create('a', {
                                attrs: {
                                    className: 'system-auth-form__item system-auth-form__scope --center --padding-sm --clickable'
                                },
                                children: [
                                    BX.create('div', {
                                        attrs: {
                                            className: 'system-auth-form__item-container --center'
                                        },
                                        children: [
                                            BX.create('div', {
                                                attrs: {
                                                    className: 'system-auth-form__item-title --light'
                                                },
                                                text: 'Аналитика'
                                            })
                                        ]
                                    })
                                ]
                            })
                        ]
                    })
                ]
            });

            if (me.ShowMenu === "Y"){
                var AddItemMenuV1 = setInterval(function() {
                    if ($('.ui-qr-popupcomponentmaker__content').length) {
                        $('.ui-qr-popupcomponentmaker__content').append(menuItemNew);

                        clearInterval(AddItemMenuV1);
                    }
                }, 100)

                var AddItemMenuV2 = setInterval(function() {
                    if ($('.menu-popup-item.menu-popup-no-icon').length) {
                        $('.menu-popup-item.menu-popup-no-icon:nth-child(3)').after(menuItem);
                        clearInterval(AddItemMenuV2);
                    }
                }, 100)

                setTimeout(function (){
                    clearInterval(AddItemMenuV1);
                    clearInterval(AddItemMenuV2);
                }, 5000);
            }
        });

        window.addEventListener('unload', function (e) {
            let data = new FormData();
            data.append('url', window.location.href);
            data.append('user', me.userId);
            data.append('vid', me.vid);
            navigator.sendBeacon(me.apiUrl + '/closeVisit', data);
        });
    },
    createHeatMap: function (data) {
        let bodyHeight = document.body.scrollHeight + 'px';
        BX.prepend(BX.create('div', {
            attrs: {
                id: 'heatmapContainerWrapper'
            },
            style: {
                width: "100%",
                height: bodyHeight,
                position: "absolute",
                top: '0',
                left: '0',
                "z-index": 991
            },
            events: {
                click: function (e) {
                    BX.remove(BX('heatmapContainerWrapper'));
                }
            },
            children: [
                BX.create('div', {
                    attrs: {
                        id: 'heatmapContainer'
                    },
                    style: {
                        width: "100%",
                        height: "100%"
                    }
                })
            ]
        }), document.body);

        var heatmap = h337.create({
            container: BX('heatmapContainer'),
            maxOpacity: .7,
            minOpacity: .05,
            radius: 100,
            blur: .85,
            backgroundColor: 'rgba(50, 120, 255, 0.25)'
        });


        BX.ajax.runAction('rns:analytics.api.Heatmap.getClicks', {
            data: data
        }).then(function (response) {
            let result = response.data;
            if (result.success) {
                let data = [];
                result.data.forEach(function (click) {
                    var element ;
                    try {
                        element = document.querySelector(click.path);
                    } catch (e) {
                        element = false
                    }
                    if (element) {
                        var rect = element.getBoundingClientRect();
                        var x = Math.round((rect.left + rect.right) / 2 + window.scrollX);
                        var y = Math.round((rect.top + rect.bottom) / 2 + window.scrollY);
                        data.push({x: x, y: y, value: click.count});
                    }
                });
                heatmap.addData(data);
            }
        }, function (response) {
            alert('Ошибка');
        });
    },
    getDomPath: function (el) {
        var stack = [];
        while (el.parentNode != null) {
            var sibCount = 0;
            var sibIndex = 0;
            for (var i = 0; i < el.parentNode.childNodes.length; i++) {
                var sib = el.parentNode.childNodes[i];
                if (sib.nodeName === el.nodeName) {
                    if (sib === el) {
                        sibIndex = sibCount;
                    }
                    sibCount++;
                }
            }
            if (el.hasAttribute('id') && el.getAttribute('id') !== null && (el.getAttribute('id').search(/[0-9]/) === -1)) {
                stack.unshift(el.nodeName.toLowerCase() + '#' + el.getAttribute('id'));
            } else if (sibCount > 1) {
                stack.unshift(el.nodeName.toLowerCase() + ':nth-of-type(' + (sibIndex + 1) + ')');
            } else {
                stack.unshift(el.nodeName.toLowerCase());
            }
            el = el.parentNode;
        }
        stack = stack.slice(1) // removes the html element
        return stack.join(' > ');
    },
    initForm: function () {
        let self = this;
        let mapSelect = $('#heartMap__select'),
            mapIntWrap = $('#heartMap__interval-wrapper'),
            mapIntFrom = $('#HMFromDate'),
            mapIntAfter = $('#HMAfterDate'),
            mapUserContainer = $('#HMUsersSelectContainer'),
            blockHMError = $('#HMError'),
            mapLastDate = $('#HMLastDays');


        //Переменные и функции для временных интервалов
        let date = new Date,
            dd = date.getDate(),
            mm = date.getMonth() + 1,
            yy = date.getFullYear(),
            days = [7, 1, 2, 3, 4, 5, 6],
            numberOfDay = days[date.getDay()];

        if (dd < 10) dd = '0' + dd;
        if (mm < 10) mm = '0' + mm;
        mapIntFrom.val(formatDateFirst());
        mapIntAfter.val(formatDate());

        function formatDate() {
            return dd + '.' + mm + '.' + yy + ' 23:59';
        };

        function formatDateFirst() {
            return '01' + '.' + mm + '.' + yy + ' 00:00';
        };
        // Переключение интервалов
        mapSelect.on('click', function () {
            mapLastDate.val('');

            mapIntWrap.removeClass().addClass($(this).val());
            if (mapIntWrap.hasClass('thisMonth')) {
                mapIntFrom.val(formatDateFirst());
                mapIntAfter.val(formatDate());
            } else if (mapIntWrap.hasClass('lastMonth')) {
                mapIntFrom.val(formatDateLastL());
                mapIntAfter.val(formatDateLastF(yy, mm));
            } else if (mapIntWrap.hasClass('thisWeek')) {
                mapIntFrom.val(formatDateWeekF());
                mapIntAfter.val(formatDate());
            } else if (mapIntWrap.hasClass('lastWeek')) {
                mapIntFrom.val(formatDateLastWeekF());
                mapIntAfter.val(formatDateLastWeekL());
            } else if (mapIntWrap.hasClass('allTime')) {
                mapIntFrom.val('08.02.2021 00:00');
                mapIntAfter.val(formatDate());
            } else if (mapIntWrap.hasClass('before')) {
                mapIntFrom.val('');
                mapIntAfter.val(formatDate());
            } else if (mapIntWrap.hasClass('after')) {
                mapIntFrom.val('08.02.2021 00:00');
                mapIntAfter.val('');
            } else {
                mapIntFrom.val('');
                mapIntAfter.val('');
            }
            Ф
            function formatDateLastF(year, month) {
                // alert(month);
                if (month == 0) {
                    month = 12;
                    year = year - 1;
                }
                let date2 = new Date(year, month - 1, 0),
                    ddLF = date2.getDate(),
                    mmLF = mm - 1;
                if (ddLF < 10) ddLF = '0' + ddLF;
                if (mmLF < 10) mmLF = '0' + mmLF;
                return ddLF + '.' + mmLF + '.' + yy + ' 00:00';
            };

            function formatDateLastL() {
                let mmLL = mm - 1,
                    yyLL = yy;
                if (mmLL == 0) {
                    mmLL = 12;
                    yyLL = yy - 1;
                }
                if (mmLL < 10) mmLL = '0' + mmLL;
                if (yyLL < 10) yyLL = '0' + mmLF;
                return '01' + '.' + mmLL + '.' + yyLL + ' 00:00';
            };

            function formatDateWeekF() {
                let ddWF = dd - numberOfDay + 1;
                return ddWF + '.' + mm + '.' + yy + ' 00:00';
            };

            function formatDateLastWeekF() {
                let ddLWF = dd - numberOfDay - 6;
                return ddLWF + '.' + mm + '.' + yy + ' 00:00';
            };

            function formatDateLastWeekL() {
                let ddLWL = dd - numberOfDay;
                return ddLWL + '.' + mm + '.' + yy + ' 00:00';
            };
        });

        // Последние дни
        mapLastDate.on('input', function () {

            if (this.value.match(/[^0-9]/g)) {
                this.value = this.value.replace(/[^0-9]/g, '');
            }

            let inpValue = this.value;

            mapIntFrom.val('');
            mapIntAfter.val('');

            if(inpValue > 0) {

                let date = new Date,
                    dd = date.getDate(),
                    mm = date.getMonth() + 1,
                    yy = date.getFullYear(),
                    hh = date.getHours(),
                    min = date.getMinutes();

                if (dd < 10) dd = '0' + dd;
                if (mm < 10) mm = '0' + mm;
                if (hh < 10) hh = '0' + hh;
                if (min < 10) min = '0' + min;

                let DateFrom = dd + '.' + mm + '.' + yy + ' ' + hh + ':' + min + ':00';

                date.setDate(date.getDate() - inpValue);
                dd = date.getDate();
                mm = date.getMonth()+1;
                yy = date.getFullYear();
                hh = date.getHours();
                min = date.getMinutes();

                if (dd < 10) dd = '0' + dd;
                if (mm < 10) mm = '0' + mm;
                if (hh < 10) hh = '0' + hh;
                if (min < 10) min = '0' + min;

                let DateTo = dd + '.' + mm + '.' + yy + ' ' + hh + ':' + min + ':00';

                mapIntFrom.val(DateTo);
                mapIntAfter.val(DateFrom);
            }
        })

        // Сабмит
        $("#heartMap__form").submit(function (event) {
            event.preventDefault();
            let error = false;
            let result = {};
            let errorClassName = "heartMap_error";
            let UIErrorClassName = "ui-ctl-danger";

            mapUserContainer.children().removeClass(errorClassName);
            mapLastDate.parent().removeClass(UIErrorClassName);
            mapIntFrom.parent().removeClass(UIErrorClassName);
            mapIntAfter.parent().removeClass(UIErrorClassName);
            $(blockHMError).empty();

            $(this).serializeArray().map(function (v) {
                result[v.name] = v.value;
            });

            let fromDate = result['fromDate'],
                toDate = result['toDate'],
                periodType = result['map_time'],
                LastDays = result['LastDays'],
                users = Object.values(result);

            if (periodType === "last" && LastDays < 1){
                error = true;
                mapLastDate.parent().addClass(UIErrorClassName)
            }

            if (fromDate === ''){
                error = true;
                mapIntFrom.parent().addClass(UIErrorClassName)
            }

            if (toDate === ''){
                error = true;
                mapIntAfter.parent().addClass(UIErrorClassName)
            }

            users.splice(-5);
            if (users.length === 0){
                error = true;
                mapUserContainer.children().addClass(errorClassName)
            }

            if(error){
                let error_msg = "<div class='ui-alert ui-alert-danger ui-alert-icon-warning alert-for-fields'><span class='ui-alert-message'>"+BX.message('RNS_ANVIEW_FORM_ERROR_MSG')+"</span></div>"
                $(blockHMError).append(error_msg);
                return false
            }

            HeatMapExt.createHeatMap({
                'url': window.location.href,
                'users': users,
                'toDate': toDate,
                'fromDate': fromDate
            });

            self.oPopup.close();
        });
    }
}
