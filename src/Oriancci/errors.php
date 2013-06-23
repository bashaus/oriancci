<?php

namespace Oriancci;

class Errors extends \ArrayObject
{
    
    public function __construct()
    {
    }

    public function clear()
    {
        $this->exchangeArray([]);
    }

    public function clearAutomated()
    {
        $this->clearByKey('isAutomated', true);
    }

    public function clearNonAutomated()
    {
        $this->clearByKey('isAutomated', false);
    }

    public function clearByKey($clearKey, $clearValue)
    {
        $toRemove = [];
        
        foreach ($this as $key => $error) {
            if ($error->$clearKey == $clearValue) {
                $toRemove[] = $key;
            }
        }

        foreach ($toRemove as $key) {
            unset($this[$key]);
        }
    }

    public function on($field)
    {
        $return = new Errors;

        foreach ($this as $error) {
            if ($error->field == $field) {
                $return[] = $error;
            }
        }

        return $return;
    }

    public function __toString()
    {
        $errors = [];

        foreach ($this as $error) {
            $errors[] = (string) $error;
        }

        return implode(', ', $errors);
    }
}
