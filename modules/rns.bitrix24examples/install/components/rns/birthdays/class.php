<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// проверяем установку модуля «Информационные блоки»
if (!CModule::IncludeModule('iblock')) {
    return;
}

// класс для 404 ошибки
use Bitrix\Iblock\Component\Tools;
// класс для загрузки необходимых файлов, классов, модулей
use Bitrix\Main\Loader;

// подключаем приложение для обращения к глобальным сущностям ядра
use \Bitrix\Main\Application;

class BirthDays extends CBitrixComponent
{
    // список переменных, которых также нужно получить из GET параметров, но их нет в url-маске. К примеру мы указали маску server.com/test/#SECTION_CODE#/ и в $arComponentVariables указали "SECTION", при парсинге server.com/test/section-code/?SECTION=123 в $arVariables будет SECTION_CODE и SECTION, несмотря на отсутствие SECTION в маске
    protected array $arComponentVariables = [
        'CODE_SECTION'
    ];

    // выполняет основной код компонента, аналог конструктора (метод подключается автоматически)
    public function executeComponent()
    {
        // если выбран режим поддержки ЧПУ, вызываем метод sefMode()
        if ($this->arParams["SEF_MODE"] == "Y") {
            $componentPage = $this->sefMode();
        }

        // если отключен режим поддержки ЧПУ, вызываем метод noSefMode()
        if ($this->arParams["SEF_MODE"] != "Y") {
            $componentPage = $this->noSefMode();
        }

        // отдаем 404 статус если не найден шаблон
        if (!$componentPage) {
            Tools::process404(
                $this->arParams["MESSAGE_404"],
                ($this->arParams["SET_STATUS_404"] === "Y"),
                ($this->arParams["SET_STATUS_404"] === "Y"),
                ($this->arParams["SHOW_404"] === "Y"),
                $this->arParams["FILE_404"]
            );
        }

        // подключается файл php из папки комплексного компонента по имени файла, если $componentPage=section, значит подключится section.php расположенный по пути templates/.default
        $this->IncludeComponentTemplate($componentPage);
    }

    // метод обработки режима ЧПУ
    protected function sefMode()
    {
        // массив предназначен для обработки HTTP GET запросов из адреса страницы, используется в режиме ЧПУ. Предназначен для задания псевдонимов из массива arParams["SEF_URL_TEMPLATES"] в вызове комплексного компонента, если нужно прокинуть GET параметр server.com/test/?ELEMENT_COUNT=1 дальше в простой компонент, в массив $arDefaultVariableAliases404 нужно добавить псевдоним, указав ключ section/element для передачи в нужный файл section.php/element.php: 'section' => array('ELEMENT_COUNT' => 'ELEMENT_COUNT'). Если нужно, чтобы в адресной строке браузера передача параметра выглядела не так: server.com/test/?ELEMENT_COUNT=1 а вот так: server.com/test/?COUNT=1. Для этого задаем псевдоним 'ELEMENT_COUNT' => 'COUNT'
        $arDefaultVariableAliases404 = [];

        // значение маски для подключения шаблона по умолчанию, section.php, element.php, index.php
        $arDefaultUrlTemplates404 = [
            "list" => "/",
            "detail" => "detail/#ELEMENT_ID#/",
        ];

        // массив будут заполнен переменными, которые будут найдены по маске шаблонов url
        $arVariables = [];

        // объект для поиска шаблонов
        $engine = new CComponentEngine($this);

        // объединение дефолтных параметров масок шаблонов и алиасов которые приходят в arParams["SEF_URL_TEMPLATES"] и из массива $arDefaultUrlTemplates404, для определения какой шаблон section.php, element.php, index.php подключать. Параметры из настроек arrParams заменяют дефолтные
        $arUrlTemplates = CComponentEngine::makeComponentUrlTemplates(
        // массив переменных масок по умолчанию
            $arDefaultUrlTemplates404,
            // массив переменных масок из входных параметров
            $this->arParams["SEF_URL_TEMPLATES"]
        );

        // объединение дефолтных алиасов которые приходят в arParams["VARIABLE_ALIASES"] в вызове комплексного компонента и из массива $arDefaultVariableAliases404, для определения HTTP GET запросов из адреса страницы. Параметры из настроек arrParams заменяют дефолтные
        $arVariableAliases = CComponentEngine::makeComponentVariableAliases(
        // массив псевдонимов переменных по умолчанию
            $arDefaultVariableAliases404,
            // массив псевдонимов из входных параметров
            $this->arParams["VARIABLE_ALIASES"]
        );

        // определение шаблона, какой файл подключать section.php, element.php, index.php
        $componentPage = $engine->guessComponentPath(
        // путь до корня секции
            $this->arParams["SEF_FOLDER"],
            // массив масок
            $arUrlTemplates,
            // путь до секции SECTION_CODE и элемента ELEMENT_CODE
            $arVariables
        );

        // проверяем, если не удалось сопоставить шаблон, значит выводим index.php
        if ($componentPage == FALSE) {
            $componentPage = 'list';
        }

        // получаем значения переменных в $arVariables
        CComponentEngine::initComponentVariables(
        // файл который будет подключен section.php, element.php, index.php
            $componentPage,
            // массив имен переменных, которые компонент может получать из GET запроса
            $this->arComponentVariables,
            // массив псевдонимов переменных из GET запроса
            $arVariableAliases,
            // востановленные переменные
            $arVariables
        );

        // формируем arResult
        $this->arResult = [
            "VARIABLES" => $arVariables,
            "ALIASES" => $arVariableAliases
        ];

        return $componentPage;
    }

