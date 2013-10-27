<?php

namespace Oriancci;

abstract class Column
{

    const SIMPLE_TYPE_STRING      = 'string';
    const SIMPLE_TYPE_TEXT        = 'text';
    const SIMPLE_TYPE_INTEGER     = 'integer';
    const SIMPLE_TYPE_FLOAT       = 'float';
    const SIMPLE_TYPE_BOOLEAN     = 'boolean';
    const SIMPLE_TYPE_DATE        = 'date';
    const SIMPLE_TYPE_TIME        = 'time';
    const SIMPLE_TYPE_DATETIME    = 'datetime';
    const SIMPLE_TYPE_ENUM        = 'enum';
    const SIMPLE_TYPE_SET         = 'set';

    public static $simpleTypes = [
        self::SIMPLE_TYPE_STRING    => true,
        self::SIMPLE_TYPE_TEXT      => true,
        self::SIMPLE_TYPE_INTEGER   => true,
        self::SIMPLE_TYPE_FLOAT     => true,
        self::SIMPLE_TYPE_BOOLEAN   => true,
        self::SIMPLE_TYPE_DATE      => true,
        self::SIMPLE_TYPE_TIME      => true,
        self::SIMPLE_TYPE_DATETIME  => true,
        self::SIMPLE_TYPE_ENUM      => true,
        self::SIMPLE_TYPE_SET       => true
    ];

    private $simpleType = null;

    abstract public function getName();         // id
    abstract public function getType();         // VARCHAR(255)
    abstract public function getArgs();         // ENUM('one', 'two') => ['one', 'two']
    abstract public function isAllowedNull();   // true/false
    abstract public function isPrimaryKey();    // true/false
    abstract public function isAutoIncrement(); // true/false
    abstract public function getDefaultValue(); // "Lorem ipsum"

    // e.g. SIMPLE_TYPE_STRING
    public function getSimpleType()
    {
        return $this->simpleType;
    }

    protected function setSimpleType($simpleType)
    {
        if (!array_key_exists($simpleType, self::$simpleTypes)) {
            throw new \Exception('Simple type does not exist: ' . $simpleType);
        }

        $this->simpleType = $simpleType;
    }

    protected static function parseArgList($args)
    {
        return array_fill_keys(str_getcsv($args, ',', "'"), true);
    }

    public function validate($filter)
    {
        $this->validateNull($filter);
        $this->validateValue($filter);
    }

    protected function validateNull($filter)
    {
        // Primary keys are allowed to be null
        if ($this->isPrimaryKey()) {
            return;
        }

        // If null is allowed, continue
        if ($this->isAllowedNull()) {
            return;
        }

        if (in_array($this->getSimpleType(), array(self::SIMPLE_TYPE_DATE, self::SIMPLE_TYPE_TIME, self::SIMPLE_TYPE_DATETIME))) {
            return;
        }

        $filter->addSoftRule($this->getName(), $filter::IS, 'strlenMin', 1);
    }

    protected function validateValue($filter)
    {
        switch ($this->simpleType) {
            case self::SIMPLE_TYPE_STRING:
                return $this->validateString($filter);
            break;
            case self::SIMPLE_TYPE_TEXT:
                return $this->validateString($filter);
            break;
            case self::SIMPLE_TYPE_INTEGER:
                return $this->validateInt($filter);
            break;
            case self::SIMPLE_TYPE_FLOAT:
                return $this->validateFloat($filter);
            break;
            case self::SIMPLE_TYPE_BOOLEAN:
                return $this->validateBoolean($filter);
            break;
            case self::SIMPLE_TYPE_DATE:
                return $this->validateDateTime($filter);
            break;
            case self::SIMPLE_TYPE_TIME:
                return $this->validateDateTime($filter);
            break;
            case self::SIMPLE_TYPE_DATETIME:
                return $this->validateDateTime($filter);
            break;
            case self::SIMPLE_TYPE_ENUM:
                return $this->validateEnum($filter);
            break;
            case self::SIMPLE_TYPE_SET:
                return $this->validateSet($filter);
            break;
        }
    }

    private function validateString($filter)
    {
        $typeArgs = $this->getArgs();
        if (is_null($typeArgs)) {
            return;
        }

        $filter->addSoftRule($this->getName(), $filter::IS_BLANK_OR, 'strlenMax', $typeArgs);
    }

    public function validateInt($filter)
    {
        $filter->addSoftRule($this->getName(), $filter::IS_BLANK_OR, 'int');
    }

    public function validateFloat($filter)
    {
        $filter->addSoftRule($this->getName(), $filter::IS_BLANK_OR, 'float');
    }

    public function validateBoolean($filter)
    {
        $filter->addSoftRule($this->getName(), $filter::IS_BLANK_OR, 'bool');
    }

    public function validateDateTime($value)
    {
    }

    private function validateEnum($filter)
    {
        $typeArgs = $this->getArgs();
        $filter->addSoftRule($this->getName(), $filter::IS_BLANK_OR, 'inKeys', $typeArgs);
    }

    private function validateSet($values)
    {
        $typeArgs = $this->getArgs();
        $filter->addSoftRule($this->getName(), $filter::IS_BLANK_OR, 'inKeys', $typeArgs);
    }
}
