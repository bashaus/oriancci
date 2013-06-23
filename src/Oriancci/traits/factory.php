<?php

namespace Oriancci\Traits;

trait Factory
{
    
    private static $instances = [];

    public static function factory($name)
    {
        if (!array_key_exists($name, static::$instances)) {
            $className = __CLASS__;
            static::$instances[$name] = new $className($name);
        }

        return static::$instances[$name];
    }
}
