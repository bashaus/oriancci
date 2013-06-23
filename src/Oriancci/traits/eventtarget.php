<?php

namespace Oriancci\Traits;

trait EventTarget
{

    public static $events = [];

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
            return;
        }

        foreach ($events as $i => $event) {
            if ($callback == $event) {
                unset(static::$events[$i]);
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
            $result = call_user_func([$this, $event]);

            if ($result === false && $propagateCancel == true) {
                return false;
            }
        }

        return true;
    }
}
