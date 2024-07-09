<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// настройки компонента, формируем массив $arParams
$arComponentParameters = [
    // основной массив с параметрами
    "PARAMETERS" => [
        // псевдоимена для комплексного компонента
        "VARIABLE_ALIASES" => [
            // элемент
            "ELEMENT_ID" => [
                "NAME" => 'Символьный код элемента',
            ],
            "BIRTHDAYS_URL" => [
                "NAME" => 'Каталог (относительно корня сайта)',
            ]
        ],
        // настройки режима ЧПУ
        "SEF_MODE" => [
            // настройки для элемента
            "list" => [
                "NAME" => 'Страница списка',
                "DEFAULT" => "/",
            ],
            // настройки для элемента
            "edit" => [
                "NAME" => 'Детальная страница',
                "DEFAULT" => "#ELEMENT_ID#/",
                "VARIABLES" => [
                    "ELEMENT_ID",
                ]
            ]
        ],
    ]
];
