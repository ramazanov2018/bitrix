<?php

namespace RNS\Integrations\Helpers;

class ICS
{
    const DT_FORMAT = 'Ymd\THis\Z';

    protected $properties = [];

    protected $userData = [];

    private $availableProperties = [
      'description',
      'dtend',
      'dtstart',
      'location',
      'summary',
      'url'
    ];

    /** @var string */
    private $uid;

    /** @var array */
    private $remind = [];

    /**
     * ICS constructor.
     * @param array $props
     * @param array $userData
     * @throws \Exception
     */
    public function __construct(array $props, array $userData) {
        $this->set($props);
        $this->userData = $userData;
    }

    /**
     * @param $key
     * @param bool $val
     * @throws \Exception
     */
    public function set($key, $val = false) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            if (in_array($key, $this->availableProperties)) {
                $this->properties[$key] = $this->sanitizeVal($val, $key);
            }
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function toString(): string
    {
        $rows = $this->build();
        return implode("\r\n", $rows);
    }

    /**
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @return array
     */
    public function getRemind(): array
    {
        return $this->remind;
    }

    /**
     * @param array $remind
     * @return ICS
     */
    public function setRemind(array $remind): ICS
    {
        $this->remind = $remind;
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function build(): array
    {
        $icsProps = [
          'BEGIN:VCALENDAR',
          'VERSION:2.0',
          'PRODID:-//Microsoft Corporation//Outlook 16.0 MIMEDIR//EN',
          'CALSCALE:GREGORIAN',
          'METHOD:REQUEST',
          'X-MS-OLK-FORCEINSPECTOROPEN:TRUE',
          'BEGIN:VTIMEZONE',
          'TZID:Russian Standard Time',
          'BEGIN:STANDARD',
          'DTSTART:16010101T000000',
          'TZOFFSETFROM:+0300',
          'TZOFFSETTO:+0300',
          'END:STANDARD',
          'END:VTIMEZONE',
          'BEGIN:VEVENT'
        ];

        $props = [];
        foreach($this->properties as $k => $v) {
            $props[strtoupper($k . ($k === 'url' ? ';VALUE=URI' : ''))] = $v;
        }

        $props['DTSTAMP'] = $this->formatTimestamp('now');
        $this->uid = uniqid();
        $props['UID'] = $this->uid;

        foreach ($props as $k => $v) {
            $icsProps[] = "$k:$v";
        }

        foreach ($this->userData['attendees'] as $attendee) {
            $icsProps[] = "ATTENDEE;{$attendee}";
        }

        $icsProps[] = "ORGANIZER;{$this->userData['organizer']}";

        $icsProps[] = 'X-MICROSOFT-CDO-BUSYSTATUS:BUSY';
        $icsProps[] = 'X-MICROSOFT-CDO-IMPORTANCE:1';
        $icsProps[] = 'X-MICROSOFT-CDO-INTENDEDSTATUS:BUSY';
        $icsProps[] = 'X-MICROSOFT-DISALLOW-COUNTER:FALSE';
        $icsProps[] = 'X-MS-OLK-AUTOFILLLOCATION:FALSE';
        $icsProps[] = 'X-MS-OLK-CONFTYPE:0';

        foreach ($this->remind as $remind) {
            $icsProps[] = 'BEGIN:VALARM';
            $icsProps[] = 'TRIGGER:' . $this->parseRemind($remind);
            $icsProps[] = 'ACTION:DISPLAY';
            $icsProps[] = 'DESCRIPTION:Reminder';
            $icsProps[] = 'END:VALARM';
        }

        $icsProps[] = 'END:VEVENT';
        $icsProps[] = 'END:VCALENDAR';

        return $icsProps;
    }

    private function parseRemind(array $remind)
    {
        switch ($remind['type']) {
            case 'min':
                return "-PT{$remind['count']}M";
            case 'daybefore':
                $minutes = $remind['before'] * 24 * 60;
                $minutes -= $remind['time'];
                return "-PT{$minutes}M";
            case 'date':
                $time = strtotime($this->properties['dtstart']);
                $diff = intval(($time - strtotime($remind['value'])) / 60);
                return "-PT{$diff}M";
        }
        return '-PT15M';
    }

    /**
     * @param $val
     * @param bool $key
     * @return string|string[]|null
     * @throws \Exception
     */
    private function sanitizeVal($val, $key = false)
    {
        switch($key) {
            case 'dtend':
            case 'dtstamp':
            case 'dtstart':
                $val = $this->formatTimestamp($val);
                break;
            default:
                $val = $this->escapeString($val);
        }

        return $val;
    }

    /**
     * @param $timestamp
     * @return string
     * @throws \Exception
     */
    private function formatTimestamp($timestamp) {
        $dt = new \DateTime($timestamp);
        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format(self::DT_FORMAT);
    }

    private function escapeString($str) {
        return preg_replace('/([\,;])/','\\\$1', $str);
    }
}
