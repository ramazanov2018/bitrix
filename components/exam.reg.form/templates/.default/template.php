<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

\Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/css/editProfile.css");
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/pages/editProfile.js", true);
\Bitrix\Main\Page\Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/js/components/regExamForm.js", true);
?>
<div class="modal-bodyCustom regExamForm" data-modal="regExamForm">
    <p class="title-page"><?=GetMessage("FORM_TITLE")?></p>
    <div id="regExam-res"></div>
        <form id="regExam-form" class="form regExamScoope" method="POST" enctype="multipart/form-data">
        <div class="form-row yurLits">
            <label for=""><?=GetMessage("FORM_TITLE")?></label>
            <div class="inputBlock">
                <input type="text" placeholder="<?=$arResult['PROPERTIES']['ORGANIZATION']['TITLE']?>" name="<?=$arResult['PROPERTIES']['ORGANIZATION']['CODE']?>">
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock">
                <input type="text" value="<?=$arResult['USER_DATA']['STUDENT_SURNAME']?>" placeholder="<?=$arResult['PROPERTIES']['STUDENT_SURNAME']['TITLE']?>" name="<?=$arResult['PROPERTIES']['STUDENT_SURNAME']['CODE']?>" required>
            </div>
            <div class="inputBlock">
                <input type="text" value="<?=$arResult['USER_DATA']['STUDENT_SURNAME_LATIN']?>" placeholder="<?=$arResult['PROPERTIES']['STUDENT_SURNAME_LATIN']['TITLE']?>" name="<?=$arResult['PROPERTIES']['STUDENT_SURNAME_LATIN']['CODE']?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock">
                <input type="text" value="<?=$arResult['USER_DATA']['STUDENT_NAME']?>" placeholder="<?=$arResult['PROPERTIES']['STUDENT_NAME']['TITLE']?>" name="<?=$arResult['PROPERTIES']['STUDENT_NAME']['CODE']?>" required>
            </div>
            <div class="inputBlock">
                <input type="text" value="<?=$arResult['USER_DATA']['STUDENT_NAME_LATIN']?>" placeholder="<?=$arResult['PROPERTIES']['STUDENT_NAME_LATIN']['TITLE']?>" name="<?=$arResult['PROPERTIES']['STUDENT_NAME_LATIN']['CODE']?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock">
                <input type="text" value="<?=$arResult['USER_DATA']['STUDENT_PATRONYMIC']?>" placeholder="<?=$arResult['PROPERTIES']['STUDENT_PATRONYMIC']['TITLE']?>" name="<?=$arResult['PROPERTIES']['STUDENT_PATRONYMIC']['CODE']?>">
            </div>
            <div class="inputBlock">
                <div class="birthday datepicker-container">
                    <label for="STUDENT_BIRTHDAY"><?=GetMessage("FORM_STUDENT_BIRTHDAY")?></label>
                    <input class="datepicker" type="text" name="STUDENT_BIRTHDAY" id="STUDENT_BIRTHDAY" value="<?=$arResult['USER_DATA']['STUDENT_BIRTHDAY']?>" placeholder="Дата">
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock select">
                <select class="select2" name="placeStudies">
                    <option></option>
                    <?php foreach ($arResult['DIRECTORY']['EXAM_PLACE']['ELEMENTS'] as $examPlace):?>
                        <option value="<?=$examPlace['ID']?>" <?=($examPlace['ID'] == $arResult['USER_DATA']['INSTITUTE']) ? " selected=\"selected\"" : ""?>><?=$examPlace['NAME']?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="inputBlock">
                <input type="text" value="<?=$arResult['USER_DATA']['WORK_PLACE']?>" placeholder="<?=$arResult['PROPERTIES']['WORK_PLACE']['TITLE']?>" name="<?=$arResult['PROPERTIES']['WORK_PLACE']['CODE']?>">
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock">
                <input type="text" value="<?=$arResult['USER_DATA']['STUDENT_NATIONALITY']?>" placeholder="<?=GetMessage("FORM_STUDENT_NATIONALITY")?>" name="STUDENT_NATIONALITY" required>
            </div>
            <div class="inputBlock">
                <input type="text" value="<?=$arResult['USER_DATA']['WORK_POSITION']?>" placeholder="<?=$arResult['PROPERTIES']['WORK_POSITION']['TITLE']?>" name="<?=$arResult['PROPERTIES']['WORK_POSITION']['CODE']?>">
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock">
                <input type="tel" value="<?=$arResult['USER_DATA']['STUDENT_PHONE']?>" placeholder="<?=$arResult['PROPERTIES']['STUDENT_PHONE']['TITLE']?>" name="<?=$arResult['PROPERTIES']['STUDENT_PHONE']['CODE']?>" required>
            </div>
            <div class="inputBlock">
                <input type="email" value="<?=$arResult['USER_DATA']['STUDENT_EMAIL']?>" placeholder="<?=$arResult['PROPERTIES']['STUDENT_EMAIL']['TITLE']?>" name="<?=$arResult['PROPERTIES']['STUDENT_EMAIL']['CODE']?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock select">
                <select class="select2" name="regionExam" required>
                    <option></option>
                    <?php foreach ($arResult['DIRECTORY']['REGION']['ELEMENTS'] as $region):?>
                    <option value="<?=$region['ID']?>"><?=$region['NAME']?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="inputBlock select">
                <select class="select2" name="placeExam" required>
                    <option></option>
                    <?php foreach ($arResult['DIRECTORY']['EXAM_PLACE']['ELEMENTS'] as $examPlace):?>
                        <option value="<?=$examPlace['ID']?>" data-region="<?=$examPlace['REGION']?>"><?=$examPlace['NAME']?></option>
                    <?php endforeach;?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock select">
                <select class="select2" name="typeExam" required>
                    <option></option>
                    <?php foreach ($arResult['DIRECTORY']['EXAM_LEVEL']['ELEMENTS'] as $examLvl):?>
                        <option value="<?=$examLvl['XML_ID']?>"><?=$examLvl['NAME']?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="inputBlock">
                <input type="text" placeholder="<?=$arResult['PROPERTIES']['EXAM_PURPOSE']['TITLE']?>" name="<?=$arResult['PROPERTIES']['EXAM_PURPOSE']['CODE']?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock">
                <div class="dateExam">
                    <label class="mr-18" for="dateExam"><?=GetMessage("FORM_STUDENT_DATE_EXAM")?></label>
                    <div class="select time">
                        <select id="examForm-date" class="select2" name="dateSelect" required></select>
                    </div>
                    <div class="select time">
                        <select id="examForm-time" class="select2" name="time" required></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="inputBlock checkboxInput">
                <input type="checkbox" name="agreement" id="agreement" required>
                <label for="agreement"><?=GetMessage("FORM_STUDENT_AGREEMENT")?></label>
            </div>
        </div>
        <div class="btns">
            <button class="btn-primary" name="exam_register_button" type="sumbit"><?=GetMessage("FORM_REGISTER")?></button>
            <button class="btn-secondary btnCloseModal" type="reset"><?=GetMessage("FORM_CANCEL")?></button>
        </div>
    </form>
</div>