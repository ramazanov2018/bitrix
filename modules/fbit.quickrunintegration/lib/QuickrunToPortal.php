<?php
namespace Fbit\Quickrunintegration;
use  \Bitrix\Crm\DealTable,
    \Bitrix\Main\Type\DateTime,
    Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;


	
	function logFile($textLog, $filename = "logFile.txt")
{	
	$file = $_SERVER["DOCUMENT_ROOT"]. "/local/modules/fbit.quickrunintegration/lib/".$filename;
	$text = "=======================\n";
	$text .= print_r($textLog, 1); //Выводим переданную переменную
	$text .= "\n". date('Y-m-d H:i:s') ."\n"; //Добавим актуальную дату после текста или дампа массива
	file_put_contents($file, $text . "\n", FILE_APPEND); 
}
	LogFile('zapusk', 'zapusk.log'); //

Loc::loadMessages(__FILE__);
class QuickrunToPortal extends quickrunBase
{
	function logFile($textLog, $filename = "logFile.txt")
{	
	$file = $_SERVER["DOCUMENT_ROOT"]. "/local/modules/fbit.quickrunintegration/lib/".$filename;
	$text = "=======================\n";
	$text .= print_r($textLog, 1); //Выводим переданную переменную
	$text .= "\n". date('Y-m-d H:i:s') ."\n"; //Добавим актуальную дату после текста или дампа массива
	file_put_contents($file, $text . "\n", FILE_APPEND); 
}
	
	
    static $CLASS_NAME = 'QuickrunToPortal';
    private static $CATEGORY_ID = '2';

    private static $workflowTemplateId = 44;

    private $courier = 'UF_CRM_1597220735036';
    private $courierComment = 'UF_CRM_1602576681';
    private $courierMoney = 'UF_CRM_1602576733';
    private $DealStatus = 'C2:WON';

    private $date = '';

    private $path = 'client/orders/';

    private $Orders = array();

    private $state = array(
        1 => 'C2:WON',       //Доставлено - Выполнено
        2 => 'C2:APOLOGY',   //Отменено - Не доставил
        3 => 'C2:EXECUTING', //Ожидание - На доставке
        //4 => 'C2:PREPARATION',//Спланировано							
    );

    function __construct()
    {
        $this->date = new DateTime();
        $this->date->add('1d');
        parent::__construct();
    }

    //Сделки для обмена
    private function DealData()
    {
        $res = false;

        $select = array('ID', 'ORDER', 'ORDER.ORDER_NUMBER', 'BEGINDATE',  'ASSIGNED_BY_ID',);
        $filter =  array(
            'CATEGORY_ID' => self::$CATEGORY_ID, //Id направления
            //'ID'                            => 11219,
            //'!CRM_DEAL_ORDER_ORDER_NUMBER' => false,
            //'!UF_CRM_1597150541' => false, 
            '!BEGINDATE' => false, 
            '<=BEGINDATE' => $this->dateDelivery(), 
            //'=BEGINDATE' => $this->dateDelivery(), //---Дата начала---поставил новое условие
            'CLOSED'=> 'N' //не закрыто
            //'!STAGE_ID' => 'C2:WON', //не выполнено - поставил новое условие
            //'>ID' => 24550,
        );

        $DealOb = DealTable::getList([
            'select' => $select,
            'order' => array('ID' => 'asc'),
            'filter' => $filter,
            'runtime' => [
                'ORDER' => [
                    'data_type' => DealOrderTable::class,
                    'reference' => [
                        '=this.UF_CRM_1597150541' => 'ref.DEAL_ID',
                    ],
                    ['join_type' => 'LEFT']
                ],
            ],
            //'limit' => 10 
        ]);

        while($data = $DealOb->Fetch()){
            
            $order['ORDER_NUMBER'] = $data['ID'];//['CRM_DEAL_ORDER_ORDER_NUMBER'];
            $order['ASSIGNED_BY_ID'] = $data['ASSIGNED_BY_ID'];
            $order['BEGINDATE'] = $data['BEGINDATE'];
            $order['ID'] = $data['ID'];
            $this->Orders[] = $order;
            $res = true;
			
			 self::LogFile($data, 'data.log'); //
        }
        
        return $res;
    }

