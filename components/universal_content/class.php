<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Highloadblock as HL,
    Bitrix\Main\Localization\Loc as Loc;

class UniversalContent  extends CBitrixComponent
{
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
        $params["IBLOCK_SECTION_ID"] = (int)$params["IBLOCK_SECTION_ID"];
        $params["IBLOCK_ID"] = (int)$params["IBLOCK_ID"];
        return $params;
    }

    private static function getHLTable(string $tableName, array $filter = []): array
    {

        $arResult = [];
        Loader::includeModule("highloadblock");

        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => $tableName)))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClass = $entity->getDataClass();
        $res = $DataClass::getList(array(

            'order' => array(
                'UF_NAME' => 'ASC',
            ),
            'filter' => $filter
        ));

        while ($row = $res->fetch()) {
            $arResult[$row['UF_XML_ID']] = $row;
        }

        return $arResult;
    }

    /**
     * получение результатов
     */
    protected function getResult()
    {
        Loader::includeModule('iblock');
        $this->arResult['DOCUMENT_CATEGORIES'] = $this->getHLTable('b_hlbd_documentcategory');

        $this->GetMainSectionInfo();
        $this->GetElements();
    }

    protected function GetElements(){
        $arSelect = Array("ID", "IBLOCK_ID","IBLOCK_SECTION_ID", "NAME", "PREVIEW_PICTURE","PREVIEW_TEXT", "PROPERTY_*");
        $arFilter = Array("ACTIVE"=>"Y", "IBLOCK_CODE" =>$this->arParams["IBLOCK_CODE"], "SECTION_CODE" => $this->arParams["IBLOCK_SECTION_CODE"]);
        $res = CIBlockElement::GetList(
            Array("SORT"=>"ASC", "ID"=>"ASC"),
            $arFilter,
            false,
            array(),
            $arSelect
        );

        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();
            $arFields["PREVIEW_PICTURE"] = CFile::GetPath($arFields["PREVIEW_PICTURE"]);
            $arFields["PROPERTIES"] = $ob->GetProperties();
            if (isset($arFields["PROPERTIES"]["FILE"]) && !empty($arFields["PROPERTIES"]["FILE"]["VALUE"]))
                if(!$arFields["PROPERTIES"]["FILE"]['FILE_DATA'] = CFile::GetFileArray($arFields["PROPERTIES"]["FILE"]["VALUE"]))
                    continue;
            $this->arResult["ITEMS"][] = $arFields;
        }
    }

    /**
     * @return void
     */
    protected function GetMainSectionInfo(){
        $arFilter = array(
            "IBLOCK_ID" => $this->getIblockCodeById($this->arParams["IBLOCK_CODE"]),
            "CODE" => $this->arParams["IBLOCK_SECTION_CODE"],
        );
        $db_iblock = CIBlockSection::GetList(array('LEFT_MARGIN' => 'ASC'), $arFilter,false, array("ID", "NAME", "DESCRIPTION", "UF_*"));

        if($arRes = $db_iblock->GetNext()){
            $arRes["PAGE_TITLE"] = $arRes["UF_BLOCK_TITLE_RU"];
            $this->arResult["MAIN_SECTION"] = $arRes;
        }
    }

    /**
     * @param $iblock_code
     * @return false|int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getIblockCodeById($iblock_code)
    {
        $iblock_obj = \Bitrix\Iblock\IblockTable::getList(
            [
                'filter' => [
                    '=CODE' => $iblock_code
                ],
                'select' => [
                    'ID'
                ]
            ]
        );
        if ($res = $iblock_obj->fetchObject())
            return $res->getId();

        return false;
    }

    /**
     * выполняет логику работы компонента
     */
    public function executeComponent()
    {
        if ($this->arParams["IBLOCK_CODE"] == "" ){
            ShowError(Loc::GetMessage("IBLOCK_NOT_SELECTED"));
            return;
        }
        if ($this->arParams["IBLOCK_SECTION_CODE"] == "" ){
            ShowError(Loc::GetMessage("IBLOCK_SECTION_NOT_SELECTED"));
            return;
        }

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
