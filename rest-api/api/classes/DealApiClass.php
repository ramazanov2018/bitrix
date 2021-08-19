<?php

namespace Api\Classes;

use Bitrix\Crm\ProductRowTable,
    Bitrix\Main\Loader,
    Serv\Register\RegisterTable;
use Serv\CheckEx\DealCheckExTable;

class DealApiClass
{
    /*Поля Сделки*/
    const DEAL_FIELDS = [
        'ORIGIN_ID' => 'ORIGIN_ID',
        'COMPANY_ID' => 'COMPANY_ID', //Покупатель/ Контрагент]
        'COMMENTS' => 'COMMENTS', //Комментарии
        'ASSIGNED_BY_ID' => 'ASSIGNED_BY_ID', //Ответственный
        'DATE_CREATE' => 'DATE_CREATE', //Дата создания сделки
        'CURRENCY_ID' => 'CURRENCY_ID', //Валюта сделки
        'UF_CRM_1571647001' => 'UF_CRM_1571647001', //Грузополучатель
        'UF_CRM_1571647076' => 'UF_CRM_1571647076', //Грузоотправитель
        'UF_CRM_1571646832' => 'UF_CRM_1571646832', //Блок СРТ
        'UF_CRM_1571647143' => 'UF_CRM_1571647143', //Номер договора
        'UF_CRM_1571647177' => 'UF_CRM_1571647177', //Файл. Дополнительное соглашение
        'UF_CRM_1571647560' => 'UF_CRM_1571647560', //Продавец
        'UF_CRM_1569484656' => 'UF_CRM_1569484656', //Номер заказа контрагента
        'UF_CRM_1569319115' => 'UF_CRM_1569319115', //Тара (список)
        'UF_CRM_1569319143' => 'UF_CRM_1569319143', //Условия оплаты (список)
        'UF_CRM_1569316998' => 'UF_CRM_1569316998', //Базис поставки (список)
        'UF_CRM_1571647960' => 'UF_CRM_1571647960', //Дата прихода к клиенту (план)
        'UF_CRM_1571648020' => 'UF_CRM_1571648020', //Дата ухода из порта (план)
        'UF_CRM_1571648204' => 'UF_CRM_1571648204', //Сумма предоплаты (факт)/Валюта
        'UF_CRM_1571648317' => 'UF_CRM_1571648317', //Дата получениея постоплаты (факт)
        'UF_CRM_1571648388' => 'UF_CRM_1571648388', //Сумма постоплаты (факт)/Валюта
        'UF_CRM_1571648571' => 'UF_CRM_1571648571', //Не отгружать заказ
        'UF_CRM_1571648602' => 'UF_CRM_1571648602', //Отгружаем с завода, не ставим на судно
        'UF_CRM_1571648622' => 'UF_CRM_1571648622', //ОСпецэтикетки
        'UF_CRM_1571648651' => 'UF_CRM_1571648651', //Нейтральные бочки
        'UF_CRM_1571648668' => 'UF_CRM_1571648668', //Обручи
        'UF_CRM_1571648689' => 'UF_CRM_1571648689', //Дата готовности заказа к отгрузке
        'UF_CRM_1569494744' => 'UF_CRM_1569494744', //Дата заказа перевозчику
        'UF_CRM_1569494753' => 'UF_CRM_1569494753', //Номер заказа перевозчику
        'UF_CRM_1569494570' => 'UF_CRM_1569494570', //Перевозчик
        'UF_CRM_1569494584' => 'UF_CRM_1569494584', //Договор с перевозчиком
        'UF_CRM_1571648827' => 'UF_CRM_1571648827', //Ставка перевозчика/Валюта
        'UF_CRM_1571648878' => 'UF_CRM_1571648878', //№контейнера(трансопртного средства)
        'UF_CRM_1571648924' => 'UF_CRM_1571648924', //Линия
        'UF_CRM_1569494593' => 'UF_CRM_1569494593', //Наименование судна
        'UF_CRM_1569494631' => 'UF_CRM_1569494631', //Дата ухода судна(план)
        'UF_CRM_1571648989' => 'UF_CRM_1571648989', //Дата прихода судна (план)
        'UF_CRM_1571649032' => 'UF_CRM_1571649032', //Сделан заказ перевозчику
        'UF_CRM_1571649053' => 'UF_CRM_1571649053', //Склад
        'UF_CRM_1571649104' => 'UF_CRM_1571649104', //Транспорт отгружен с завода
        'UF_CRM_1571649124' => 'UF_CRM_1571649124', //Дата ГТД
        'UF_CRM_1571649156' => 'UF_CRM_1571649156', //Трансопрт переадресован
        'UF_CRM_1571649174' => 'UF_CRM_1571649174', //Фактический вес
        'UF_CRM_1571649194' => 'UF_CRM_1571649174', //Дата сдачи документов
        'UF_CRM_1569494763' => 'UF_CRM_1569494763', //№контейнера после перетарки
        'UF_CRM_1571649281' => 'UF_CRM_1571649281', //Дата ухода с завода
        'UF_CRM_1571649298' => 'UF_CRM_1571649298', //Планируемая дата прихода к клиету
        'UF_CRM_1571649320' => 'UF_CRM_1571649320', //Примечание логиста
        'UF_CRM_1569329439' => 'UF_CRM_1569329439', //Планируемая дата отгрузки
        'UF_CRM_1571647733' => 'UF_CRM_1571647733', //Количесвто бочек (шт)
        'UF_CRM_1571831071' => 'UF_CRM_1571831071', //Договор заказчика


        'UF_CRM_1571651807' => 'UF_CRM_1571651807', //Дата ухода
        'UF_CRM_1571651825' => 'UF_CRM_1571651825', //Дата ухода судна(факт)
        'UF_CRM_1571655965' => 'UF_CRM_1571655965', //Сумма ТР
        'UF_CRM_1571652063' => 'UF_CRM_1571652063', //Погранпереход
        'UF_CRM_1571652160' => 'UF_CRM_1571652160', //Общее количество
        'UF_CRM_1571652201' => 'UF_CRM_1571652201', //Страна/порт назначений
        'UF_CRM_1571652244' => 'UF_CRM_1571652244', //Номера ж/д квитанций
        'UF_CRM_1571652260' => 'UF_CRM_1571652260', //№ платформ
        'UF_CRM_1571652273' => 'UF_CRM_1571652273', //Номер автотранспорта
        'UF_CRM_1571652288' => 'UF_CRM_1571652288', //Цена приложения
        'UF_CRM_1571652370' => 'UF_CRM_1571652370', //Приложение номер
        'UF_CRM_1571652385' => 'UF_CRM_1571652385', //Вид транспорта
        'UF_CRM_1571652506' => 'UF_CRM_1571652506', //Наша организация (покупатель)
        'UF_CRM_1571652520' => 'UF_CRM_1571652520', //Наша организация (продавец)
        'UF_CRM_1571652540' => 'UF_CRM_1571652540', //Номер ГТД
        'UF_CRM_1571652591' => 'UF_CRM_1571652591', //Валюта продажи
        'UF_CRM_1571652649' => 'UF_CRM_1571652649', //Валюта ТР
        'UF_CRM_1571653180' => 'UF_CRM_1571653180', //Дата прихода судна (факт)
        'UF_CRM_1569494555' => 'UF_CRM_1569494555', //Дата прихода к клиенту судна(план)
        'UF_CRM_1571653235' => 'UF_CRM_1571653235', //Поставщик
        'UF_CRM_1571653250' => 'UF_CRM_1571653250', //История отгрузки
        'UF_CRM_1571653277' => 'UF_CRM_1571653277', //Сумма прихода
        'UF_CRM_1571655982' => 'UF_CRM_1571655982', //Валюта прихода
        'UF_CRM_1571656012' => 'UF_CRM_1571656012', //Сумма расхода
        'UF_CRM_1571656040' => 'UF_CRM_1571656040', //Валюта расхода
        'UF_CRM_1571653621' => 'UF_CRM_1571653621', //Договор перевозчика
        'UF_CRM_1569494792' => 'UF_CRM_1569494792', //Инвойс (перевозчик)



    ];

