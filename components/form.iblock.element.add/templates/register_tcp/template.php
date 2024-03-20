<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
?>
<script src="<?=$templateFolder?>/jquery.maskedinput.min.js"></script>
<? if (count($arResult["ERRORS"])) : ?>
    <?= ShowError(implode("<br />", $arResult["ERRORS"])) ?>
<? endif ?>
<? if (strlen($arResult["MESSAGE"]) > 0) : ?>
    <?= ShowNote($arResult["MESSAGE"]) ?>
<? endif ?>
<p><?=GetMessage('IBLOCK_FORM_required_prop_title')?></p>
<form name="iblock_add" action="<?= POST_FORM_ACTION_URI ?>" method="post" enctype="multipart/form-data">

    <?= bitrix_sessid_post() ?>

    <? if ($arParams["MAX_FILE_SIZE"] > 0) : ?><input type="hidden" name="MAX_FILE_SIZE" value="<?= $arParams["MAX_FILE_SIZE"] ?>" /><? endif ?>

    <? if (is_array($arResult["PROPERTY_LIST"]) && count($arResult["PROPERTY_LIST"]) > 0) : ?>
        <? foreach ($arResult["PROPERTY_LIST"] as $propertyID) :
        ?>

            <div class="col-margin-bottom inputBlock" <? if ($propertyID == "DATE_ACTIVE_FROM" || $propertyID == "DATE_ACTIVE_TO") : ?> style="display:none" <? endif; ?>>
                <?if($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == "STREET"):?>
                    <h5>Адрес проживания:</h5>
                <?endif?>
                <?if($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == "PASSPORT_SERIAL"):?>
                    <h5>Паспорт:</h5>
                <?endif?>
                <?if($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == "FIO_CHILDREN"):?>
                    <h5>Законный представитель ребенка-инвалида:</h5>
                <?endif?>
                <?if($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == "BIRTH_CERT_SERIAL"):?>
                    <h5>Свидетельство о рождении:</h5>
                <?endif?>
                <div class="mb10">
                    <p class="form-label">
                        <? if (intval($propertyID) > 0 && $arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] != "POLIS") : ?>
                            <?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["NAME"] ?>
                        <? elseif (intval($propertyID) > 0 && $arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == "POLIS") : ?>
                            <?= GetMessage("POLIS") ?>
                        <?
                        else : ?>
                            <?= !empty($arParams["CUSTOM_TITLE_" . $propertyID]) ? $arParams["CUSTOM_TITLE_" . $propertyID] : GetMessage("IBLOCK_FIELD_" . $propertyID) ?>
                        <? endif ?>
                        <? if (in_array($propertyID, $arResult["PROPERTY_REQUIRED"])) : ?>
                            <span class="req">*</span>
                        <? endif ?>
                    </p>
                </div>
                <?
                if (intval($propertyID) > 0) {
                    if (
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "T"
                        &&
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] == "1"
                    )
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "S";
                    elseif (
                        ($arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "S"
                            ||
                            $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "N"
                        )
                        &&
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] > "1"
                    )
                        $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "T";
                } elseif (($propertyID == "TAGS") && CModule::IncludeModule('search'))
                    $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] = "TAGS";

                if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y") {
                    $inputNum = ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) ? count($arResult["ELEMENT_PROPERTIES"][$propertyID]) : 0;
                    $inputNum += $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE_CNT"];
                } else {
                    $inputNum = 1;
                }

                if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["GetPublicEditHTML"])
                    $INPUT_TYPE = "USER_TYPE";
                else
                    $INPUT_TYPE = $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"];

                if ($propertyID == "DATE_ACTIVE_FROM") : ?>
                    <input type="hidden" name="PROPERTY[<?= $propertyID ?>][0]" value="<?= date($DB->DateFormatToPHP(CLang::GetDateFormat())) ?>" />
                <? elseif ($propertyID == "DATE_ACTIVE_TO") : ?>
                    <? if (COption::GetOptionString("bitrix.sitemedicine", 'feedback_period')) : ?>
                        <input type="hidden" name="PROPERTY[<?= $propertyID ?>][0]" value="<?= date($DB->DateFormatToPHP(CLang::GetDateFormat()), strtotime('+' . COption::GetOptionString("bitrix.sitemedicine", 'feedback_period') . ' day')) ?>" />
                    <? else : ?>
                        <input type="hidden" name="PROPERTY[<?= $propertyID ?>][0]" value="" />
                    <? endif; ?>
                    <?
                else :
                    switch ($INPUT_TYPE):
                        case "USER_TYPE":
                            for ($i = 0; $i < $inputNum; $i++) {
                                if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                    $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["~VALUE"] : $arResult["ELEMENT"][$propertyID];
                                    $description = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["DESCRIPTION"] : "";
                                } elseif ($i == 0) {
                                    $value = intval($propertyID) <= 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];
                                    $description = "";
                                } else {
                                    $value = "";
                                    $description = "";
                                }
                                echo call_user_func_array(
                                    $arResult["PROPERTY_LIST_FULL"][$propertyID]["GetPublicEditHTML"],
                                    array(
                                        $arResult["PROPERTY_LIST_FULL"][$propertyID],
                                        array(
                                            "VALUE"       => $value,
                                            "DESCRIPTION" => $description,
                                        ),
                                        array(
                                            "VALUE"       => "PROPERTY[" . $propertyID . "][" . $i . "][VALUE]",
                                            "DESCRIPTION" => "PROPERTY[" . $propertyID . "][" . $i . "][DESCRIPTION]",
                                            "FORM_NAME"   => "iblock_add",
                                        ),
                                    )
                                );?>
                                <!-- <input type="date" name="PROPERTY[<?= $propertyID ?>][<?= $i ?>]" size="25" value="" /> -->
                            <?}
                            break;
                        case "TAGS":
                            $APPLICATION->IncludeComponent(
                                "bitrix:search.tags.input",
                                "",
                                array(
                                    "VALUE" => $arResult["ELEMENT"][$propertyID],
                                    "NAME"  => "PROPERTY[" . $propertyID . "][0]",
                                    "TEXT"  => 'size="' . $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] . '"',
                                ),
                                null,
                                array("HIDE_ICONS" => "Y")
                            );
                            break;
                        case "HTML":
                            $LHE = new CLightHTMLEditor;
                            $LHE->Show(array(
                                'id'               => preg_replace("/[^a-z0-9]/i", '', "PROPERTY[" . $propertyID . "][0]"),
                                'width'            => '100%',
                                'height'           => '200px',
                                'inputName'        => "PROPERTY[" . $propertyID . "][0]",
                                'content'          => $arResult["ELEMENT"][$propertyID],
                                'bUseFileDialogs'  => false,
                                'bFloatingToolbar' => false,
                                'bArisingToolbar'  => false,
                                'toolbarConfig'    => array(
                                    'Bold', 'Italic', 'Underline', 'RemoveFormat',
                                    'CreateLink', 'DeleteLink', 'Image', 'Video',
                                    'BackColor', 'ForeColor',
                                    'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
                                    'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent',
                                    'StyleList', 'HeaderList',
                                    'FontList', 'FontSizeList',
                                ),
                            ));
                            break;
                        case "T":
                            for ($i = 0; $i < $inputNum; $i++) {
                                if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                    $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
                                } elseif ($i == 0) {
                                    $value = intval($propertyID) > 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];
                                } else {
                                    $value = "";
                                }
                                ?>
                                <textarea class="input input-block" cols="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?>" rows="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] ?>" name="PROPERTY[<?= $propertyID ?>][<?= $i ?>]"><?= $value ?></textarea><?
                            }
                            break;
                        case "S":
                        case "N":
                            for ($i = 0; $i < $inputNum; $i++) {
                                if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                    $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
                                } elseif ($i == 0) {
                                    $value = intval($propertyID) <= 0 ? "" : $arResult["PROPERTY_LIST_FULL"][$propertyID]["DEFAULT_VALUE"];
                                } else {
                                    $value = "";
                                }
                                if (!$value && $USER->IsAuthorized()) {
                                    $rsUser = CUser::GetByID($USER->GetID());
                                    $arUser = $rsUser->Fetch();
                                    if ($propertyID == "NAME") {
                                        if (strpos($APPLICATION->GetCurPage(), "feedback.php") === false)
                                            $value = $arUser['LAST_NAME'];
                                        else
                                            $value = $arUser['LAST_NAME'] . " " . $arUser['NAME'] . " " . $arUser['SECOND_NAME'];
                                    } elseif ($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == "FIRST_NAME")
                                        $value = $arUser['NAME'];
                                    elseif ($arUser[$arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE']])
                                        $value = $arUser[$arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE']];
                                    elseif($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE']=="SECOND_NAME")
                                    $value=$arUser['SECOND_NAME'];
                                elseif($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE']=="PERSONAL_PHONE")
                                    $value=$arUser['PERSONAL_PHONE'];
                                elseif($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE']=="EMAIL")
                                    $value=$arUser['EMAIL'];			
                                }
                                if($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == 'PHONE'){
                                    $type = 'phone';
                                }elseif($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == 'EMAIL'){
                                    $type = 'email';
                                }elseif($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == 'PASSPORT_SERIAL'){
                                    $type = 's_serial';
                                }elseif($arResult["PROPERTY_LIST_FULL"][$propertyID]['CODE'] == 'PASSPORT_NUMBER'){
                                    $type = 's_number';
                                }else{
                                    $type = 'text';
                                }
                                ?>
                                <input data-type="<?=$type?>" class="input col-6" type="<?=$type?>" name="PROPERTY[<?= $propertyID ?>][<?= $i ?>]" size="<?= $arParams["DEFAULT_INPUT_SIZE"] ?>" value="<?= htmlspecialcharsbx($value) ?>" />
                                <?if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["USER_TYPE"] == "DateTime") :
                                    $APPLICATION->IncludeComponent(
                                        'bitrix:main.calendar',
                                        '',
                                        array(
                                            'FORM_NAME'    => 'iblock_add',
                                            'INPUT_NAME'   => "PROPERTY[" . $propertyID . "][" . $i . "]",
                                            'INPUT_VALUE'  => $value,
                                            'SHOW_TIME'    => 'Y',
                                            'HIDE_TIMEBAR' => 'N'
                                        ),
                                        null,
                                        array('HIDE_ICONS' => 'Y')
                                    );
                                    ?>
                                    <small><?= GetMessage("IBLOCK_FORM_DATE_FORMAT") ?><?= FORMAT_DATETIME ?></small>
                                <?endif?>
                            <?}
                            break;
                        case "F":
                            for ($i = 0; $i < $inputNum; $i++) {
                                $value = intval($propertyID) > 0 ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE"] : $arResult["ELEMENT"][$propertyID];
                                ?>
                                <input type="hidden" name="PROPERTY[<?= $propertyID ?>][<?= $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i ?>]" value="<?= $value ?>" />
                                <input type="file" size="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["COL_COUNT"] ?>" name="PROPERTY_FILE_<?= $propertyID ?>_<?= $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i ?>" />
                                <?if (!empty($value) && is_array($arResult["ELEMENT_FILES"][$value])) {?>
                                    <input type="checkbox" name="DELETE_FILE[<?= $propertyID ?>][<?= $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] ? $arResult["ELEMENT_PROPERTIES"][$propertyID][$i]["VALUE_ID"] : $i ?>]" id="file_delete_<?= $propertyID ?>_<?= $i ?>" value="Y" /><label for="file_delete_<?= $propertyID ?>_<?= $i ?>"><?= GetMessage("IBLOCK_FORM_FILE_DELETE") ?></label>
                                    <?if ($arResult["ELEMENT_FILES"][$value]["IS_IMAGE"]) {?>
                                        <img src="<?= $arResult["ELEMENT_FILES"][$value]["SRC"] ?>" height="<?= $arResult["ELEMENT_FILES"][$value]["HEIGHT"] ?>" width="<?= $arResult["ELEMENT_FILES"][$value]["WIDTH"] ?>" border="0" />
                                    <?} else {?>
                                        <?= GetMessage("IBLOCK_FORM_FILE_NAME") ?>: <?= $arResult["ELEMENT_FILES"][$value]["ORIGINAL_NAME"] ?>

                                        <?= GetMessage("IBLOCK_FORM_FILE_SIZE") ?>: <?= $arResult["ELEMENT_FILES"][$value]["FILE_SIZE"] ?> 
                                        b[<a href="<?= $arResult["ELEMENT_FILES"][$value]["SRC"] ?>"><?= GetMessage("IBLOCK_FORM_FILE_DOWNLOAD") ?></a>]<?
                                    }
                                }
                            }
                            break;
                        case "L":
                            /*if($arResult["PROPERTY_LIST_FULL"][$propertyID]['IS_REQUIRED']=="N")
                            $arResult["PROPERTY_LIST_FULL"][$propertyID]['ENUM'][0]=array('ID'=>0,"VALUE"=>GetMessage("NULL_VALUE"));*/
                            if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["LIST_TYPE"] == "C")
                                $type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" || count($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"]) == 1 ? "checkbox" : "radio";
                            else
                                $type = $arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" ? "multiselect" : "dropdown";
                                
                            switch ($type):
                                case "checkbox":
                                case "radio":
                                    foreach ($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"] as $key => $arEnum) {
                                        $checked = false;
                                        if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                            if (is_array($arResult["ELEMENT_PROPERTIES"][$propertyID])) {
                                                foreach ($arResult["ELEMENT_PROPERTIES"][$propertyID] as $arElEnum) {
                                                    if ($arElEnum["VALUE"] == $arEnum['ID']) {
                                                        $checked = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        } else {
                                            if ($arEnum["DEF"] == "Y")
                                                $checked = true;
                                        }

                                        ?>
                                            <input class="checkbox" type="<?= $type ?>" name="PROPERTY[<?= $propertyID ?>]<?= $type == "checkbox" ? "[" . $key . "]" : "" ?>" value="<?= $arEnum['ID'] ?>" id="property_<?= $key ?>" <?= $checked ? " checked=\"checked\"" : "" ?> /><label for="property_<?= $key ?>"><span></span><?= $arEnum["VALUE"] ?></label>
                                        <?
                                    }
                                    break;
                                case "dropdown":
                                case "multiselect":
                                    ?>
                                    <select class="styler col-6" name="PROPERTY[<?= $propertyID ?>]<? if ($type == "multiselect") : ?>[]" size="<?= $arResult["PROPERTY_LIST_FULL"][$propertyID]["ROW_COUNT"] ?>" multiple="multiple<? elseif ($arResult["PROPERTY_LIST_FULL"][$propertyID]['USER_TYPE'] == "UserID") : ?>[0]<?endif ?>">
                                        <?
                                    if (intval($propertyID) > 0)
                                        $sKey = "ELEMENT_PROPERTIES";
                                    else $sKey = "ELEMENT";

                                    foreach ($arResult["PROPERTY_LIST_FULL"][$propertyID]["ENUM"] as $key => $arEnum) {
                                        $checked = false;
                                        if ($arParams["ID"] > 0 || count($arResult["ERRORS"]) > 0) {
                                            foreach ($arResult[$sKey][$propertyID] as $elKey => $arElEnum) {
                                                if ($arEnum['ID'] == $arElEnum["VALUE"]) {
                                                    $checked = true;
                                                    break;
                                                }
                                            }
                                        } else {
                                            if ($arEnum["DEF"] == "Y")
                                                $checked = true;
                                        }
                                        ?>
                                            <option value="<?= $arEnum['ID'] ?>" <?= $checked ? " selected=\"selected\"" : "" ?> <?= ($arEnum['DISABLED'] == "Y") ? " class=\"sect\" disabled=\"disabled\"" : "" ?>><?= $arEnum["VALUE"] ?></option>
                                        <?
                                    }?>
                                    </select>
                                    <?break;
                            endswitch;
                            break;
                        endswitch;
                    endif; ?>
            </div>
        <? endforeach; ?>
        <? if (class_exists('Bitrix\Main\UserConsent\Agreement') && !empty($arParams['USER_CONSENT_ID'])) { ?>
            <div class="col-margin-bottom">
                <input type="hidden" name="iblock_submit" value="Y">
                <? $APPLICATION->IncludeComponent(
                    "bitrix:main.userconsent.request",
                    "",
                    array(
                        "AUTO_SAVE"  => $arParams['USER_CONSENT'],
                        "ID"         => $arParams['USER_CONSENT_ID'],
                        "IS_CHECKED" => $arParams['USER_CONSENT_IS_CHECKED'],
                        "IS_LOADED"  => $arParams['USER_CONSENT_IS_LOADED']
                    )
                ); ?>
            </div>
        <? } ?>
        <? if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0) : ?>
            <div class="col-margin-bottom">
                <div class="mb10"><?= GetMessage("IBLOCK_FORM_CAPTCHA_TITLE") ?></div>
                <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>" />
                <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>" width="180" height="40" alt="CAPTCHA" />
            </div>
            <div class="col-margin-bottom inputBlock">
                <div class="mb10"><?= GetMessage("IBLOCK_FORM_CAPTCHA_PROMPT") ?><span class="form-required starrequired">*</span>:</div>
                <input class="input col-6" type="text" name="captcha_word" maxlength="50" value="">
            </div>
        <? endif ?>
    <? endif ?>
    <div class="col-margin-bottom">
        <input class="btn btn-large" type="submit" name="iblock_submit" value="<?= GetMessage("IBLOCK_FORM_SUBMIT") ?>" />
        <? if (strlen($arParams["LIST_URL"]) > 0 && $arParams["ID"] > 0) : ?>
            <input type="submit" name="iblock_apply" value="<?= GetMessage("IBLOCK_FORM_APPLY") ?>" /><? endif ?>
        <? /*<input type="reset" value="<?=GetMessage("IBLOCK_FORM_RESET")?>" />*/ ?>
    </div>

    <? if (strlen($arParams["LIST_URL"]) > 0) : ?><a href="<?= $arParams["LIST_URL"] ?>"><?= GetMessage("IBLOCK_FORM_BACK") ?></a><? endif ?>
</form>

<h3>Загрузите сканы документов:</h3>
<p>
- Паспорт (титульная страница и страница с регистрацией, свидетельство о рождении ребенка-инвалида).<br>
* Инвалид (ветеран) либо лицо, представляющее его интересы, вправе по собственной инициативе представить в уполномоченный орган документ, подтверждающий регистрацию инвалида (ветерана) в системе индивидуального (персонифицированного) учета, в системе обязательного пенсионного страхования (СНИЛС), а также программу реабилитации, содержащую рекомендации по обеспечению техническими средствами.<br>
*ИПР (все страницы).<br>
Размер файла вложения не может превышать 4 Мб. (Общий объем прикрепленных файлов не более - 15 Мб.) Для вложений допустимы следующие форматы файлов: docx, doc, xls, xlsx, ppt, pptx, pdf, jpeg, jpg, png.<br>
<ul>
    <li>
    Введите текст обращения – текст, необязательно
    </li>
    <li>
    Чекбокс «С порядком приема, рассмотрения обращений и обработки данных ознакомлен(а)» - обязательно
    </li>
</ul>
</p>
<h3>Уважаемые пользователи!</h3>
<p>
Просим Вас внимательно ознакомиться с порядком приема и рассмотрения обращений в форме электронного документа.
<ul>
    <li>
    1. Поступившие в данный раздел обращения рассматриваются в соответствии с федеральными законами от 2 мая 2006 года No 59-ФЗ «О порядке рассмотрения обращений граждан в Российской Федерации».
    </li>
    <li>
    2. В обращении указывается: фамилия, имя, отчество (при наличии) заявителя; почтовый/ электронный адрес, по которому должен быть направлен ответ/ уведомление; излагается суть предложения, заявления, жалобы. На обращение, в котором не указаны фамилия заявителя, направившего обращение, или почтовый/ электронный адрес, по которому должен быть направлен ответ, ответ на обращение не дается.
    </li>
    <li>
    3. Порядок рассмотрения отдельных обращений осуществляется в соответствии с требованиями, установленными статьей 11 Федеральным законом Российской Федерации от 2 мая 2006 года No 59-ФЗ.
    </li>
</ul>
</p>