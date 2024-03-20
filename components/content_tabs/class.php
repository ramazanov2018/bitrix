<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Localization\Loc as Loc;

class ContentTabs  extends CBitrixComponent
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
        return $params;
    }

    /**
     * получение результатов
     */
    protected function getResult()
    {
        Loader::includeModule('iblock');

        global $APPLICATION;
        $url = $APPLICATION->GetCurPage();
        $obj = \Bitrix\Iblock\Elements\ElementBreedingPageTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'NAME', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'DETAIL_TEXT'
            ],
            'order' => ['sort' => 'ASC'],
            'filter' =>  array(["CODE" => $url]),
        ]);

        $item = array();
        if ($row = $obj->fetch()) {
            $item["TITLE"] = $row["NAME"];
            $item["CONTENT"] = $row["DETAIL_TEXT"];
            if ($row["PREVIEW_PICTURE"])
                $item["TOP_IMAGE_URL"] = CFile::GetPath($row["PREVIEW_PICTURE"]);
            if ($row["DETAIL_PICTURE"])
                $item["BOTTOM_IMAGE_URL"] = CFile::GetPath($row["DETAIL_PICTURE"]);
        }
        $this->arResult["ITEM"] = $item;
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
