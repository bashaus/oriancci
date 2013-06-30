<?php

namespace Oriancci\DataType;

use Oriancci\Error;

trait DataType
{

    public $errors = [];

    public function resetErrors()
    {
        $this->errors = [];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    abstract public function isNull();
    abstract public function toDB();
}