    /*Поля Товара*/
    const UF_COUNT_PLAN = 'UF_COUNT_PLAN'; //Количесвто товара план
    const UF_PRICE_PLAN = 'UF_PRICE_PLAN'; //Цена товара план
    const UF_TRANS_TAX_PLAN = 'UF_TRANS_TAX_PLAN'; //Трансп. расход (план) (поле из сетки товара)

    const DEAL_ID_1C = 'DEAL_ID_1C'; //ID Сделки в 1С
    const DIRECTION = 'DIRECTION'; //Направление 1С

    /*Ошибка*/
    private $errorRes = '';
    private $ErrorAdd = 'Ошибка при сохранении';
    private $ErrorUpdate = 'Ошибка при обновлении';
    private $ErrorAddProduct = 'Ошибка при добавлении товаров';
    private $Error = 'Неправильно указан направление 1C (DIRECTION)';
    /*Ошибка*/

    private $DealId = '';

    //Возврат статуса
    private function renderJson($status)
    {
        echo json_encode($status, JSON_UNESCAPED_UNICODE);
    }

    //обновить или создать ?
    public function Controller($data ,$DIRECTION)
    {
        $data[self::DIRECTION] = ($DIRECTION) ? $DIRECTION : '1';
        Loader::includeModule('crm');

        $data['ID'] = trim($data['ID']);
        $data['ORIGIN_ID'] = trim($data['ORIGIN_ID']);

        if ($data['ID'] > 0) {
            if ($this->DealId = $this->CheckExistenceCompany(array('ID' => $data['ID']))) {
                $this->UpdateElement($this->DealId, $data);
            }
        }

        if (!empty($data['ORIGIN_ID']) && $this->DealId <= 0) {
            if ($this->DealId = $this->CheckExistenceCompany(array('ORIGIN_ID' => $data['ORIGIN_ID']))) {
                $this->UpdateElement($this->DealId, $data);
            }
        }

        if ($this->DealId <= 0) {
            $this->AddElement($data);
        }


        if ($this->errorRes !== ''){
            $this->renderJson(['status'=>'error', 'error'=>$this->errorRes]);
        }else{
            $this->renderJson(['status'=>'success']);
        }
    }

