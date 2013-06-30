<?php

namespace Oriancci\Traits;

trait Singleton
{
    
    private static $instance = null;
    
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            $className = __CLASS__;
            static::$instance = new $className;
        }

        return static::$instance;
    }

    private function __construct()
    {
    }
}
