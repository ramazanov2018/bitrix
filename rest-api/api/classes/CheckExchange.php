<?php

namespace Api\Classes;


use Serv\CheckEx\CompanyCheckExTable;
use Serv\CheckEx\ContactCheckExTable;
use Serv\CheckEx\DealCheckExTable;


/*Класс контролирует успешность обмена*/

class CheckExchange
{
    public function CheckController($data)
    {
        if (empty($data["RES_ID"])){
            return;
        }

        switch ($data['ENTITY']){
            case 'DEAL':
                $this->CheckExDeal($data['RES_ID']);
                break;
            case 'COMPANY':
                $this->CheckExCompany($data['RES_ID']);
                break;
            case 'CONTACT':
                $this->CheckExContact($data['RES_ID']);
                break;
            default:
                $this->renderJson(['status'=>'error', 'error' => 'Сущность не найдено!']);
                return;
        }
    }

    private function CheckExDeal($dealId)
    {
        $obDealEx = DealCheckExTable::GetList(array('filter' => array('=UF_DEAL_ID'=>$dealId)));
        while ($DealEx = $obDealEx->fetch())
        {
            DealCheckExTable::Delete($DealEx["ID"]);
        }

        $this->renderJson(['status'=>'success']);
    }

    private function CheckExCompany($CompanyId)
    {
        $obCompanyEx = CompanyCheckExTable::GetList(array('filter' => array('=UF_COMPANY_ID'=>$CompanyId)));
        while ($CompanyEx = $obCompanyEx->fetch())
        {
            CompanyCheckExTable::Delete($CompanyEx["ID"]);
        }

        $this->renderJson(['status'=>'success']);
    }

    private function CheckExContact($ContactId)
    {
        $obContactEx = ContactCheckExTable::GetList(array('filter' => array('=UF_CONTACT_ID'=>$ContactId)));
        while ($ContactEx = $obContactEx->fetch())
        {
            ContactCheckExTable::Delete($ContactEx["ID"]);
        }

        $this->renderJson(['status'=>'success']);
    }


    //Возврат статуса
    private function renderJson($status)
    {
        echo json_encode($status, JSON_UNESCAPED_UNICODE);
    }
}