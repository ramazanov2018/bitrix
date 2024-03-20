<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Highloadblock as HL,
    Bitrix\Main\Localization\Loc as Loc;

class DigitalCultureDocs  extends CBitrixComponent
{
    private $arMonths = [
        "январь" => "01",
        "февраль" => "02",
        "март" => "03",
        "апрель" => "04",
        "май" => "05",
        "июнь" => "06",
        "июль" => "07",
        "август" => "08",
        "сентябрь" => "09",
        "октябрь" => "10",
        "ноябрь" => "11",
        "декабрь" => "12",
    ];
    const IBLOCK_CODE = 'statisticFiles';
    const SECTION_TABLE_NAME = 'b_hlb_stat_sections';
    /**
     * подключает языковые файлы
     */
    public function onIncludeComponentLang()
    {
        $this->includeComponentLang(basename(__FILE__));
        Loc::loadMessages(__FILE__);
    }

    /**
     * подготавливает входные параметры
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        return $params;
    }

    private function getSections()
    {

        $arResult = [];
        Loader::includeModule("highloadblock");

        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => self::SECTION_TABLE_NAME)))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClass = $entity->getDataClass();
        $res = $DataClass::getList(array(
            "order" => array(
                "UF_SORT" => "ASC",
            ),
            "filter" => array(
                "UF_ACTIVE" => "1",
                "!UF_MARKING" => ""
            )
        ));

        while ($row = $res->fetch()) {
            $lang = strtoupper(LANGUAGE_ID);
            $item = [];
            if(array_key_exists("UF_NAME_".$lang, $row)){
                $item['NAME'] = $row["UF_NAME_".$lang];
            }else{
                $item['NAME'] = $row["UF_NAME_RU"];
            }
            $this->arResult["SECTIONS"][$row['UF_MARKING']] = $item;
        }
    }

    /**
     * @param $filter
     * @return void
     */
    private function getItemsIBDocs($filter = array())
    {
        $keys = array_keys($this->arResult["SECTIONS"]);
        $filter['TYPE'] = $keys;
        $obj = \Bitrix\Iblock\Elements\ElementStatisticFilesTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'NAME',
                'DOCUMENT_ID' => 'FILE.VALUE',
                'DATE_CREATE',
                'PERIOD_DATA_FROM' => 'PERIOD_FROM.VALUE',
                'PERIOD_DATA_TO' => 'PERIOD_TO.VALUE',
                'TYPE' => 'DATA_TYPE.VALUE',
            ],
            'order' => ['NAME' => 'DESC'],
            'filter' =>  $filter,
        ]);
        while ($row = $obj->fetch()) {
            $item = [];
            $item['TITLE'] = $row['NAME'];
            $item['DOCUMENT']["SRC"] = CFile::GetPath((int) $row['DOCUMENT_ID']);
            $item['DOCUMENT']["TYPE"] = GetFileExtension($item['DOCUMENT']["SRC"]);
            $item['DOCUMENT']["IS_FILE"] = "Y";

            $this->arResult["SECTIONS"][$row['TYPE']]["ITEMS"][] = $item;
        }
    }

    /**
     * @param $filter
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    private function prepareData($filter): void
    {
        Loader::includeModule("iblock");

        $this->getSections();
        $this->getItemsIBDocs($filter);
        $this->setActiveTab();
    }
    private function setActiveTab()
    {
        if (is_array($this->arResult["SECTIONS"]))
            $this->arResult["SECTIONS"][array_key_first($this->arResult["SECTIONS"])]["CURRENT"] = 'Y';

        if (isset($this->arResult['REQUEST_PARAM']['activeTab']) && $this->arResult['REQUEST_PARAM']['activeTab'] != "" && is_array($this->arResult["SECTIONS"])){
            foreach ($this->arResult["SECTIONS"] as $key => $val){
                $this->arResult["SECTIONS"][$key]["CURRENT"] = 'N';
                if ($this->arResult['REQUEST_PARAM']['activeTab'] == $key)
                    $this->arResult["SECTIONS"][$key]["CURRENT"] = 'Y';
            }
        }
    }
 
    /**
     * получение результатов
     */
    protected function getResult()
    {
        $context = Context::getCurrent();
        $request = $context->getRequest();
        $params = $request->getQueryList()->toArray();
        if (isset($params['date_filter']) && $params['date_filter'] == 'Y'){
            global $APPLICATION;
            $_SESSION['date_from'] = $params["date_from"];
            $_SESSION['date_to'] = $params["date_to"];
            $_SESSION['activeTab'] = trim($params["activeTab"]);
            LocalRedirect($APPLICATION->GetCurPage());
        }
        $params['date_from'] = $_SESSION['date_from'];
        $params['date_to'] = $_SESSION['date_to'];
        $params['activeTab'] = $_SESSION['activeTab'];

        $_SESSION['date_from'] = '';
        $_SESSION['date_to'] = '';
        $_SESSION['activeTab'] = '';

        if(empty($params["date_to"])){
            $params["date_to"] = $this->GetDefaultDateTo();
        };

        $filter['ACTIVE'] = 'Y';
        if($params["date_from"]){
            $filter[] = [
                "LOGIC"=>"OR",
                [
                    "PERIOD_DATA_FROM"=>"",
                ],
                [
                    ">=PERIOD_DATA_FROM"=>$this->BuildDateFrom($params["date_from"])
                ],
            ];
        };

        if($params["date_to"]){
            $filter[] = [
                "LOGIC"=>"OR",
                [
                    "PERIOD_DATA_TO"=>"",
                ],
                [
                    "<=PERIOD_DATA_TO"=>$this->BuildDateTo($params["date_to"])
                ]
            ];
        };
        $this->arResult['REQUEST_PARAM'] = $params;
        $this->prepareData($filter);
    }

    protected function BuildDateFrom($date)
    {
        $date = explode(' ', $date);
        $date[0] = trim($date[0]);
        $date[1] = trim($date[1]);
        $date[2] = trim($date[2]);
        return $date[2].'-'.$this->arMonths[mb_strtolower($date[1])].'-'.$date[0];
    }
    protected function BuildDateTo($date)
    {
        $date = explode(' ', $date);
        $date[0] = trim($date[0]);
        $date[1] = trim($date[1]);
        $date[2] = trim($date[2]);
        return $date[2].'-'.$this->arMonths[mb_strtolower($date[1])].'-'.$date[0];
    }

    protected function GetDefaultDateTo()
    {
        $objDateTime = new  \Bitrix\Main\Type\DateTime();
        $d = $objDateTime->format("d");
        $m = array_search(mb_strtolower($objDateTime->format("m")), $this->arMonths);;
        $Y = $objDateTime->format("Y");

        return $d." ".$m." ".$Y;
    }



    /**
     * выполняет логику работы компонента
     */
    public function executeComponent()
    {

        try
        {
            $this->getResult();
            $this->includeComponentTemplate();

        }
        catch (Exception $e)
        {

            ShowError($e->getMessage());
        }
    }
}
