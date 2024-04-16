<?php
namespace Rns\Analytics\Controller;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use CEventLog;

class AnalyticsAbstractController extends Controller
{
    const MODULE_ID = "rns.analytics";
    const HL_LOG_NAME = 'RnsAnalyticsLog';
    const LOG_TYPE = null;
    const EVENT_NAME = null;

    /**
     * @return array
     */
    public function configureActions()
    {
        return [];
    }

    /**
     * @param $userId
     * @param $url
     * @param $result
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function writeToLog($userId, $url, $result)
    {
        $logTable = HighloadBlockTable::getList([
            'filter' => [
                'NAME' => self::HL_LOG_NAME
            ]
        ]);
        if ($hldata = $logTable->fetch()) {
            $hlentity = HighloadBlockTable::compileEntity($hldata);
            $logClass = $hlentity->getDataClass();
        } else {
            return false;
        }
        $data = [
            "UF_RNS_USER_ID" => $userId,
            "UF_RNS_EVENT_NAME" => static::EVENT_NAME,
            "UF_RNS_URL" => $url,
            "UF_RNS_RESULT" => $result,
            "UF_RNS_DATE" => new DateTime
        ];
        $logClass::add($data);

        CEventLog::Add([
            "SEVERITY" => "SECURITY",
            "AUDIT_TYPE_ID" => static::LOG_TYPE,
            "MODULE_ID" => self::MODULE_ID,
            "ITEM_ID" => self::MODULE_ID,
            "DESCRIPTION" => $result
        ]);
    }
}