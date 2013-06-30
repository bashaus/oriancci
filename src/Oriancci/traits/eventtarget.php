<?php

namespace Oriancci\Traits;

trait EventTarget
{

    protected static $events = [];

    public static function eventAttach($eventName, callable $callback)
    {
        if (!array_key_exists($eventName, static::$events)) {
            static::$events[$eventName] = [];
        }

        static::$events[$eventName][] = $callback;
    }

    public static function eventDetach($eventName, callable $callback)
    {
        if (!array_key_exists($eventName, static::$events)) {
            return false;
        }

        foreach (static::$events[$eventName] as $i => $event) {
            if ($callback == $event) {
                unset(static::$events[$eventName][$i]);
                return true;
            }
        }

        return false;
    }

    public function eventDispatch($eventName, $propagateCancel = false)
    {
        if (!array_key_exists($eventName, static::$events)) {
            return true;
        }

        $events = static::$events[$eventName];

        foreach ($events as $event) {
            if (is_array($event)) {
                $result = call_user_func([$this, $event]);
            } else {
                $result = $event();
            }

            if ($result === false && $propagateCancel == true) {
                return false;
            }
        }

        return true;
    }

    public static function eventCount($eventName)
    {
        if (!array_key_exists($eventName, static::$events)) {
            return 0;
        }

        return count(static::$events[$eventName]);
    }
}
