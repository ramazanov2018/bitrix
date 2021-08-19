<?php

namespace Api\Classes;

use Bitrix\Main\Loader,
    Bitrix\Crm\EntityRequisite,
    Bitrix\Crm\AddressTable;
use Serv\CheckEx\CompanyCheckExTable;

class CompanyApiClass
{
    /*Компания*/
    const ID = 'ID'; //ID Элемента в портале
    const COMPANY_NAME = 'COMPANY_NAME';
    const PHONE = 'PHONE';
    const EMAIL = 'EMAIL';
    const WEB = 'WEB';
    const ASSIGNED_BY_ID = 'ASSIGNED_BY_ID';
    const COMMENTS = 'COMMENTS';
    const ORIGIN_ID = 'ORIGIN_ID';
    const UF_CRM_1568792370086 = 'UF_CRM_1568792370086'; //Статус компании
    const UF_CRM_1571650551 = 'UF_CRM_1571650551';
    const UF_CRM_1571650539 = 'UF_CRM_1571650539';
    const UF_CRM_1571650563 = 'UF_CRM_1571650563';
    const UF_CRM_1571650585 = 'UF_CRM_1571650585';
    const UF_CRM_1571650602 = 'UF_CRM_1571650602';
    const UF_CRM_1571650645 = 'UF_CRM_1571650645';  //Категория клиента
    const UF_CRM_1571650663 = 'UF_CRM_1571650663';
    /*Компания*/

    const DIRECTION = 'DIRECTION';
    const COMPANY_TYPE = ['1' => 97, '2' => 96];

    /*Реквизиты*/
    const RQ_PRESET_ID = 'PRESET_ID'; // Тип шаблона [Организация = 1, ИП = 2, Физ лицо = 3]
    const RQ_NAME = 'RQ_NAME'; // Реквизит: Название
    const RQ_COMPANY_FULL_NAME = 'RQ_COMPANY_FULL_NAME'; // Полное наименование организации

    const ADDRESS_TYPE_ID = 'ADDRESS_TYPE_ID'; // Адрес (тип) [Фактический адрес = 1, Адрес регистрации = 4, Юридический адрес = 6, Адрес бенефициара = 9 ]
    const ADDRESS = 'ADDRESS';
    const COUNTRY = 'COUNTRY'; // Страна
    const ADDRESS_1 = 'ADDRESS_1'; // ADDRESS_1
    /*Реквизиты*/

    /*Ошибка*/
    private $errorRes = '';
    private $ErrorAdd = 'Ошибка при сохранении';
    private $ErrorUpdate = 'Ошибка при обновлении';
    private $ErrorUpdateRQ = 'Ошибка при обновлении реквизита';
    private $ErrorAddRQ = 'Ошибка при сохранении реквизита';
    private $ErrorAddRQAdrr = 'Ошибка при сохранении адреса реквизита';
    private $Error = 'Неправильно указан направление 1C (DIRECTION)';
    /*Ошибка*/

    private $CompanyId = '';

    //Возврат статуса
    protected function renderJson($status)
    {
        echo json_encode($status, JSON_UNESCAPED_UNICODE);
    }

    public function Controller($data ,$DIRECTION)
    {
        $data[self::DIRECTION] = ($DIRECTION) ? $DIRECTION : '1';
        Loader::includeModule('crm');

        $data[self::ID] = trim($data[self::ID]);
        $data[self::ORIGIN_ID] = trim($data[self::ORIGIN_ID]);

        if ($data['ID'] > 0) {
            if ($this->CompanyId = $this->CheckExistenceCompany(array('ID' => $data[self::ID]))) {
                $this->UpdateElement($this->CompanyId, $data);
            }
        }

        if (!empty($data['ORIGIN_ID']) && $this->CompanyId <= 0) {
            if ($this->CompanyId = $this->CheckExistenceCompany(array('ORIGIN_ID' => $data[self::ORIGIN_ID]))) {
                $this->UpdateElement($this->CompanyId, $data);
            }
        }

        if ($this->CompanyId <= 0) {
            $this->AddElement($data);
        }
    }

    //Существуетли компания
    public function CheckExistenceCompany($arFilter)
    {
        $arFilter['CHECK_PERMISSIONS'] = 'N';
        $dbResMultiFields = \CCrmCompany::GetList(Array(), $arFilter, Array('ID'), false);
        while ($arMultiFields = $dbResMultiFields->Fetch()) {
            return $arMultiFields['ID'];
        }

        return 0;
    }

