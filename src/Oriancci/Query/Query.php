<?php

namespace Oriancci\Query;

use \Oriancci\ConnectionManager;

class Query
{

    public $sql;
    public $model;

    public static $statementClass = 'Oriancci\Statement';

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function connection()
    {
        $model = $this->model;
        return $model::connection();
    }

    /* */

    public function prepare()
    {
        $connection = $this->connection();
        $connection->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [static::$statementClass, [$connection, $this]]);
        
        return $connection->prepare($this->sql);
    }

    public function fetch($statement)
    {
        return $statement->fetchObject($this->model);
    }
}
