<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Highloadblock as HL,
    Bitrix\Main\Localization\Loc as Loc;

CModule::IncludeModule("iblock");
class catalogDetail extends CBitrixComponent
{

    protected $multyProps = [
        "" => [
            'ETC',
            'COMPANY_REVIEWS',
            'PHOTO',
            'SERTIFICATES',
            'INDUSTRY'],
        "personal" => [

        ]
    ];
    protected $multyImagesProps = [
        "" => [
            'ETC',
            'COMPANY_REVIEWS',
            'PHOTO',
            'SERTIFICATES',
        ],
    ];
    /**
     * подключает языковые файлы
     */

    /**
     * подготавливает входные параметры
     * @param array $arParams
     * @return array
     */

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

        $this->GetElements();
    }

    protected function GetElements()
    {
        $arSelect = array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PREVIEW_PICTURE", "PROPERTY_*");
        $arFilter = array("ACTIVE" => "Y", "IBLOCK_CODE" => $this->arParams["IBLOCK_CODE"], "SECTION_CODE" => $this->arParams["IBLOCK_SECTION_CODE"]);
        $res = CIBlockElement::GetList(
            array(),
            $arFilter,
            false,
            array(),
            $arSelect
        );

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arFields["PREVIEW_PICTURE"] = CFile::GetPath($arFields["PREVIEW_PICTURE"]);
            $arFields["PROPERTIES"] = $ob->GetProperties();
            if (isset($arFields["PROPERTIES"]["FILE"]) && !empty($arFields["PROPERTIES"]["FILE"]["VALUE"]))
                $arFields["PROPERTIES"]["FILE"]['FILE_SRC'] = CFile::GetPath($arFields["PROPERTIES"]["FILE"]["VALUE"]);
            $this->arResult["ITEMS"][] = $arFields;
        }
    }

    public static function getTableName($templateName)
    {
        if ($templateName == "") {
            return 'companies';
        }
        if ($templateName == "requests") {
            return 'request';
        }
    }

    protected function getIBlockIDByCode($code)
    {
        $iBlockId = \Bitrix\Iblock\IblockTable::getList(['filter' => ['CODE' => $code]])->fetch()['ID'];
        $this->arResult["IBLOCK_ID"] = $iBlockId;

        return $iBlockId;
    }

    protected function getCompanyINN()
    {
        global $USER;
        if (!$USER->IsAuthorized())
            LocalRedirect("/auth/");
        $rsUser = CUser::GetByID($USER->GetID());
        $INN = $rsUser->Fetch()['WORK_DEPARTMENT'];
        return $INN;
    }

    protected function getProperties()
    {
        $id = $this->getIBlockIDByCode();
        $rsProperty = \Bitrix\Iblock\PropertyTable::getList(array(
            'filter' => array('IBLOCK_ID' => $id, 'ACTIVE' => 'Y'),

        ));
        $arProp = [];
        while ($arProperty = $rsProperty->fetch()) {
            $arProp[] = 'PROPERTY_' . $arProperty['CODE'];
        }
        if ($this->getTemplateName() == 'requests') {
            array_push($arProp, 'ID', 'NAME', 'DATE_CREATE');
        }
        return $arProp;
    }

    protected function getCompanyForUser()
    {
        global $USER;
        $rsUser = CUser::GetByID($USER->GetID());
        $company = $rsUser->Fetch()['UF_USER_COMPANIES'];
        return $company;
    }

    protected function prepareImage($imageId)
    {
        $file = \CFile::GetByID($imageId)->fetch();
        return $file;
    }

    protected function templateAddPrepare(&$data)
    {
        if ($this->getTemplateName() == "") {
            foreach ($data as $keyData => $dataElem) {
                $data[$keyData]['PROPERTY_LOGO_VALUE'] = $this->prepareImage($data['PROPERTY_LOGO_VALUE']);
            }
        }
        if ($this->getTemplateName() == "requests") {
            foreach ($data as $keyData => $dataElem) {
                if (in_array($dataElem['PROPERTY_STATUS_VALUE'], ['Отклонена', 'Не отправлена'])) {
                    $data[$keyData]['BUTN'] = 'Удалить';
                }
                if (in_array($dataElem['PROPERTY_STATUS_VALUE'], ['Одобрена'])) {
                    $data[$keyData]['BUTN'] = 'Отозвать';
                }
            }
        }
    }

    protected function getFilter($companyId, $iblockId)
    {
        if ($this->getTemplateName() == '') {
            return ['IBLOCK_ID' => $iblockId, 'ID' => $companyId];
        } elseif ($this->getTemplateName() == 'requests') {
            return ['IBLOCK_ID' => $iblockId, 'MAKER' => $companyId];
        }
    }

    protected function prepareMultiplyImagesProperties($myltiplyFields, $keys)
    {
        foreach ($myltiplyFields as $key => $myltiplyField) {
            if (in_array($key, $keys)) {
                foreach ($myltiplyField as $value) {
                    $result[$key][] = $this->prepareImage($value);
                }
            }
        }
        return $result;
    }

    protected function getMultiplyProperty($iblockId, $elementId, $prop)
    {
        $arRes = CIBlockElement::GetProperty($iblockId, $elementId, [], ['CODE' => $prop]);
        while ($res = $arRes->fetch()) {
            $result[] = $res['VALUE'];
        }
        return $result;
    }

    protected function getMultiplyProperties($iblockId, $elementId,$props)
    {
        foreach ($props as $prop) {
            $result[$prop] = $this->getMultiplyProperty($iblockId, $elementId, $prop);
        }

        return $result;
    }

    /**
     * Функции заполняющии данные
     */
    protected function prepareDocFile(string $prop)
    {

        $arr = (explode("\\", $_FILES[$prop]['tmp_name']));


        array_pop($arr);
        $arr[] = $_FILES[$prop]['name'];
        $filePath = implode('\\', $arr);
        move_uploaded_file($_FILES[$prop]['tmp_name'], $filePath);

        return CFile::MakeFileArray($filePath);
    }

    protected function getCompanyData()
    {
        $iblockId = $this->getIBlockIDByCode('companies');
        $rs = CIBlockElement::GetList('', ['ID' => $_REQUEST['id'],'IBLOCK_ID'=> $iblockId], false, false, ['ID', 'NAME', 'PROPERTY_ETC',
            'PROPERTY_COMPANY_REVIEWS', 'PROPERTY_PHOTO', 'PROPERTY_SERTIFICATES', 'PROPERTY_DESCRIPTION', 'PROPERTY_LOGO',
            'PROPERTY_COMPANY_PHONE', 'PROPERTY_COMPANY_EMAIL','PROPERTY_SHORT_NAME','PROPERTY_FULL_NAME']);
        $data = $rs->fetch();
        $data['PROPERTY_LOGO_VALUE'] = $this->prepareImage($data['PROPERTY_LOGO_VALUE']);
        $myltiplyFields = $this->getMultiplyProperties($iblockId, $_REQUEST['id'],['ETC', 'COMPANY_REVIEWS', 'PHOTO', 'SERTIFICATES','INDUSTRY']);
        $myltiplyImages = $this->prepareMultiplyImagesProperties($myltiplyFields, ['ETC', 'COMPANY_REVIEWS', 'PHOTO', 'SERTIFICATES']);


        $this->arResult['COMPANY']['DATA'] = $data;
        $this->arResult['COMPANY']['MULTIPLY_FIELDS'] = ['IMAGES' => $myltiplyImages, 'FIELDS' => $myltiplyFields];

    }

    protected function getRequestsByCompany(){
        $iblockId=$this->getIBlockIDByCode('request');
        $rs = CIBlockElement::GetList('', ['PROPERTY_MAKER' => $_REQUEST['id'],'IBLOCK_ID'=>$iblockId], false, false, ['ID', 'NAME','PROPERTY_STATUS']);
        while ($arRs = $rs->fetch()){

            $data[]=$arRs['ID'];
        }
        return $data;
    }

    protected function getProductsData()
    {
        $iblockId = $this->getIBlockIDByCode('products');
        $requstIds=$this->getRequestsByCompany();

        $rs = CIBlockElement::GetList(array(), ['PROPERTY_REQUEST.ID'=>$requstIds,'IBLOCK_ID'=>$iblockId], false, false, ['ID', 'NAME', 'PROPERTY_FACT_ADRESS', 'PROPERTY_DATE_INCLUDE_CATALOG']);
        while ($arRs=$rs->fetch()) {
            $data[] = $arRs;
        }

        foreach ($data as $key=>$dataItem) {
            $myltiplyFields = $this->getMultiplyProperties($iblockId, $dataItem['ID'], ['PHOTO_PRODUCTION','TECH_DESCRIPTION','PATENT_PRODUCTION','SERTIFICATES_GOST_ISO','DATA_COST_IMPORTED_COMPONENTS','ETC_DOCS','SERTIFICATES_CONFORMITY']);
            $data[$key]['MULTIPLY_FIELDS']['IMAGES'] = $this->prepareMultiplyImagesProperties($myltiplyFields, ['PHOTO_PRODUCTION','TECH_DESCRIPTION','PATENT_PRODUCTION','SERTIFICATES_GOST_ISO','DATA_COST_IMPORTED_COMPONENTS','ETC_DOCS','SERTIFICATES_CONFORMITY']);
        }

        $this->arResult['PRODUCT']['DATA'] = $data;
    }

    protected function getData()
    {
        /*$filter = $this->getFilter($companyId, $iblockId);*/

        /*$arSelectedFields = $this->getProperties();*/

        $this->getCompanyData();
        $this->getProductsData();
/*        $this->templateAddPrepare($data);
        $myltiplyFields = $this->getMultiplyProperties($iblockId, $companyId);
        $myltiplyImages = $this->prepareMultiplyImagesProperties($myltiplyFields, $this->multyImagesProps[$this->getTemplateName()]);
        $this->arResult['DATA'] = $data;
        $this->arResult['MULTIPLY_FIELDS'] = ['IMAGES' => $myltiplyImages, 'FIELDS' => $myltiplyFields];*/
    }

    public function executeComponent()
    {

        /** @var string $templateFile */

        $this->getData();
        $this->includeComponentTemplate();

    }
}
