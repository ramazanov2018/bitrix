<?php
namespace Fbit\Exchange;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;

Loader::IncludeModule('highloadblock');
class HLBlockMigration
{
    static $error = '';
    static $HLBlockName = 'ExchangeLogs';
    static $HLBlockTable = 'b_fbit_exchange_logs';

    public static function CreateHLBlock()
    {
        $arLangs = Array(
            'ru' => 'Логи обмена',
            'en' => 'Exchange logs'
        );

        //создание HL-блока
        $result = HL\HighloadBlockTable::add(array(
            'NAME' => self::$HLBlockName,
            'TABLE_NAME' => self::$HLBlockTable,
        ));

        if ($result->isSuccess()) {
            $id = $result->getId();
            foreach($arLangs as $lang_key => $lang_val){
                HL\HighloadBlockLangTable::add(array(
                    'ID' => $id,
                    'LID' => $lang_key,
                    'NAME' => $lang_val
                ));
            }

            //создаем поля HL-блока.
            $arCartFields = self::GetFields($id);
            $arSavedFieldsRes = Array();
            foreach($arCartFields as $arCartField){
                $obUserField  = new \CUserTypeEntity();
                $ID = $obUserField->Add($arCartField);
                $arSavedFieldsRes[] = $ID;
            }
        } else {
            self::$error = $result->getErrorMessages();
        }
    }

    public static function DeleteHLBlock()
    {
        $filter = array(
            'select' => array('ID'),
            'filter' => array('=NAME' => self::$HLBlockName)
        );
        $hlblock = HL\HighloadBlockTable::getList($filter)->fetch();
        if(is_array($hlblock) && !empty($hlblock))
        {
            HL\HighloadBlockTable::delete($hlblock['ID']);
        }
    }

    public static function GetFields($hlID)
    {
        $UFObject = 'HLBLOCK_'.$hlID;

        $arCartFields = Array(
            'UF_DATE_EXCHANGE'=>Array(
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_DATE_EXCHANGE',
                'USER_TYPE_ID' => 'date',
                'MANDATORY' => 'Y',
                'SETTINGS' => array('DEFAULT_VALUE' => array('TYPE' => 'NOW')),
                "EDIT_FORM_LABEL" => Array('ru'=>'Дата обмена', 'en'=>'Date exchange'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Дата обмена', 'en'=>'Date exchange'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Дата обмена', 'en'=>'Date exchange'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),
            'UF_SERVICE_NAME'=>Array(
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_SERVICE_NAME',
                'USER_TYPE_ID' => 'string',
                'MANDATORY' => 'N',
                "EDIT_FORM_LABEL" => Array('ru'=>'Название сервиса', 'en'=>'Service name'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Название сервиса', 'en'=>'Service name'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Название сервиса', 'en'=>'Service name'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),
            'UF_ERROR_DESC'=>Array(
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_ERROR_DESC',
                'USER_TYPE_ID' => 'string',
                'MANDATORY' => 'N',
                "EDIT_FORM_LABEL" => Array('ru'=>'Описание ошибки', 'en'=>'Error description'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Описание ошибки', 'en'=>'Error description'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Описание ошибки', 'en'=>'Error description'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),
        );

        return $arCartFields;
    }
}