<?

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class ReminderPopup  extends CBitrixComponent implements Controllerable
{

    /**
     * подключает языковые файлы
     */
    public function onIncludeComponentLang()
    {
        $this->includeComponentLang(basename(__FILE__));
        Loc::loadMessages(__FILE__);
    }

    /**
     * подготавливает входные параметры
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        return $params;
    }

    // Описываем действия
    public function configureActions(): array
    {
        return [
            'reminded' => [
                'prefilters' => [
                    // здесь указываются опциональные фильтры, например:
                    //new ActionFilter\Authentication(), // проверяет авторизован ли пользователь
                ]
            ]
        ];
    }

    public function remindedAction(): array
    {
        return [
            "result" => "Ваше сообщение принято",
        ];
    }

    /**
     * выполняет логику работы компонента
     */
    public function executeComponent()
    {
        global $USER;
        $this->user = $USER;
        try
        {
            $this->includeComponentTemplate();
        }
        catch (Exception $e)
        {
            ShowError($e->getMessage());
        }
    }
}
