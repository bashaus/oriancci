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

    abstract public function getType();         // VARCHAR(255)
    abstract public function getName();         // VARCHAR(255) => VARCHAR
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

    public function validate($value)
    {
        // Check if NULL is allowed
        $isNull = false;

        if ($value instanceof DataType\DataTypeInterface) {
            $isNull = $value->isNull();
        } elseif (is_null($value)) {
            $isNull = true;
        }

        if ($isNull) {
            if ($this->isPrimaryKey()) {
                return;
            } elseif ($this->isAllowedNull()) {
                return;
            } else {
                return $this->errorGenerate('NULL_NOT_EXPECTED');
            }
        }

        // If this is a DataType, assign the field name and passthrough errors
        if ($value instanceof DataType\DataTypeInterface) {
            if ($value->hasErrors()) {
                $errors = $value->getErrors();
                
                foreach ($errors as $error) {
                    $error->field = $this->field;
                }

                return $errors;
            }
        }

        // If the value is exactly blank, we don't need to check it
        // as we will assume that it is going to be treated as NULL
        if ($value === '') {
            return;
        }

        switch ($this->simpleType) {
            case self::SIMPLE_TYPE_STRING:
                return $this->validateString($value);
            break;
            case self::SIMPLE_TYPE_TEXT:
                return $this->validateString($value);
            break;
            case self::SIMPLE_TYPE_INTEGER:
                return $this->validateInt($value);
            break;
            case self::SIMPLE_TYPE_FLOAT:
                return $this->validateFloat($value);
            break;
            case self::SIMPLE_TYPE_BOOLEAN:
                return $this->validateBoolean($value);
            break;
            case self::SIMPLE_TYPE_DATE:
                return $this->validateDateTime($value);
            break;
            case self::SIMPLE_TYPE_TIME:
                return $this->validateDateTime($value);
            break;
            case self::SIMPLE_TYPE_DATETIME:
                return $this->validateDateTime($value);
            break;
            case self::SIMPLE_TYPE_ENUM:
                return $this->validateEnum($value);
            break;
            case self::SIMPLE_TYPE_SET:
                return $this->validateSet($value);
            break;
        }
    }

    private function validateString($value)
    {
        $typeArgs = $this->getArgs();
        if (!is_null($typeArgs)) {
            if (strlen($value) > $typeArgs) {
                return $this->errorGenerate('STRING_TOO_LONG');
            }
        }
    }

    public function validateInt($value)
    {
        $filterValue = filter_var($value, FILTER_VALIDATE_INT);
        if ($filterValue !== 0 && $filterValue === false) {
            return $this->errorGenerate('NUMBER_NOT_NUMERIC');
        }
    }

    public function validateFloat($value)
    {
        $filterValue = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($filterValue !== 0 && $filterValue === false) {
            return [static::errorGenerate('NUMBER_NOT_NUMERIC')];
        }
    }

    public function validateBoolean($value)
    {
        if ($value != 0 && $value != 1) {
            return [$this->errorGenerate('BOOLEAN_NOT_VALID')];
        }
    }

    public function validateDateTime($value)
    {
        if ($value->hasErrors()) {
            return $value->getErrors();
        }
    }

    private function validateEnum($value)
    {
        $typeArgs = $this->getArgs();
        if (!array_key_exists($value, $typeArgs)) {
            return $this->errorGenerate('ENUM_NOT_VALID');
        }
    }

    private function validateSet($values)
    {
        $typeArgs = $this->getArgs();
        foreach ($values as $value) {
            if (!array_key_exists($value, $typeArgs)) {
                return $this->errorGenerate('SET_NOT_VALID');
            }
        }
    }

    private function errorGenerate($code)
    {
        return new Error(['code' => $code, 'field' => $this->getName(), 'isAutomated' => true]);
    }
}
