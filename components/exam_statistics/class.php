<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Localization\Loc as Loc,
    Bitrix\Highloadblock as HL,
    Bitrix\Main\Type\DateTime as BxDateTime;
class ExamStatistics  extends CBitrixComponent
{

    protected $ip;
    protected $StartYear = 1999;
    protected $selectedItem = '';
    protected $arrLastDates = [
        '01' => 31,
        '02' => 28,
        '03' => 31,
        '04' => 30,
        '05' => 31,
        '06' => 30,
        '07' => 31,
        '08' => 31,
        '09' => 30,
        '10' => 31,
        '11' => 30,
        '12' => 31,
    ];

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
        Loader::includeModule("highloadblock");
        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => 'b_hlb_institutions')))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClassLocation = $entity->getDataClass();

        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => 'b_hlb_regions')))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClassRegion = $entity->getDataClass();

        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => 'b_hlb_districts')))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClassDistricts = $entity->getDataClass();

        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => 'b_hlb_exam_level')))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClassLevel = $entity->getDataClass();

        $obj = \Bitrix\Iblock\Elements\ElementResultsTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'EXAM_DATE' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.EXAM_DATE.VALUE',
                'EXAM_SCHEDULE_ID' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.ID',
                'EXAM_WRITING' => 'EXAM_WRITING_GRADE.VALUE',
                'EXAM_READING' => 'EXAM_READING_GRADE.VALUE',
                'EXAM_LISTENING' => 'EXAM_LISTENING_GRADE.VALUE',
                'EXAM_SPEAKING' => 'EXAM_SPEAKING_GRADE.VALUE',
                'LOCATION_ID' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.EXAM_LOCATION.VALUE',
                'LEVEL_ID' => 'EXAM_REGISTRATION.ELEMENT.EXAM_SCHEDULE.ELEMENT.EXAM_LEVEL.VALUE',
                'LEVEL_NAME' => 'PROPERTY_LEVEL.UF_NAME',
                'LOCATION_NAME' => 'PROPERTY_LOCATION.UF_NAME',
                'REGION_ID' => 'PROPERTY_LOCATION.UF_REGION',
                'REGION_NAME' => 'PROPERTY_REGION.UF_NAME',
                'DISTRICT_ID' => 'PROPERTY_REGION.UF_DISTRICT',
                'DISTRICT_NAME' => 'PROPERTY_DISTRICT.UF_NAME',
            ],
            'order' => ['sort' => 'ASC'],
            'filter' =>  $filter,
            'runtime' => [
                'PROPERTY_LEVEL' => [
                    'data_type' => $DataClassLevel,
                    'reference' => [
                        '=this.LEVEL_ID' => 'ref.UF_XML_ID',
                    ]
                ],

                'PROPERTY_LOCATION' => [
                    'data_type' => $DataClassLocation,
                    'reference' => [
                        '=this.LOCATION_ID' => 'ref.UF_XML_ID',
                    ]
                ],
                'PROPERTY_REGION' => [
                    'data_type' => $DataClassRegion,
                    'reference' => [
                        '=this.REGION_ID' => 'ref.ID',
                    ]
                ],
                'PROPERTY_DISTRICT' => [
                    'data_type' => $DataClassDistricts,
                    'reference' => [
                        '=this.DISTRICT_ID' => 'ref.ID',
                    ]
                ],
            ],
        ]);

        while ($row = $obj->fetch()) {
            $arResult[$row['ID']] = $row;
        }

        return $arResult;
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
        $this->arResult['DISTRICTS'] = $this->getHLTable('b_hlb_districts');
        $this->arResult['LOCATIONS'] = $this->getLocationsAction();
        $this->arResult['TYPE_EXAM'] = $this->getExamTypeAction();
        //$this->arResult['EXAM_RESULT'] = $this->getHLTable('b_hlbd_result_exam');
        $this->arResult['YEARS'] = $this->getYears();

        $context = Context::getCurrent();
        $request = $context->getRequest();
        $params = $request->getQueryList()->toArray();
        $filter = ['REFUSAL.VALUE' => false];
        $filter_item = $params['filterItem'];

        switch ($filter_item) {
            case 'filterItemDistrict':
                $this->selectedItem = 'filterItemDistrict';
                $filter['DISTRICT_ID'] = $params['stat_districts'];
                break;
            case 'filterItemRegion':
                $this->selectedItem = 'filterItemRegion';
                $filter['REGION_ID'] = $params['stat_reg'];
                break;
            case 'filterItemLocation':
                $this->selectedItem = 'filterItemLocation';
                $filter['LOCATION_ID'] = $params['stat_loc'];
                break;
            case 'filterItemLevel':
                $this->selectedItem = 'filterItemLevel';
                $filter['LEVEL_ID'] = $params['stat_type'];
                break;
        }

        $filter_item_date = $params['filterDate'];

        switch ($filter_item_date){
            case 'filterDate1':
                $filter['>=EXAM_DATE'] = $this->GetDateFrom( $params['stat_month'], $params['stat_years']);
                $filter['<=EXAM_DATE'] = $this->GetDateTo( $params['stat_month'], $params['stat_years']);
                break;
            case 'filterDate2':
                if($this->validateDate($params['statics_date_from']))
                    $filter['>=EXAM_DATE'] = $this->ChangeDateFormat($params['statics_date_from']);
                if($this->validateDate($params['statics_date_to']))
                    $filter['<=EXAM_DATE'] = $this->ChangeDateFormat($params['statics_date_to']);
                break;
        }
        if($filter && $params['set_filter_statistics'] == 'Y'){
            $arExamResults = $this->getIblockElementExamResults($filter);
            //foreach($arExamResults as $ID => $row){
            switch ($this->selectedItem) {
                case 'filterItemDistrict':
                    $this->arResult['ITEMS']['DIAGRAM_BY_QUANTITY'] = $this->GetItemsDiagramByQuantity('DISTRICT',$arExamResults, $params['stat_districts']);
                    $this->arResult['ITEMS']['HISTOGRAM'] = $this->GetItemsHistogram('DISTRICT', $arExamResults, $params['stat_districts']);
                    $this->arResult['ITEMS']['DIAGRAM_BY_SCORES'] = $this->GetItemsDiagramByScores('DISTRICT', $arExamResults, $params['stat_districts']);
                    break;
                case 'filterItemRegion':
                    $this->arResult['ITEMS']['DIAGRAM_BY_QUANTITY'] = $this->GetItemsDiagramByQuantity('REGION',$arExamResults, $params['stat_reg']);
                    $this->arResult['ITEMS']['HISTOGRAM'] = $this->GetItemsHistogram('REGION', $arExamResults, $params['stat_districts']);
                    $this->arResult['ITEMS']['DIAGRAM_BY_SCORES'] = $this->GetItemsDiagramByScores('REGION', $arExamResults, $params['stat_reg']);
                    break;
                case 'filterItemLocation':
                    $this->arResult['ITEMS']['DIAGRAM_BY_QUANTITY'] = $this->GetItemsDiagramByQuantity('LOCATION',$arExamResults, $params['stat_loc']);
                    $this->arResult['ITEMS']['HISTOGRAM'] = $this->GetItemsHistogram('LOCATION', $arExamResults, $params['stat_districts']);
                    $this->arResult['ITEMS']['DIAGRAM_BY_SCORES'] = $this->GetItemsDiagramByScores('LOCATION', $arExamResults, $params['stat_loc']);
                    break;
                case 'filterItemLevel':
                    $this->arResult['ITEMS']['DIAGRAM_BY_QUANTITY'] = $this->GetItemsDiagramByQuantity('LEVEL',$arExamResults, $params['stat_type']);
                    $this->arResult['ITEMS']['HISTOGRAM'] = $this->GetItemsHistogram('LEVEL', $arExamResults, $params['stat_districts']);
                    $this->arResult['ITEMS']['DIAGRAM_BY_SCORES'] = $this->GetItemsDiagramByScores('LEVEL', $arExamResults, $params['stat_type']);
                    break;
                default:
                    $this->arResult['ITEMS']['DIAGRAM_BY_QUANTITY'] = $this->GetItemsDiagramByQuantity('DEFAULT', array(), array());
                    $this->arResult['ITEMS']['HISTOGRAM'] = $this->GetItemsHistogram('DEFAULT', array(), $params['stat_districts']);
                    $this->arResult['ITEMS']['DIAGRAM_BY_SCORES'] = $this->GetItemsDiagramByScores('DEFAULT', array(), array());

            }
        }
        $this->arResult['PARAMS'] = $params;
    }

    protected function GetItemsDiagramByQuantity($type, $data = array(), $filterParams)
    {
        $res['names'] = [];
        $res['quantity'] = [];
        if($type == "DEFAULT")
            return $res;

        foreach ($data as $item){
            $res['names'][$item[$type."_ID"]] = $item[$type."_NAME"];
            $res['quantity'][$item[$type."_ID"]]++;
        }

        switch ($type){
            case 'DISTRICT':
                $ResultKey = 'DISTRICTS';
                break;
            case 'REGION':
                $ResultKey = 'REGION';
                break;
            case 'LOCATION':
                $ResultKey = 'LOCATIONS';
                break;
            default:
                $ResultKey = 'TYPE_EXAM';
                break;
        }

        if(is_array($filterParams)){
            foreach ($filterParams as $value){
                if (!key_exists($value,  $res['names']) && key_exists($value,  $this->arResult[$ResultKey])){
                    $res['names'][$value] = $this->arResult[$ResultKey][$value]['UF_NAME'];
                    $res['quantity'][$value] = 0;
                }

            }
        }else if($filterParams != ''){
            if (!key_exists($filterParams,  $res['names']) && key_exists($filterParams,  $this->arResult[$ResultKey])){
                $res['names'][$filterParams] = $this->arResult[$ResultKey][$filterParams]['UF_NAME'];
                $res['quantity'][$filterParams] = 0;
            }

        }

         return $res;
    }

    protected function GetItemsHistogram($type, $data = array())
    {
        $items = [
            'result' => [],
            'listen' => [],
            'reade' => [],
            'write' => [],
            'spek' => [],
        ];

        if($type == "DEFAULT")
            return $items;

        foreach ($data as $item){
            $Result = (int)$item['EXAM_LISTENING'];
            $listen = (int)$item['EXAM_LISTENING'];
            $reade = (int)$item['EXAM_READING'];
            $write = (int)$item['EXAM_WRITING'];
            $spek = (int)$item['EXAM_SPEAKING'];

            if($Result > 0)
                $items['result'][$Result]++;
            if($listen > 0)
                $items['listen'][$listen]++;
            if($reade > 0)
                $items['reade'][$reade]++;
            if($write > 0)
                $items['write'][$write]++;
            if($spek > 0)
                $items['spek'][$spek]++;
        }
        ksort( $items['result']);
        ksort( $items['listen']);
        ksort( $items['reade']);
        ksort( $items['write']);
        ksort( $items['spek']);
        return $items;
    }

    protected function GetItemsDiagramByScores($type, $data = array(), $filterParams)
    {
        $res['names'] = [];
        $res['quantity'] = [];
        if($type == "DEFAULT")
            return $res;

        $items = [];
        foreach ($data as $item){
            $items[$item[$type."_ID"]]['name'] = $item[$type."_NAME"];
            $items[$item[$type."_ID"]]['count']++;
            $items[$item[$type."_ID"]]['listen'] += (int)$item['EXAM_LISTENING'];
            $items[$item[$type."_ID"]]['reade'] += (int)$item['EXAM_READING'];
            $items[$item[$type."_ID"]]['write'] += (int)$item['EXAM_WRITING'];
            $items[$item[$type."_ID"]]['spek'] += (int)$item['EXAM_SPEAKING'];
        }


        switch ($type){
            case 'DISTRICT':
                $ResultKey = 'DISTRICTS';
                break;
            case 'REGION':
                $ResultKey = 'REGION';
                break;
            case 'LOCATION':
                $ResultKey = 'LOCATIONS';
                break;
            default:
                $ResultKey = 'TYPE_EXAM';
                break;
        }

        if(is_array($filterParams)){
            foreach ($filterParams as $value){
                if (!key_exists($value,  $items) && key_exists($value,  $this->arResult[$ResultKey])){
                    $items[$value]['name'] = $this->arResult[$ResultKey][$value]['UF_NAME'];
                    $items[$value]['count'] = 1;
                    $items[$value]['listen'] = 0;
                    $items[$value]['reade'] = 0;
                    $items[$value]['write'] = 0;
                    $items[$value]['spek'] = 0;
                }
            }
        }else if($filterParams != ''){
            if (!key_exists($filterParams,  $items) && key_exists($filterParams,  $this->arResult[$ResultKey])){
                $items[$filterParams] = $this->arResult[$ResultKey][$filterParams]['UF_NAME'];
                $items[$filterParams]['count'] = 1;
                $items[$filterParams]['listen'] = 0;
                $items[$filterParams]['reade'] = 0;
                $items[$filterParams]['write'] = 0;
                $items[$filterParams]['spek'] = 0;
            }
        }

        foreach ($items as $item){
            $res['names'][] = $item['name'];
            $res['quantity'][] = $item['listen']/$item['count'];
            $res['names'][] = $item['name'];
            $res['quantity'][] = $item['reade']/$item['count'];
            $res['names'][] = $item['name'];
            $res['quantity'][] = $item['write']/$item['count'];
            $res['names'][] = $item['name'];
            $res['quantity'][] = $item['spek']/$item['count'];
        }

         return $res;

    }

    protected function GetDateFrom($month = '', $year = '')
    {
        $date = '';
        if($year != '')
            $date .= $year;
        else
            $date .= $this->getCurrentYear();

        if($month != '')
            $date .= '-'.$month;
        else
            $date .= '-01';

        $date .= '-01 00:00:00';

        return $date;
    }

    protected function GetDateTo($month = '', $year = '')
    {
        $date = '';
        if($year != '')
            $date .= $year;
        else
            $date .= $this->getCurrentYear();

        if($month == '')
            $month = '12';

        $date .= '-'.$month;
        $date .= '-'.$this->arrLastDates[$month].' 00:00:00';

        return $date;
    }

    protected function ChangeDateFormat($date, $format = 'Y-m-d H:i:s')
    {
        if ($date != '' && $this->validateDate($date)) {
            $date = new BxDateTime($date);
            return $date->format($format);
        }
        return '';
    }

    public function getLocationsAction($param = [])
    {
        return $this->getHLTable('b_hlb_institutions', ['UF_REGION' => $param['id']]);
    }

    protected function getYears()
    {
        $year = (int)$this->getCurrentYear();
        $startYear = (int)$this->StartYear;
        $arYears = [];
        for($year ; $year >= $startYear; $startYear++){
            $arYears[$startYear] = $startYear;
        }

        return $arYears;
    }

    protected function getCurrentYear()
    {
        $objDateTime = new BxDateTime();
        return $objDateTime->format("Y");
    }

    public function getExamTypeAction($param = [])
    {
        return $this->getHLTable('b_hlb_exam_level', []);
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
