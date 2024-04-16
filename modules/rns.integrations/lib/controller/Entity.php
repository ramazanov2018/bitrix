<?php

namespace RNS\Integrations\Controller;

use Bitrix\Main\Engine\Controller;
use CURLFile;
use RNS\Integrations\Helpers\EntityFacade;
use RNS\Integrations\Helpers\HLBlockHelper;

class Entity extends Controller
{
    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function statusesAction()
    {
        $request = $this->getRequest();

        $entityType = $request->get('entityType');

        $list = HLBlockHelper::getList('b_hlsys_status_entity', ['ID', 'UF_CODE', 'UF_RUS_NAME'], ['ID'],
          'UF_CODE', ['UF_ENTITY_TYPE_BIND' => $entityType, 'UF_ACTIVE' => 1], false);

        return ['list' => $list];
    }

    public function testDbConnectionAction()
    {
        $request = $this->getRequest();
        $type = $request->get('type');
        $host = $request->get('host');
        $port = $request->get('port');
        $dbName = $request->get('dbname');
        $userName = $request->get('username');
        $password = $request->get('password');

        return EntityFacade::testDbConnection($type, $host, $port, $dbName, $userName, $password);
    }

    public function testConverterConnectionAction()
    {
        $request = $this->getRequest();
        $url = $request->get('url');

        $filePath = $_SERVER['DOCUMENT_ROOT'].'/local/modules/rns.integrations/templates/tasks.mpp';

        $file = new CURLFile($filePath, 'application/vnd.ms-project', basename($filePath));

        $data = ['file' => $file];

        $curlOptions = [
          CURLOPT_URL => $url,
          CURLOPT_POST => true,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_POSTFIELDS => $data
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);

        $response = curl_exec($ch);
        $result = !empty($response) && !curl_errno($ch);
        if ($result) {
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result = $code == 200;
        }
        curl_close($ch);
        return $result;
    }
}
