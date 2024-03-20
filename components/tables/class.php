<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Localization\Loc as Loc;

class ContentTabs  extends CBitrixComponent
{
    const TABLE_IBLOCK_CODE = "tableContent";
    const UF_FILE_RU = "UF_FILE_RU";
    const UF_FILE_EN = "UF_FILE_EN";
    const UF_SECTION_NAME_EN = "UF_SECTION_NAME_EN";
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
        return $params;
    }

    /**
     * @return void
     */
    protected function GetMainSectionInfo(){
        $arFilter = array(
            "IBLOCK_ID" => $this->arResult["IBLOCK_ID"],
            "ID" => $this->arParams["IBLOCK_SECTION_ID"],
        );
        $db_iblock = CIBlockSection::GetList(array('LEFT_MARGIN' => 'ASC'), $arFilter,false, array("ID", "NAME","UF_*"));

        if($arRes = $db_iblock->GetNext()){
            $arRes[self::UF_FILE_RU] = CFile::GetPath($arRes[self::UF_FILE_RU]);
            $arRes[self::UF_FILE_EN] = CFile::GetPath($arRes[self::UF_FILE_EN]);
            $this->arResult["MAIN_SECTION"] = $arRes;
        }
    }

    protected function GetSections(){
        $arSections = [];
        $rsParentSection = CIBlockSection::GetByID($this->arParams["IBLOCK_SECTION_ID"]);
        if ($arParentSection = $rsParentSection->GetNext())
        {
            $arSections[$arParentSection["ID"]] = $arParentSection["NAME"];
            $arFilter = array(
                'IBLOCK_ID' => $this->arResult["IBLOCK_ID"],
                '>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],
                '<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],
                '>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']
            );
            $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter, false, array("ID", "NAME", "UF_SECTION_NAME_EN"));
            while ($arSect = $rsSect->GetNext())
            {
                $arSections[$arSect["ID"]]["NAME_RU"] = $arSect["NAME"];
                $arSections[$arSect["ID"]]["NAME_EN"] = $arSect[self::UF_SECTION_NAME_EN];
            }
        }
        $this->arResult["SECTIONS"] = $arSections;
    }

    protected function GetElements(){
        $arSelect = Array("ID", "IBLOCK_ID","IBLOCK_SECTION_ID", "NAME", "PROPERTY_*");
        $arFilter = Array("IBLOCK_ID" => $this->arResult["IBLOCK_ID"], "ACTIVE"=>"Y","INCLUDE_SUBSECTIONS"=>"Y", "SECTION_ID" => $this->arParams["IBLOCK_SECTION_ID"]);
        $res = CIBlockElement::GetList(
            Array("SORT"=>"ASC", "PROPERTY_PRIORITY"=>"ASC"),
            $arFilter,
            false,
            array(),
            $arSelect
        );
        while($ob = $res->GetNextElement()){
            $arFields = $ob->GetFields();
            $this->arResult["ITEMS"][$arFields["IBLOCK_SECTION_ID"]][] = $ob->GetProperties();
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
     * получение результатов
     */
    protected function getResult()
    {
        Loader::includeModule('iblock');
        $this->arResult["IBLOCK_ID"] = $this->getIblockCodeById(self::TABLE_IBLOCK_CODE);


        $this->GetMainSectionInfo();
        $this->GetSections();
        $this->GetElements();
    }

    /**
     * выполняет логику работы компонента
     */
    public function executeComponent()
    {
        if ($this->arParams["IBLOCK_SECTION_ID"] <= 0 ){
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
