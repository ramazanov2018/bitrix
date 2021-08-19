<?php
namespace Api\Classes;

use Bitrix\Main\Loader;
use Serv\CheckEx\ContactCheckExTable;

class ContactApiClass
{
    const NAME ='NAME'; //Имя
    const LAST_NAME = 'LAST_NAME'; //Фамилия
    const POST = 'POST'; //Должность
    const EMAIL = 'EMAIL'; //e-mail
    const PHONE_WORK = 'PHONE_WORK'; //Телефон офисный
    const PHONE_MOBILE = 'PHONE_MOBILE'; //Телефон мобильный
    const COMPANY_ID = 'COMPANY_ID'; //Id компании
    const ASSIGNED_BY_ID = 'ASSIGNED_BY_ID'; //Ответственный

    private $ErrorAdd = 'Ошибка при сохранении';
    private $ErrorUpdate = 'Ошибка при обновлении';
    private $ErrorNotFound = 'Контакт не найден';
    private $ContactId = 0;

    //Возврат статуса
    protected function renderJson($status)
    {
        echo json_encode($status, JSON_UNESCAPED_UNICODE);
    }

    public function Controller($data, $DIRECTION)
    {
        Loader::includeModule('crm');

        $data['ID'] = trim($data['ID']);
        $data['ORIGIN_ID'] = trim($data['ORIGIN_ID']);

        if ($data['ID'] > 0) {
            if ($this->ContactId = $this->CheckExistenceCompany(array('ID' => $data['ID']))) {
                $this->UpdateElement($this->ContactId, $data);
            }
        }

        if (!empty($data['ORIGIN_ID']) && $this->ContactId <= 0) {
            if ($this->ContactId = $this->CheckExistenceCompany(array('ORIGIN_ID' => $data['ORIGIN_ID']))) {
                $this->UpdateElement($this->ContactId, $data);
            }
        }

        if ($this->ContactId <= 0) {
            $this->renderJson(['status'=>'error', 'error'=>$this->ErrorNotFound]);
        }
    }

    public function UpdateElement($ContactId, $data)
    {

        $options['CURRENT_USER'] = 1;
        $arFields = [
            'MODIFY_BY_ID' => 1,
            'CREATE_BY_ID' => 1,
            'FM' => []];

        foreach ($data as $key => $value){
            if ($key == 'COMPANY_ID'){
                $Company = \CCrmCompany::GetById($data[self::COMPANY_ID], false);
                $arFields['COMPANY_ID'] = $value;
                $arFields['ASSIGNED_BY_ID'] = ($Company[self::ASSIGNED_BY_ID] !== "") ? $Company[self::ASSIGNED_BY_ID]: 1;
            }elseif ($key == 'EMAIL' || $key == 'PHONE_WORK' || $key == 'PHONE_MOBILE' ){
                switch ($key){
                    case 'PHONE_WORK':
                        $FMID = $this->GetIdCrmFieldMulti(array('ELEMENT_ID' => $ContactId, 'TYPE_ID' => 'PHONE', 'VALUE_TYPE' => 'WORK'));
                        $FMID = ($FMID !== 0) ? $FMID : 'n0';
                        $arFields['FM']['PHONE'][$FMID] = ['VALUE' => $value, 'VALUE_TYPE' => 'WORK'];
                        break;

                    case 'PHONE_MOBILE':
                        $FMID = $this->GetIdCrmFieldMulti(array('ELEMENT_ID' => $ContactId, 'TYPE_ID' => 'PHONE', 'VALUE_TYPE' => 'MOBILE'));
                        $FMID = ($FMID !== 0) ? $FMID : 'n1';
                        $arFields['FM']['PHONE'][$FMID] = ['VALUE' => $value, 'VALUE_TYPE' => 'MOBILE'];
                        break;
                    case 'EMAIL':
                        $FMID = $this->GetIdCrmFieldMulti(array('ELEMENT_ID' => $ContactId, 'TYPE_ID' => 'EMAIL', 'VALUE_TYPE' => 'WORK'));
                        $FMID = ($FMID !== 0) ? $FMID : 'n0';
                        $arFields['FM']['EMAIL'][$FMID] = ['VALUE' => $value, 'VALUE_TYPE' => 'WORK'];
                        break;
                }
            }else{
                $arFields[$key] = $value;
            }
        }

        $Contact = new \CCrmContact(false);
        if($PRODUCT_ID = $Contact->Update($ContactId, $arFields)){
            $this->renderJson(['status' => 'success']);
        } else{
            $this->renderJson(['status'=>'error', 'error'=>$this->ErrorUpdate]);
        }

    }

