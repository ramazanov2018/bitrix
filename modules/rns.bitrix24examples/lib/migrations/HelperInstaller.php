<?php

namespace Rns\Bitrix24Examples\Migrations;

use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CDBResult;
use CIBlock;
use CIBlockProperty;
use CIBlockPropertyEnum;
use CIBlockType;
use CLanguage;
use CUserFieldEnum;
use CUserOptions;
use CUserTypeEntity;
use Exception;

class HelperInstaller
{
    protected $errors;
    protected $titles = [];
    protected $props = [];
    protected $lastIblockId;
    protected $iblock;

    /**
     * @param $hlblockName
     * @return array|false
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getHlblock($hlblockName)
    {
        if (is_array($hlblockName)) {
            $filter = $hlblockName;
        } elseif (is_numeric($hlblockName)) {
            $filter = ['ID' => $hlblockName];
        } else {
            $filter = ['NAME' => $hlblockName];
        }
        $hlblock = HighloadBlockTable::getList(
            [
                'select' => ['*'],
                'filter' => $filter,
            ]
        )->fetch();

        return $hlblock;
    }

    /**
     * @param array $names
     * @param array $langs
     * @param array $fields
     * @return bool|mixed
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function createHighloadBlock(array $names, array $langs, array $fields)
    {
        $hlblock = $this->getHlblock($names['NAME']);
        if ($hlblock) {
            return $hlblock['ID'];
        }

        $result = HighloadBlockTable::add([
            'NAME' => $names['NAME'],
            'TABLE_NAME' => $names['TABLE_NAME']
        ]);

        if ($result->isSuccess()) {
            $id = $result->getId();
            foreach($langs as $langKey => $langVal){
                HighloadBlockLangTable::add([
                    'ID' => $id,
                    'LID' => $langKey,
                    'NAME' => $langVal
                ]);
            }
        } else {
            $this->errors = $result->getErrorMessages();
            return false;
        }

        $entityId = 'HLBLOCK_' . $id;

        foreach ($fields as $field) {
            $field['ENTITY_ID'] = $entityId;
            $this->saveUserTypeEntity($field);
        }

        return true;
    }

    /**
     * метод инициации хлблоков
     * @return bool|void
     * @throws LoaderException
     */
    public function initHighloadBlockData(){
        if(!Loader::IncludeModule('highloadblock')) return false;

        $this->initHlbUserBirthdays();

        return true;
    }

    /**
     * Собираем данные для добавления ХЛБ
     * @throws SystemException
     */

