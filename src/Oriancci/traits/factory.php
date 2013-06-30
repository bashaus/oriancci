<?php

namespace Oriancci\Traits;

trait Factory
{
    
    protected static $instances = [];

    public static function factory($name)
    {
        if (!array_key_exists($name, static::$instances)) {
            $className = get_called_class();
            static::$instances[$name] = new $className($name);
        }

        return static::$instances[$name];
    }
}
