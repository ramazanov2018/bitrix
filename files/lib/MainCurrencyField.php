<?php
namespace Serv;
use Bitrix\Main;
use Bitrix\Main\Type;


class CurrencyField extends \Bitrix\Main\UserField\TypeBase
{

    const USER_TYPE_ID = 'currency_field';

    /**
     * Returns property type description.
     *
     * @return array
     */
    function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => static::USER_TYPE_ID,
            "CLASS_NAME" => __CLASS__,
            "DESCRIPTION" => 'Валюта',
            "BASE_TYPE" => \CUserTypeManager::BASE_TYPE_DOUBLE,
            "VIEW_CALLBACK" => array(__CLASS__, 'GetPublicView'),
            "EDIT_CALLBACK" => array(__CLASS__, 'getPublicEdit'),
        );
    }

    function GetDBColumnType($arUserField)
    {
        global $DB;
        switch(strtolower($DB->type))
        {
            case "mysql":
                return "varchar(200)";
            case "oracle":
                return "varchar2(200 char)";
            case "mssql":
                return "varchar(200)";
        }
        return '';
    }

    function PrepareSettings($arUserField)
    {
        $prec = intval($arUserField["SETTINGS"]["PRECISION"]);
        $size = intval($arUserField["SETTINGS"]["SIZE"]);
        $min = doubleval($arUserField["SETTINGS"]["MIN_VALUE"]);
        $max = doubleval($arUserField["SETTINGS"]["MAX_VALUE"]);

        return array(
            "PRECISION" => ($prec < 0? 0: ($prec > 12? 12: $prec)),
            "SIZE" =>  ($size <= 1? 20: ($size > 255? 225: $size)),
            "MIN_VALUE" => $min,
            "MAX_VALUE" => $max,
            "DEFAULT_VALUE" => strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0? doubleval($arUserField["SETTINGS"]["DEFAULT_VALUE"]): "",
        );
    }

    function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        $result = '';
        
        return $result;
    }

    function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        if($arUserField["ENTITY_VALUE_ID"]<1 && strlen($arUserField["SETTINGS"]["DEFAULT_VALUE"])>0)
            $arHtmlControl["VALUE"] = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
        if(strlen($arHtmlControl["VALUE"])>0)
            $arHtmlControl["VALUE"] = round(doubleval($arHtmlControl["VALUE"]), $arUserField["SETTINGS"]["PRECISION"]);
        $arHtmlControl["VALIGN"] = "middle";
        return '<input type="text" '.
            'name="'.$arHtmlControl["NAME"].'" '.
            'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
            'value="'.$arHtmlControl["VALUE"].'" '.
            ($arUserField["EDIT_IN_LIST"]!="Y"? 'disabled="disabled" ': '').
            '>';
    }

    function GetFilterHTML($arUserField, $arHtmlControl)
    {
        if(strlen($arHtmlControl["VALUE"]))
            $value = round(doubleval($arHtmlControl["VALUE"]), $arUserField["SETTINGS"]["PRECISION"]);
        else
            $value = "";

        return '<input type="text" '.
            'name="'.$arHtmlControl["NAME"].'" '.
            'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
            'value="'.$value.'" '.
            '>';
    }

    function GetFilterData($arUserField, $arHtmlControl)
    {
        return array(
            "id" => $arHtmlControl["ID"],
            "name" => $arHtmlControl["NAME"],
            "type" => "number",
            "filterable" => ""
        );
    }

    function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        if(strlen($arHtmlControl["VALUE"])>0)
            return round(doubleval($arHtmlControl["VALUE"]), $arUserField["SETTINGS"]["PRECISION"]);
        else
            return '&nbsp;';
    }

    function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        return '<input type="text" '.
            'name="'.$arHtmlControl["NAME"].'" '.
            'size="'.$arUserField["SETTINGS"]["SIZE"].'" '.
            'value="'.round(doubleval($arHtmlControl["VALUE"]), $arUserField["SETTINGS"]["PRECISION"]).'" '.
            '>';
    }

    function CheckFields($arUserField, $value)
    {
        $aMsg = array();

        $value = str_replace(array(',', ' '), array('.', ''), $value);

        if(strlen($value)>0 && $arUserField["SETTINGS"]["MIN_VALUE"]!=0 && doubleval($value)<$arUserField["SETTINGS"]["MIN_VALUE"])
        {
            $aMsg[] = array(
                "id" => $arUserField["FIELD_NAME"],
                "text" => GetMessage("USER_TYPE_DOUBLE_MIN_VALUE_ERROR",
                    array(
                        "#FIELD_NAME#"=>$arUserField["EDIT_FORM_LABEL"],
                        "#MIN_VALUE#"=>$arUserField["SETTINGS"]["MIN_VALUE"]
                    )
                ),
            );
        }
        if(strlen($value)>0 && $arUserField["SETTINGS"]["MAX_VALUE"]<>0 && doubleval($value)>$arUserField["SETTINGS"]["MAX_VALUE"])
        {
            $aMsg[] = array(
                "id" => $arUserField["FIELD_NAME"],
                "text" => GetMessage("USER_TYPE_DOUBLE_MAX_VALUE_ERROR",
                    array(
                        "#FIELD_NAME#"=>$arUserField["EDIT_FORM_LABEL"],
                        "#MAX_VALUE#"=>$arUserField["SETTINGS"]["MAX_VALUE"]
                    )
                ),
            );
        }
        return $aMsg;
    }
   

    function OnSearchIndex($arUserField)
    {
        if(is_array($arUserField["VALUE"]))
            return implode("\r\n", $arUserField["VALUE"]);
        else
            return $arUserField["VALUE"];
    }

   

    public static function getPublicEdit($arUserField, $arAdditionalParameters = array())
    {
        \Bitrix\Main\UI\Extension::load("ui.forms"); 
        
        \CJSCore::Init(array('jquery'));
        
        //$html = '<pre>'.print_r($arUserField, true).'</pre>';
        //       $html .= '<pre>'.print_r($arAdditionalParameters, true).'</pre>';
        
        $value = $arUserField["VALUE"];
        
        if($arUserField['ENTITY_VALUE_ID'] == 0 && $arUserField['FIELD_NAME'] == 'UF_CURRENCY_MAIN_EXP'){
            $value = 'USD';
            
        }
       
        
        $currencyList = \Bitrix\Currency\Helpers\Editor::getListCurrency();
        
        /*$html .= '<pre>';
        $html .= print_r($value, true);
        $html .= '</pre>';*/
        
        $html .= '
        <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
            <div class="ui-ctl-after ui-ctl-icon-angle"></div>
            <select class="ui-ctl-element" id="Currency" name="'.$arUserField['FIELD_NAME'].'">';
        
        foreach ($currencyList as $CurrItem){
            
            
            $html .= '<option value="'.$CurrItem['CURRENCY'].'" '.($CurrItem['CURRENCY'] == $value ? 'selected="selected"' : '').'>'.$CurrItem['NAME'].'</option>';
        }
        /*
               
                
                <option value="">Опция #3</option>*/
        $html .= '</select></div>';
                
        $html .= '<script>
                 $(document).ready(function(){
                   
                   $("#Currency").change(function(){
                      let val = $( this ).val();
                      
                      if($("#CURRENCY_ID")){
                          $("#CURRENCY_ID").val(val);
                          $("#CURRENCY_ID").change(); 
                      }
                      $(\'.field_money\').each(function() { 
                           $(this).val(val);
                           $(this).change(); 
                      });
                    });
                }); 
                </script>';
        

        static::initDisplay();

        return static::getHelper()->wrapDisplayResult($html);
    }

    

    public function GetPublicView($arUserField, $arAdditionalParameters = array())
    {
      
        $currencyList = \Bitrix\Currency\Helpers\Editor::getListCurrency();
        $value = $arUserField["VALUE"];
        
        
        $html = $currencyList[$value]['NAME'];

        
        static::initDisplay();

        return static::getHelper()->wrapDisplayResult($html);
    }


}