    protected function initHlbUserBirthdays(){
        $names = [
            'NAME' => 'UserBirthdays',
            'TABLE_NAME' => 'b_hlb_user_info',
        ];
        $langs = [
            'ru' => 'Дни рождения сотрудников',
            'en' => 'User Birthdays'
        ];

        $fields = Array(
            'UF_XML_ID'=>array (
                'FIELD_NAME' => 'UF_XML_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'XML_ID',
                'SORT' => '100',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'S',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    array (
                        'SIZE' => 16,
                        'ROWS' => 1,
                        'REGEXP' => '',
                        'MIN_LENGTH' => 0,
                        'MAX_LENGTH' => 0,
                        'DEFAULT_VALUE' => '',
                    ),
                'EDIT_FORM_LABEL' =>
                    array (
                        'en' => 'UF_XML_ID',
                        'ru' => 'Внешний код',
                    ),
                'LIST_COLUMN_LABEL' =>
                    array (
                        'en' => 'UF_XML_ID',
                        'ru' => 'Внешний код',
                    ),
                'LIST_FILTER_LABEL' =>
                    array (
                        'en' => 'UF_XML_ID',
                        'ru' => 'Внешний код',
                    ),
                'ERROR_MESSAGE' =>
                    array (
                        'en' => NULL,
                        'ru' => NULL,
                    ),
                'HELP_MESSAGE' =>
                    array (
                        'en' => NULL,
                        'ru' => NULL,
                    ),
            ),
            'UF_DATE_BIRTHDAY'=>Array(
                'FIELD_NAME' => 'UF_DATE_BIRTHDAY',
                'USER_TYPE_ID' => 'date',
                'XML_ID' => '',
                'SORT' => '100',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    array (
                        'DEFAULT_VALUE' =>
                            array (
                                'TYPE' => 'NONE',
                                'VALUE' => '',
                            ),
                    ),
                'EDIT_FORM_LABEL' =>
                    array (
                        'en' => '',
                        'ru' => 'День рождения',
                    ),
                'LIST_COLUMN_LABEL' =>
                    array (
                        'en' => '',
                        'ru' => 'День рождения',
                    ),
                'LIST_FILTER_LABEL' =>
                    array (
                        'en' => '',
                        'ru' => 'День рождения',
                    ),
                'ERROR_MESSAGE' =>
                    array (
                        'en' => '',
                        'ru' => '',
                    ),
                'HELP_MESSAGE' =>
                    array (
                        'en' => '',
                        'ru' => '',
                    ),
            ),
            'UF_USER'=>Array(
                'FIELD_NAME' => 'UF_USER',
                'USER_TYPE_ID' => 'employee',
                'XML_ID' => '',
                'SORT' => '100',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    array (
                    ),
                'EDIT_FORM_LABEL' =>
                    array (
                        'en' => '',
                        'ru' => 'Пользователь',
                    ),
                'LIST_COLUMN_LABEL' =>
                    array (
                        'en' => '',
                        'ru' => 'Пользователь',
                    ),
                'LIST_FILTER_LABEL' =>
                    array (
                        'en' => '',
                        'ru' => 'Пользователь',
                    ),
                'ERROR_MESSAGE' =>
                    array (
                        'en' => '',
                        'ru' => '',
                    ),
                'HELP_MESSAGE' =>
                    array (
                        'en' => '',
                        'ru' => '',
                    ),
            ),
            'UF_ADDRESS_CITY'=>Array(
                'FIELD_NAME' => 'UF_ADDRESS_CITY',
                'USER_TYPE_ID' => 'enumeration',
                'XML_ID' => '',
                'SORT' => '100',
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' =>
                    array (
                        'DISPLAY' => 'LIST',
                        'LIST_HEIGHT' => 1,
                        'CAPTION_NO_VALUE' => '',
                        'SHOW_NO_VALUE' => 'Y',
                    ),
                'EDIT_FORM_LABEL' =>
                    array (
                        'en' => '',
                        'ru' => 'Город',
                    ),
                'LIST_COLUMN_LABEL' =>
                    array (
                        'en' => '',
                        'ru' => 'Город',
                    ),
                'LIST_FILTER_LABEL' =>
                    array (
                        'en' => '',
                        'ru' => 'Город',
                    ),
                'ERROR_MESSAGE' =>
                    array (
                        'en' => '',
                        'ru' => '',
                    ),
                'HELP_MESSAGE' =>
                    array (
                        'en' => '',
                        'ru' => '',
                    ),
                'ENUM_VALUES' =>
                    array (
                        0 =>
                            array (
                                'VALUE' => 'Москва',
                                'DEF' => 'N',
                                'SORT' => '500',
                                'XML_ID' => 'moscow',
                            ),
                        1 =>
                            array (
                                'VALUE' => 'Санкт-Петербург',
                                'DEF' => 'N',
                                'SORT' => '500',
                                'XML_ID' => 'saint-petersburg',
                            ),
                    ),
            )
        );

        $hlb = $this->createHighloadBlock($names, $langs, $fields);
        if(is_bool($hlb)) {
            $this->executeXML(__DIR__ . '/xml/'.$names['NAME'].'.xml', $names['NAME']);
        }
    }

    /**
     * парсинг XML файлов
     * @param $filePath
     * @param $hlbName
     * @throws Exception
     */
    protected function executeXML($filePath, $hlbName)
    {
        if(!is_file($filePath))
            return true;

        $xml = simplexml_load_file($filePath);
        $arFields = [];
        $arElements = [];
        foreach ($xml->items->item as $item) {
            $arFields[] = $item;
        }
        foreach ($arFields as $arFieldsKey => $arFieldsValue) {
            foreach ($arFieldsValue as $fieldKey => $fieldValue) {
                if ($fieldKey != 'id') {
                    $arElements[$arFieldsKey][mb_strtoupper($fieldKey)] = current($fieldValue);
                }
            }
        }
        $this->addHlbElements($hlbName, $arElements);
    }

