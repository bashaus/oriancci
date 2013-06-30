<?php

namespace Oriancci;

class Errors extends \ArrayObject implements \JsonSerializable
{

    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function clear()
    {
        $this->exchangeArray([]);
    }

    public function clearByKey($clearKey, $clearValue)
    {
        $filter = new Filter\Equals($this, $clearKey, $clearValue);
        foreach ($filter as $key => $object) {
            unset($this[$key]);
        }
    }

    public function on($field)
    {
        return new Filter\Equals('field', $field);
    }

    public function __toString()
    {
        $errors = [];

        foreach ($this as $error) {
            $errors[] = (string) $error;
        }

        return implode(', ', $errors);
    }

    /* JSON Serializable */
    public function jsonSerialize()
    {
        $return = [];

        foreach ($this as $key => $value) {
            if ($value instanceof \JsonSerializable) {
                $return[$key] = $value->jsonSerialize();
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}
