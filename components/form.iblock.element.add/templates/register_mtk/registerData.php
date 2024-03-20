<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    \Bitrix\Main\Type\DateTime as BXDateTime,
    Bitrix\Main\Web\Json;
global $USER, $APPLICATION;
Loader::includeModule("highloadblock");

$request = Context::getCurrent()->getRequest();
$params[] = ['name' => 'TOPICS', 'value' => 'med_expertise'];
$weekType = $request->get('weekType');
$btnSchedule = $request->get('btnSchedule');

if (!Loader::includeModule("iblock")) return false;

foreach($params as $regData){
    if($regData['value']){
        $regDataProps[$regData['name']] = $regData['value'];
    }
}
if(isset($regDataProps)){
    $reservedDateTime = [];

        $obj = \Bitrix\Iblock\Elements\ElementMtkrecordTable::getList([
            'select' => [
                'ID',
                'REG_DATE' => 'REGISTRY_DATE_TIME.VALUE'
            ],
            'order' => ['sort' => 'ASC'],
            'filter' =>  [
                    '<=REG_DATE' =>  BXDateTime::createFromTimestamp(strtotime('Monday next week'))->add("7 day")->format('Y-m-d H:i:s'),
                    '>=REG_DATE' =>  BXDateTime::createFromTimestamp(strtotime('Monday this week'))->format('Y-m-d H:i:s')
            ],
        ]);

        while ($row = $obj->fetch()) {
            $dt = new BXDateTime($row['REG_DATE'], "Y-m-d H:i:s");
            $reservedDateTime[$dt->getTimestamp()][] = $row["ID"];
        }

    $mtkRegisterData = RcDimosHelpers::getHlBlockData(HLB_MTK,
        ["ID", "UF_NAME", "UF_XML_ID", "UF_SLOT_DURATION", "UF_PERIOD_START", "UF_PERIOD_END", "UF_BREAK_START", "UF_BREAK_END", "UF_WEEKENDS", "UF_HOLIDAYS"], //, "UF_WEEKENDS", "UF_HOLIDAYS"
        //["UF_SORT" => "ASC"],
        [],
        ["UF_XML_ID" => "REGISTER_MTK"]
    );

    if(is_array($mtkRegisterData)) {
        $mtkRegisterData = current($mtkRegisterData);

        function second ($time)
        {
            $part = explode(':', $time);
            $a = $part[0]*3600+$part[1]*60+$part[2]; //$part[0]-это часы, $part[1]-минуты и на всякий случай $part[2]-секунды.
            return $a;
        }

        function getMinutes ($periodStart, $periodEnd, $breakStart, $breakEnd, $duration, $minutesToAdd)
        {
            $arMinutes[] = $periodStart;
            while($periodStart < $periodEnd) {
                $timeStart = new DateTime($periodStart);
                $timeStart->add(new DateInterval('PT' . $minutesToAdd . 'M'));
                $periodStart = $timeStart->format('H:i');
                $periodStartInTime = second($periodStart);
                $timeStart->add(new DateInterval('PT' . $minutesToAdd . 'M'));

                if(($periodStartInTime < $breakStart && ($breakStart - $periodStartInTime) >= $duration) ||
                    ($periodStartInTime >= $breakEnd)){
                    $arMinutes[] = $periodStart;
                }

                if ($periodEnd < $timeStart->format('H:i'))
                    break;
            }
            return $arMinutes;
        }

        $scheduleHolidays = RcDimosHelpers::getHlBlockData(HLB_HOLIDAYS,
            ["ID", "UF_NAME", "UF_XML_ID", "UF_DATE", "UF_SLOT_DURATION", "UF_PERIOD_START", "UF_PERIOD_END", "UF_BREAK_START", "UF_BREAK_END", "UF_TOPICS"],
            //["UF_SORT" => "ASC"],
            [],
            ["UF_TOPICS" => $mtkRegisterData['ID']]
        );
        if(is_array($scheduleHolidays)){
            foreach ($scheduleHolidays as $holiday){
                $timeStamp = MakeTimeStamp($holiday['UF_DATE'], "DD.MM.YYYY");
                $arHolidaySchedule[$timeStamp]['TIME_FORMAT'] = $timeStamp;
                $arHolidaySchedule[$timeStamp]['SLOT_DURATION_MINUTES'] = $holiday['UF_SLOT_DURATION'];
                $arHolidaySchedule[$timeStamp]['SLOT_DURATION'] = second("0:".$holiday['UF_SLOT_DURATION']);
                $arHolidaySchedule[$timeStamp]['PERIOD_START'] = $holiday['UF_PERIOD_START'];
                $arHolidaySchedule[$timeStamp]['PERIOD_END'] = $holiday['UF_PERIOD_END'];
                $arHolidaySchedule[$timeStamp]['BREAK_START'] = second($holiday['UF_BREAK_START']);
                $arHolidaySchedule[$timeStamp]['BREAK_END'] = second($holiday['UF_BREAK_END']);
            }
        }

        $periodStart = (!empty($mtkRegisterData['UF_PERIOD_START'])) ? $mtkRegisterData['UF_PERIOD_START'] : HLB_TOPICS_PROPERTY_PERIOD_START;
        $periodEnd = (!empty($mtkRegisterData['UF_PERIOD_END'])) ? $mtkRegisterData['UF_PERIOD_END'] : HLB_TOPICS_PROPERTY_PERIOD_END;
        $minutesToAdd  = (!empty($mtkRegisterData['UF_SLOT_DURATION'])) ? $mtkRegisterData['UF_SLOT_DURATION'] : HLB_TOPICS_PROPERTY_SLOT_DURATION;
        $breakStart = (!empty($mtkRegisterData['UF_BREAK_START'])) ? $mtkRegisterData['UF_BREAK_START'] : HLB_TOPICS_PROPERTY_BREAK_START;
        $breakEnd = (!empty($mtkRegisterData['UF_BREAK_END'])) ? $mtkRegisterData['UF_BREAK_END'] : HLB_TOPICS_PROPERTY_BREAK_END;
        $breakStart = second($breakStart);
        $breakEnd = second($breakEnd);
        $duration = second("0:".$minutesToAdd);

        //Выходные дни
        if(!empty($mtkRegisterData['UF_WEEKENDS'])){
            foreach ($mtkRegisterData['UF_WEEKENDS'] as $topicWeekend){
                $rsPropWeekend = CUserFieldEnum::GetList([], ["ID" => $topicWeekend]);
                if($arPropWeekend = $rsPropWeekend->GetNext()){
                    $arWeekends[$arPropWeekend['XML_ID']] = explode("_", $arPropWeekend['XML_ID'])[1];
                }
            }
        }
        //Праздничные дни
        if(!empty($mtkRegisterData['UF_HOLIDAYS'])) {
            foreach ($mtkRegisterData['UF_HOLIDAYS'] as $key => $topicHoliday) {
                if ($holidayTimeFormat = MakeTimeStamp($topicHoliday, "DD.MM.YYYY"))
                {
                    $arHolidays[$key] = $holidayTimeFormat;
                }
            }
        }
    }
}