    /**
     * Добавление элементов в ХЛБ
     * @param $hlbName
     * @param $arElements
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function addHlbElements($hlbName, $arElements){
        if(!Loader::IncludeModule('highloadblock')) return false;

        $hlblock = HighloadBlockTable::getList(['filter' => ['NAME' => $hlbName]])->fetch();
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();
        if($entity_data_class){
            foreach($arElements as $element){
                $result = $entity_data_class::add($element);
            }
        }
    }

    /**
     * @param $arHighloadBlocks
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function uninstallHighloadBlocks($arHighloadBlocks){
        if(!Loader::IncludeModule('highloadblock')) return false;
        if(is_array($arHighloadBlocks)){
            foreach ($arHighloadBlocks as $highloadBlockName){
                if(!$this->deleteHighloadBlock($highloadBlockName))
                    return false;
            }
        }
        return true;
    }

    /**
     * @param $hlblockName
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws Exception
     */
    public function deleteHighloadBlock($hlblockName)
    {
        $item = $this->getHlblock($hlblockName);
        if (!$item) {
            return false;
        }

        $result = HighloadBlockTable::delete($item['ID']);
        if ($result->isSuccess()) {
            return true;
        }

        $this->errors = $result->getErrorMessages();
        return false;
    }

    /**
     * @param array $fields
     * @return mixed
     * @throws Exception
     */
    public function addInfoBlockType(array $fields)
    {
        $item = $this->getIblockType($fields['ID']);
        if (!empty($item)) {
            return $fields['ID'];
        }

        $default = [
            'ID' => '',
            'SECTIONS' => 'Y',
            'IN_RSS' => 'N',
            'SORT' => 100,
            'LANG' => [
                'ru' => [
                    'NAME' => 'Catalog',
                    'SECTION_NAME' => 'Sections',
                    'ELEMENT_NAME' => 'Elements',
                ],
                'en' => [
                    'NAME' => 'Catalog',
                    'SECTION_NAME' => 'Sections',
                    'ELEMENT_NAME' => 'Elements',
                ],
            ],
        ];

        $fields = array_replace_recursive($default, $fields);

        $ib = new CIBlockType;
        if ($ib->Add($fields)) {
            return $fields['ID'];
        }

        throw new \Exception($ib->LAST_ERROR);
    }

    /**
     * @param $typeId
     * @return array
     */
    public function getIblockType($typeId)
    {
        /** @compatibility filter or $typeId */
        $filter = is_array($typeId) ? $typeId : [
            '=ID' => $typeId,
        ];

        $filter['CHECK_PERMISSIONS'] = 'N';
        $item = CIBlockType::GetList(['SORT' => 'ASC'], $filter)->Fetch();

        if ($item) {
            $item['LANG'] = $this->getIblockTypeLangs($item['ID']);
        }

        return $item;
    }

    /**
     * @param $typeId
     * @return array
     */
    public function getIblockTypeLangs($typeId)
    {
        $result = [];
        $dbres = CLanguage::GetList($lby = 'sort', $lorder = 'asc');
        while ($item = $dbres->GetNext()) {
            $values = CIBlockType::GetByIDLang($typeId, $item['LID'], false);
            if (!empty($values)) {
                $result[$item['LID']] = [
                    'NAME' => $values['NAME'],
                    'SECTION_NAME' => $values['SECTION_NAME'],
                    'ELEMENT_NAME' => $values['ELEMENT_NAME'],
                ];
            }
        }
        return $result;
    }

    /**
     * @param array $fields
     * @return bool
     * @throws Exception
     */
    public function addInfoBlock(array $fields)
    {
        $default = [
            'ACTIVE'           => 'Y',
            'NAME'             => '',
            'CODE'             => '',
            'LIST_PAGE_URL'    => '',
            'DETAIL_PAGE_URL'  => '',
            'SECTION_PAGE_URL' => '',
            'IBLOCK_TYPE_ID'   => 'main',
            'LID'              => ['s1'],
            'SORT'             => 500,
            'GROUP_ID'         => ['2' => 'R'],
            'VERSION'          => 2,
            'BIZPROC'          => 'N',
            'WORKFLOW'         => 'N',
            'INDEX_ELEMENT'    => 'N',
            'INDEX_SECTION'    => 'N',
        ];

        $fields = array_replace_recursive($default, $fields);

        $ib = new CIBlock;
        $iblockId = $ib->Add($fields);

        if ($iblockId) {
            return $iblockId;
        }

        throw new \Exception($ib->LAST_ERROR);
    }

    /**
     * @param $iblockId
     * @param array $fields
     * @return bool
     */
    public function saveIblockFields($iblockId, array $fields = [])
    {
        if ($iblockId && !empty($fields)) {
            CIBlock::SetFields($iblockId, $fields);
            return true;
        }
        return false;
    }

    /**
     * @param $iblockId
     * @param array $permissions
     */
    public function saveGroupPermissions($iblockId, array $permissions = [])
    {
        $groupHelper = new UserGroupHelper();

        $result = [];
        foreach ($permissions as $groupCode => $letter) {
            $groupId = is_numeric($groupCode) ? $groupCode : $groupHelper->getGroupId($groupCode);
            $result[$groupId] = $letter;
        }

        $this->setGroupPermissions($iblockId, $result);
    }

