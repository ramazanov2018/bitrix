<?php
namespace Fbit\Quickrunintegration;

class IntegrationHandler
{
    // Передача данных из Б24 в Бегунок
    public static function PortalToQuickrunAgent()
    {
        $Quickrun = new PortalToQuickrun();
        $Quickrun->Exchange();
        return __CLASS__."::PortalToQuickrunAgent();";
    }

    // Обновление статуса заказа из Бегунка в Битрикс
    public static function QuickrunToPortalAgent()
    {
        $Quickrun = new QuickrunToPortal();
        $Quickrun->Exchange();
        return __CLASS__."::QuickrunToPortalAgent();";
    }
}