<?php
namespace Fbit\Quickrunintegration;


class quickrunBase
{
    private $ModuleId     = 'fbit.quickrunintegration';
    private $quickrunPath     = '';
    private $quickrunToken    = '';

    function __construct()
    {
        $this->quickrunPath     = \Bitrix\Main\Config\Option::get($this->ModuleId, 'QUICRUN_INTEGRATION_SERVER_IP', '');
        $this->quickrunToken    = \Bitrix\Main\Config\Option::get($this->ModuleId, 'QUICRUN_INTEGRATION_TOKEN', '');
    }

    public function request($path, $request = array(), $post = true)
    {
        if(!$post)
            $path = $path."?".http_build_query($request);

        $request = json_encode($request);

        $header = array(
            "Content-Type: application/json",
            "Authorization: $this->quickrunToken",

        );
        $ch = curl_init($path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        }


        $response  = curl_exec($ch);
        $header = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $ContentType =  curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        curl_close($ch);

        if(stripos($ContentType, 'application/json') !== false)
            $response = json_decode($response, true);

        if($header == '200'){
            return $response;
        }

        return false;
    }

    function GetPath()
    {
        return $this->quickrunPath;
    }
    function GetToken()
    {
        return $this->quickrunToken;
    }
}