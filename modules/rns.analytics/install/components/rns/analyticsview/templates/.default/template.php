<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;

CJSCore::Init(["jquery"]);
Extension::load("ui.buttons");
Extension::load("ui.forms");
CJSCore::Init(['amcharts']);
CJSCore::Init(['amcharts_serial']);
global $APPLICATION;
?>
<div class="stats">
    <form class="stats__form" action="">
        <div id="statsHMError"></div>
        <div class="heartMap__form-label-container"><label class="heartMap__form-label"><?=Loc::getMessage("RNS_ANVIEW_employee")?></label></div>
        <?php $APPLICATION->IncludeComponent(
            'rns:analytic.widget.member.selector',
            '',
            [
                'DISPLAY' => 'inline',
                'MAX' => 99999,
                'MIN' => 1,
                'TYPES' => ['USER', 'USER.EXTRANET', 'USER.MAIL'],
                'ATTRIBUTE_PASS' => ['ID'],
                'TEMPLATE_CONTROLLER_ID' => 'stats__form'
            ],
            false,
            ["HIDE_ICONS" => "Y", "ACTIVE_COMPONENT" => "Y"],
            true
        );
        ?>
        <div class="heartMap__form-label-container margin-top"><label class="heartMap__form-label"><?=Loc::getMessage("RNS_ANVIEW_period")?></label></div>

        <div class="stats__wrapper">
            <div id="stats__time-wrapper">
                <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
                    <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                    <select id="stats__select" class="ui-ctl-element" name="map_time">
                        <option value="thisMonth"><?=Loc::getMessage("RNS_ANVIEW_thisMonth")?></option>
                        <option value="lastMonth"><?=Loc::getMessage("RNS_ANVIEW_lastMonth")?></option>
                        <option value="thisWeek"><?=Loc::getMessage("RNS_ANVIEW_thisWeek")?></option>
                        <option value="lastWeek"><?=Loc::getMessage("RNS_ANVIEW_lastWeek")?></option>
                        <option value="last"><?=Loc::getMessage("RNS_ANVIEW_last")?></option>
                        <option value="before"><?=Loc::getMessage("RNS_ANVIEW_before")?></option>
                        <option value="after"><?=Loc::getMessage("RNS_ANVIEW_after")?></option>
                        <option value="interval"><?=Loc::getMessage("RNS_ANVIEW_interval")?></option>
                        <option value="allTime"><?=Loc::getMessage("RNS_ANVIEW_allTime")?></option>
                    </select>
                </div>
                <div id="stats__interval-wrapper">
                    <div class="stats__form-block-50 left">
                        <div class="stats__form-label-container with-top-margin">
                            <label class="stats__form-label"><?=Loc::getMessage("RNS_ANVIEW_from")?></label>
                        </div>
                        <div class="ui-ctl ui-ctl-textbox">
                            <input id="statsHMFromDate"  onclick="BX.calendar({node: this, field: this, bTime: true, bHideTime: false});" name="fromDate" class="ui-ctl-element">
                        </div>
                    </div>
                    <div class="stats__form-block-50 right">
                        <div class="stats__form-label-container with-top-margin">
                            <label class="stats__form-label"><?=Loc::getMessage("RNS_ANVIEW_to")?></label>
                        </div>
                        <div class="ui-ctl ui-ctl-textbox">
                            <input id="statsHMAfterDate"  onclick="BX.calendar({node: this, field: this, bTime: true, bHideTime: true});" name="toDate" class="ui-ctl-element">
                        </div>
                    </div>
                    <div class="stats__form-block-50 center">
                        <div class="stats__form-label-container with-top-margin">
                            <label class="stats__form-label"><?=Loc::getMessage("RNS_ANVIEW_days")?></label>
                        </div>
                        <div class="ui-ctl ui-ctl-textbox">
                            <input id="statsHMLastDays"  name="LastDays" class="ui-ctl-element">
                        </div>
                    </div>
                </div>
            </div>
            <div class="stats__btn-wrapper active">
                <button class="ui-btn ui-btn-primary" type="submit"><?=Loc::getMessage("RNS_ANVIEW_BTN_SAVE")?></button>
                <button class="ui-btn ui-btn-danger stats__btn-reset" type="reset"><?=Loc::getMessage("RNS_ANVIEW_BTN_CANCEL")?></button>
            </div>
        </div>
    </form>
    <div id="stats-grafs">
        <div class="stats__table-tabs">
            <p class="stats__table-tabs_item active" data-stats="number"><?=Loc::getMessage("RNS_ANVIEW_DATA_NUMBER")?></p>
            <p class="stats__table-tabs_item" data-stats="time"><?=Loc::getMessage("RNS_ANVIEW_DATA_TIME")?></p>
        </div>
        <div class="statistic-table active" id="ww" data-stats="number">
            <div id="chartdiv" class="chart-wrapper"></div>
        </div>
        <div class="statistic-table" data-stats="time">
            <div id="chartdivTime" class="chart-wrapper"></div>
        </div>
    </div>
