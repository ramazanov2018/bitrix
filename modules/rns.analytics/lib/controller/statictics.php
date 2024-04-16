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

class Statictics extends AnalyticsAbstractController
{
    const LOG_TYPE = 'RNS_ANALYTICS_STATISTICS_CREATE_LOG';
    const DYNAMIC_URL_SEPARATOR = 'dynamic_url';
    const EVENT_NAME = 'Формирование статистики';

    /**
     * @param $url
     * @param $users
     * @param $fromDate
     * @param $toDate
     * @return array|mixed|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getVisitsAction($url, $users, $fromDate, $toDate)
    {
        global $USER;
        $success = true;
        try {
            $apiUrl = Option::get(self::MODULE_ID, 'RNSANALYTICS_OPT_API_URL');
            $apiUrl .= '/getVisits';
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
                $pattern = '/(.+)?'.self::DYNAMIC_URL_SEPARATOR.'(.+)?'.self::DYNAMIC_URL_SEPARATOR.'/';
                foreach ($result['data'] as &$item) {
                    if(strpos($item['url'], self::DYNAMIC_URL_SEPARATOR)){
                        try {
                            $textBase64 = preg_replace($pattern, '$2', $item['url']);
                            $replace =  '$1?'.base64_decode($textBase64);
                            $item['url'] = preg_replace($pattern, $replace,  $item['url']);
                        }catch (Exception $e){
                            $item['url'] = preg_replace($pattern, '$1',  $item['url']);
                        }
                    }
                    $item['title'] = $item['title'] . ' (' . $item['url'] . ')';
                    $item['time'] = gmdate("H:i:s", $item['active']);
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