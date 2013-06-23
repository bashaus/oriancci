<?php

namespace Oriancci\DataType;

use Oriancci\Error;

class Datetime extends \DateTime implements \JsonSerializable, DataTypeInterface
{
    use Datatype;

    const INPUT_DATE     = 'DATE';
    const INPUT_TIME     = 'TIME';

    const INPUT_YEAR     = 'Y';
    const INPUT_MONTH    = 'M';
    const INPUT_DAY      = 'D';
    const INPUT_HOUR     = 'H';
    const INPUT_MINUTE   = 'I';
    const INPUT_SECOND   = 'S';
    const INPUT_TIMEZONE = 'Z';

    public static $keys = [
        self::INPUT_DATE => [self::INPUT_YEAR, self::INPUT_MONTH, self::INPUT_DAY],
        self::INPUT_TIME => [self::INPUT_HOUR, self::INPUT_MINUTE, self::INPUT_SECOND]
    ];

    protected $input = null;
    
    public function __construct($time = 'now', $timezone = null)
    {
        parent::__construct('now', $timezone);
        $this->setInput($time);
    }

    /* Input */
    /**
     * @return string
     */
    public function getInput($granularity = null)
    {
        if (!is_array($this->input)) {
            return null;
        }

        if (is_null($granularity)) {
            return $this->input;
        }

        if (!array_key_exists($granularity, $this->input)) {
            return null;
        }

        return $this->input[$granularity];
    }

    /**
     * @return void
     */
    public function setInput($time)
    {
        if (is_null($time)) {
            $this->modify('now 00:00:00');
            $this->input = null;
            return;
        }

        if (is_string($time)) {
            parent::modify($time);
            $this->updateInput();
            return;
        }

        if (is_array($time)) {
            $this->modify('now 00:00:00');

            if (is_array($this->input)) {
                $this->input += $time;
            } else {
                $this->input = $time;
            }

            if (array_key_exists(static::INPUT_TIMEZONE, $time)) {
                try {
                    $timezone = new \DateTimeZone($time[static::INPUT_TIMEZONE]);
                    $this->setTimezone($timezone);
                } catch (Exception $e) {
                    $this->errorGenerate('TIMEZONE_INVALID');
                }
            }
            
            if (array_intersect(array_keys($time), static::$keys[static::INPUT_DATE])) {
                $year = array_key_exists(static::INPUT_YEAR, $time)
                    ? $time[static::INPUT_YEAR]
                    : $this->getInput('Y');

                $month = array_key_exists(static::INPUT_MONTH, $time)
                    ? $time[static::INPUT_MONTH]
                    : $this->getInput('M');
                $day = array_key_exists(static::INPUT_DAY, $time)
                    ? $time[static::INPUT_DAY]
                    : $this->getInput('D');

                if (!static::validateDate($year, $month, $day)) {
                    $this->errorGenerate('DATE_INVALID');
                } else {
                    $this->setDate($year, $month, $day);
                }
            }

            if (array_intersect(array_keys($time), static::$keys[static::INPUT_TIME])) {
                $hour   = array_key_exists(static::INPUT_HOUR, $time)
                    ? $time[static::INPUT_HOUR]
                    : $this->getInput('H');

                $minute = array_key_exists(static::INPUT_MINUTE, $time)
                    ? $time[static::INPUT_MINUTE]
                    : $this->getInput('I');

                $second = array_key_exists(static::INPUT_SECOND, $time)
                    ? $time[static::INPUT_SECOND]
                    : $this->getInput('S');

                if (!static::validateTime($hour, $minute, $second)) {
                    $this->errorGenerate('TIME_INVALID');
                } else {
                    $this->setTime($hour, $minute, $second);
                }
            }

            return;
        }

        if (is_object($time)) {
            if ($time instanceof DateTime) {
                if ($time->isNull()) {
                    parent::modify('now 00:00:00');
                    $this->input = null;
                    return;
                }
            }

            if ($time instanceof \DateTime) {
                $this->setTimezone($time->getTimezone());

                $this->setDate(
                    $time->format('Y'),
                    $time->format('m'),
                    $time->format('d')
                );

                $this->setTime(
                    $time->format('H'),
                    $time->format('i'),
                    $time->format('s')
                );

                return;
            }
        }

        throw new \Exception('Could not interpret time');
    }

    /**
     * @return DateTime
     */
    public function add(/* DateInterval */ $interval)
    {
        if (is_null($this->input) || $this->hasErrors()) {
            return null;
        }

        $datetime = parent::add($interval);
        $datetime->updateInput();
        return $datetime;
    }

    /**
     * @return DateInterval
     */
    public function diff(/* DateTime */ $datetime2, $absolute = false)
    {
        if (is_null($this->input) || $this->hasErrors()) {
            return null;
        }

        if ($datetime2 instanceof Datetime && $datetime2->isNull()) {
            return null;
        }

        $datetime = parent::diff($datetime2, $absolute);
        $datetime->updateInput();
        return $datetime;
    }

    /**
     * @return string
     */
    public function format($format)
    {
        if (is_null($this->input) || $this->hasErrors()) {
            return null;
        }

        return parent::format($format);
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        if (is_null($this->input) || $this->hasErrors()) {
            return null;
        }

        return parent::getOffset();
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        if (is_null($this->input) || $this->hasErrors()) {
            return null;
        }

        return parent::getTimestamp();
    }