$schedule_next_week = GetMessage("SCHEDULE_NEXT_WEEK");
$schedule_prev_week = GetMessage("SCHEDULE_PREV_WEEK");
$schedule_now_week = GetMessage("SCHEDULE_NOW_WEEK");
$arDates = [];
if(isset($weekType) && $weekType == "nextWeek"){
    $thisWeek = strtotime('Monday next week');
}
else{
    $thisWeek = strtotime('Monday this week');
}

for($i = 0; $i < 7; $i++){
    $timeFormat = strtotime('+'.$i.' day', $thisWeek);
    $dayNumber = date('j', $timeFormat);
    $month = date('n', $timeFormat);
    $year = date('Y', $timeFormat);
    $n = date("w", mktime(0,0,0,date("m"),date("d"),date("Y")));
    $weekDay = date("N", $timeFormat);
    $arDates[$i]['TIME_FORMAT'] = $timeFormat;
    $arDates[$i]['DATE'] =  date("d.m.Y", $timeFormat);
    $arDates[$i]['WEEK_DAY'] =  $weekDay;
    $arDates[$i]['DAY_MONTH'] =  $dayNumber.'.'.$month;
    $arDates[$i]['DAY'] =  $dayNumber;
    $arDates[$i]['MONTH'] =  $month;
    $arDates[$i]['YEAR'] =  $year;
}

