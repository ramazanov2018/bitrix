<?php
namespace Rns\AccessMatrix;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;

Loader::IncludeModule('highloadblock');
class HLBlockAccessMatrix
{
    static $error = '';
    static $HLBlockName = 'AccessMatrix';
    static $HLBlockTable = 'b_hlb_access_matrix';

    public static function CreateHLBlock()
    {
        $arLangs = Array(
            'ru' => 'Матрица прав',
            'en' => 'Access matrix'
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
            'UF_ACCESS_GROUP_CODE'=>Array(
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_ACCESS_GROUP_CODE',
                'USER_TYPE_ID' => 'string',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'N',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    array (
                        'SIZE' => 20,
                        'ROWS' => 1,
                        'REGEXP' => '',
                        'MIN_LENGTH' => 0,
                        'MAX_LENGTH' => 0,
                        'DEFAULT_VALUE' => '',
                    ),
                "EDIT_FORM_LABEL" => Array('ru'=>'Код группы прав', 'en'=>'access group code'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Код группы прав', 'en'=>'access group code'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Код группы прав', 'en'=>'access group code'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),
            'UF_ACCESS_GROUP_VALUE'=>Array(
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_ACCESS_GROUP_VALUE',
                'USER_TYPE_ID' => 'string',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'N',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    array (
                        'SIZE' => 50,
                        'ROWS' => 5,
                        'REGEXP' => '',
                        'MIN_LENGTH' => 0,
                        'MAX_LENGTH' => 0,
                        'DEFAULT_VALUE' => '',
                    ),
                "EDIT_FORM_LABEL" => Array('ru'=>'Права', 'en'=>'value'),
                "LIST_COLUMN_LABEL" => Array('ru'=>'Права', 'en'=>'value'),
                "LIST_FILTER_LABEL" => Array('ru'=>'Права', 'en'=>'value'),
                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
            ),
            'UF_DATE_UPDATE' =>array (
                'ENTITY_ID' => $UFObject,
                'FIELD_NAME' => 'UF_DATE_UPDATE',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => '',
                'SORT' => '100',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'N',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    array (
                        'DEFAULT_VALUE' =>
                            array (
                                'TYPE' => 'NOW',
                                'VALUE' => '',
                            ),
                        'USE_SECOND' => 'Y',
                        'USE_TIMEZONE' => 'N',
                    ),
                'EDIT_FORM_LABEL' =>
                    array (
                        'br' => '',
                        'en' => '',
                        'fr' => '',
                        'la' => '',
                        'pl' => '',
                        'ru' => 'Дата и вркмя изменения',
                        'ua' => '',
                    ),
                'LIST_COLUMN_LABEL' =>
                    array (
                        'br' => '',
                        'en' => '',
                        'fr' => '',
                        'la' => '',
                        'pl' => '',
                        'ru' => 'Дата и вркмя изменения',
                        'ua' => '',
                    ),
                'LIST_FILTER_LABEL' =>
                    array (
                        'br' => '',
                        'en' => '',
                        'fr' => '',
                        'la' => '',
                        'pl' => '',
                        'ru' => 'Дата и вркмя изменения',
                        'ua' => '',
                    ),
                'ERROR_MESSAGE' =>
                    array (
                        'br' => '',
                        'en' => '',
                        'fr' => '',
                        'la' => '',
                        'pl' => '',
                        'ru' => '',
                        'ua' => '',
                    ),
                'HELP_MESSAGE' =>
                    array (
                        'br' => '',
                        'en' => '',
                        'fr' => '',
                        'la' => '',
                        'pl' => '',
                        'ru' => '',
                        'ua' => '',
                    ),
            )
        );

        return $arCartFields;
    }
}