</div>


<script>
    BX.message({
        RNS_ANVIEW_FORM_ERROR_MSG: '<?=Loc::getMessage("RNS_ANVIEW_FORM_ERROR_MSG")?>',
    })
    BX.ready(function(){
        let mapSelect = $('#stats__select'),
            mapIntWrap = $('#stats__interval-wrapper'),
            mapIntFrom = $('#statsHMFromDate'),
            mapIntAfter = $('#statsHMAfterDate'),
            mapLastDate = $('#statsHMLastDays');
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
            }else if (mapIntWrap.hasClass('before')) {
                mapIntFrom.val('');
                mapIntAfter.val(formatDate());
            }else if (mapIntWrap.hasClass('after')) {
                mapIntFrom.val('08.02.2021 00:00');
                mapIntAfter.val('');
            } else {
                mapIntFrom.val('');
                mapIntAfter.val('');
            }

            function formatDateLastF(year, month) {
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
    });
</script>
<script>
    // Сабмит
    BX.ready(function(){
        function splitN(str) {
            if (str.length > 150){
                let nev = str.match(/.{1,150}/g);
                return nev.join('\n');
            }
            return str
        }

        let mapIntFrom = $('#statsHMFromDate'),
            mapIntAfter = $('#statsHMAfterDate'),
            mapUserContainer = $('#bx-component-scope-stats__form'),
            blockHMError = $('#statsHMError'),
            mapLastDate = $('#statsHMLastDays');
        let form = $(".stats__form");
        form.on("reset", function() {
            setTimeout(function() {
                $('#stats__select').trigger('click');
                BX.Tasks.Component.TasksWidgetMemberSelector.getInstance('stats__form').replaceAll({});
            }, 10);
        });
        let is_add_switch = false;
        form.submit(function (event) {
            event.preventDefault();

            let error = false;
            let errorClassName = "statsHMerror";
            let UIErrorClassName = "ui-ctl-danger";
            let result = {};
            mapUserContainer.removeClass(errorClassName);
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
                mapUserContainer.addClass(errorClassName)
            }

            if(error){
                let error_msg = "<div class='ui-alert ui-alert-danger ui-alert-icon-warning alert-for-fields'><span class='ui-alert-message'>"+BX.message('RNS_ANVIEW_FORM_ERROR_MSG')+"</span></div>"
                $(blockHMError).append(error_msg);
                return false
            }

            BX.ajax.runAction('rns:analytics.api.Statictics.getVisits', {
                data: {
                    'url': window.location.href,
                    'users': users,
                    'toDate': toDate,
                    'fromDate': fromDate
                }
            }).then(function (response) {
                let result = response.data;
                if (result.success) {
                    let chartsData = result.data,
                        chartH = chartsData.length * 40 + 60,
                        statTabs = $('.stats__table-tabs_item');
                    $('#stats-grafs').css( "display", "block" );
                    $('.chart-wrapper').css( "height", chartH );
                    if (!is_add_switch){
                        statTabs.on('click', function(event) {
                            $('.statistic-table').removeClass('active');
                            $('.statistic-table[data-stats='+$(this).attr('data-stats')+']')
                                .toggleClass('active', 500);
                            statTabs.removeClass('active');
                            $(this).toggleClass('active', 500);
                        });
                        is_add_switch = true;
                    }
                    for(let i = 0; i < chartsData.length; i++){
                        chartsData[i].title = splitN(chartsData[i].title);
                    }
                    AmCharts.makeChart("chartdiv", {
                        "type": "serial",
                        "theme": "none",
                        "categoryField": "title",
                        "rotate": true,
                        "startDuration": 1,
                        "categoryAxis": {
                            "gridPosition": "start",
                            "position": "left"
                        },
                        "graphs": [{
                            "labelText": "[[count]]",
                            "fillAlphas": 1,
                            "id": "AmGraph-1",
                            "lineColor": "#12b1e3",
                            "type": "column",
                            "valueField": "count"
                        }],
                        "valueAxes": [],
                        "dataProvider": chartsData.sort(function (a, b) {
                            return b.count - a.count
                        })
                    });
                    AmCharts.makeChart("chartdivTime", {
                        "type": "serial",
                        "theme": "none",
                        "categoryField": "title",
                        "rotate": true,
                        "startDuration": 1,
                        "categoryAxis": {
                            "gridPosition": "start",
                            "position": "left"
                        },
                        "graphs": [{
                            "balloonText": "[[time]]",
                            "labelText": "[[time]]",
                            "fillAlphas": 1,
                            "id": "AmGraph-2",
                            "lineColor": "#12b1e3",
                            "type": "column",
                            "valueField": "active"
                        }],
                        "valueAxes": [],
                        "dataProvider": chartsData.sort(function (a, b) {
                            return b.active - a.active
                        })
                    });

                }
            }, function (response) {
                alert('Ошибка');
            });
        });
    });
