<?php

namespace Rns\TestReminder\Controller;

use Bitrix\Main\Engine\Controller;
use \Bitrix\Main\Type\DateTime;
use Rns\TestReminder\TestRemindTable;

class RemindController extends Controller
{

    public function configureActions()
    {
        return [
            /*'reminded' => [
                'prefilters' => [

                ]
            ]*/
        ];
    }

    public function remindedAction()
    {
        global $USER;

        if (!$USER->IsAuthorized())
            return ['result' => 'error'];

        $dt = new DateTime();
        $resOb = TestRemindTable::getList( [
                'filter' => [
                    ">UF_DATE_REMIND" => $dt->format("d.m.Y 00:00:00"),
                    "<UF_DATE_REMIND" => $dt->format("d.m.Y 23:59:59"),
                    "UF_USER_ID" => $USER->GetID()
                ]
            ]
        )->fetch();

        if($resOb)
            return ['result' => 'success'];

        $res = TestRemindTable::add(["UF_DATE_REMIND" => $dt, "UF_USER_ID" => $USER->GetID()]);
        if ($res->isSuccess())
            return ['result' => 'success'];

        return ['result' => 'error'];
    }
}