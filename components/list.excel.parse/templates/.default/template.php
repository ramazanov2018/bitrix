<?

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
IncludeTemplateLangFile(__FILE__);
?>
<div class="xlsx-parse">
    <div id="FileForm" class="form-content">
        <?if($arResult['STATUS'] == '1'){?>
            <div class="form-status">
                <span class="span-status"><?=Loc::getMessage("STATUS_XLSX_PARSE")?></span>
            </div>
        <?}?>
        <div class="form-title">
            <span>
                <h2><?=Loc::getMessage('TITLE_XLSX_PARSE')?></h2>
            </span>
        </div>

        <div class="form-content-file">
            <form enctype="multipart/form-data" action="/xlsx-parse/" method="POST">
                <div class="div-file">
                    <input name="advertising_file" type="file">
                </div>
                </span>
                <div class="form-content-buttons">
                    <input class="form-input-button" name="submitExcel" type="submit" value="Отправить файл"/>
                </div>
            </form>
        </div>
    </div>
</div>
</form>