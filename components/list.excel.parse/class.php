<?php

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    Paint\PaintTable,
    SpreadsheetReader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);

class ExcelParseClass extends CBitrixComponent
{
    public $PolygraphIblockId = 33;
    public $PolygraphPropDateSend = 141; //Дата отправки
    public $PolygraphPropDatePlacing = 142; //Дата размещения

    public $SRIblockId = 30; //Целевая роспись

    public $DealCBookings = 3; //Букинги
    public $DealCRelease = 0; //Релиз

    public $fileExts = array('xlsx');

    private $ErrorMsg = "";


    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    public function executeComponent()
    {
        Loader::includeModule("crm");
        Loader::includeModule("iblock");

        $this->includeExcelLibrary();

        if ($_POST['submitExcel']){
            if (!$this->Parse()){
                ShowError($this->ErrorMsg);
            };
        }
        $this->includeComponentTemplate();
    }

    protected function includeExcelLibrary()
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/spreadsheet-reader/php-excel-reader/excel_reader2.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/spreadsheet-reader/SpreadsheetReader.php';
    }


    public function Parse()
    {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();

        $file = $request->getFile('advertising_file');

        if (!$this->checkFile($file)) {
           return false;
        }

        $uploads_dir = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $file['name'];

        if (!move_uploaded_file($file['tmp_name'], $uploads_dir)) {
            $this->ErrorMsg = Loc::getMessage('PARSE_NOT_FILE_READE');
            return false;
        };

        try {
            $reader = new SpreadsheetReader($uploads_dir);
            $sheets = $reader->Sheets();
            unlink($uploads_dir);
            foreach ($sheets as $index => $name) {
                $reader->ChangeSheet($index);
                $this->UpdatePolygraph($reader);
            }

            $this->arResult['STATUS'] = 1;

            return true;
        } catch (Exception $e) {

            $this->ErrorMsg =  Loc::getMessage('PARSE_NOT_FILE_READE');

            unlink($uploads_dir);
            return false;
        }

    }

    public function checkFile($file)
    {
        if ($file['size'] <= 0) {
            $this->ErrorMsg =  Loc::getMessage('PARSE_FILE_EMPTY');
            return false;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (!in_array($ext, $this->fileExts)) {
            $this->ErrorMsg =  Loc::getMessage('PARSE_FILE_EXTS');
            return false;
        }

        return true;
    }


    public function UpdatePolygraph($reader)
    {
        $i = 1;
        foreach ($reader as $row){
            if ($i > 2){
                $this->SearchRelease($row);
            }
            ++$i;
        }

    }

    public function SearchRelease($row)
    {
        $Release = trim($row[0]);

        if (empty($Release)){
            return;
        };

        //Сделка (релиз)
        $dealId = (int)\Bitrix\Crm\DealTable::GetList(array('select' => array('ID'), 'filter' => array('TITLE'=> $Release, 'CATEGORY_ID' => $this->DealCRelease)))->fetch()['ID'];


        if ($dealId == 0){
            return;
        }

        //Целевая роспись
        $SRId = (int)\CIBlockElement::GetList(Array(), array('IBLOCK_ID' => $this->SRIblockId, '=PROPERTY_99' => $dealId), false, false, array('ID'))->fetch()['ID'];

        if ($SRId == 0){
            return;
        }


        //PaintTable (дополнение к Целевая роспись)
        $cinemaKode = trim($row[8]);

        $prams = array(
            'filter' => array('IblockId' => $SRId, 'Xml' => $cinemaKode),
            'select' => array('DealId')
        );

        $dealBukId = (int)PaintTable::GetList($prams)->fetch()['DealId'];

        if ($dealBukId == 0){
            return;
        }

        $stmp = MakeTimeStamp($row[3], "MM-DD-YYYY");
        $DateSent = ConvertTimeStamp($stmp, "SHORT", "ru");

        $stmp = MakeTimeStamp($row[4], "MM-DD-YYYY");
        $DatePlacing = ConvertTimeStamp($stmp, "SHORT", "ru");

        $PROP = array();
        $poligrOb = \CIBlockElement::GetList(Array(), array('IBLOCK_ID' => $this->PolygraphIblockId, '=PROPERTY_136' => 'D_'.$dealBukId), false, false, array('ID'));
        while ($poligr = (int)$poligrOb->fetch()['ID']){
            $PROP[$this->PolygraphPropDateSend] = $DateSent;
            $PROP[$this->PolygraphPropDatePlacing] = $DatePlacing;
            \CIBlockElement::SetPropertyValuesEx($poligr, $this->PolygraphIblockId, $PROP);
        }

    }

}