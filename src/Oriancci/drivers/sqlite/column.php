<?php

namespace Oriancci\Drivers\SQLite;

class Column extends \Oriancci\Column
{

    // The following come from the SQL query

    protected $name;
    protected $type;
    protected $notnull;
    protected $pk;
    protected $dfltValue;
    protected $extra;

    public static $types = [
        // SQLite
        'INTEGER'    => Column::SIMPLE_TYPE_INTEGER,
        'NUMERIC'    => Column::SIMPLE_TYPE_INTEGER,
        'TEXT'       => Column::SIMPLE_TYPE_TEXT
    ];

    public function __construct()
    {
        $this->setSimpleType(static::$types[$this->typeName]);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getArgs()
    {
        return $this->typeArgs;
    }

    public function isAllowedNull()
    {
        return $this->notnull == 0;
    }

    public function isPrimaryKey()
    {
        return $this->pk == 1;
    }

    public function getDefaultValue()
    {
        return $this->dfltValue;
    }

    public function isAutoIncrement()
    {
        return $this->extra == 'auto_increment';
    }
}
