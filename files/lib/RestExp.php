<?php 
namespace Serv;

class RestExp
{
   public static function OnRestServiceBuildDescription()
   {
      return array(
         'planning' => array(
            'planning.get' => array(
               'callback' => array(__CLASS__, 'get'),
               'options' => array(),
            ),
            'planning.update' => array(
               'callback' => array(__CLASS__, 'update'),
               'options' => array(),
            ),
         ),
        
      );
   }

   public static function get($query, $n, \CRestServer $server)
   {
   	  $DealId = (int)$query['deal_id'];
   	  $Items = [];
   	  if($DealId > 0)
   	      $Items = RegisterCost::GetDataDeal($DealId);
      if($query['error'])
      {
         throw new \Bitrix\Rest\RestException(
            'Message',
            'ERROR_CODE',
            \CRestServer::STATUS_PAYMENT_REQUIRED
         );
      }

      return array('items' => $Items);
   }
  
   public static function update($query, $n, \CRestServer $server)
   {
   	  $Id = (int)$query['ID'];
   	  $Fields = $query['fields'];
   	  $Items = RegisterCost::UpdateDeal($Id, $Fields);
   	  if($Id)
   	  	$resutn = ['statusUpdate' => true];
   	  else 
   	  	$resutn = ['newElement' => $Items];	
   	  
   	
      if($query['error'])
      {
         throw new \Bitrix\Rest\RestException(
            'Message',
            'ERROR_CODE',
            \CRestServer::STATUS_PAYMENT_REQUIRED
         );
      }

      return $resutn;
   }
}



?>