    // метод обработки режима без ЧПУ
    protected function noSefMode()
    {
        // переменная в которую запишем название подключаемой страницы
        $componentPage = "";

        // массив предназначен для обработки HTTP GET запросов из адреса страницы, используется в режиме не ЧПУ. Предназначен для задания псевдонимов из массива arParams["VARIABLE_ALIASES"] в вызове комплексного компонента, если нужно прокинуть GET параметр server.com/test/?ELEMENT_ID=1 дальше в простой компонент, в массив $arDefaultVariableAliases нужно добавить псевдоним: 'ELEMENT_ID' => 'ELEMENT_ID'. Если нужно, чтобы в адресной строке браузера передача параметра выглядела не так: server.com/test/?ELEMENT_ID=1 а вот так: server.com/test/?ID=1. Для этого задаем псевдоним 'ELEMENT_ID' => 'ID'
        $arDefaultVariableAliases = [];

        // объединение дефолтных алиасов которые приходят в arParams["VARIABLE_ALIASES"] и из массива $arDefaultVariableAliases, для определения HTTP GET запросов из адреса страницы. Параметры из настроек arrParams заменяют дефолтные
        $arVariableAliases = CComponentEngine::makeComponentVariableAliases(
        // массив псевдонимов переменных по умолчанию
            $arDefaultVariableAliases,
            // массив псевдонимов из входных параметров
            $this->arParams["VARIABLE_ALIASES"]
        );

        // массив будут заполнен переменными, которые будут найдены по маске шаблонов url
        $arVariables = [];

        // получаем значения переменных в $arVariables
        CComponentEngine::initComponentVariables(
        // файл который будет подключен section.php, element.php, index.php, для режима ЧПУ
            false,
            // массив имен переменных, которые компонент может получать из GET запроса
            $this->arComponentVariables,
            // массив псевдонимов переменных из GET запроса
            $arVariableAliases,
            // востановленные переменные
            $arVariables
        );

        // получаем контекст текущего хита
        $context = Application::getInstance()->getContext();
        // получаем объект Request
        $request = $context->getRequest();
        // получаем директорию запрошенной страницы
        $rDir = $request->getRequestedPageDirectory();

        // если запрошенная директория равна переданой в arParams["CATALOG_URL"], определяем тип страницы стартовая
        if ($arVariableAliases["BIRTHDAYS_URL"] == $rDir) {
            $componentPage = "list";
        }

        // по найденным параметрам $arVariables определяем тип страницы элемент
        if ((isset($arVariables["ELEMENT_ID"]) && intval($arVariables["ELEMENT_ID"]) > 0) || (isset($arVariables["ELEMENT_CODE"]) && $arVariables["ELEMENT_CODE"] <> '')) {
            $componentPage = "edit";
        }

        // по найденным параметрам $arVariables определяем тип страницы секция
        if ((isset($arVariables["SECTION_ID"]) && intval($arVariables["SECTION_ID"]) > 0) || (isset($arVariables["SECTION_CODE"]) && $arVariables["SECTION_CODE"] <> '')) {
            $componentPage = "section";
        }

        // формируем arResult
        $this->arResult = [
            "VARIABLES" => $arVariables,
            "ALIASES" => $arVariableAliases
        ];

        return $componentPage;
    }
}
