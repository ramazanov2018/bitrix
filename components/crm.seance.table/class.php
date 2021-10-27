<?php

use Paint\PaintTable,
    Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);

class SeancesTable extends CBitrixComponent
{
    public $DealID = "";
    public $formatIblockID = 32;
    public $TargetIblockID = 30;
    public $weekDays = 7;

    public function onPrepareComponentParams($arParams)
    {
        ($arParams['DEAL_ID']) ? $this->DealID = (int) trim($arParams['DEAL_ID']) : $this->DealID = '';
        return $arParams;
    }

    public function executeComponent()
    {
        Loader::includeModule("iblock");

        if ($this->GetPointTarget()){

        $this->GetSeanceFormat();

        }

        $this->includeComponentTemplate();
    }

    public function GetPointTarget()
    {
        $prams = array(
            'filter' => array('DealId' => $this->DealID),
            'select' => array('Id', 'IblockId', 'Week1Plan', 'Week1Actual', 'Week2Plan', 'Week2Actual',
                'Week3Plan', 'Week3Actual', 'Week4Plan', 'Week4Actual', 'Week5Plan', 'Week5Actual', 'DealId')
        );

        $target = PaintTable::GetList($prams)->fetch();

        if (empty($target['IblockId'])){
            $this->arResult['error'] = 1;
            return false;
        }else{
            $this->arResult['error'] = 0;
        }

        $this->arResult['DATA']['PLAN'] = $target;
        $this->arResult['SEANCES'][0]['TITLE'] = Loc::getMessage("SEANCE_TITLE_PLANE");
        $this->arResult['SEANCES'][0]['ID'] =  $target['Id'];
        $this->arResult['SEANCES'][0]['OWNER_ID'] =  $target['IblockId'];
        $this->arResult['SEANCES'][0]['1_WEEK_PLAN'] =  $target['Week1Plan'];
        $this->arResult['SEANCES'][0]['2_WEEK_PLAN'] =  $target['Week2Plan'];
        $this->arResult['SEANCES'][0]['3_WEEK_PLAN'] =  $target['Week3Plan'];
        $this->arResult['SEANCES'][0]['4_WEEK_PLAN'] =  $target['Week4Plan'];
        $this->arResult['SEANCES'][0]['5_WEEK_PLAN'] =  $target['Week5Plan'];



        $arFilter = array('ID'=> $this->arResult['SEANCES'][0]['OWNER_ID'],'IBLOCK_ID'=>$this->TargetIblockID);
        $arSelect = array('ID', 'PROPERTY_100', 'PROPERTY_102');
        $resOb = \CIblockElement::GetList(array(),$arFilter , false, false, $arSelect)->Fetch();

        $this->arResult['WEEK_COUNT'] = $resOb['PROPERTY_102_VALUE'];
        $this->arResult['DATE_START'] = $resOb['PROPERTY_100_VALUE'];
        $this->arResult['SEANCES'][0]['DATE_START'] =  $resOb['PROPERTY_100_VALUE'];
        $this->arResult['SEANCES'][0]['DATE_END'] =  $this->dateEnd();

        return true;
    }

    public function GetSeanceFormat()
    {
        $arFilter = array('IBLOCK_ID'=>$this->formatIblockID, '=PROPERTY_128' => $this->DealID);
        $arSelect = array('ID', 'NAME', 'PROPERTY_129', 'PROPERTY_130', 'PROPERTY_131', 'PROPERTY_132', 'PROPERTY_133','PROPERTY_134','PROPERTY_135',);
        $resOb = \CIblockElement::GetList(array(), $arFilter, false, false,$arSelect);

        $this->arResult['SEANCES'][1]['TITLE'] = Loc::getMessage("SEANCE_TITLE_CONTRACT");
        $this->arResult['SEANCES'][1]['DATE_START'] = $this->arResult['SEANCES'][0]['DATE_START'];
        $this->arResult['SEANCES'][1]['DATE_END'] = $this->dateEnd();

        while ($res = $resOb->fetch()){
            $this->arResult['DATA']['FORMAT'][$res['ID']] = $res;
            $this->arResult['SEANCES'][$res['ID']]['TITLE'] = $res['NAME'];
            $this->arResult['SEANCES'][$res['ID']]['ID'] =  $res['ID'];
            $this->arResult['SEANCES'][$res['ID']]['1_WEEK_PLAN'] = '<input class="seance-format" type="number" name="SEANCE[FORMAT]['.$res['ID'].'][129]" value="'.$res['PROPERTY_129_VALUE'].'"> ';
            $this->arResult['SEANCES'][$res['ID']]['2_WEEK_PLAN'] = '<input class="seance-format" type="number"  name="SEANCE[FORMAT]['.$res['ID'].'][130]" value="'.$res['PROPERTY_130_VALUE'].'">';
            $this->arResult['SEANCES'][$res['ID']]['3_WEEK_PLAN'] = '<input class="seance-format" type="number"  name="SEANCE[FORMAT]['.$res['ID'].'][131]" value="'.$res['PROPERTY_131_VALUE'].'">';
            $this->arResult['SEANCES'][$res['ID']]['4_WEEK_PLAN'] = '<input class="seance-format" type="number"  name="SEANCE[FORMAT]['.$res['ID'].'][132]" value="'.$res['PROPERTY_132_VALUE'].'">';
            $this->arResult['SEANCES'][$res['ID']]['5_WEEK_PLAN'] ='<input class="seance-format" type="number"  name="SEANCE[FORMAT]['.$res['ID'].'][133]" value="'.$res['PROPERTY_133_VALUE'].'">' ;
            $this->arResult['SEANCES'][$res['ID']]['DATE_START'] = $res['PROPERTY_134_VALUE'];
            $this->arResult['SEANCES'][$res['ID']]['DATE_END'] = $res['PROPERTY_135_VALUE'];

            $this->arResult['SEANCES'][1]['1_WEEK_PLAN'] += $res['PROPERTY_129_VALUE'];
            $this->arResult['SEANCES'][1]['2_WEEK_PLAN'] += $res['PROPERTY_130_VALUE'];
            $this->arResult['SEANCES'][1]['3_WEEK_PLAN'] += $res['PROPERTY_131_VALUE'];
            $this->arResult['SEANCES'][1]['4_WEEK_PLAN'] += $res['PROPERTY_132_VALUE'];
            $this->arResult['SEANCES'][1]['5_WEEK_PLAN'] += $res['PROPERTY_133_VALUE'];
        }
    }

    private function dateEnd()
    {
        $date = $this->arResult['DATE_START'];
        $days = $this->arResult['WEEK_COUNT'] * $this->weekDays;

        return date('d.m.Y', strtotime($date."+".$days." days"));
    }


}