    //Обмен
    public function Exchange()
    {
        if (!$this->DealData())
            return;

        $Result =  "######### ".$this->date->format("d-m-Y")." ######### ".PHP_EOL.PHP_EOL;
        foreach ($this->Orders as $order){
            $DID = (int)$order['ID'];

            $url = $this->CreateURL($order['BEGINDATE'], $order['ORDER_NUMBER']);//54
           
            //request запрос
            $req = $this->request($url, array(), false);
            if ($req != false && !empty($req['result']['0']) && $req['success'] == 1){
			
				 //self::LogFile($req['result'], 'req_result.log'); //

                if ($this->UpdateDeal($DID, $req['result']['0'])){
                    $Result .=  Loc::getMessage('QINTEGRATION_EXCHANGE_SUCCESSFUL', array('#DID#' => $DID)). PHP_EOL;
                }else{
                    $Result .= $this->CreateErrorMsg($DID, Loc::getMessage('QINTEGRATION_EXCHANGE_ERROR'));
                }

            }elseif (!empty($req['error'])){
                $Result .= $this->CreateErrorMsg($DID, $req['error']);
            }else{
                $Result .= $this->CreateErrorMsg($DID, Loc::getMessage('QINTEGRATION_NO_ORDER'));
            }
        }

        $Result .=  PHP_EOL."######### ".$this->date->format("d-m-Y")." ######### ".PHP_EOL;
        
		
        //запись в лог
        quickrunLog::SaveLog(self::$CLASS_NAME, $Result);
    }

    //Обновление сделки
    private function UpdateDeal($ID, $req = array())
    {
        $res = true;

        $arFields[$this->courier] = "[".$req['courier']['id']."] ".$req['courier']['name'];

        $delivery =  $req['delivery']['state']['id'];
       
	    self::LogFile($delivery, 'delivery.log'); //
		//self::LogFile($req, 'req.log'); //
		
        if (empty($delivery) && !empty($req['courier']['id'])){
            $delivery = 3;
        }

        if (!empty($req['delivery']['comment'])){
            $arFields[$this->courierComment] = $req['delivery']['comment'];
        }

        if (!empty($req['delivery']['money'])){
            $arFields[$this->courierMoney] = $req['delivery']['money'];
			
        }
        $DealStatus = '';
        if(array_key_exists($delivery, $this->state)) {
            $arFields['STAGE_ID'] = $this->state[$delivery];
            $DealStatus = $this->state[$delivery];
			
			
			
        }
        
		
		 self::LogFile($DealStatus, 'DealStatus.log'); //
        // 14.04
        //4.04 = распределено 
        //13.04 = спланировано
        //12.04 = спланировано
        
        if( $delivery == 3 ){ // на доставке
            // если дата достаки заказа = завтра.
            
            $Deal = DealTable::getList(['filter' => ['=ID' => $ID], 'select' => ['BEGINDATE']])->Fetch();
            
            $Tomorrow = new \Bitrix\Main\Type\DateTime(date('d.m.Y'));
            $Tomorrow->Add('1d');
            
            if($Deal['BEGINDATE'] == $Tomorrow){
                $DealStatus = 'C2:PREPARATION';
                $arFields['STAGE_ID']  = 'C2:PREPARATION'; //Переопределяем на спланировано
            }
            
            
            
        }
       // PRE($DealStatus);
       
        /*PRE('-------------------------');
        PRE($ID);
        PRE($arFields);*/
        
        $result = DealTable::update($ID, $arFields);
		
		//self::LogFile($result, 'result.log'); //

        if (!$result->isSuccess()){
            $res = false;
        }elseif ($result->isSuccess() && $DealStatus == $this->DealStatus){
           
            self::StartWorkflow($ID, self::$workflowTemplateId); //44 - C2:WON
			
            
        }elseif ($result->isSuccess() && $DealStatus == 'C2:EXECUTING'){
            
            self::StartWorkflow($ID, 43); // Запуск Робота на стадии "Распределено"
            
        }elseif ($result->isSuccess() && $DealStatus == 'C2:PREPARATION'){
            
            self::StartWorkflow($ID, 41); // Запуск Робота на стадии "Спланировано"
        }
        

        return $res;
    }

    private function CreateURL( DateTime $dateOb, $number)
    {
        $url = $this->GetPath();

        $url .= $this->path.$dateOb->format("Y-m-d")."/".$number;
        return $url;
    }

    public static function GetClassName()
    {
        return self::$CLASS_NAME;
    }

    private function dateDelivery()
    {
        return  $this->date->format("d.m.Y");
    }

    private function CreateErrorMsg($DID, $msg){
        return Loc::getMessage('QINTEGRATION_EXCHANGE_ERROR_MSG', array('#DID#' => $DID, '#MSG#' => $msg)). PHP_EOL;
    }

    public static function StartWorkflow($dID, $WFId)
    {
        Loader::includeModule('workflow');
        Loader::includeModule('bizproc');

        $arErrorsTmp = array();

        $wfId =\CBPDocument::StartWorkflow(
            $WFId,
            array("crm","CCrmDocumentDeal","DEAL_".$dID),
            array(),
            $arErrorsTmp
        );
    }

}