    //Существуетли сделка
    public function CheckExistenceCompany($arFilter)
    {
        $arFilter['CHECK_PERMISSIONS'] = 'N';
        $dbResMultiFields = \CCrmDeal::GetList(Array(), $arFilter, Array(), false);
        while ($arMultiFields = $dbResMultiFields->Fetch()) {
            return $arMultiFields['ID'];
        }

        return 0;
    }

    //Новый элемент
    public function AddElement($data )
    {
        $PlaSum = $this->GetPlanSum($data);

        $Fields = Array(
            '1C' => 1,
            'TITLE' => 'Сделка ' . $data[self::DEAL_ID_1C],
            'CATEGORY_ID' => ($data[self::DIRECTION] == '1') ? 1 : 0,
            'TYPE_ID' => 'SALE',
            'UF_CRM_1569316201' => $PlaSum.'|'.$data['CURRENCY_ID'],
        );

        foreach (self::DEAL_FIELDS as $key => $value) {
            if ($key == 'UF_CRM_1571647177' && !empty($data[$value])){
                $fContent = file_get_contents($data[$value]);
                $fName = basename($data[$value]);
                $fType = pathinfo($fName, PATHINFO_EXTENSION);
                $arIMAGE = Array(
                    "name" => $fName,
                    "size" => "",
                    "type" => $fType,
                    "old_file" => "",
                    "del" => "",
                    "MODULE_ID" => "crm",
                    "description" => "",
                    "content" => $fContent
                );
                $fid = \CFile::SaveFile($arIMAGE, "main");
                $Fields[$key] = (string)$fid;
            }else{
                $Fields[$key] = $data[$value];
            }
        }

        $c = new \CCrmDeal();
        if ($g = $c->Add($Fields, true, array('CURRENT_USER' => 1, 'IS_RESTORATION' => 1))) {
            $this->AddProduct($g, $data);
        } else {
            $this->errorRes = $this->ErrorAdd;
        };
    }

