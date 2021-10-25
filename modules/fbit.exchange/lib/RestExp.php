<?php 
namespace Fbit\Exchange;


class RestExp
{
    /****** Расширяем стандартный модуль restApi *****/
    public static function OnRestServiceBuildDescription()
    {
        return array(
            'fbit.Appl' => array(
                'fbit.Appl.SetStatus' => array('callback' => array(__CLASS__, 'SetStatus'),'options' => array()),
            )
        );
    }
    // Обработчbr обновления статуса
    public static function SetStatus($query, $n, \CRestServer $server){
        
        foreach($query['items'] as $item){
            
            $guid = $item['guid'];
            
            $result[] = ['guid' => $guid, 'status' => 'success'];
            
        }
        return $result;
    }
}

