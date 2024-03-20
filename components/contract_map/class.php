<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Type\DateTime,
    Bitrix\Highloadblock as HL,
    Bitrix\Main\Localization\Loc as Loc;

class ContractMap  extends CBitrixComponent
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

    /**
     * получение результатов
     */
    protected function getResult()
    {
        Loader::includeModule('iblock');
        $this->arResult['LIST_DISTRICT'] = $this->getHLTable('b_hlbd_rayony');
        $this->arResult['mapCoordinate'] = $this->MapCoordinateGenerateData();
        $this->arResult['coordsObject'] = $this->GetContarcts();

    }

    protected function MapCoordinateGenerateData()
    {
        $areas = $this->getHLTable('b_hlbd_rayony');
        $mapCoordinate = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"].$this->GetPath()."/mapCoordinate.json"), true);
        foreach ($mapCoordinate["mapCoordinate"] as $key => $area){
            if(key_exists($area["id"], $areas)){

                $mapCoordinate["mapCoordinate"][$key]['content']['title'] = $areas[$area["id"]]['UF_NAME'];
                $mapCoordinate["mapCoordinate"][$key]['content']['amountContractsImplemented'] = $areas[$area["id"]]['UF_CONTRACT_SUMM'];
                //$mapCoordinate["mapCoordinate"][$key]['content']['prefix'] = preg_replace("/[0-9 ,]/", "", $areas[$area["id"]]['UF_CONTRACT_SUMM']);
                $mapCoordinate["mapCoordinate"][$key]['content']['numberContractsImplemented'] = $areas[$area["id"]]['UF_CONTRACT_COUNT'];
                $mapCoordinate["mapCoordinate"][$key]['content']['amountContractsPotential'] = $areas[$area["id"]]['UF_SUM_CONTRACT_POTENTIAL'];
                $mapCoordinate["mapCoordinate"][$key]['content']['numberContractsPotential'] = $areas[$area["id"]]['UF_COUNT_CONTRACT_POTENTIAL'];


            }
        }
        return $mapCoordinate["mapCoordinate"];
    }

    private function GetContarcts(): array
    {

        $arContractsResult = [];
        $TypeList = $this->GetOrganisationTypeList();

        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => 'b_hlbd_rayony')))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClassRayony = $entity->getDataClass();

        $hlblock = HL\HighloadBlockTable::getList(array('filter' => array('=TABLE_NAME' => 'b_hlbd_contractstage')))->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $DataClassStage = $entity->getDataClass();

        //Получение контрактов
        $obj = \Bitrix\Iblock\Elements\ElementOrganizationsTable::getList([
            'cache' => [
                'ttl' => 3600
            ],
            'select' => [
                'ID',
                'NAME',
                'TYPE' => 'ORGANIZATION_TYPE.VALUE',
                'CONTRACT_LAT' => 'CONTRACT_LATITUDE.VALUE',
                'CONTRACT_LAN' => 'CONTRACT_LONGTUDE.VALUE',
                'PROP_CUSTOMER' => 'CUSTOMER.VALUE',
                'PROP_EXECUTER' => 'EXECUTER.VALUE',
                'PROP_INN' => 'INN.VALUE',
                'ECONOMY' => 'ENERG_SAVING.VALUE',
                'SUBJECT' => 'CONTRACT_SUBJECT.VALUE',
                'DATE' => 'CONTRACT_DATE.VALUE',
                'PERIOD' => 'CONTRACT_PERIOD.VALUE',
                'PRICE' => 'CONTRACT_PRICE.VALUE',
                'AREA_XML_ID' => 'PROP_AREA.VALUE',
                'AREA_NAME' => 'PROPERTY_RAYONY.UF_NAME',
                'CONTRACT_TYPE_VALUE' => 'CONTRACT_TYPE.VALUE',
                'STAGES_XML_ID' => 'CONTRACT_STAGES.VALUE',
                'STAGES_NAME' => 'PROPERTY_STAGES.UF_NAME',
            ],
            'order' => ['sort' => 'ASC'],
            'runtime' => [
                'PROPERTY_RAYONY' => [
                    'data_type' => $DataClassRayony,
                    'reference' => [
                        '=this.AREA_XML_ID' => 'ref.UF_XML_ID',
                    ],
                ],
                'PROPERTY_STAGES' => [
                    'data_type' => $DataClassStage,
                    'reference' => [
                        '=this.STAGES_XML_ID' => 'ref.UF_XML_ID',
                    ],
                ],
            ],
        ]);

        while ($row = $obj->fetch()) {
            $item = [];
            $item['areaId'] = $row['AREA_XML_ID'];
            $item['area'] = $row['AREA_NAME'];
            $item['clusterCaption'] = $TypeList[$row['TYPE']];
            $item['mapCoordinate'] = [$row['CONTRACT_LAT'], $row['CONTRACT_LAN']];
            $item['mapHintContent'] = $row['NAME'];
            $item['mapBalloonContent']['header'] = $row['NAME'];
            $item['mapBalloonContent']['body'] = [
                'text' => Loc::getMessage("CONTRACT_MAP_ECONOMY"),
                'number' => $row['ECONOMY'],
                'prefix' => ''
            ];
            if (strlen($row['PROP_CUSTOMER']) > 0){
                $item['mapBalloonContent']['footer']['customer'] = [
                    'label' => Loc::getMessage("CONTRACT_MAP_PROP_CUSTOMER"),
                    'value' => $row['PROP_CUSTOMER'],
                ];
            }
            if (strlen($row['PROP_EXECUTER']) > 0){
                $item['mapBalloonContent']['footer']['executor'] = [
                    'label' => Loc::getMessage("CONTRACT_MAP_PROP_PROP_EXECUTER"),
                    'value' => $row['PROP_EXECUTER'],
                ];
            }
            if (strlen($row['PROP_INN']) > 0){
                $item['mapBalloonContent']['footer']['inn'] = [
                    'label' => Loc::getMessage("CONTRACT_MAP_PROP_PROP_INN"),
                    'value' => $row['PROP_INN']
                ];
            }
            if (strlen($row['SUBJECT']) > 0){
                $item['mapBalloonContent']['footer']['subjectOfContract'] = [
                    'label' => Loc::getMessage("CONTRACT_MAP_PROP_PROP_SUBJECT"),
                    'value' => $row['SUBJECT']
                ];
            }
            $date = '';
            if(strlen($row['DATE']) > 0){
                $dateTime = new DateTime($row['DATE'], "Y-m-d");
                $date = $dateTime->format("d/m/Y");
            }
            if (strlen($date) > 0){
                $item['mapBalloonContent']['footer']['dateContract'] = [
                    'label' => Loc::getMessage("CONTRACT_MAP_PROP_PROP_dateContract"),
                    'value' => $date
                ];
            }
            if(strlen($row['PERIOD']) > 0){
                $item['mapBalloonContent']['footer']['termContract'] = [
                    'label' => Loc::getMessage("CONTRACT_MAP_PROP_PROP_termContract"),
                    'value' => $row['PERIOD']
                ];
            }
            if(strlen($row['PRICE']) > 0){
                $item['mapBalloonContent']['footer']['priceContract'] = [
                    'label' => Loc::getMessage("CONTRACT_MAP_PROP_PROP_priceContract"),
                    'value' => $row['PRICE']
                ];
            }
            if(strlen($row['STAGES_NAME']) > 0){
                $item['mapBalloonContent']['footer']['stageContract'] = [
                    'label' => Loc::getMessage("CONTRACT_MAP_PROP_PROP_stageContract"),
                    'value' => $row['STAGES_NAME'],
                ];
            }

            if ($row['CONTRACT_TYPE_VALUE'] == "TYPE_1"){
                $item['iconLayout'] = [
                    'color' => 'bg-orange'

                ];
            }elseif ($row['CONTRACT_TYPE_VALUE'] == "TYPE_2"){
                $item['iconLayout'] = [
                    'color' => 'bg-yellow'
                ];
            }else{
                $item['iconLayout'] = [
                    'name' => 'round',
                    'color' => 'bg-orange-yellow'
                ];
            }

            $arContractsResult[] = $item;
        }
        return $arContractsResult;
    }

    protected function GetOrganisationTypeList(){
        $data = [];
        $sizes = CIBlockPropertyEnum::GetList([],[
            "IBLOCK_ID" => $this->getIblockCodeById('organizations'),
            "CODE" => "ORGANIZATION_TYPE"
        ]);
        while ($size = $sizes->Fetch()){
            $data[$size["ID"]] = $size["VALUE"];
        }
        return $data;
    }

    public function getIblockCodeById($iblock_code)
    {
        $iblock_obj = \Bitrix\Iblock\IblockTable::getList(
            [
                'filter' => [
                    '=CODE' => $iblock_code
                ],
                'select' => [
                    'ID'
                ],
                'cache' => ['ttl' => 86400]
            ]
        );

        if ($res = $iblock_obj->fetchObject()) {
            return $res->getId();
        }

        return false;
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
