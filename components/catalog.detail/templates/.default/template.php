<?

if (isset($arResult['COMPANY']['DATA']['PROPERTY_SHORT_NAME_VALUE']) && $arResult['COMPANY']['DATA']['PROPERTY_SHORT_NAME_VALUE'] != "") {
    $APPLICATION->SetTitle($arResult['COMPANY']['DATA']['PROPERTY_SHORT_NAME_VALUE']);
}
if (isset($arResult['COMPANY']['DATA']['PROPERTY_FULL_NAME_VALUE']) && $arResult['COMPANY']['DATA']['PROPERTY_FULL_NAME_VALUE'] != "") {
    $APPLICATION->SetPageProperty("description", $arResult['COMPANY']['DATA']['PROPERTY_FULL_NAME_VALUE']);
}
$APPLICATION->SetPageProperty("keywords",  "");

?>
<section class="container mb-60 mb-xs-40">
  <!--  <h1 class="title-first"><?php /*=$arResult['COMPANY']['DATA']['PROPERTY_SHORT_NAME_VALUE']*/?></h1>-->
    <div><a class="btn btn btn-success btn-fs-16 max-width-248" href="/deyatelnost/katalog-otechestvennoy-produktsii/">← Обратно в каталог</a>
    </div>
    <div class="mt-54 mt-xs-48"><img class="image-preview bvi-background-image max-width-content w-100" src="<?=$arResult['COMPANY']['DATA']['PROPERTY_LOGO_VALUE']['SRC']?>"></div>
    <div class="page__text mt-38 mt-xs-19">
        <div class="text-substring"><?=$arResult['COMPANY']['DATA']['PROPERTY_DESCRIPTION_VALUE']['TEXT']?></div>
        <button class="border-0 bg-transparent link-black mt-20 fw-600 btn-substring" type="button">Читать далее ↓</button>
    </div>
    <div class="mt-6 mt-xs-4">
        <h2 class="title-three fw-500 m-0">Контактная информация:</h2>
        <div class="mt-22 d-flex flex-wrap g-18"><a class="w-fit-content link-black page__text" href="tel:<?=$arResult['COMPANY']['DATA']['PROPERTY_COMPANY_PHONE_VALUE']?>"><?=$arResult['COMPANY']['DATA']['PROPERTY_COMPANY_PHONE_VALUE']?></a><a class="w-fit-content link-green decoration-underline page__text" href="mailto:<?=$arResult['COMPANY']['DATA']['PROPERTY_COMPANY_EMAIL_VALUE']?>"><?=$arResult['COMPANY']['DATA']['PROPERTY_COMPANY_EMAIL_VALUE']?></a>
        </div>
    </div>
    <div class="mt-6 mt-xs-4 d-flex flex-wrap g-20">
        <div class="feedbackFilesList">
            <h2 class="title-three fw-500">Сертификаты</h2>
            <ul class="d-flex flex-wrap g-20 m-0">
                <?

                if ($arResult['COMPANY']['MULTIPLY_FIELDS']['IMAGES']['SERTIFICATES'][0]) {
                foreach ($arResult['COMPANY']['MULTIPLY_FIELDS']['IMAGES']['SERTIFICATES'] as $sertificate) {
                $arr = explode('.', $sertificate['FILE_NAME']);
                $extension = strtolower(array_pop($arr));
                ?>
                <li class="feedbackFilesList__item"><a class="feedbackFilesList__link" href="<?=$sertificate['SRC']?>" target="_self" download="<?=$sertificate['SRC']?>" title="Скачать <?=$sertificate['FILE_NAME']?>"><img class="feedbackFilesList__icon bvi-background-image" src="/local/templates/gbuce/build/img/icons/icons.svg#<?=$extension?>">
                        <div class="feedbackFilesList__blockText"><span class="feedbackFilesList__subtitle"><?=$sertificate['FILE_NAME']?></span></div></a></li>
                <?}
                }?>
            </ul>
        </div>
        <div class="feedbackFilesList">
            <h2 class="title-three fw-500">Фото</h2>
            <ul class="d-flex flex-wrap g-20 m-0">
                <?

                if ($arResult['COMPANY']['MULTIPLY_FIELDS']['IMAGES']['PHOTO'][0]) {
                    foreach ($arResult['COMPANY']['MULTIPLY_FIELDS']['IMAGES']['PHOTO'] as $photo) {
                    $arr = explode('.', $photo['FILE_NAME']);
                    $extension = strtolower(array_pop($arr));
                ?>
                <li class="feedbackFilesList__item"><a class="feedbackFilesList__link" href="<?=$photo['SRC']?>" target="_self" download="<?=$photo['SRC']?>" title="Скачать <?=$photo['FILE_NAME']?>"><img class="feedbackFilesList__icon bvi-background-image" src="/local/templates/gbuce/build/img/icons/icons.svg#<?=$extension?>">
                        <div class="feedbackFilesList__blockText"><span class="feedbackFilesList__subtitle"><?=$photo['FILE_NAME']?></span></div></a></li>
                <?}
                }?>
            </ul>
        </div>
        <div class="feedbackFilesList">
            <h2 class="title-three fw-500">Отзывы о компании</h2>
            <ul class="d-flex flex-wrap g-20 m-0">
                <?                if ($arResult['COMPANY']['MULTIPLY_FIELDS']['IMAGES']['COMPANY_REVIEWS'][0]) {
                foreach ($arResult['COMPANY']['MULTIPLY_FIELDS']['IMAGES']['COMPANY_REVIEWS'] as $reviews) {
                $arr = explode('.', $reviews['FILE_NAME']);
                $extension = strtolower(array_pop($arr));
                ?>
                <li class="feedbackFilesList__item"><a class="feedbackFilesList__link" href="<?=$reviews['SRC']?>" target="_self" download="<?=$reviews['SRC']?>" title="Скачать <?=$reviews['FILE_NAME']?>"><img class="feedbackFilesList__icon bvi-background-image" src="/local/templates/gbuce/build/img/icons/icons.svg#<?=$extension?>">
                        <div class="feedbackFilesList__blockText"><span class="feedbackFilesList__subtitle"><?=$reviews['FILE_NAME']?></span></div></a></li>
                <?}
                }?>
            </ul>
        </div>
        <div class="feedbackFilesList">
            <h2 class="title-three fw-500">Прочее</h2>
            <ul class="d-flex flex-wrap g-20 m-0">
                <?
                if ($arResult['COMPANY']['MULTIPLY_FIELDS']['IMAGES']['ETC'][0]) {
                    foreach ($arResult['COMPANY']['MULTIPLY_FIELDS']['IMAGES']['ETC'] as $etc) {
                    $arr = explode('.', $etc['FILE_NAME']);
                    $extension = strtolower(array_pop($arr));
                ?>
                <li class="feedbackFilesList__item"><a class="feedbackFilesList__link" href="<?=$etc['SRC']?>" target="_self" download="<?=$etc['SRC']?> title="Скачать <?=$etc['FILE_NAME']?>"><img class="feedbackFilesList__icon bvi-background-image" src="/local/templates/gbuce/build/img/icons/icons.svg#<?=$extension?>">
                        <div class="feedbackFilesList__blockText"><span class="feedbackFilesList__subtitle">Сертификат <?=$etc['FILE_NAME']?> </span></div></a></li>
                <?}
                }?>
            </ul>
        </div>
    </div>
    <div class="mt-6 mt-xs-4">
        <h2 class="title-three fw-500 m-0">Каталог продукции</h2>
        <ul class="d-flex flex-column g-20 mt-44 mt-xs-22" id="listCatalog">
            <?foreach ($arResult['PRODUCT']['DATA'] as $key=>$item){
                $countPhoto=$item['MULTIPLY_FIELDS']['IMAGES']['PHOTO_PRODUCTION'][0]?count($item['MULTIPLY_FIELDS']['IMAGES']['PHOTO_PRODUCTION'] ):0;
                ?>
            <li class="border border-green border-radius-4 pt-13 pl-05 pr-05 pb-06 d-flex align-items-start flex-sm-row flex-column g-18">
                <?if($countPhoto>0){?>
                    <div class="swiper max-width-271 max-width-sm-100 swiper-initialized swiper-horizontal swiper-backface-hidden" id="swiper-<?=$key?>" data-id="swiper-<?=$key?>" data-swiper="{&quot;spaceBetween&quot;:20,&quot;slidesPerView&quot;:1}">
                    <div class="swiper-wrapper d-grid grid-columns-auto mb-20 mb-xs-14" id="swiper-wrapper-3feb110f20cae756b" aria-live="polite" style="transform: translate3d(-1455px, 0px, 0px); transition-duration: 0ms;">
                       <?
                       foreach ($item['MULTIPLY_FIELDS']['IMAGES']['PHOTO_PRODUCTION'] as $numberPhoto=>$photo){
                           ?>
                        <div class="d-flex flex-column img-signature swiper-slide swiper-slide-modal cursor-zoom-in" style="width: 271px; margin-right: 20px;" role="group" aria-label="<?=$numberPhoto?> / <?=$countPhoto?>"><img class="img d-block w-100 flex-grow-1" src="<?=$photo['SRC']?>" alt="<?=$photo['FILE_NAME']?>">
                        </div>
                        <?}?>
                    </div>
                    <div class="swiper-bullets-counter d-flex g-10 position-relative">
                        <div class="swiper-pagination swiper-pagination-sm swiper-pagination-clickable swiper-pagination-bullets swiper-pagination-horizontal swiper-pagination-bullets-dynamic" style="width: 185px;"><span class="swiper-pagination-bullet" tabindex="0" role="button" aria-label="Go to slide 1" style="left: -111px;"></span><span class="swiper-pagination-bullet" tabindex="0" role="button" aria-label="Go to slide 2" style="left: -111px;"></span><span class="swiper-pagination-bullet" tabindex="0" role="button" aria-label="Go to slide 3" style="left: -111px;"></span><span class="swiper-pagination-bullet swiper-pagination-bullet-active-prev-prev" tabindex="0" role="button" aria-label="Go to slide 4" style="left: -111px;"></span><span class="swiper-pagination-bullet swiper-pagination-bullet-active-prev" tabindex="0" role="button" aria-label="Go to slide 5" style="left: -111px;"></span><span class="swiper-pagination-bullet swiper-pagination-bullet-active swiper-pagination-bullet-active-main" tabindex="0" role="button" aria-label="Go to slide 6" style="left: -111px;" aria-current="true"></span></div>
                        <div class="counter d-none d-sm-block"><span class="counter__current">6</span>/<span class="counter__total">6</span></div>
                    </div>
                    <button class="bg-transparent swiper-button-prev swiper-button d-sm-block d-none color-green" tabindex="0" aria-label="Previous slide" aria-controls="swiper-wrapper-3feb110f20cae756b" aria-disabled="false">
                        <svg class="bvi-img" role="img">
                            <use xlink:href="/local/templates/gbuce/build/img/icons/icons.svg#prev"></use>
                        </svg>
                    </button>
                    <button class="bg-transparent swiper-button-next swiper-button d-sm-block d-none color-green swiper-button-disabled" tabindex="-1" aria-label="Next slide" aria-controls="swiper-wrapper-3feb110f20cae756b" aria-disabled="true" disabled="">
                        <svg class="bvi-img" role="img">
                            <use xlink:href="/local/templates/gbuce/build/img/icons/icons.svg#next"></use>
                        </svg>
                    </button>
                    <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span></div>
                <?}else{?>
                    <div class="max-width-271 max-width-sm-100 w-100"><img class="d-block w-100 object-fit-cover" src="/local/templates/gbuce/build/img/catalog/no_photo.png" alt="<?=$item['NAME']?>"></div>
                <?}?>
                <div class="mt-23">
                    <p class="m-0 title-three fw-500"><?=$item['NAME']?></p>
                    <div class="mt-38 mt-xs-11 page__text">
                        <div class="d-flex flex-wrap g-20">
                            <div class="d-flex flex-column g-05">
                                <div class="d-flex flex-wrap g-20 g-xs-row-05"><span class="w-272 w-sm-fit-content">Дата включения в каталог</span><span class="font-weight-bold"><?=$item['PROPERTY_DATE_INCLUDE_CATALOG_VALUE']?></span></div>
                                <div class="d-flex flex-wrap g-20 g-xs-row-05"><span class="w-272 w-sm-fit-content">Регион производства</span><span class="w-272 w-sm-fit-content font-weight-bold"><?=$item['PROPERTY_FACT_ADRESS_VALUE']['TEXT']?></span></div>
                            </div>
                            <div class="d-flex flex-column g-05"><span>Сертификаты/патенты/знаки качества</span>
                                <div class="d-flex flex-wrap g-05">
                                    <?
                                    $count=1;
                                    foreach ($item['MULTIPLY_FIELDS']['IMAGES']['SERTIFICATES_CONFORMITY'] as $image){if($image){?>
                                    <span><a class="link-green decoration-underline" href="<?=$image['SRC']?>" target="_blank"><?=$count?></a></span>
                                    <?$count++;}}?>
                                    <?foreach ($item['MULTIPLY_FIELDS']['IMAGES']['PATENT_PRODUCTION'] as $image){if($image){?>
                                    <span><a class="link-green decoration-underline" href="<?=$image['SRC']?>" target="_blank"><?=$count?></a></span>
                                    <?$count++;}}?>
                                    <?foreach ($item['MULTIPLY_FIELDS']['IMAGES']['SERTIFICATES_GOST_ISO'] as $image){if($image){?>
                                    <a class="link-green decoration-underline" href="<?=$image['SRC']?>" target="_blank"><?=$count?></a>
                                        <?$count++;}}?>

                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap g-20 mt-25"><a class="w-fit-content link-green decoration-underline link-download" href="<?=$item['MULTIPLY_FIELDS']['IMAGES']['TECH_DESCRIPTION'][0]['SRC']?>" download="#">Техническое описание
                                <svg class="bvi-img download" role="img">
                                    <use xlink:href="/local/templates/gbuce/build/img/icons/icons.svg#download"></use>
                                </svg></a><a class="w-fit-content link-green decoration-underline link-download" href="<?=$item['MULTIPLY_FIELDS']['IMAGES']['DATA_COST_IMPORTED_COMPONENTS'][0]['SRC']?>" download="#">Сведения об импортных комплектующих
                                <svg class="bvi-img download" role="img">
                                    <use xlink:href="/local/templates/gbuce/build/img/icons/icons.svg#download"></use>
                                </svg></a>
                        </div>
                        <div class="d-flex flex-wrap g-20 mt-25"><span>Прочее</span>

                            <div class="d-flex flex-wrap g-05">
                                <?foreach ($item['MULTIPLY_FIELDS']['IMAGES']['ETC_DOCS'] as $etc){?>
                                    <span><a class="link-green decoration-underline" href="<?=$etc['SRC']?>" target="_blank"><?=$etc['FILE_NAME']?></a></span>
                                <?}?>
                            </div>
                        </div>
                    </div>
                </div>

            </li>
            <?}?>
        </ul>
    </div>
    <div class="modalCustom modalCustom_center modalCustom_bg-white modalCustom_border-green p-14">
        <div class="modalCustom__container">
            <svg class="bvi-img modalCustom__close modalCustom__close_absolute" role="img">
                <use xlink:href="/local/templates/gbuce/build/img/icons/icons.svg#close"></use>
            </svg>
            <div class="modalCustom__content modalCustom__content_p-0">
            </div>
        </div>
    </div>
    <div class="modalCustom-overlay"></div>
</section>
<script src="/local/templates/gbuce/build/js/page/catalogProduct.js"></script>