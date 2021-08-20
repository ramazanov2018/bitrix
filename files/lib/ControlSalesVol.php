<?php 
namespace Serv; 

use Bitrix\Main\Localization\Loc;
use Bitrix\Currency\CurrencyTable;
use Bitrix\Currency\Helpers\Editor;
use Bitrix\Main\Type\RandomSequence;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class ControlSalesVol
{
    const USER_TYPE = 'ControlSalesVol';
    const SEPARATOR = '|';
    
    /**
     * Returns property type description.
     *
     * @return array
     */
    public static function getUserTypeDescription()
    {
        $className = get_called_class();
        return array(
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => self::USER_TYPE,
            'DESCRIPTION' => 'Покупаемость',
            'GetPublicEditHTML' => array($className, 'getPublicEditHTML'),
            'GetPublicViewHTML' => array($className, 'getPublicViewHTML'),
            'GetPropertyFieldHtml' => array($className, 'getPropertyFieldHtml'),
            'GetAdminListViewHTML' => array($className, 'getAdminListViewHTML'),
            'CheckFields' => array($className, 'checkFields'),
            'GetLength' => array($className, 'getLength'),
            'ConvertToDB' => array($className, 'convertToDB'),
            'ConvertFromDB' => array($className, 'convertFromDB'),
            'AddFilterFields' => array($className, 'addFilterFields'),
        );
    }
    
    /**
     * Return html for public edit value.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @param array $controlSettings Form data.
     * @return string
     */
    public static function getPublicEditHTML($property, $value, $controlSettings)
    {
        $Id = $value['ELEMENT_ID'];
       
        return  self::GetData($Id);
    }
    
    /**
     * Return html for public view value.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @param array $controlSettings Form data.
     * @return string
     */
    public static function getPublicViewHTML($property, $value, $controlSettings)
    {
        $Id = $value['ELEMENT_ID'];
        return self::GetData($Id);
    }
    static function GetData($Id){
        $data = ControlSalesVolTable::GetList(['select' => ['*'], 'filter' => ['IBLOCK_ELEMENT_ID' => $Id]])->Fetch();
        
        $Period         = (int)$data['PROPERTY_414'];
        $Plan           = round($data['PROPERTY_410'], 2);
        $ProdId         = (int)$data['PROPERTY_409'];
        $ContragentId   = (int)$data['PROPERTY_413'];
        
        
        $PeriodStart  = new \Bitrix\Main\Type\DateTime();
        $PeriodStart->add('-'.$Period.' day');
        $PeriodEnd    = new \Bitrix\Main\Type\DateTime();
        
        $Percent = 0;
        $Period = 'c '.$PeriodStart->format('d.m.Y').' по '.$PeriodEnd->format('d.m.Y');
        
        
        $Sales = self::GetControlSales($PeriodStart, $PeriodEnd, $ProdId, $ContragentId);
        if( $Plan > 0 )
            $Percent = round($Sales/$Plan*100, 2);
        
        
        return $Sales.' из '.$Plan.' ('.$Percent.'%)<br/><small>'.$Period.'</small>';
        
    }
    static function GetControlSales($PeriodStart, $PeriodEnd, $CatProdId, $ContragentId){
       
        $rs = \Bitrix\Crm\DealTable::GetList([
            'select' => [
               //'INDEX' => 'ProdCat.PROPERTY_340',
               //'PRODUCT_ID' => 'Prod.PRODUCT_ID',
               new \Bitrix\Main\Entity\ExpressionField('SALES_VOL', 'SUM(%s * %s)', array('Prod.QUANTITY', 'ProdCat.PROPERTY_340'))
            ],
            'filter' => [
                'COMPANY_ID' => $ContragentId, 
                'STAGE_SEMANTIC_ID' => 'S',
                '>CLOSEDATE' => $PeriodStart,
                '<=CLOSEDATE' => $PeriodEnd,
                'ProdCat.PROPERTY_352' => $CatProdId,
            ],
            'runtime' => [
                new \Bitrix\Main\Entity\ReferenceField(
                    'Prod',
                    'Bitrix\Crm\ProductRowTable',
                    array(
                        '=this.ID' => 'ref.OWNER_ID',
                        '=ref.OWNER_TYPE' => new \Bitrix\Main\DB\SqlExpression('?s', 'D'),
                    )
                ),
                new \Bitrix\Main\Entity\ReferenceField(
                    'ProdCat',
                    'Serv\ProductPropertyTable',
                    array(
                        '=this.Prod.PRODUCT_ID' => 'ref.IBLOCK_ELEMENT_ID',
                        //'ref.PROPERTY_352' => $CatProdId
                    )
                )   
            ]
            
        ]);
        
        $Ar = $rs->Fetch();
      
        return round($Ar['SALES_VOL'], 2);
    }
    /**
     * The method should return the html display for editing property values in the administrative part.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @param array $controlSettings Form data.
     * @return string
     */
    public static function getPropertyFieldHtml($property, $value, $controlSettings)
    {
        $seed = (!empty($controlSettings['VALUE'])) ? $controlSettings['VALUE'] : 'IMPSeed';
        $randomGenerator = new RandomSequence($seed);
        $randString = strtolower($randomGenerator->randString(6));
        
        $explode = is_string($value['VALUE']) ? explode(self::SEPARATOR, $value['VALUE']) : array();
        $currentValue = $explode[0] ? $explode[0] : '';
        $currentCurrency = $explode[1] ? $explode[1] : '';
        
        $html = '<input type="text" style="width: auto;" value="'.htmlspecialcharsbx($currentValue).
        '" id="input-'.$randString.'">';
        $html .= '<input type="hidden" id="hidden-'.$randString.'" name="'.
            htmlspecialcharsbx($controlSettings['VALUE']).'" value="'.htmlspecialcharsbx($value["VALUE"]).'">';
            $listCurrency = self::getListCurrency();
            if($listCurrency)
            {
                if($property['MULTIPLE'] == 'Y')
                    $html .= '<input type="hidden" data-id="'.$randString.'">';
            }
            
            return  $html;
    }
    
    /**
     * The method must return safe HTML display the value of the properties on the list of items the administrative part.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @param array $controlSettings Form data.
     * @return mixed|string
     */
    public static function getAdminListViewHTML($property, $value, $controlSettings)
    {
            return  'getAdminListViewHTML';
    }
    
    /**
     * Check fields before inserting into the database.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @return array An empty array, if no errors.
     */
    public static function checkFields($property, $value)
    {
        $result = array();
      
            
        return $result;
    }
    
    /**
     * Get the length of the value. Checks completion of mandatory.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @return int
     */
    public static function getLength($property, $value)
    {
        return strlen(trim($value['VALUE'], "\n\r\t"));
    }
    
    /**
     * The method is to convert the value of a format suitable for storage in a database.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @return mixed
     */
    public static function convertToDB($property, $value)
    {
        return '----';
    }
    
    /**
     * The method is to convert the property value in the processing format.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @return mixed
     */
    public static function convertFromDB($property, $value)
    {
        return '------';
    }
    
    private static function getSeparatedValues($value)
    {
        $explode = is_string($value) ? explode(self::SEPARATOR, $value) : array();
        $currentValue = $explode[0] ? $explode[0] : '';
        $currentCurrency = $explode[1] ? $explode[1] : '';
        $format = \CCurrencyLang::GetFormatDescription($currentCurrency);
        $explode = explode($format['DEC_POINT'], $currentValue);
        $currentValue = $explode[0] ? $explode[0] : '';
        $decimalsValue = $explode[1] ? $explode[1] : '';
        return '1';//array($currentValue, $currentCurrency, $decimalsValue);
    }
    
    /**
     * Add values in filter.
     *
     * @param array $property Property data.
     * @param array $controlSettings Form data.
     * @param array &$filter Filter data.
     * @param bool &$filtered Marker filter.
     * @return void
     */
    public static function addFilterFields($property, $controlSettings, &$filter, &$filtered)
    {
        $filtered = false;
        
       
    }
    
    
  
}