    /**
     * @param $iblockId
     * @param array $permissions
     */
    public function setGroupPermissions($iblockId, array $permissions = [])
    {
        CIBlock::SetPermission($iblockId, $permissions);
    }

    /**
     * @param $iblockId
     * @param $fields
     * @return bool
     * @throws Exception
     */
    public function addProperty($iblockId, $fields)
    {
        $default = [
            'NAME'           => '',
            'ACTIVE'         => 'Y',
            'SORT'           => '500',
            'CODE'           => '',
            'PROPERTY_TYPE'  => 'S',
            'USER_TYPE'      => '',
            'ROW_COUNT'      => '1',
            'COL_COUNT'      => '30',
            'LIST_TYPE'      => 'L',
            'MULTIPLE'       => 'N',
            'IS_REQUIRED'    => 'N',
            'FILTRABLE'      => 'Y',
            'LINK_IBLOCK_ID' => 0,
        ];

        if (!empty($fields['VALUES'])) {
            $default['PROPERTY_TYPE'] = 'L';
        }

        if (!empty($fields['LINK_IBLOCK_ID'])) {
            $default['PROPERTY_TYPE'] = 'E';
        }

        $fields = array_replace_recursive($default, $fields);

        if (false !== strpos($fields['PROPERTY_TYPE'], ':')) {
            list($ptype, $utype) = explode(':', $fields['PROPERTY_TYPE']);
            $fields['PROPERTY_TYPE'] = $ptype;
            $fields['USER_TYPE'] = $utype;
        }

        if (false !== strpos($fields['LINK_IBLOCK_ID'], ':')) {
            $fields['LINK_IBLOCK_ID'] = $this->getIblockIdByUid($fields['LINK_IBLOCK_ID']);
        }

        $fields['IBLOCK_ID'] = $iblockId;

        $ib = new CIBlockProperty;
        $propertyId = $ib->Add($fields);

        if ($propertyId) {
            return $propertyId;
        }

        throw new \Exception($ib->LAST_ERROR);
    }

    /**
     * @param $iblockUid
     * @return int
     */
    public function getIblockIdByUid($iblockUid)
    {
        $iblockId = 0;

        if (empty($iblockUid)) {
            return $iblockId;
        }

        list($type, $code) = explode(':', $iblockUid);
        if (!empty($type) && !empty($code)) {
            $iblockId = $this->getIblockId($code, $type);
        }

        return $iblockId;
    }

    /**
     * @param $code
     * @param string $typeId
     * @return int
     */
    public function getIblockId($code, $typeId = '')
    {
        $iblock = $this->getIblock($code, $typeId);
        return ($iblock && isset($iblock['ID'])) ? $iblock['ID'] : 0;
    }

    /**
     * @param $code
     * @param string $typeId
     * @return array
     */
    public function getIblock($code, $typeId = '')
    {
        if (is_array($code)) {
            $filter = $code;
        } elseif (is_numeric($code)) {
            $filter = ['ID' => $code];
        } else {
            $filter = ['=CODE' => $code];
        }

        if (!empty($typeId)) {
            $filter['=TYPE'] = $typeId;
        }

        $filter['CHECK_PERMISSIONS'] = 'N';

        $item = CIBlock::GetList(['SORT' => 'ASC'], $filter)->Fetch();
        return $this->prepareIblock($item);
    }

    /**
     * @param $item
     * @return array
     */
    protected function prepareIblock($item)
    {
        if (empty($item['ID'])) {
            return $item;
        }
        $item['LID'] = $this->getIblockSites($item['ID']);

        $messages = CIBlock::GetMessages($item['ID']);
        $item = array_merge($item, $messages);
        return $item;
    }

    /**
     * @param $iblockId
     * @return array
     */
    public function getIblockSites($iblockId)
    {
        $dbres = CIBlock::GetSite($iblockId);
        return $this->fetchAll($dbres, false, 'LID');
    }

    /**
     * @param CDBResult $dbres
     * @param bool $indexKey
     * @param bool $valueKey
     * @return array
     */
    protected function fetchAll(CDBResult $dbres, $indexKey = false, $valueKey = false)
    {
        $res = [];

        while ($item = $dbres->Fetch()) {
            if ($valueKey) {
                $value = $item[$valueKey];
            } else {
                $value = $item;
            }

            if ($indexKey) {
                $indexVal = $item[$indexKey];
                $res[$indexVal] = $value;
            } else {
                $res[] = $value;
            }
        }
        return $res;
    }

