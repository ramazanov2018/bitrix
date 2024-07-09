<?php

use Bitrix\Main\Context;

class BirthDaysDetail extends CBitrixComponent
{
    protected function prepareData(): array
    {
        $result = [];
        $context = Context::getCurrent();
        $this->arParams['IFRAME'] = $context->getRequest()->get('IFRAME') == 'Y' ? 'Y' : 'N';
        return $result;
    }

    public function executeComponent()
    {
        #Готовим данные
        $this->arResult = $this->prepareData();

        #Подключаем шаблон
        if ($this->arParams['IFRAME'] == 'Y') {
            /** @var CMain $APPLICATION */
            global $APPLICATION;
            $APPLICATION->RestartBuffer();
            $this->includeComponentTemplate();

            require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
            exit;
        }else{
            $this->includeComponentTemplate();
        }
    }
}