<?php

namespace Oriancci\Drivers\MySQL;

class Column extends \Oriancci\Column
{

    //The following come from the SQL query
    protected $Field;   // e.g. id
    protected $Type;    // e.g. int(11)
    protected $Null;    // e.g. NO
    protected $Key;     // e.g. PRI
    protected $Default; // e.g. ''
    protected $Extra;   // e.g. auto_increment

    public static $types = [
        'char'       => Column::SIMPLE_TYPE_STRING,
        'varchar'    => Column::SIMPLE_TYPE_STRING,
        'tinytext'   => Column::SIMPLE_TYPE_TEXT,
        'text'       => Column::SIMPLE_TYPE_TEXT,
        'mediumtext' => Column::SIMPLE_TYPE_TEXT,
        'longtext'   => Column::SIMPLE_TYPE_TEXT,

        'bit'        => Column::SIMPLE_TYPE_INTEGER,
        'tinyint'    => Column::SIMPLE_TYPE_INTEGER,
        'smallint'   => Column::SIMPLE_TYPE_INTEGER,
        'mediumint'  => Column::SIMPLE_TYPE_INTEGER,
        'int'        => Column::SIMPLE_TYPE_INTEGER,
        'bigint'     => Column::SIMPLE_TYPE_INTEGER,

        'decimal'    => Column::SIMPLE_TYPE_FLOAT,
        'float'      => Column::SIMPLE_TYPE_FLOAT,
        'double'     => Column::SIMPLE_TYPE_FLOAT,
        'real'       => Column::SIMPLE_TYPE_FLOAT,

        'bool'       => Column::SIMPLE_TYPE_BOOLEAN,

        'date'       => Column::SIMPLE_TYPE_DATE,
        'time'       => Column::SIMPLE_TYPE_TIME,
        'datetime'   => Column::SIMPLE_TYPE_DATETIME,
        'timestamp'  => Column::SIMPLE_TYPE_DATETIME,
        'year'       => Column::SIMPLE_TYPE_INTEGER,

        'enum'       => Column::SIMPLE_TYPE_ENUM,
        'set'        => Column::SIMPLE_TYPE_SET
    ];

    public static $translateType = [
        'tinyint(1)' => 'bool'
    ];

    public $typeName;
    public $typeArgs;

    public function __construct()
    {
        if (array_key_exists($this->getName(), self::$translateType)) {
            $this->Type = self::$translateType[$this->getName()];
        }

        $typeStart = strpos($this->getType(), '(');
        if ($typeStart !== false) {
            $typeCease = strpos($this->getType(), ')', $typeStart);

            $this->typeName = substr($this->getType(), 0, $typeStart);
            $this->typeArgs = substr($this->getType(), $typeStart + 1, $typeCease - $typeStart - 1);
        } else {
            $typeCease = strpos($this->getType(), ' ');

            if ($typeCease === false) {
                $this->typeName = $this->getType();
            } else {
                $this->typeName = substr($this->getType(), 0, $typeCease);
            }
        }

        $this->setSimpleType(static::$types[$this->typeName]);

        if (empty($this->typeArgs)) {
            $this->typeArgs = null;
        }

        switch ($this->getSimpleType())
        {
            case static::SIMPLE_TYPE_ENUM:
            case static::SIMPLE_TYPE_SET:
                $this->typeArgs = static::parseArgList($this->typeArgs);
                break;
        }
    }

    public function getType()
    {
        return $this->Type;
    }

    public function getName()
    {
        return $this->Field;
    }

    public function getArgs()
    {
        return $this->typeArgs;
    }

    public function isAllowedNull()
    {
        return $this->Null != 'NO';
    }

    public function isPrimaryKey()
    {
        return $this->Key == 'PRI';
    }

    public function isAutoIncrement()
    {
        return $this->Extra == 'auto_increment';
    }

    public function getDefaultValue()
    {
        return $this->Default;
    }
}
