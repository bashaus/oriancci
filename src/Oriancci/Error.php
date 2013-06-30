<?php

namespace Oriancci;

class Error
{
    
    public static $accessible = [
        'field'         => true,
        'code'          => true,
        'message'       => true
    ];

    public $field;
    public $code;
    public $message;

    public function __construct($attributes)
    {
        foreach (static::$accessible as $attributeName => $attributeValue) {
            if (array_key_exists($attributeName, $attributes)) {
                $this->$attributeName = $attributes[$attributeName];
            }
        }
    }

    public function __toString()
    {
        return (string) $this->code;
    }
}