    //Существуетли контакт
    public function CheckExistenceCompany($arFilter)
    {
        $arFilter['CHECK_PERMISSIONS'] = 'N';
        $dbResMultiFields = \CCrmContact::GetList(Array(), $arFilter, Array('ID'), false);
        while ($arMultiFields = $dbResMultiFields->Fetch()) {
            return $arMultiFields['ID'];
        }

        return 0;
    }

    private function GetIdCrmFieldMulti($arFilter)
    {
        $arFilter['ENTITY_ID'] = 'CONTACT';

        $res = \CCrmFieldMulti::GetList(
            array('ID' => 'asc'),
            $arFilter
        );
        while($ar = $res->Fetch()){
            return  $ar['ID'];
        }
        return 0;
    }



    public function PushDeals($data){

        Loader::includeModule('crm');

        $contacts['CONTACTS'] = array();

        $contactsId = array();

        //Id КОНТАКТОВ которое нужно передать
        $obCheckEx = ContactCheckExTable::GetList(array());
        while ($CheckEx = $obCheckEx->fetch()){
            $contactsId[] = (int)$CheckEx['UF_CONTACT_ID'];
        }



        //нет измененных контактов
        if (empty($contactsId)){
            $this->renderJson($contacts);
            return;
        }

        $arFilter['ID'] = $contactsId;
        $arFilter['CHECK_PERMISSIONS'] = 'N';
        $dbResMultiFields = \CCrmContact::GetList(Array(), $arFilter, Array(), false);
        while ($arMultiFields = $dbResMultiFields->Fetch()){
            $contacts['CONTACTS'][] = $arMultiFields;
        }

        //push company
        $this->renderJson($contacts);
    }



    //Новый элемент
    /*public function AddElement($data)
    {

        $options['CURRENT_USER'] = 1;
        $data[self::COMPANY_ID] = (int)$data[self::COMPANY_ID];

        $Company = \CCrmCompany::GetById($data[self::COMPANY_ID], false);

        $arFields = [
            self::NAME => $data[self::NAME],
            self::POST => $data[self::POST],
            self::LAST_NAME => $data[self::LAST_NAME],
            self::COMPANY_ID => $data[self::COMPANY_ID],
            self::ASSIGNED_BY_ID => ($Company[self::ASSIGNED_BY_ID] !== "") ? $Company[self::ASSIGNED_BY_ID]: 1,
            'MODIFY_BY_ID' => 1,
            'CREATE_BY_ID' => 1,
            'FM' => Array(
                'PHONE' => Array(
                    'n0' => Array(
                        'VALUE' => $data[self::PHONE_WORK],
                        'VALUE_TYPE' => 'WORK'
                    ),
                    'n1' => Array(
                        'VALUE' => $data[self::PHONE_MOBILE],
                        'VALUE_TYPE' => 'MOBILE'
                    )
                ),
                self::EMAIL => Array(
                    'n0' => Array(
                        'VALUE' => $data[self::EMAIL],
                        'VALUE_TYPE' => 'WORK'
                    )
                )
            ),
        ];

        $Contact = new \CCrmContact();
        if($PRODUCT_ID = $Contact->Add($arFields, true, $options)){
            $this->renderJson(['status' => 'success']);
            return $PRODUCT_ID;
        } else{
            $this->renderJson(['status'=>'error', 'error'=>$this->ErrorAdd]);
            return false;
        }

    }*/

}