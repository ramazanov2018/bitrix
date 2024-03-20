<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Localization\Loc as Loc,
    Bitrix\Main\Engine\Contract\Controllerable,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Highloadblock as HL,
    Bitrix\Main\Entity;

class ExamResults  extends CBitrixComponent implements Controllerable
{

    protected $ip;
    static protected $DataClass;

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
            $params['NAW_COUNT'] = 10;
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

    private function getIblockElementExamResults($filter): array
    {
        $arResult = [];
        Loader::includeModule("iblock");

        $obj = \Bitrix\Iblock\Elements\ElementResultsTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'EXAM_LOCATION' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.EXAM_LOCATION.VALUE',
                'EXAM_LEVEL' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.EXAM_LEVEL.VALUE',
                'EXAM_DATE' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.EXAM_DATE.VALUE',
                'EXAM_RESULT_' => 'EXAM_RESULT'
            ],
            'order' => ['sort' => 'ASC'],
            'filter' =>  $filter,
        ]);

        while ($row = $obj->fetch()) {
            $arResult[$row['ID']] = $row;
        }

        return $arResult;
    }

    private function getIblockElementExamSchedule($filter): array
    {

        $arExamResult = [];
        Loader::includeModule("iblock");
        Loader::includeModule("highloadblock");

        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => 'b_hlb_institutions')))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClassLocation = $entity->getDataClass();

        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => 'b_hlb_regions')))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClassRegion = $entity->getDataClass();

        //Результаты экзаменов
        $obj = \Bitrix\Iblock\Elements\ElementResultsTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'EXAM_ID' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.ID',
                'EXAM_LOCATION' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.EXAM_LOCATION.VALUE',
                'EXAM_LEVEL' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.EXAM_LEVEL.VALUE',
                'EXAM_DATE' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.EXAM_DATE.VALUE',
                'EXAM_RESULT_' => 'EXAM_RESULT',
                'LOCATION_NAME' => 'PROPERTY_LOCATION.UF_NAME',
                'REGION_ID' => 'PROPERTY_LOCATION.UF_REGION',
                'REGION_NAME' => 'PROPERTY_REGION.UF_NAME',
            ],
            'order' => ['sort' => 'ASC'],
            'filter' =>  $filter,
            'runtime' => [
                'PROPERTY_LOCATION' => [
                    'data_type' => $DataClassLocation,
                    'reference' => [
                        '=this.EXAM_LOCATION' => 'ref.UF_XML_ID',
                    ]
                ],
                'PROPERTY_REGION' => [
                    'data_type' => $DataClassRegion,
                    'reference' => [
                        '=this.REGION_ID' => 'ref.ID',
                    ]
                ],
            ],
        ]);

        while ($row = $obj->fetch()) {
            $row['QUANTITY'] = $arExamResult[$row['EXAM_ID']]['QUANTITY'];
            $arExamResult[$row['EXAM_ID']] = $row;
            $arExamResult[$row['EXAM_ID']]['QUANTITY'] = $arExamResult[$row['EXAM_ID']]['QUANTITY'] + 1;
        }

        $this->arResult['NAW'] = new \Bitrix\Main\UI\PageNavigation("exam-result-naw");
        $this->arResult['NAW']->allowAllRecords(true)
            ->setPageSize($this->arParams['NAW_COUNT'])
            ->initFromUri();
        $arrExamList = [];
        //Расписание экзаменов
        $obj = \Bitrix\Iblock\Elements\ElementsCheduleTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
            ],
            "count_total" => true,
            'order' => ['sort' => 'ASC'],
            'filter' =>  ['ID' => array_keys($arExamResult)],
            "offset" => $this->arResult['NAW']->getOffset(),
            "limit" => $this->arResult['NAW']->getLimit(),
        ]);

        $this->arResult['NAW']->setRecordCount($obj->getCount());
        while ($row = $obj->fetch()) {
            $arrExamList[$row['ID']] = $arExamResult[$row['ID']];
        }

        return $arrExamList;
    }

    protected function validateDate($date, $format = 'd.m.Y'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * получение результатов
     */
    protected function getResult()
    {
        $this->arResult['REGION'] = $this->getHLTable('b_hlb_regions');
        $this->arResult['EXAM_RESULT'] = $this->getHLTable('b_hlbd_result_exam');
        $this->arResult['TYPE_EXAM'] = $this->getExamTypeAction();


        $context = Context::getCurrent();
        $request = $context->getRequest();
        if($request->getQuery("reg")){
            $param['id'] = $request->getQuery("reg");
            $this->arResult['LOCATIONS'] = $this->getLocationsAction($param);
        };
        $params = $request->getQueryList()->toArray();
        $filter = ['REFUSAL.VALUE' => false];
        foreach($params as $name => $param )
        {
            if(empty($param)) continue;
            switch ($name) {
                case 'reg':
                    $filter['REGION_ID'] = $param;
                    continue;
                case 'loc':
                    $filter['EXAM_LOCATION'] = $param;
                    continue;
                case 'type':
                    $filter['EXAM_LEVEL'] = $param;
                    continue;
                case 'res':
                    $arParam = explode('_', $param);
                    $filter['>=EXAM_RESULT.VALUE'] = $arParam[0];
                    $filter['<=EXAM_RESULT.VALUE'] = $arParam[1];
                    continue;
                case 'date_from':
                    $this->validateDate($param);
                    $filter['>=EXAM_DATE'] = $this->ChangeDateFormat($param);
                    continue;
                case 'date_to':
                    $filter['<=EXAM_DATE'] = $this->ChangeDateFormat($param);
                    continue;
            }
        }
        if($filter && $params['set_filter_result'] == "Y"){
            $arExamResults = $this->getIblockElementExamSchedule($filter);
            foreach($arExamResults as $ID => $row){

                foreach($this->arResult['EXAM_RESULT'] as $exResult){
                    $arRangeResult = explode('_', $exResult['UF_XML_ID']);
                    if($row['EXAM_RESULT_VALUE'] >= $arRangeResult[0] && $row['EXAM_RESULT_VALUE'] <= $arRangeResult[1]){
                        $row['EXAM_RESULT'] = $exResult['UF_NAME'];
                    }
                }
                $this->arResult['TABLE'][$ID] = $row;
            }

        }
        $this->arResult['PARAMS'] = $params;
    }

    protected function ChangeDateFormat($date, $format = 'Y-m-d H:i:s')
    {
        if ($date != '' && $this->validateDate($date)) {
            $date = new \DateTime($date);
            return $date->format($format);
        }
        return '';
    }

    public function getLocationsAction($param = [])
    {
        return $this->getHLTable('b_hlb_institutions', ['UF_REGION' => $param['id']]);
    }

    public function getExamTypeAction($param = [])
    {
        return $this->getHLTable('b_hlb_exam_level', []);
    }


    public function configureActions()
    {
        return [
            'editView' => [
                'prefilters' => [
                    // new ActionFilter\Authentication,
                    // new ActionFilter\HttpMethod([
                    //     array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    // ])
                ],
            ],
        ];
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
