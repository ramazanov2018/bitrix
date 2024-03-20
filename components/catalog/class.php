<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Localization\Loc as Loc,
    Bitrix\Highloadblock as HL,
    Bitrix\Main\Type\DateTime as BxDateTime;
class ProductCatalog  extends CBitrixComponent
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
        if ($params['NAW_COUNT'] <= 0)
            $params['NAW_COUNT'] = 25;
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

    private function getCatalogData($filter = array(), $order = array()): array
    {

        $arResult = [];
        if(isset($filter['PRODUCT']) && $filter['PRODUCT'] != ''){
            $filter["ID"] = $this->getCompanyByProducts($filter['PRODUCT']);
            if (count($filter["ID"]) <= 0)
                return $arResult;
        }

        $compIndustry = $this->getCompanyIndustrial();
        unset($filter['PRODUCT']);

        Loader::includeModule("highloadblock");
        $this->arResult['NAW'] = new \Bitrix\Main\UI\PageNavigation("exam-result-naw");
        $this->arResult['NAW']->allowAllRecords(true)
            ->setPageSize($this->arParams['NAW_COUNT'])
            ->initFromUri();

        $obj = \Bitrix\Iblock\Elements\ElementCompaniesTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'NAME',
                'SHOW_ELEMENT_VALUE' => 'SHOW_ELEMENT.VALUE',
                'INN_VALUE' => 'INN.VALUE',
                'RATING_VALUE' => 'RATING.VALUE',
                'REGION_ID' => 'REGION.VALUE',
                'PERCENTAGE_OF_IMPORTS_VALUE' => 'PERCENTAGE_OF_IMPORTS.VALUE',
                'SHOW_ICON_VALUE' => 'SHOW_ICON.VALUE',
            ],
            "count_total" => true,
            'order' => $order,
            "offset" => $this->arResult['NAW']->getOffset(),
            "limit" => $this->arResult['NAW']->getLimit(),
            'filter' =>  $filter,
        ]);
        $this->arResult['NAW']->setRecordCount($obj->getCount());
        while ($row = $obj->fetch()) {
                $row['INN_VALUE'] = (int)$row['INN_VALUE'];
                $row['RATING_VALUE'] = (int)$row['RATING_VALUE'];
                $row['PERCENTAGE_OF_IMPORTS_VALUE'] = (int)$row['PERCENTAGE_OF_IMPORTS_VALUE'];
                $arResult[$row['ID']] = $row;
                $arResult[$row['ID']]['INDUSTRY_NAME'] = implode(', ', $compIndustry[$row['ID']]);
        }

        return $arResult;
    }

    protected function getCompanyByProducts($productName = ''){
        $result = [];
        $approved = CIBlockPropertyEnum::GetList(Array(), Array("IBLOCK_ID"=>GbuceHelpers::getIblockCodeById("request"), "CODE"=>"STATUS", "XML_ID"=>"APPROVED"))->Fetch()["ID"];
        $filter['%NAME'] = $productName;
        $filter['STATUS_VAL'] = $approved;
        //Результаты экзаменов
        $obj = \Bitrix\Iblock\Elements\ElementProductsTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'COMPANY_ID' => 'REQUEST.ELEMENT.MAKER.ELEMENT.ID',
                'STATUS_VAL' => 'REQUEST.ELEMENT.STATUS.VALUE',
            ],
            //'order' => ['sort' => 'ASC'],
            'group' => array('COMPANY_ID'),
            'filter' =>  $filter,
        ]);
        while ($row = $obj->fetch()) {
            $result[] = $row['COMPANY_ID'];
        }
        return $result;
    }

    /**
     * получение результатов
     */
    protected function getResult()
    {
        $this->arResult['REGION'] = $this->getHLTable('b_hlbd_regions');
        $this->arResult['INDUSTRIAL'] = $this->getHLTable('b_hlbd_industry');
        $params = $this->getRequest();
        $arSort[$params['sort']] = $params['order'];
        $filter = $this->FilterData($params);
        $this->arResult['ITEMS'] = $this->getCatalogData($filter, $arSort);
        $this->arResult['PARAMS'] = $params;
    }

    protected function getCompanyIndustrial()
    {
        $arResult = [];
        $obj = \Bitrix\Iblock\Elements\ElementCompaniesTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'INDUSTRY_NAME' => 'INDUSTRY.VALUE',
                'SHOW_ELEMENT_VALUE' => 'SHOW_ELEMENT.VALUE',
            ],
            'filter' => [
                '!INDUSTRY_NAME' => false,
                '!=SHOW_ELEMENT_VALUE' => false
            ],
        ]);
        while ($row = $obj->fetch()) {
            $arResult[$row['ID']][] = $row['INDUSTRY_NAME'];
        }

        return $arResult;
    }

    protected function mb_strtoupper_first($str, $encoding = 'UTF8')
    {
        return
            mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) .
            mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
    }

    protected function FilterData($params = array()):array{
        $filter['!=SHOW_ELEMENT_VALUE'] = false;

        if (!empty($params['name']))
            $filter['%NAME'] = $params['name'];

        if (!empty($params['catalog-product']))
            $filter['PRODUCT'] = $params['catalog-product'];

        if (!empty($params['selectIndustry']))
            $filter['INDUSTRY.VALUE'] = $params['selectIndustry'];

        if (!empty($params['range-percent-lower']))
            $filter['>=PERCENTAGE_OF_IMPORTS_VALUE'] = $params['range-percent-lower'];

        if (!empty($params['range-percent-upper']))
            $filter['<=PERCENTAGE_OF_IMPORTS_VALUE'] = $params['range-percent-upper'];

        if (!empty($params['range-rating-lower']))
            $filter['>=RATING_VALUE'] = $params['range-rating-lower'];

        if (!empty($params['range-rating-upper']))
            $filter['<=RATING_VALUE'] = $params['range-rating-upper'];

        if (!empty($params['selectRegion']))
            $filter['REGION_ID'] = $params['selectRegion'];
        return $filter;
    }

    protected function getRequest():array
    {
        $context = Context::getCurrent();
        $request = $context->getRequest();
        $params = $request->getQueryList()->toArray();
        foreach ($params as $key => $value){
            $params[$key] = trim($value);
        }
        $params['sort'] = $this->checkSort($params['sort']);
        $params['order'] = $this->checkOrder($params['order']);
        $params['range-percent-lower'] = $this->checkLower($params['range-percent-lower']);
        $params['range-percent-upper'] = $this->checkUpper($params['range-percent-upper']);
        $params['range-rating-lower'] = $this->checkLower($params['range-rating-lower']);
        $params['range-rating-upper'] = $this->checkUpper($params['range-rating-upper']);
        return $params;
    }

    protected function checkSort($sort){
        if ($sort == "NAME" || $sort == "RATING_VALUE" || $sort == "PERCENTAGE_OF_IMPORTS_VALUE" || $sort == "INDUSTRY_NAME" )
            return $sort;
        else
            return "RATING_VALUE";
    }
    protected function checkLower($lower){
       return (int)$lower;
    }
    protected function checkUpper($upper){
        $upper = (int)$upper;
        return ($upper <= 0) ? 100 : $upper;
    }

    protected function checkOrder($order){
        if ($order == "asc")
            return $order;
        else
            return "desc";
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