    /**
     * @param $iblockId
     * @param array $formData
     * @return bool
     * @throws Exception
     */
    public function addElementForm($iblockId, array $formData = [])
    {
        $this->initializeIblockVars($iblockId);

        return $this->buildForm($formData, [
            'name' => 'form_element_' . $iblockId,
        ]);
    }

    /**
     * @param $iblockId
     * @return bool
     * @throws Exception
     */
    protected function initializeIblockVars($iblockId)
    {
        $iblock = $this->getIblockIfExists($iblockId);

        $this->lastIblockId = $iblockId;
        $this->iblock = $iblock;
        $this->props = [];
        $this->titles = [];

        $props = $this->getProperties($iblockId);
        foreach ($props as $prop) {
            if (!empty($prop['CODE'])) {
                $this->titles['PROPERTY_' . $prop['ID']] = $prop['NAME'];
                $this->props[] = $prop;
            }
        }

        $iblockMess = IncludeModuleLangFile('/bitrix/modules/iblock/iblock.php', 'ru', true);

        $this->titles['ACTIVE_FROM'] = $iblockMess['IBLOCK_FIELD_ACTIVE_PERIOD_FROM'];
        $this->titles['ACTIVE_TO'] = $iblockMess['IBLOCK_FIELD_ACTIVE_PERIOD_TO'];

        foreach ($iblockMess as $code => $value) {
            if (false !== strpos($code, 'IBLOCK_FIELD_')) {
                $fcode = str_replace('IBLOCK_FIELD_', '', $code);
                $this->titles[$fcode] = $value;
            }
        }

        return true;
    }

    /**
     * @param $code
     * @param string $typeId
     * @return array
     * @throws Exception
     */
    public function getIblockIfExists($code, $typeId = '')
    {
        $item = $this->getIblock($code, $typeId);
        if ($item && isset($item['ID'])) {
            return $item;
        }
        throw new \Exception('ERR_IB_NOT_FOUND');
    }

    /**
     * @param $iblockId
     * @param array $filter
     * @return array
     */
    public function getProperties($iblockId, array $filter = [])
    {
        $filter['IBLOCK_ID'] = $iblockId;
        $filter['CHECK_PERMISSIONS'] = 'N';

        $filterIds = false;
        if (isset($filter['ID']) && is_array($filter['ID'])) {
            $filterIds = $filter['ID'];
            unset($filter['ID']);
        }

        $dbres = CIBlockProperty::GetList(['SORT' => 'ASC'], $filter);

        $result = [];

        while ($property = $dbres->Fetch()) {
            if ($filterIds) {
                if (in_array($property['ID'], $filterIds)) {
                    $result[] = $this->prepareProperty($property);
                }
            } else {
                $result[] = $this->prepareProperty($property);
            }
        }
        return $result;
    }