    //Новый элемент
    public function AddElement($data)
    {
        $options['CURRENT_USER'] = 1;
        $arFields = [
            'TITLE' => $data[self::COMPANY_NAME],
            'ORIGIN_ID' => $data[self::ORIGIN_ID],
            "COMPANY_TYPE" => 'CUSTOMER',
            'MODIFY_BY_ID' => 1,
            'CREATE_BY_ID' => 1,
            'UF_CRM_5D10CF6541EC8' => self::COMPANY_TYPE[$data[self::DIRECTION]],
            self::ASSIGNED_BY_ID => (int)$data[self::ASSIGNED_BY_ID],
            self::COMMENTS => $data[self::COMMENTS],
            'FM' => Array(
                self::PHONE => Array(
                    'n0' => Array(
                        'VALUE' => $data[self::PHONE],
                        'VALUE_TYPE' => 'WORK'
                    )

                ),
                self::EMAIL => Array(
                    'n0' => Array(
                        'VALUE' => $data[self::EMAIL],
                        'VALUE_TYPE' => 'WORK'
                    )

                ),
                self::WEB => Array(
                    'n0' => Array(
                        'VALUE' => $data[self::WEB],
                        'VALUE_TYPE' => 'WORK'
                    )

                )
            ),
            self::UF_CRM_1568792370086 => $data[self::UF_CRM_1568792370086],
            self::UF_CRM_1571650551 => $data[self::UF_CRM_1571650551],
            self::UF_CRM_1571650539 => $data[self::UF_CRM_1571650539],
            self::UF_CRM_1571650563 => $data[self::UF_CRM_1571650563],
            self::UF_CRM_1571650585 => $data[self::UF_CRM_1571650585],
            self::UF_CRM_1571650602 => $data[self::UF_CRM_1571650602],
            self::UF_CRM_1571650645 => $data[self::UF_CRM_1571650645],
            self::UF_CRM_1571650663 => $data[self::UF_CRM_1571650663],
        ];

        $Company = new \CCrmCompany();
        if ($Company_ID = $Company->Add($arFields, true, $options)) {
            if (!empty($data['RQ'])) {
                foreach ($data['RQ'] as $rq) {
                    $this->AddRQ($Company_ID, $rq);
                }
            }
        } else {
            $this->errorRes = $this->ErrorAdd;
        }

        if ($this->errorRes !== '') {
            $this->renderJson(['status' => 'error', 'error' => $this->errorRes]);
        } else {
            $this->renderJson(['status' => 'success']);
        }
    }

    //Реквизиты добавить
    protected function AddRQ($companyId, $rq)
    {
        $arFields = [
            'CREATED_BY_ID' => 1,
            'MODIFY_BY_ID' => 1,
            'ACTIVE' => 'Y',
            'ENTITY_ID' => $companyId,
            'ENTITY_TYPE_ID' => 4,
        ];

        foreach ($rq as $key => $datum) {
            $arFields[$key] = $datum;
        }

        $RQ = new EntityRequisite();

        if ($RQ_ID = $RQ->Add($arFields)->getId()) {
        } else {
            $this->errorRes = $this->ErrorAddRQ;
        }
    }

    //Обновление элемента
    public function UpdateElement($CompanyId, $data)
    {
        $arFields = [];
        $arFields ['FM'] = array();

        foreach ($data as $key => $value) {
            if ($key == 'PHONE' || $key == 'EMAIL' || $key == 'WEB') {
                $res = \CCrmFieldMulti::GetList(
                    array('ID' => 'asc'),
                    array('ENTITY_ID' => 'COMPANY', 'ELEMENT_ID' => $CompanyId, 'TYPE_ID' => $key, 'VALUE_TYPE' => 'WORK')
                );
                $FMID = 0;
                while($ar = $res->Fetch()){
                    $FMID = $ar['ID'];
                }
                $FMID = ($FMID !== 0) ? $FMID : 'n0';
                $arFields ['FM'][$key] = Array(
                    $FMID => Array(
                        'VALUE' => $value,
                        'VALUE_TYPE' => 'WORK'
                    )
                );
            }elseif($key != "RQ" && $key != "ID"){
                $arFields[$key] = $value;
            }
        }

        $Comp = new \CCrmCompany(false);

        if ($res = $Comp->Update($CompanyId, $arFields, true, true, array('CHECK_PERMISSIONS' => 'N', 'CURRENT_USER' => 1))) {
            if (!empty($data['RQ'])) {

                foreach ($data['RQ'] as $rq) {

                    if ($rqId = $this->CheckExistenceRQ($CompanyId, $rq['PRESET_ID'])) {
                        $this->UpdateRQ($rqId, $CompanyId, $rq);
                    } else {
                        $this->AddRQ($CompanyId, $rq);
                    };

                }

            }
        } else {
            $this->errorRes = $this->ErrorUpdate;
        }

        if ($this->errorRes !== '') {
            $this->renderJson(['status' => 'error', 'error' => $this->errorRes]);
        } else {
            $this->renderJson(['status' => 'success']);
        }

    }

