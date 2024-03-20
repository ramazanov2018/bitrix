<?php
namespace Rns\TestReminder;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;

Loader::IncludeModule('highloadblock');
class HLBlockTestRemind
{
    static $error = '';
    static $HLBlockName = 'TestReminder';
    static $HLBlockTable = 'b_hlb_test_reminder';

    public static function CreateHLBlock()
    {
        $arLangs = Array(
            'ru' => 'Напоминании о тестировании',
            'en' => 'Test Reminder'
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
            return false;
        }

        return true;
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
            'UF_DATE_REMIND'=>Array(
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_DATE_REMIND',
                'USER_TYPE_ID' => 'datetime',
                'MANDATORY' => 'Y',
                'SETTINGS' => array('DEFAULT_VALUE' => array('TYPE' => 'NOW')),
                "EDIT_FORM_LABEL" => Array('ru'=>'Время напоминании', 'en'=>'date remind'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Время напоминании', 'en'=>'date remind'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Время напоминании', 'en'=>'date remind'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),
            'UF_USER_ID'=>Array(
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_USER_ID',
                'USER_TYPE_ID' => 'integer',
                'MANDATORY' => 'Y',
                "EDIT_FORM_LABEL" => Array('ru'=>'ID пользователя', 'en'=>'user id'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'ID пользователя', 'en'=>'user id'),
                "LIST_FILTER_LABEL" => Array('ru'=>'ID пользователя', 'en'=>'user id'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),
        );

        return $arCartFields;
    }
}