    /**
     * @param $property
     * @return mixed
     */
    protected function prepareProperty($property)
    {
        if ($property && $property['PROPERTY_TYPE'] == 'L' && $property['IBLOCK_ID'] && $property['ID']) {
            $property['VALUES'] = $this->getPropertyEnums(
                [
                    'IBLOCK_ID'   => $property['IBLOCK_ID'],
                    'PROPERTY_ID' => $property['ID'],
                ]
            );
        }
        return $property;
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getPropertyEnums(array $filter = [])
    {
        $result = [];
        $dbres = CIBlockPropertyEnum::GetList(
            [
                'SORT'  => 'ASC',
                'VALUE' => 'ASC',
            ], $filter
        );
        while ($item = $dbres->Fetch()) {
            $result[] = $item;
        }
        return $result;
    }

    /**
     * @param array $formData
     * @param array $params
     * @return bool
     */
    public function buildForm(array $formData = [], array $params = [])
    {
        $params = array_merge(
            [
                'name' => '',
                'category' => 'form',
            ],
            $params
        );

        if (empty($formData)) {
            CUserOptions::DeleteOptionsByName(
                $params['category'],
                $params['name']
            );
            return true;
        }

        $tabIndex = 0;
        $tabVals = [];

        foreach ($formData as $tabTitle => $fields) {
            list($tabTitle, $tabId) = explode('|', $tabTitle);

            if (!$tabId) {
                $tabId = 'edit' . ($tabIndex + 1);
            }

            $tabId = ($tabIndex == 0) ? $tabId : '--' . $tabId;

            $tabVals[$tabIndex][] = $tabId . '--#--' . $tabTitle . '--';

            foreach ($fields as $fieldKey => $fieldValue) {
                if (is_numeric($fieldKey)) {
                    /** @compability */
                    list($fcode, $ftitle) = explode('|', $fieldValue);
                } else {
                    $fcode = $fieldKey;
                    $ftitle = $fieldValue;
                }

                $fcode = $this->transformCode($fcode);
                $ftitle = $this->prepareTitle($fcode, $ftitle);

                $tabVals[$tabIndex][] = '--' . $fcode . '--#--' . $ftitle . '--';
            }

            $tabIndex++;
        }

        $opts = [];
        foreach ($tabVals as $fields) {
            $opts[] = implode(',', $fields);
        }

        $opts = implode(';', $opts) . ';--';

        $value = [
            'tabs' => $opts,
        ];

        CUserOptions::DeleteOptionsByName(
            $params['category'],
            $params['name']
        );
        CUserOptions::SetOption(
            $params['category'],
            $params['name'],
            $value,
            true
        );

        return true;
    }

    /**
     * @param $fieldCode
     * @return false|mixed|string
     */
    protected function transformCode($fieldCode)
    {
        if (0 === strpos($fieldCode, 'PROPERTY_')) {
            $fieldCode = substr($fieldCode, 9);
            foreach ($this->props as $prop) {
                if ($prop['CODE'] == $fieldCode) {
                    $fieldCode = $prop['ID'];
                    break;
                }
            }
            $fieldCode = 'PROPERTY_' . $fieldCode;
        }
        return $fieldCode;
    }

    /**
     * @param $fieldCode
     * @param string $fieldTitle
     * @return mixed|string
     */
    protected function prepareTitle($fieldCode, $fieldTitle = '')
    {
        if (!empty($fieldTitle)) {
            return $fieldTitle;
        }

        if (isset($this->titles[$fieldCode])) {
            return $this->titles[$fieldCode];
        }

        return $fieldCode;
    }

    /**
     * @param $iblockId
     * @param array $formData
     * @return bool
     * @throws Exception
     */
    public function addSectionForm($iblockId, array $formData = [])
    {
        $this->initializeIblockVars($iblockId);

        return $this->buildForm($formData, [
            'name' => 'form_section_' . $iblockId,
        ]);
    }

    /**
     * @param $iblockId
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function addElementGrid($iblockId, array $params = [])
    {
        return $this->buildGrid($this->getSectionGridId($iblockId), $params);
    }

    /**
     * @param $iblockId
     * @return string
     * @throws Exception
     */
    public function getSectionGridId($iblockId)
    {
        $this->initializeIblockVars($iblockId);
        return 'tbl_iblock_section_' . md5($this->iblock['IBLOCK_TYPE_ID'] . "." . $iblockId);
    }

    /**
     * @param $gridId
     * @param array $options
     * @return bool
     */
    public function buildGrid($gridId, array $options = [])
    {
        foreach ($options['views'] as $viewCode => $view) {
            $view['columns'] = $this->transformCodesToColumns($view['columns']);
            $options['views'][$viewCode] = $view;
        }

        CUserOptions::DeleteOptionsByName(
            'main.interface.grid',
            $gridId
        );
        CUserOptions::setOption(
            "main.interface.grid",
            $gridId,
            $options,
            true
        );

        return true;
    }

    /**
     * @param $columns
     * @return array|string
     */
    protected function transformCodesToColumns($columns)
    {
        if (is_array($columns)) {
            foreach ($columns as $index => $columnCode) {
                $columns[$index] = $this->transformCode($columnCode);
            }
            return implode(',', $columns);
        }
        return $columns;
    }

    /**
     * @param $iblockId
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws Exception
     */
    public function getElementGridId($iblockId)
    {
        $this->initializeIblockVars($iblockId);

        if (CIBlock::GetAdminListMode($iblockId) == 'S') {
            $prefix = defined('CATALOG_PRODUCT') ? 'tbl_product_admin_' : 'tbl_iblock_element_';
        } else {
            $prefix = defined('CATALOG_PRODUCT') ? 'tbl_product_list_' : 'tbl_iblock_list_';
        }

        return $prefix . md5($this->iblock['IBLOCK_TYPE_ID'] . '.' . $iblockId);
    }

    /**
     * @param $code
     * @param string $typeId
     * @return bool
     */
    public function deleteIblockIfExists($code, $typeId = '')
    {
        $iblock = $this->getIblock($code, $typeId);
        if (!$iblock) {
            return false;
        }
        return $this->deleteIblock($iblock['ID']);
    }

    /**
     * @param $iblockId
     * @return bool
     */
    public function deleteIblock($iblockId)
    {
        if (CIBlock::Delete($iblockId)) {
            return true;
        }

        return false;
    }

    /**
     * @param array $fields
     * @return bool|int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function saveUserTypeEntity($fields = [])
    {
        return $this->addUserTypeEntity(
            $fields['ENTITY_ID'],
            $fields['FIELD_NAME'],
            $fields
        );
    }
    /**
     * @param $entityId
     * @param $fieldName
     * @param $fields
     * @return bool|int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function addUserTypeEntity($entityId, $fieldName, $fields)
    {
        $default = [
            "ENTITY_ID"         => '',
            "FIELD_NAME"        => '',
            "USER_TYPE_ID"      => '',
            "XML_ID"            => '',
            "SORT"              => 500,
            "MULTIPLE"          => 'N',
            "MANDATORY"         => 'N',
            "SHOW_FILTER"       => 'I',
            "SHOW_IN_LIST"      => '',
            "EDIT_IN_LIST"      => '',
            "IS_SEARCHABLE"     => '',
            "SETTINGS"          => [],
            "EDIT_FORM_LABEL"   => ['ru' => '', 'en' => ''],
            "LIST_COLUMN_LABEL" => ['ru' => '', 'en' => ''],
            "LIST_FILTER_LABEL" => ['ru' => '', 'en' => ''],
            "ERROR_MESSAGE"     => '',
            "HELP_MESSAGE"      => '',
        ];

        $fields = array_replace_recursive($default, $fields);
        $fields['FIELD_NAME'] = $fieldName;
        $fields['ENTITY_ID'] = $this->revertEntityId($entityId);

        $this->revertSettings($fields);
        $enums = $this->revertEnums($fields);

        $obUserField = new CUserTypeEntity;
        $userFieldId = $obUserField->Add($fields);

        $enumsCreated = true;
        if ($userFieldId && $fields['USER_TYPE_ID'] == 'enumeration') {
            $enumsCreated = $this->setUserTypeEntityEnumValues($userFieldId, $enums);
        }

        if ($userFieldId && $enumsCreated) {
            return $userFieldId;
        }

        return false;
    }

    /**
     * @param $entityId
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function revertEntityId($entityId)
    {
        if (0 === strpos($entityId, 'HLBLOCK_')) {
            $hlblockId = substr($entityId, 8);
            if (!is_numeric($hlblockId)) {
                $hlblockId = $this->getHlblockIdByUid($hlblockId);
            }
            return 'HLBLOCK_' . $hlblockId;
        }

        $matches = [];
        if (preg_match('/^IBLOCK_(.+)_SECTION$/', $entityId, $matches)) {
            $iblockId = $matches[1];
            if (!is_numeric($iblockId)) {
                $iblockId = $this->getIblockIdByUid($iblockId);
            }
            return 'IBLOCK_' . $iblockId . '_SECTION';
        }

        return $entityId;
    }

    /**
     * @param $hlblockUid
     * @return int|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getHlblockIdByUid($hlblockUid)
    {
        $hlblockId = 0;
        if (empty($hlblockUid)) {
            return $hlblockId;
        }

        return $this->getHlblockId($hlblockUid);
    }

    /**
     * @param $hlblockName
     * @return int|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getHlblockId($hlblockName)
    {
        $item = $this->getHlblock($hlblockName);
        return ($item && isset($item['ID'])) ? $item['ID'] : 0;
    }

    /**
     * @param $fields
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function revertSettings(&$fields)
    {
        if ($fields['USER_TYPE_ID'] == 'iblock_element') {
            if (!empty($fields['SETTINGS']['IBLOCK_ID'])) {
                $fields['SETTINGS']['IBLOCK_ID'] = $this->getIblockIdByUid(
                    $fields['SETTINGS']['IBLOCK_ID']
                );
            }
        }
        if ($fields['USER_TYPE_ID'] == 'hlblock') {
            if (!empty($fields['SETTINGS']['HLBLOCK_ID'])) {
                $fields['SETTINGS']['HLBLOCK_ID'] = $this->getHlblockIdByUid(
                    $fields['SETTINGS']['HLBLOCK_ID']
                );
                if (!empty($fields['SETTINGS']['HLFIELD_ID'])) {
                    $fields['SETTINGS']['HLFIELD_ID'] = $this->getFieldIdByUid(
                        $fields['SETTINGS']['HLBLOCK_ID'],
                        $fields['SETTINGS']['HLFIELD_ID']
                    );
                }
            }
        }
    }

    /**
     * @param $hlblockName
     * @param $fieldUid
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getFieldIdByUid($hlblockName, $fieldUid)
    {
        $fieldId = 0;

        if (empty($fieldUid)) {
            return $fieldId;
        }

        if (is_numeric($fieldUid)) {
            return $fieldUid;
        }

        $field = $this->getField($hlblockName, $fieldUid);

        return ($field) ? (int)$field['ID'] : 0;
    }

    /**
     * @param $hlblockName
     * @param $fieldName
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getField($hlblockName, $fieldName)
    {
        return $this->getUserTypeEntity(
            $this->getEntityId($hlblockName),
            $fieldName
        );
    }

    /**
     * @param $entityId
     * @param $fieldName
     * @return bool
     */
    public function getUserTypeEntity($entityId, $fieldName)
    {
        $item = CUserTypeEntity::GetList(
            [],
            [
                'ENTITY_ID'  => $entityId,
                'FIELD_NAME' => $fieldName,
            ]
        )->Fetch();

        return (!empty($item)) ? $this->getUserTypeEntityById($item['ID']) : false;
    }

    /**
     * @param $hlblockName
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getEntityId($hlblockName)
    {
        $hlblockId = is_numeric($hlblockName) ? $hlblockName : $this->getHlblockId($hlblockName);
        return 'HLBLOCK_' . $hlblockId;
    }

    /**
     * @param $fieldId
     * @return array|bool
     */
    public function getUserTypeEntityById($fieldId)
    {
        $item = CUserTypeEntity::GetByID($fieldId);
        if (empty($item)) {
            return false;
        }

        if ($item['USER_TYPE_ID'] == 'enumeration') {
            $item['ENUM_VALUES'] = $this->getEnumValues($fieldId);
        }

        return $item;
    }

    /**
     * @param $fieldId
     * @return array
     */
    protected function getEnumValues($fieldId)
    {
        $obEnum = new CUserFieldEnum;
        $dbres = $obEnum->GetList([], ["USER_FIELD_ID" => $fieldId]);
        return $this->fetchAll($dbres);
    }

    /**
     * @param $fields
     * @return array|mixed
     */
    private function revertEnums(&$fields)
    {
        $enums = [];
        if (isset($fields['ENUM_VALUES'])) {
            $enums = $fields['ENUM_VALUES'];
            unset($fields['ENUM_VALUES']);
        }

        return $enums;
    }

    /**
     * @param $fieldId
     * @param $newenums
     * @return bool
     */
    public function setUserTypeEntityEnumValues($fieldId, $newenums)
    {
        $newenums = is_array($newenums) ? $newenums : [];
        $oldenums = $this->getEnumValues($fieldId);

        $index = 0;

        $updates = [];
        foreach ($oldenums as $oldenum) {
            $newenum = $this->searchEnum($oldenum, $newenums);
            if ($newenum) {
                $updates[$oldenum['ID']] = $newenum;
            } else {
                $oldenum['DEL'] = 'Y';
                $updates[$oldenum['ID']] = $oldenum;
            }
        }

        foreach ($newenums as $newenum) {
            $oldenum = $this->searchEnum($newenum, $oldenums);
            if ($oldenum) {
                $updates[$oldenum['ID']] = $newenum;
            } else {
                $updates['n' . $index++] = $newenum;
            }
        }

        $obEnum = new CUserFieldEnum();
        return $obEnum->SetEnumValues($fieldId, $updates);
    }

    /**
     * @param $enum
     * @param array $haystack
     * @return bool|mixed
     */
    protected function searchEnum($enum, $haystack = [])
    {
        foreach ($haystack as $item) {
            if (isset($item['XML_ID']) && strlen($item['XML_ID']) > 0 && $item['XML_ID'] == $enum['XML_ID']) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @param $arUserTypeEntities
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function uninstallUserTypeEntity($arUserTypeEntities){
        if(is_array($arUserTypeEntities)){
            foreach ($arUserTypeEntities as $userTypeEntityName => $userTypeEntityId){
                if(!$this->deleteUserTypeEntityIfExists($userTypeEntityId, $userTypeEntityName))
                    return false;
            }
        }
        return true;
    }

    /**
     * @param $entityId
     * @param $fieldName
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function deleteUserTypeEntityIfExists($entityId, $fieldName)
    {
        $item = $this->getUserTypeEntity(
            $this->revertEntityId($entityId),
            $fieldName
        );

        if (empty($item)) {
            return false;
        }

        $entity = new CUserTypeEntity();
        if ($entity->Delete($item['ID'])) {
            return true;
        }
        return false;
    }

}