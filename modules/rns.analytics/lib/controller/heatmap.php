<?php

namespace Rns\Analytics\Controller;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;
use Exception;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Heatmap extends AnalyticsAbstractController
{
    const LOG_TYPE = 'RNS_ANALYTICS_HEATMAP_CREATE_LOG';
    const EVENT_NAME = 'Формирование тепловой карты кликов';

    /**
     * @param $url
     * @param $users
     * @param $fromDate
     * @param $toDate
     * @return mixed|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getClicksAction($url, $users, $fromDate, $toDate)
    {
        global $USER;
        $success = true;
        try {
            $apiUrl = Option::get(self::MODULE_ID, 'RNSANALYTICS_OPT_API_URL');
            $apiUrl .= '/getClicks';
            $data = [
                'url' => $url,
                'users' => $users,
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ];
            $httpClient = new HttpClient();
            $httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded', true);
            $result = $httpClient->post($apiUrl, $data);
            if (!$result) {
                $result = $httpClient->getError();
                $success = false;
            } else {
                $result = json_decode($result, true);
                if (!$result['success']) {
                    $result = $result['data'];
                    $success = false;
                }
            }
        } catch (Exception $e) {
            $result = $e->getMessage();
            $success = false;
        }
        if ($success) {
            $message = Loc::getMessage('RNS_LIB_MSG', ["#USERS#" => print_r($users, true), "#DATE_FROM#" => $fromDate, "#DATE_TO#" =>$toDate]);
        } else {
            $message = $result;
        }
        self::writeToLog($USER->GetID(), $url, $message);
        return $result;
    }
}