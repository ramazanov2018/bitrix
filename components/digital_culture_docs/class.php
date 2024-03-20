<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
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
    const IBLOCK_CODE = 'docs';
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
        $arFilter = ['IBLOCK_CODE'=>self::IBLOCK_CODE, 'ACTIVE'=>'Y'];
        $arSelect = ['ID', 'NAME'];
        $db_list = CIBlockSection::GetList([], $arFilter, true, $arSelect);
        while($ar_result = $db_list->GetNext())
        {
            $this->arResult["SECTIONS"][$ar_result['ID']] = $ar_result;
        }
    }

    private function getItemsIBDocs($filter)
    {
        $filter['IBLOCK_SECTION_ID'] = array_keys($this->arResult["SECTIONS"]);

        $obj = \Bitrix\Iblock\Elements\ElementDocsTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'TITLE' => 'NAME',
                'DOCUMENT_ID' => 'DOCUMENT.VALUE',
                'DATE_CREATE',
                'ACTIVE_FROM',
                'ACTIVE_TO',
                'IBLOCK_SECTION_ID',
            ],
            'order' => ['sort' => 'ASC'],
            'filter' =>  $filter,
        ]);
        while ($row = $obj->fetch()) {
            $item = [];
            $item['TITLE'] = $row['TITLE'];
            $item['DOCUMENT']["SRC"] = CFile::GetPath((int) $row['DOCUMENT_ID']);
            $item['DOCUMENT']["TYPE"] = GetFileExtension($item['DOCUMENT']["SRC"]);
            $item['DOCUMENT']["IS_FILE"] = "Y";

            $this->arResult["SECTIONS"][$row['IBLOCK_SECTION_ID']]["ITEMS"][] = $item;
        }
    }
    private function getItemsIBAntiCorrupt($filter)
    {
        $this->arResult["SECTIONS"]['antiCorruption']['NAME'] = 'Противодействие коррупции';
        $obj = \Bitrix\Iblock\Elements\ElementAntiCorruptionTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'TITLE' => 'PREVIEW_TEXT',
                'DOCUMENT_ID' => 'FILE.VALUE',
                'EXTERNAL_LINK' => 'EXT_LINK.VALUE',
                'DATE_CREATE',
                'ACTIVE_FROM',
                'ACTIVE_TO',
                'IBLOCK_SECTION_ID',
            ],
            'order' => ['sort' => 'ASC'],
            'filter' =>  $filter,
        ]);
        while ($row = $obj->fetch()) {
            $item = [];
            $item['TITLE'] = $row['TITLE'];
            $item['EXTERNAL_LINK'] = $row['EXTERNAL_LINK'];
            if ($row['DOCUMENT_ID']){
                $item['DOCUMENT']["SRC"] = CFile::GetPath((int) $row['DOCUMENT_ID']);
                $item['DOCUMENT']["TYPE"] = GetFileExtension($item['DOCUMENT']["SRC"]);
                $item['DOCUMENT']["IS_FILE"] = "Y";
            }else{
                $item['DOCUMENT']["SRC"] = $row['EXTERNAL_LINK'];
                $item['DOCUMENT']["TYPE"] = 'url';
                $item['DOCUMENT']["IS_FILE"] = "N";
            }

            $this->arResult["SECTIONS"]['antiCorruption']["ITEMS"][] = $item;
        }
    }

    private function prepareData($filter):void
    {
        Loader::includeModule("iblock");

        $this->getSections();
        $this->getItemsIBDocs($filter);
        $this->getItemsIBAntiCorrupt($filter);
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
                    "ACTIVE_FROM"=>""
                ],
                [
                    ">=ACTIVE_FROM"=>$this->BuildDateFrom($params["date_from"])
                ],
            ];
        };

        if($params["date_to"]){
            $filter[] = [
                "LOGIC"=>"OR",
                [
                    "ACTIVE_FROM" => ""
                ],
                [
                    "<=ACTIVE_FROM" => $this->BuildDateFrom($params["date_to"])
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
        $res =  $date[2].'-'.$this->arMonths[mb_strtolower($date[1])].'-'.$date[0].' 00:00:00';

        return \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($res));
    }
    protected function BuildDateTo($date)
    {
        $date = explode(' ', $date);
        $date[0] = trim($date[0]);
        $date[1] = trim($date[1]);
        $date[2] = trim($date[2]);
        $res =  $date[2].'-'.$this->arMonths[mb_strtolower($date[1])].'-'.$date[0].' 24:00:00';

        return \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($res));
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
