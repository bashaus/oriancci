<?php

namespace Oriancci;

class Table
{
    use Traits\Factory;

    protected $modelName;

    protected $columns;
    protected $indexes;

    private function __construct($modelName)
    {
        $this->modelName = $modelName;

        // Get column data
        $this->columns();

        // Get index data
        $this->indexes();
    }

    public function connectionName()
    {
        $modelName = $this->modelName;
        return $modelName::connectionName();
    }

    /* DB helpers */

    protected function databaseName()
    {
        $modelName = $this->modelName;
        return $modelName::databaseName();
    }

    protected function tableName()
    {
        $modelName = $this->modelName;
        return $modelName::tableName();
    }

    public function tableFullName()
    {
        $tableFullName = '';

        $databaseName = $this->databaseName();
        $tableName = $this->tableName();

        if ($databaseName) {
            $tableFullName .= $databaseName . '.';
        }

        $tableFullName .= $tableName;

        return $tableFullName;
    }

    /* Schema */

    public function columns()
    {
        if (is_null($this->columns)) {
            $this->columns = [];

            $connection = ConnectionManager::getInstance()->get($this->connectionName());
            $query = $connection->query(
                $connection->sqlDescribeTable($this->tableFullName())
            );

            while ($column = $query->fetchObject($connection->getColumnClass())) {
                $this->columns[$column->getName()] = $column;
            }
        }

        return $this->columns;
    }

    public function column($columnName)
    {
        return array_key_exists($columnName, $this->columns) ? $this->columns[$columnName] : null;
    }

    public function indexes()
    {
        /*
        if (is_null($this->indexes)) {
            $this->indexes = [];

            $connection = ConnectionManager::getInstance()->get($this->connectionName());
            $query = $connection->query('SHOW INDEXES FROM ' . $this->tableFullName());

            while ($index = $query->fetchObject('\Oriancci\Index')) {
                $this->indexes[$index->Key_name] = $index;
            }
        }

        return $this->indexes;
        */
    }

    /* Helpers */

    public function columnsAsSet()
    {
        $return = [];
        
        foreach ($this->columns as $column) {
            $return[$column->getName()] = ':' . $column->getName();
        }

        return $return;
    }

    /* Statements */

    public function aggregate($sqlParameters)
    {
        if (!array_key_exists(FROM, $sqlParameters)) {
            $sqlParameters[FROM] = $this->tableFullName();
        }

        $query = new Query\Aggregate($this->modelName, $sqlParameters);
        return $query->prepare();
    }

    public function select($sqlParameters)
    {
        if (!array_key_exists(FROM, $sqlParameters)) {
            $sqlParameters[FROM] = $this->tableFullName();
        }

        $query = new Query\Select($this->modelName, $sqlParameters);
        return $query->prepare();
    }

    public function insert($sqlParameters)
    {
        if (!array_key_exists(INSERT, $sqlParameters)) {
            $sqlParameters[INSERT] = $this->tableFullName();
        }

        $query = new Query\Insert($this->modelName, $sqlParameters);
        return $query->prepare();
    }

    public function update($sqlParameters)
    {
        if (!array_key_exists(UPDATE, $sqlParameters)) {
            $sqlParameters[UPDATE] = $this->tableFullName();
        }

        $query = new Query\Update($this->modelName, $sqlParameters);
        return $query->prepare();
    }

    public function delete($sqlParameters)
    {
        $query = new Query\Delete($this->modelName, $sqlParameters);
        return $query->prepare();
    }
}