$firstWeekDay = current($arDates);
$endWeekDay = end($arDates);
?>
<div id="mfp-ready" class="mfp-bg mfp-ready"></div>
<div id="mfp-wrap" class="mfp-wrap mfp-close-btn-in mfp-auto-cursor mfp-ready" tabindex="-1" style="overflow: hidden auto;    position: absolute;">
    <div class="mfp-container-modal">
        <div class="mfp-content-modal">
            <div class="cn-modal cn-modal-medium">
                <div class="mfp-close cn-modal-close">×</div>
                <div class="cn-modal-header"><?=GetMessage('regFormTitle')?></div>
                <div class="schedule-header">
                    <?if(isset($weekType) && $weekType == "nextWeek"):?>
                        <div class="fl-l">
                            <a id="prevWeek"><?=$schedule_prev_week?></a>
                        </div>
                        <div class="ta-center">
                            <?=$firstWeekDay['DAY']. " ". GetMessage('YEAR_MONTH_'.$firstWeekDay['MONTH']). " ". $firstWeekDay['YEAR'] . " — ". $endWeekDay['DAY']. " ". GetMessage('YEAR_MONTH_'.$endWeekDay['MONTH']). " ". $endWeekDay['YEAR'];?>
                        </div>
                    <?else:?>
                        <div class="fl-r">
                            <a id="nextWeek"><?=$schedule_next_week?></a>
                        </div>
                        <div class="ta-center">
                            <?=$firstWeekDay['DAY']. " ". GetMessage('YEAR_MONTH_'.$firstWeekDay['MONTH']). " ". $firstWeekDay['YEAR'] . " — ". $endWeekDay['DAY']. " ". GetMessage('YEAR_MONTH_'.$endWeekDay['MONTH']). " ". $endWeekDay['YEAR'];?>
                        </div>
                    <?endif;?>
                </div>

                <div class="schedule-week-wrap">
                    <div class="schedule-week-header">
                        <?foreach ($arDates as $date):?>
                            <div class="schedule-day-header">
                                <?=GetMessage('WEEK_DAY_'.$date['WEEK_DAY'])?>, <?=$date['DAY_MONTH']?>
                            </div>
                        <?endforeach;?>
                    </div>
                </div>

                <div class="schedule-week-wrap">
                    <div class="schedule-week collapsed" data-week-id="ww73">
                        <?foreach ($arDates as $date):?>
                            <div class="schedule-day equal" data-date="<?=$date['DATE']?>">
                                <?if(!in_array($date['WEEK_DAY'], $arWeekends) && !in_array($date['TIME_FORMAT'], $arHolidays)):?>
                                    <div class="talon">
                                        <div class="disabled">
                                            <?php
                                            if(key_exists($date['TIME_FORMAT'], $arHolidaySchedule)){
                                                $arMinutes = getMinutes($arHolidaySchedule[$date['TIME_FORMAT']]['PERIOD_START'],
                                                    $arHolidaySchedule[$date['TIME_FORMAT']]['PERIOD_END'],
                                                    $arHolidaySchedule[$date['TIME_FORMAT']]['BREAK_START'],
                                                    $arHolidaySchedule[$date['TIME_FORMAT']]['BREAK_END'],
                                                    $arHolidaySchedule[$date['TIME_FORMAT']]['SLOT_DURATION'],
                                                    $arHolidaySchedule[$date['TIME_FORMAT']]['SLOT_DURATION_MINUTES']
                                                );
                                            }
                                            else{
                                                $arMinutes = getMinutes($periodStart, $periodEnd, $breakStart, $breakEnd, $duration, $minutesToAdd);
                                            }

                                            foreach ($arMinutes as $minute) {
                                                $currentDateTime = strtotime("now");
                                                $dateTime = strtotime($date['DATE'] . " " . $minute);
                                                $reserv = false;

                                                if(!empty($arHolidaySchedule[$date['TIME_FORMAT']]['SLOT_DURATION_MINUTES'])){
                                                    $slot = $arHolidaySchedule[$date['TIME_FORMAT']]['SLOT_DURATION_MINUTES'];
                                                }
                                                else{
                                                    if(!empty($mtkRegisterData['UF_SLOT_DURATION'])){
                                                        $slot = $mtkRegisterData['UF_SLOT_DURATION'];
                                                    }
                                                    else{
                                                        $slot = HLB_TOPICS_PROPERTY_SLOT_DURATION;
                                                    }
                                                }
                                                //$slot = (!empty($mtkRegisterData['UF_SLOT_DURATION'])) ? $mtkRegisterData['UF_SLOT_DURATION'] : HLB_TOPICS_PROPERTY_SLOT_DURATION;
                                                $slot = $slot * 60;
                                                foreach ($reservedDateTime as $dateReserved => $reserved){
                                                    if ($dateReserved >= $dateTime && $dateReserved < $dateTime+$slot) {
                                                        $reserv = true;
                                                    }
                                                }?>
                                                <div class="hour">
                                                    <?if($reserv == true || $dateTime < $currentDateTime):?>
                                                        <?=$minute?>
                                                    <?else:?>
                                                        <a data-type="registry" data-date="<?=$date['DATE']?> <?=$minute?>:00" data-m="<?=$minute?>" data-topic="<?=$regDataProps['TOPICS']?>" class="btn-schedule-hour">
                                                            <?=$minute?>
                                                        </a>
                                                    <?endif;?>
                                                </div>
                                            <?}?>
                                        </div>
                                    </div>
                                <?else:?>
                                    <div class="hour hour-height-3 hour-relax">
                                        <div class="hour-text">
                                            <?=GetMessage('SCHEDULE_NO_DATE');?>
                                        </div>
                                    </div>
                                <?endif;?>
                            </div>
                        <?endforeach;?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>