    //Существуетли реквизит
    private function CheckExistenceRQ($CompanyId, $preset_id)
    {

        $rqOb = new EntityRequisite();
        $param = [
            'filter' => ['ENTITY_ID' => $CompanyId, 'PRESET_ID' => $preset_id],
            'select' => ['ID', 'ENTITY_TYPE_ID'],
        ];
        $rqs = $rqOb->getList($param);
        while ($rq = $rqs->fetch()) {
            return $rq;
        }
        return 0;
    }

    //Реквизиты обновить
    private function UpdateRQ($RQid,$CompanyId, $rq)
    {
        $arFields = array();
        foreach ($rq as $key => $datum) {
            $arFields[$key] = $datum;
        }

        $arFields['ENTITY_TYPE_ID'] = $RQid['ENTITY_TYPE_ID'];
        $arFields['ENTITY_ID'] = $CompanyId;

        $rqOb = new EntityRequisite();
        if ($res = $rqOb->update($RQid['ID'], $arFields, array())) {
        } else {
            $this->errorRes = $this->ErrorUpdateRQ;
        }

    }


    public function PushDeals($data){

        Loader::includeModule('crm');

        $company['COMPANY'] = array();

        $companyId = array();

        //Id компаний которое нужно передать
        $obCheckEx = CompanyCheckExTable::GetList(array());
        while ($CheckEx = $obCheckEx->fetch()){
            $companyId[] = (int)$CheckEx['UF_COMPANY_ID'];
        }



        //нет измененных КОМПАНИЙ
        if (empty($companyId)){
            $this->renderJson($company);
            return;
        }

        $arFilter['ID'] = $companyId;
        $arFilter['CHECK_PERMISSIONS'] = 'N';
        $dbResMultiFields = \CCrmCompany::GetList(Array(), $arFilter, Array(), false);
        while ($arMultiFields = $dbResMultiFields->Fetch()){
            $company['COMPANY'][] = $arMultiFields;
        }

        //push company
        $this->renderJson($company);
    }






    //Реквизиты (Адрес)
    /*protected function AddRQAddress($companyId, $RQ_ID, $rq)
    {
        $arFields = array(
            'ENTITY_ID' => $RQ_ID,
            'ENTITY_TYPE_ID' => 8,
            'ANCHOR_TYPE_ID' => 4,
            'ANCHOR_ID' => $companyId,

        );

        if (is_array($rq[self::ADDRESS]) && !empty($rq[self::ADDRESS])) {
            foreach ($rq[self::ADDRESS] as $id => $datum) {
                $arFields['TYPE_ID'] = $datum[self::ADDRESS_TYPE_ID];
                $arFields[self::ADDRESS_1] = $datum[self::ADDRESS_1];
                $arFields[self::COUNTRY] = $datum[self::COUNTRY];

                $RQAddress = new AddressTable();
                if (!$Addr_ID = $RQAddress->Add($arFields)->getId()) {
                    $this->errorRes = $this->ErrorAddRQAdrr;
                }
            }
        }
    }*/


    /*//Существуетли реквизит
    private function CheckExistenceRQAddr($CompanyId, $RQid){
        $rqOb = new EntityRequisite();
        $param = [
            'filter' => ['ENTITY_ID'=> $CompanyId, 'PRESET_ID' => $preset_id],
            'select' => ['ID'],
        ];
        $rqs = $rqOb->getList($param);
        while($rq = $rqs->fetch()){
            return $rq['ID'];
        }
        return 0;
    }*/

    /*public function CheckDirection($data)
   {
       if ($data[self::DIRECTION] == '1' || $data[self::DIRECTION] == '2'){
           return true;
       }
       return false;
   }*/

}