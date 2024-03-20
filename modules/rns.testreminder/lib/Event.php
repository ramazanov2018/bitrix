<?php

namespace Rns\TestReminder;
use \Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;

class Event
{

    public static function OnRemind()
    {
        $TEST_REMAINDER_USE = Option::get("rns.testreminder", 'TEST_REMAINDER_USE', '/');

        global $USER;
        //Проверка на авторизацию и админ страниц
        if ($TEST_REMAINDER_USE != "Y" || !$USER->IsAuthorized() || defined('ADMIN_SECTION') && ADMIN_SECTION ) {
            return true;
        }

        //проверка на состояние пользователя  в группе напоминании
        $ReminderUserGroups = (array)unserialize(Option::get("rns.testreminder", 'TEST_REMAINDER_GROUPS', ""));
        $userGroups = $USER->GetUserGroupArray();
        $isGroup = false;
        foreach ($ReminderUserGroups as $group)
            if (in_array($group, $userGroups))
                $isGroup = true;

        if (!$isGroup)
            return true;

        //проверка на сушествование напоминании
        $dt = new DateTime();
        $resOb = TestRemindTable::getList( [
                'filter' => [
                    ">UF_DATE_REMIND" => $dt->format("d.m.Y 00:00:00"),
                    "<UF_DATE_REMIND" => $dt->format("d.m.Y 23:59:59"),
                    "UF_USER_ID" => $USER->GetID()
                ]
            ]
        )->fetch();

        if($resOb)
            return true;

        $TEST_REMAINDER_TEST_URL = Option::get("rns.testreminder", 'TEST_REMAINDER_TEST_URL', '/');
        $TEST_REMAINDER_NOTIFY = Option::get("rns.testreminder", 'TEST_REMAINDER_NOTIFY', '/');
        $TEST_REMAINDER_NOTIFY = nl2br($TEST_REMAINDER_NOTIFY);
        $TEST_REMAINDER_NOTIFY = str_replace("\r" , "", $TEST_REMAINDER_NOTIFY);
        $TEST_REMAINDER_NOTIFY = str_replace("\n" , "", $TEST_REMAINDER_NOTIFY);
        $TEST_REMAINDER_PERIOD_FROM = Option::get("rns.testreminder", 'TEST_REMAINDER_PERIOD_FROM', '09:00');
        $TEST_REMAINDER_PERIOD_TO = Option::get("rns.testreminder", 'TEST_REMAINDER_PERIOD_TO', '00:00');

        $remindStartTame = new DateTime($dt->format("d.m.Y $TEST_REMAINDER_PERIOD_FROM:00"));
        $remindEndTame = new DateTime($dt->format("d.m.Y $TEST_REMAINDER_PERIOD_TO:59"));

        if($dt->getTimestamp() < $remindStartTame->getTimestamp() || $dt->getTimestamp() > $remindEndTame->getTimestamp())
            return true;

        \Bitrix\Main\UI\Extension::load("ui.buttons");

        $r = "<script>BX.ready(function () {
                var testUrl = '$TEST_REMAINDER_TEST_URL';
                var text = '$TEST_REMAINDER_NOTIFY';
                var popup = BX.PopupWindowManager.create('popup-message', BX('element'), {
                    content: text,
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
                                    TestingUrlRedirect();
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
                        onPopupClose: function() {
                            CloseEvent();
                        }
                    }
                });
                popup.show();
            
                function ClosePopup(){
                    popup.close();
                }
                
                function TestingUrlRedirect() {
                    window.location.href = testUrl;
                    ClosePopup();
                }
            
                function CloseEvent()
                {
                    BX.ajax.runAction('rns:testreminder.api.remindcontroller.reminded', {
                        getParameters: {}
                    }).then((response) => {
                        
                    });
                }
            });</script>";

        echo $r;

    }

    public static function RemindTableClearAgent()
    {
        $dt = new DateTime();
        $dt->add("-7 day");

        $resOb = TestRemindTable::getList( [
                'filter' => [
                    "<UF_DATE_REMIND" => $dt,
                ]
            ]
        );
        while ($res = $resOb->fetch())
            TestRemindTable::delete($res["ID"]);

        return __CLASS__."::RemindTableClearAgent();";
    }
}