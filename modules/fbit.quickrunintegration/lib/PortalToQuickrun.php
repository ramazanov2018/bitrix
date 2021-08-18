<?php
namespace Fbit\Quickrunintegration;
use  \Bitrix\Crm\DealTable,
    \Bitrix\Main\Type\DateTime,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PortalToQuickrun extends quickrunBase
{
    static $CLASS_NAME = 'PortalToQuickrun';

    private static $CATEGORY_ID = '2'; // Доставка - направление сделки

    private $date = '';
    private $today = '';
    private $DiffDaysTo = "1 day";
    private $path = 'client/orders/';
    private $bezPriborov = 'Без приборов';
    private $priborId = 34;

    private $Orders = array();
    

    function __construct()
    {
        $this->date = new DateTime();
        
        
        $this->today = new DateTime();
        $this->date->add($this->DiffDaysTo);
        parent::__construct();
    }

    //Сделки для обмена
    private function DealData()
    {
        $res = false;

        global $DB;

        $select = array('ID', 'ASSIGNED_BY_ID', 'UF_CRM_1596788387342', 'OPPORTUNITY',
            'UF_CRM_1596788332050','UF_CRM_1597150541','COMMENTS',
            'BEGINDATE', 'TITLE', 'COMMENTS', 'CONTACT_ID', 'CONTACT' ,
            'CONTACT.PHONE', 'TIME', 'PRODUCTS', 'ORDER', 'ORDER.ORDER_NUMBER','UF_CRM_1600941899298',
            'UF_CRM_1596789844', 'UF_CRM_1597149817420', 'UF_CRM_1598357706836', 'TYPE_PAY'
        );

        $filter =array(
           //'ID'                            => 10665,
            'CATEGORY_ID'                   => self::$CATEGORY_ID, 
            'BEGINDATE'                     => $this->dateDelivery(),
            //'!CRM_DEAL_ORDER_ORDER_NUMBER'  => false,
            //'!UF_CRM_1597150541'            => false, 
            'CLOSED'                        => 'N',
            //'>ID'                           => 24438
        );
        $sqlProducts = array(
            '('.$DB->TopSql('SELECT GROUP_CONCAT(CONCAT(
                            prod.PROPERTY_437
                    	)) 
                             FROM b_crm_product_row crm_prod 
                             LEFT JOIN  b_iblock_element_prop_s70 prod
                             ON crm_prod.PRODUCT_ID = prod.IBLOCK_ELEMENT_ID
                             WHERE crm_prod.PRODUCT_ID != 2291 AND crm_prod.OWNER_TYPE = "D" AND  crm_prod.OWNER_ID = %s',0).')', 'ID'
        );

        $sqlTypePay = array(
            '('.$DB->TopSql('SELECT VALUE
                             FROM b_user_field_enum user_e
                             WHERE user_e.ID = %s',1).')', 'UF_CRM_1598357706836'
        );

        $DealOb = DealTable::getList([
            'select' => $select,
            'filter' => $filter,
            'runtime' => [
                'CONTACT' => [
                    'data_type' => quickrunContactTable::class,
                    'reference' => [
                        '=this.CONTACT_ID' => 'ref.ID',
                    ],
                    ['join_type' => 'LEFT']
                ],

                'ORDER' => [
                    'data_type' => DealOrderTable::class,
                    'reference' => [
                        '=this.UF_CRM_1597150541' => 'ref.DEAL_ID',
                    ],
                    ['join_type' => 'LEFT']
                ],
                'TIME' => [
                    'data_type' => quiqrunEnumTable::class,
                    'reference' => [
                        '=this.UF_CRM_1596788387342' => 'ref.ID',
                    ],
                    ['join_type' => 'LEFT']
                ],
                'PRODUCTS' => [
                    'data_type' => 'string',
                    'expression' => $sqlProducts,
                    ['join_type' => 'LEFT']
                ],

                'TYPE_PAY' => [
                    'data_type' => 'string',
                    'expression' => $sqlTypePay,
                    ['join_type' => 'LEFT']
                ],
            ],
           
        ]);

        while($data = $DealOb->Fetch()){

            $goods = $data['PRODUCTS'];
            if ($data['UF_CRM_1596789844'] == $this->priborId){
                $goods .= ', '.$this->bezPriborov;
            }

            //$comments = strip_tags($data['COMMENTS']);// strip_tags
            
			$data_comm = $data['COMMENTS'];
			$chars = ['&lt;','br&gt;', '&nbsp;']; // символы для удаления &lt; br&gt; &lt; br&gt; &nbsp;
			$comments = str_replace($chars, '', $data_comm); // 

			
			
			
			if ($data['UF_CRM_1597149817420'] == 1){
                $comments .=', способ оплаты - '. $data['TYPE_PAY'] ;
            }
            $time = explode('-', $data['CRM_DEAL_TIME_VALUE']);
            $order['timeFrom']          = trim($time[0]);
            $order['timeTo']            = trim($time[1]);
            $order['address']           = $data['UF_CRM_1596788332050'];
            $order['buyerName']         = $data['CRM_DEAL_CONTACT_LAST_NAME']. ' ' .$data['CRM_DEAL_CONTACT_NAME']. ' ' .$data['CRM_DEAL_CONTACT_SECOND_NAME'];
            $order['goods']             = $goods;
            $order['number']            = $data['ID'];//$data['CRM_DEAL_ORDER_ORDER_NUMBER'];
            $order['phone']             = $data['CRM_DEAL_CONTACT_PHONE'];
            $order['price']             = $data['UF_CRM_1600941899298'];//$data['OPPORTUNITY'];
            $order['additionalInfo']    = $comments;
            $order['ASSIGNED_BY_ID']    = $data['ASSIGNED_BY_ID'];
            
            $this->Orders[$data["ID"]]  = $order;
            $res = true;
        }
        return $res;
    }

    //Обмен
    public function Exchange()
    {

        if (!$this->DealData())
            return;

        $url = $this->CreateURL();

        $error = array();

        $Result =  "######### ".$this->today->format("d-m-Y")." ######### ".PHP_EOL.PHP_EOL;
          foreach ($this->Orders as $dId => $order){
            $assigned = ($order['ASSIGNED_BY_ID']) ? (int)$order['ASSIGNED_BY_ID'] : 1;
            unset($order['ASSIGNED_BY_ID']);

            //request запрос
            $resp = $this->request($url, $order, true);
            pre($dId);
            PRE($resp);
            if ($resp['success']){
                $Result .=  Loc::getMessage('QINTEGRATION_EXCHANGE_SUCCESSFUL', array('#DID#' => $dId)). PHP_EOL;
            }else{
                $msg = $this->CreateErrorMsg($dId, $resp['error']);
                $Result .= $msg.PHP_EOL;
                $error[$assigned][] = $msg;
            }
        }

        $Result .=  PHP_EOL."######### ".$this->today->format("d-m-Y")." ######### ".PHP_EOL;
       
        //запись в лог
        quickrunLog::SaveLog(self::$CLASS_NAME, $Result);

        $this->Notify($error);
    }

    //Уведомление ответственного
    private function Notify($arFields = array())
    {
        if(!\Bitrix\Main\Loader::includeModule('im') || empty($arFields))
            return;

        foreach ($arFields as $id => $arField) {
            $fields = [
                "TO_USER_ID" => $id, // ID пользователя
                "FROM_USER_ID" => 0,  // От кого (0 - системное)
                "MESSAGE_TYPE" => "S",
                "NOTIFY_MODULE" => "im",
                "NOTIFY_TITLE" => Loc::getMessage('QINTEGRATION_NOTIFY_TITLE').$this->today->format("d-m-Y"), // Тема сообщения
                "NOTIFY_MESSAGE" => implode(PHP_EOL, $arField), // Текст сообщения
            ];
            $msg = new \CIMMessenger();

            $res = $msg->Add($fields);
        }
    }

    private function dateDelivery()
    {
        return  $this->date->format("d.m.Y");
    }

    private function CreateURL()
    {
        $url = $this->GetPath();

        $url .= $this->path.$this->date->format("Y-m-d");
        return $url;
    }

    public static function GetClassName()
    {
        return self::$CLASS_NAME;
    }
    private function CreateErrorMsg($DID, $msg){
        return Loc::getMessage('QINTEGRATION_EXCHANGE_ERROR_MSG', array('#DID#' => $DID, '#MSG#' => $msg));
    }

}