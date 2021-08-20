<?php
/*Класс для работы со списком "Планируемые отгрузки" */
namespace Serv;

class ShipmentClass
{
    static $IblockId = 44;
    static $ConfirfField = '310'; // Статус согласования 
    static $ConfirfValue = '506'; // Согласован
    static $workflowTemplateId = 74; // ИД Бизнес-процесса для уведомления
    static $PropProduct = '279'; // Продукт / Тара и кол-во
    static $PropProductCount = '280'; // Количество, шт
    static $PropProductPrice = '282'; // Цена р/т
	static $PropManager = '290'; // Ответственный менеджер
    
    
    /*[Id свойства ифоблока "Планируемые отгрузки"] => [Код свойства сделки]*/
    static $arProps = [
        '275' => 'UF_CRM_1569329439', //Дата планируемой отгрузки
        '276' => 'UF_CRM_1569484656', //Номер/Номер заказа контрагента
        '277' => 'UF_CRM_1569484705', //Наименование компании
        '278' => 'UF_CRM_1569484750', //Адрес компании (почтовый адрес)
        '283' => 'UF_CRM_1569319143', //Условия оплаты
        '284' => 'UF_CRM_1569484978', //Вид упаковки
        '285' => 'UF_CRM_1569316998', //Базис поставки
        '286' => 'UF_CRM_1569485043', //Место разгрузки
        '287' => 'UF_CRM_1569485061', //ФИО водителя
        '288' => 'UF_CRM_1569485101', //Номер машины
        '289' => 'UF_CRM_1569494460', //Дата разгрузки
        '290' => 'ASSIGNED_BY_ID',    //Ответственный менеджер
        '291' => 'UF_CRM_1569494495', //Ответственный логист
        '292' => 'UF_CRM_1569494536', //Продавец/Отгрузка от
        '293' => 'UF_CRM_1569494555', //Дата прихода к клиенту судна(план)
        '294' => 'UF_CRM_1569494570', //Перевозчик
        '295' => 'UF_CRM_1569494584', //Договор с перевозчиком
        '296' => 'UF_CRM_1569494593', //Наименование судна
        '297' => 'UF_CRM_1569494631', //Дата ухода судна(план)
        '298' => 'UF_CRM_1569494653', //Примечание коммерсантов
        '299' => 'UF_CRM_1569494674', //Валюта перевозки
        '300' => 'UF_CRM_1569494685', //Дата прихода к клиенту (план)
        '301' => 'UF_CRM_1569494703', //Дата ухода из порта (план)
        '302' => 'UF_CRM_1569494726', //Примечания логистов
        '303' => 'UF_CRM_1569494744', //Дата заказа перевозчику
        '304' => 'UF_CRM_1569494753', //Номер заказа перевозчику
        '305' => 'UF_CRM_1569494763', //№ контейнера после перетарки
        '306' => 'UF_CRM_1569494778', //Договор перевозчика
        '307' => 'UF_CRM_1569494792', //Инвойс (перевозчик)
        '308' => 'CREATED_BY_ID',     //Направление сделки
    ];

    static $author = '1';  //Создатель элемента


    /*
     * Объём // TODO (Откуда брать?)
     * */

    // Запуск БП для уведомления об одобрения
    static function StartWorkflow($ElemId)
    {
        \CModule::IncludeModule('workflow');
        \CModule::IncludeModule('bizproc');


        $arErrorsTmp = array();


        $wfId = \CBPDocument::StartWorkflow(
            static::$workflowTemplateId,
            array('lists', 'Bitrix\Lists\BizprocDocumentLists', $ElemId),
            array(),
            $arErrorsTmp
        );

    }

    //Метод для БП-са.
    function AddProduct($DealId)
    {
        $DealId = (int)$DealId;

        if (!\CModule::IncludeModule('iblock') && !\CModule::IncludeModule("crm")) return false;

        $PRODUCTS_ID = self::CreateProduct($DealId);
        return $PRODUCTS_ID ;
    }

    //Создание товара
    function CreateProduct($DealId)
    {
        $PRODUCTS_ID = [];

        $el = new \CIBlockElement;

        $Props = self::CreateProps($DealId);
        $DealProducts = self::GetDealProducts($DealId);

        foreach ($DealProducts as $Product){

            $CostRegister = RegisterCost::GetList(array('*'), array('UF_OWNER_ID' => $Product['ID']))->Fetch();

            $Props[self::$PropProduct] = $Product['ORIGINAL_PRODUCT_NAME']; // TODO Уточнить(Продукт / Тара и кол-во)
            $Props[self::$PropProductCount] = $CostRegister['UF_COUNT_PLAN']; //TODO Уточнить(Количество план)
            $Props[self::$PropProductPrice] = $CostRegister['UF_PRICE_PLAN']; // TODO Уточнить(Какая Цена)

            $arLoadProductArray = Array(
                "MODIFIED_BY" => self::$author ,
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID" => self::$IblockId,
                "PROPERTY_VALUES" => $Props,
                "NAME" => $Product['PRODUCT_NAME'],
                "ACTIVE" => "Y",
            );

            if ($PRODUCT_ID = $el->Add($arLoadProductArray))
                $PRODUCTS_ID[] = $PRODUCT_ID;
        }

        return $PRODUCTS_ID;
    }

    //Поля товара
    function CreateProps($DealId)
    {
        $Props = [];

        $Deals = \CCrmDeal::GetList(Array(), Array('ID' => $DealId), Array(), false);
        $Deal = $Deals->Fetch();

        if (empty($Deal)) return $Props;


        self::$author = $Deal['CREATED_BY'];

        foreach (self::$arProps as $ShipmentPropId => $DealPropCode) {
            $Props[$ShipmentPropId] = $Deal[$DealPropCode];
        }

        return $Props;
    }

    //Товары сделки
    function GetDealProducts($DealId)
    {
        $Products = \CCrmDeal::LoadProductRows($DealId);
        return $Products;
    }
    // Установка прав на элемент
	function SetRight($arParams){
        if($arParams['IBLOCK_ID'] != self::$IblockId )
            return true;
            
		$ManagerId = $arParams['PROPERTY_VALUES'][self::$PropManager];
	    $ManagerId = array_shift($ManagerId)['VALUE'];
        
		IblockElementRight(self::$IblockId, $arParams['ID'], $ManagerId);
        
    	return true;
    }
}

?>

