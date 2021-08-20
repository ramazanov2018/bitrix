<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.07.2020
 * Time: 13:11
 */

namespace Serv;


class UnitOfMeasure
{

    const USER_TYPE = 'UnitOfMeasure';

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
                'DESCRIPTION' => 'Единица измерения',
                'GetPropertyFieldHtml' => array($className, 'getPropertyFieldHtml'),
                "GetPublicEditHTML" => array($className,"GetPublicEditHTML"),
                "GetPublicViewHTML" => array($className, "GetPublicViewHTML"),
                'GetAdminListViewHTML' => array($className, 'GetPublicViewHTML'),


        );
    }


    public static function GetPublicEditHTML($property, $value, $controlSettings)
    {

        $multi = (isset($property['MULTIPLE']) && $property['MULTIPLE'] == 'Y');

        if ($multi){
            return  self::GetPropertyFieldHtmlMulty($property, $value, $controlSettings);
        }

        return  self::getPropertyFieldHtml($property, $value, $controlSettings);
    }

    /**
     * Return html for public view value.
     *
     * @param array $property Property data.
     * @param array $value Current value.
     * @param array $controlSettings Form data.
     * @return string
     */
    public static function GetPublicViewHTML($property, $value, $controlSettings)
    {
        if (isset($value['VALUE'])){
            return self::GetMeasureName($value['VALUE']);
        }
        return '';
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {

        $Measures = self::GetList();

        $html = '<select name = "'.$strHTMLControlName["VALUE"].'">';
        $html .= '<option value="">Не установлено</option>';
        foreach ($Measures as $measure){
            $selected = ($measure['CODE'] == $value["VALUE"]) ? 'selected' : '';
            $html .= '<option '.$selected.' value="'.$measure['CODE'].'">'.
                htmlspecialcharsbx($measure['MEASURE_TITLE']).'</option>';
        }
        $html .= '</select>';

        //return pre([$Measures, $arProperty, $value, $strHTMLControlName]);//$result;
        return $html;

    }

    public static function GetPropertyFieldHtmlMulty($arProperty, $value, $strHTMLControlName)
    {

        
       
        $Measures = self::GetList();

        $html = '<select name = "'.$strHTMLControlName["VALUE"].'">';
        $html .= '<option value="">Не установлено</option>';
        foreach ($Measures as $measure){
            $selected = ($measure['CODE'] == $value["VALUE"]) ? 'selected' : '';
            $html .= '<option '.$selected.' value="'.$measure['CODE'].'">'.
                htmlspecialcharsbx($measure['MEASURE_TITLE']).'</option>';
        }
        $html .= '</select>';

        return $html;

    }


    public static function GetList()
    {
        $Measures = array();
        $params = array('select' => array('*'));
        $resOb = MeasureTable::getList($params);

        while ($res = $resOb->fetch()){
            $Measures[] = $res;
        }

        return $Measures;
    }

    public static function GetMeasureName($code)
    {
        //$params = array('select' => array('MEASURE_TITLE'), 'filter' => array('=CODE' => $code));
        $params = array('select' => array('MEASURE_TITLE'), 'filter' => array('=ID' => $code));

        return  MeasureTable::getList($params)->fetch()["MEASURE_TITLE"];
    }

}