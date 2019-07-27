<?php
use Courses\CoursesControl;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");// первый общий пролог
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rns.courses/include.php"); // инициализация модуля
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/rns.courses/prolog.php"); // пролог модуля

// подключим языковой файл
IncludeModuleLangFile(__FILE__);

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("rns.courses");

// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("course_tab_course"), "ICON"=>"main_user_edit", "TITLE"=>""),
    array("DIV" => "edit2", "TAB" => GetMessage("course_tab_additional_property"), "ICON"=>"main_user_edit", "TITLE"=>""),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$ID = intval($ID);		// идентификатор редактируемой записи
$message = null;		// сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

$CoursesControl = new CoursesControl(); // Класс для работы с БД (DataManager)

if(
    $REQUEST_METHOD == "POST" // проверка метода вызова страницы
    &&
    $POST_RIGHT=="W"          // проверка наличия прав на запись для модуля
    &&
    check_bitrix_sessid()     // проверка идентификатора сессии
)
{
    // обработка данных формы
    $arFields = Array(
            "NAME" => $_REQUEST["NAME"],
            "CODE" => $_REQUEST["CODE"],
            "DESCRIPTION" => $_REQUEST["DESCRIPTION"],
            "PRICE" => $_REQUEST["PRISE"],
            "SORT" => $_REQUEST["SORT"],
            "ACTIVE" => ($_REQUEST["ACTIVE"])?"Y":"N",
            "DATE_START" => $_REQUEST["DATE_START"],
            "DATE_END" => $_REQUEST["DATE_END"],
    );

    // сохранение данных
    if($ID > 0)
    {
        echo $ID;
        $res = $CoursesControl->Update($ID, $arFields);
    }
    else
    {
        $ID = $CoursesControl->Add($arFields);
        echo $ID;
        $res = ($ID > 0);
    }

    if($res)
    {
        // если сохранение прошло удачно - перенаправим на новую страницу
        // (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
        if ($apply != "")
            // если была нажата кнопка "Применить" - отправляем обратно на форму.
            LocalRedirect("/bitrix/admin/course_edit.php?ID=".$ID."&mess=ok⟨=".LANG."&".$tabControl->ActiveTabParam());
        else
            // если была нажата кнопка "Сохранить" - отправляем к списку элементов.
            LocalRedirect("/bitrix/admin/courses_list.php?lang=".LANG);
    }
    else
    {
        // если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
        if($e = $APPLICATION->GetException())
            $message = new CAdminMessage(GetMessage("course_save_error"), $e);
        $bVarsFromForm = true;
    }
}

















$str_NAME          = "";
$str_CODE          = "";
$str_DESCRIPTION   = "";
$str_PRICE         = "";
$str_SORT          = 100;
$str_ACTIVE        = "Y";
$str_DATE_START    = "";
$str_DATE_END      = "";




if($ID>0)
{
    $courses=[];
    $arrCourse = $CoursesControl::GetByID($ID);
    while ($cours = $arrCourse->fetch())
    {
        $courses[] = $cours;
    }

    if (!empty($courses))
    {
        foreach ($courses["0"] as $key=>$value)
        {
            switch ($key)
            {
                case "NAME":
                    $str_NAME = $value;
                    break;
                case "CODE":
                    $str_CODE = $value;
                    break;
                case "DESCRIPTION":
                    $str_DESCRIPTION = $value;
                    break;
                case "PRICE":
                    $str_PRICE = $value;
                    break;
                case "SORT":
                    $str_SORT = $value;
                    break;
                case "ACTIVE":
                    $str_ACTIVE = $value;
                    break;
                case "DATE_START":
                    $str_DATE_START = $value;
                    break;
                case "DATE_END":
                    $str_DATE_END = $value;
                    break;
            }
        }
    }
}

// если данные переданы из формы, инициализируем их
/*if($bVarsFromForm)
$DB->InitTableVarsForEdit("b_list_rubric", "", "str_");
$APPLICATION->SetTitle(($ID>0? GetMessage("rub_title_edit").$ID : GetMessage("rub_title_add")));*/
// здесь будет вся серверная обработка и подготовка данных

?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
?>
<?
$aMenu = array(
    array(
        "TEXT"=>GetMessage("course_list"),
        "TITLE"=>GetMessage("course_list_title"),
        "LINK"=>"courses_list.php?lang=".LANG,
        "ICON"=>"btn_list",
    )
);
$context = new CAdminContextMenu($aMenu);
$context->Show();
// здесь будет вывод страницы

if($_REQUEST["mess"] == "ok" && $ID>0)
    CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("course_saved"), "TYPE"=>"OK"));

if($message)
    echo $message->Show();

//elseif($rubric->LAST_ERROR!="")
//    CAdminMessage::ShowMessage($rubric->LAST_ERROR);
?>










<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>"  name="post_form">
    <?// проверка идентификатора сессии ?>
    <?echo bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANG?>">
    <?if($ID>0 && !$bCopy):?>
        <input type="hidden" name="ID" value="<?=$ID?>">
    <?endif;?>
    <?
    // отобразим заголовки закладок
    $tabControl->Begin();
    ?>
    <?
    //********************
    // первая закладка - форма редактирования параметров курсов
    //********************
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%"><?echo GetMessage("course_act")?></td>
        <td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?> /></td>
    </tr>

    <!--  HTML-код строк таблицы -->

    <tr class="adm-detail-required-field">
        <td><?echo GetMessage("course_name")?></td>
        <td><input type="text" name="NAME" value="<?echo $str_NAME;?>" size="45" maxlength="100"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("course_sort")?></td>
        <td><input type="text" name="SORT" value="<?echo $str_SORT;?>" size="6"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("course_code")?></td>
        <td><input type="text" name="CODE" value="<?echo $str_CODE;?>" size="45"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("course_price")?></td>
        <td><input type="number" name="PRISE" value="<?echo $str_PRICE;?>" size="45"></td>
    </tr>
    <tr>
        <td><?echo GetMessage("course_start")?></td>
        <td>
                <input  type="date" name="DATE_START"  value="<?echo $str_DATE_START;?>">
        </td>
    </tr>
    <tr>
        <td><?echo GetMessage("course_end")?></td>
        <td>
                <input  type="date" name="DATE_END"  value="<?echo $str_DATE_END;?>">
        </td>
    </tr>
    <tr>
        <td class="adm-detail-valign-top"><?echo GetMessage("course_desc")?></td>
        <td><textarea class="typearea" name="DESCRIPTION" cols="45" rows="5" wrap="VIRTUAL" style="width:100%"><?echo $str_DESCRIPTION; ?></textarea></td>
    </tr>
    <?
    //********************
    // вторая закладка - параметры автоматической генерации рассылки
    //********************
    $tabControl->BeginNextTab();
    ?>
    <?
    // завершение формы - вывод кнопок сохранения изменений
    $tabControl->Buttons(
        array(
            "disabled"=>($POST_RIGHT<"W"),
            "back_url"=>"courses_list.php?lang=".LANG,
        )
    );
    ?>
    <?
    // завершаем интерфейс закладки
    echo bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANG?>">
    <?if($ID>0 && !$bCopy):?>
    <input type="hidden" name="ID" value="<?=$ID?>">
    <?endif;
    $tabControl->End();
    ?>
</form>

    <?
    $tabControl->ShowWarnings("post_form", $message);
    ?>

    <script language="JavaScript">
        <!--
        if(document.post_form.AUTO.checked)
            tabControl.EnableTab('edit2');
        else
            tabControl.DisableTab('edit2');
        //-->
    </script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