    //Добавление товара
    private function AddProduct($DealId, $data)
    {
        if (!empty($data['PRODUCTS'])):

            $Fields = ['OWNER_ID' => $DealId, 'OWNER_TYPE' => 'D'];
            foreach ($data['PRODUCTS'] as $product) {
                $Fields['PRODUCT_ID'] = $product['PRODUCT_ID'];
                $ProdId = ProductRowTable::Add($Fields);

                if ($ProdId->getId()) {
                    $UF_SUMM_PLAN = $product[self::UF_COUNT_PLAN] * $product[self::UF_PRICE_PLAN];
                    $additionalFields = [
                        'UF_DEAL' => $DealId,
                        'UF_PRODUCT_ID' => $product['PRODUCT_ID'],
                        'UF_OWNER_ID' => $ProdId->getId(),
                        'UF_SUMM_PLAN' => $UF_SUMM_PLAN,
                        self::UF_COUNT_PLAN =>$product[self::UF_COUNT_PLAN],
                        self::UF_PRICE_PLAN =>$product[self::UF_PRICE_PLAN],
                        self::UF_TRANS_TAX_PLAN =>$product[self::UF_TRANS_TAX_PLAN],
                        ];
                    $additional = RegisterTable::Add($additionalFields); //Дополнительные поля товара

                }else{
                    $this->errorRes = $this->ErrorAddProduct;
                }
            }
        endif;
    }

    //Обновление элемента
    public  function UpdateElement($DealId, $data)
    {
        unset ($data['ID'], $data['PRODUCTS'],$data[self::DIRECTION]);
        $arFields = [];
        foreach ($data as $key => $value){
            $arFields['1C'] = 1;
            $arFields[$key] = $value;
        }
        $c = new \CCrmDeal(false);
        if ($g = $c->Update($DealId, $arFields, true, true, array('CURRENT_USER' => 1))) {

        }else{
            $this->errorRes = $this->ErrorUpdate;
        }
    }

    //Планируемая сумма сделки
    private function GetPlanSum($data)
    {
        $PlanSum = 0;
        if (!empty($data['PRODUCTS'])):
            foreach ($data['PRODUCTS'] as $product) {
                $PlanSum += $product[self::UF_COUNT_PLAN] * $product[self::UF_PRICE_PLAN];
            }
            endif;
        return$PlanSum;
    }

    public function PushDeals($data){

        Loader::includeModule('crm');

        $deals['DEALS'] = array();

        $dealExId = array();
        $dealId = array();

        //Id сделок которое нужно передать
        $obCheckEx = DealCheckExTable::GetList(array());
        while ($CheckEx = $obCheckEx->fetch()){
            $dealExId[] = (int)$CheckEx['UF_DEAL_ID'];
        }

        //нет измененных сделок
        if (empty($dealExId)){
         $this->renderJson($deals);
         return;
        }

        //проверка направлении 1С
        switch ($data[self::DIRECTION]){
            case '1':
                $category_id = 1;
                break;
            case '2':
                $category_id = 0;
                break;

            default:
                $this->renderJson(['status'=>'error', 'error' => 'Неправильно указан направление 1C (DIRECTION)']);
                return;

        }

        //фильтрация сделок по нужному направлению 1С
        $obDealSEx = \CCrmDeal::GetListEx(array(), array('@ID'=> $dealExId, 'CHECK_PERMISSIONS' => 'N', 'CATEGORY_ID'=>$category_id), false, false, array('ID','CATEGORY_ID'));
        while($DealEx = $obDealSEx->Fetch())
        {
            $dealId[] = $DealEx['ID'];
        }

        $arFilter['ID'] = $dealId;
        $arFilter['CHECK_PERMISSIONS'] = 'N';
        $dbResMultiFields = \CCrmDeal::GetList(Array(), $arFilter, Array(), false);
        while ($arMultiFields = $dbResMultiFields->Fetch()){
            $deals['DEALS'][] = $arMultiFields;
        }

        //push deals
        $this->renderJson($deals);
    }
}