    /**
     * @return DateTimeZone
     */
    public function getTimezone()
    {
        if (is_null($this->input) || $this->hasErrors()) {
            return null;
        }

        return parent::getTimezone();
    }

    /**
     * @return DateTime
     */
    public function modify($modify)
    {
        if (is_null($this->input) || $this->hasErrors()) {
            return null;
        }

        parent::modify($modify);
        $this->updateInput();
    }

    /**
     * @return DateTime
     */
    public function setDate($year, $month, $day)
    {
        if (is_null($this->input)) {
            parent::setTime(0, 0, 0);
        }

        $datetime = parent::setDate($year, $month, $day);
        $datetime->updateInput();
        return $datetime;
    }

    /**
     * @return DateTime
     */
    public function setISODate($year, $week, $day = null)
    {
        if (is_null($this->input)) {
            parent::setTime(0, 0, 0);
        }

        $datetime = parent::setISODate($year, $week, $day);
        $datetime->updateInput();
        return $datetime;
    }

    /**
     * @return DateTime
     */
    public function setTime($hour, $minute, $second = 0)
    {
        if (is_null($this->input)) {
            parent::setTime(0, 0, 0);
        }

        $datetime = parent::setTime($hour, $minute, $second);
        $datetime->updateInput();
        return $datetime;
    }

    /**
     * @return DateTime
     */
    public function setTimestamp($unixtimestamp)
    {
        $datetime = parent::setTimestamp($unixtimestamp);
        $datetime->updateInput();
        return $datetime;
    }

    /**
     * @return DateTime
     */
    public function setTimezone(/* DateTimeZone */ $timezone)
    {
        if (is_null($this->input)) {
            return null;
        }

        $datetime = parent::setTimezone($timezone);
        $datetime->updateInput();
        return $datetime;
    }

    /**
     * @return DateTime
     */
    public function sub(/* DateInterval */ $interval)
    {
        if (is_null($this->input) || $this->hasErrors()) {
            return null;
        }

        $datetime = parent::sub($interval);
        $datetime->updateInput();
        return $datetime;
    }

    /**
     * @return boolean
     */
    public function isNull()
    {
        if ($this->hasErrors()) {
            return true;
        }

        if (is_null($this->input)) {
            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    protected function errorGenerate($code)
    {
        $error = new Error(['code' => $code, 'isAutomated' => true]);
        $this->errors[] = $error;

        return false;
    }

    /* Validation */

    /**
     * @return boolean
     */
    public static function validateDate($year, $month, $day)
    {
        $year  = ltrim($year, '0') ?: false;
        $month = ltrim($month, '0') ?: false;
        $day   = ltrim($day, '0') ?: false;

        $filterYear  = filter_var($year, FILTER_VALIDATE_INT, ['min_range' => 1, 'max_range' => 9999]);
        $filterMonth = filter_var($month, FILTER_VALIDATE_INT, ['min_range' => 1, 'max_range' => 12]);
        $filterDay   = filter_var($day, FILTER_VALIDATE_INT, ['min_range' => 1, 'max_range' => 31]);

        if ($filterYear !== 0 && $filterYear === false) {
            return false;
        }

        if ($filterMonth !== 0 && $filterMonth === false) {
            return false;
        }

        if ($filterDay !== 0 && $filterDay === false) {
            return false;
        }

        if (!checkdate($month, $day, $year)) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    public static function validateTime($hour, $minute, $second = 0)
    {
        $hour = ltrim($hour, '0') ?: false;
        $minute = ltrim($minute, '0') ?: false;
        $second = ltrim($second, '0') ?: 0;

        $filterHour = filter_var($hour, FILTER_VALIDATE_INT, ['min_range' => 0, 'max_range' => 23]);
        $filterMinute = filter_var($minute, FILTER_VALIDATE_INT, ['min_range' => 0, 'max_range' => 59]);
        $filterSecond = filter_var($second, FILTER_VALIDATE_INT, ['min_range' => 0, 'max_range' => 59]);

        if ($filterHour !== 0 && $filterHour === false) {
            return false;
        }

        if ($filterMinute !== 0 && $filterMinute === false) {
            return false;
        }

        if ($filterSecond !== 0 && $filterSecond === false) {
            return false;
        }

        return true;
    }

    protected function updateInput()
    {
        // $this->errors->clearAutomated();
        $this->errors = [];
        $this->input = $this->toArray();
    }

    /* SERIALIZATION */
    /**
     * @return array
     */
    public function toArray()
    {
        $timezone = $this->getTimezone();

        return [
            static::INPUT_YEAR     => $this->format('Y'),
            static::INPUT_MONTH    => $this->format('m'),
            static::INPUT_DAY      => $this->format('d'),
            static::INPUT_HOUR     => $this->format('H'),
            static::INPUT_MINUTE   => $this->format('i'),
            static::INPUT_SECOND   => $this->format('s'),
            static::INPUT_TIMEZONE => $timezone ? $timezone->getName() : null
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $value = $this->toDB();

        if (is_null($value)) {
            return '';
        }

        return $value;
    }
    
    /**
     * @return string
     */
    public function toDB()
    {
        if (is_null($this->input)) {
            return null;
        }

        return $this->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->format(\DateTime::ISO8601);
    }
}