</script>
<style>
    #stats-grafs {
        display: none;
    }
    .statsHMerror {
        border-color:#ff5752
    }
    .statistic-table {
        display: none;
    }
    .statistic-table.active {
        display: table;
    }
    .stats__table-tabs {
        display: flex;
    }
    .stats__table-tabs p {
        padding: 0 5px;
        position: relative;
        margin-right: 5px;
        color: #a3a9b1;
        cursor: pointer;
    }
    .stats__table-tabs p.active:after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -2px;
        right: 0;
        height: 2px;
        background: #a3a9b1;
    }
    .stats__btn-wrapper {
        margin-left: 15px;
        height: 0;
        overflow: hidden;
    }
    .stats__btn-wrapper.active {
        height: 39px;
    }
    .stats__btn-wrapper button {
        opacity: 0;
        transition: .2s;
    }
    .stats__btn-wrapper.active button {
        opacity: 1;
    }
    .stats__wrapper {
        display: flex;
    }
    .margin-top {
        margin-top: 20px;
    }
    .heartMap__form-label {
        color: #a3a9b1;
    }
    .heartMap__form-label-container {
        display: block;
        margin-bottom: 5px;
    }
    #stats__time-wrapper {
        display: flex;
    }
    #stats__time-wrapper .ui-ctl-dropdown {
        width: 166px;
    }
    .last .center, .before .left, .after .right, .interval .left,.interval .right, .allTime .left,.allTime .right {
        display: flex;
    }
    .stats__form-block-50
    .stats__form-label {
        color: #a3a9b1;
        margin: 12px 10px 0 0;
        display: block;
    }
    #stats__interval-wrapper {
        display: flex;
    }
    .stats__form-block-50 {
        display: none;
        margin-left: 30px;
        width: 165px;
    }
    .statistic-table {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
    }

    .statistic-table tbody th, .statistic-table tbody td {
        padding: 5px;
        border: 1px solid #666;
    }
    .statistic-table thead th, .statistic-table thead td {
        padding: 10px;
    }
</style>