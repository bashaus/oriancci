<?php

namespace Oriancci;

class Statement extends \PDOStatement
{

    public $connection;
    public $query;

    protected $resultClass = '\Oriancci\Result\Collection';

    protected function __construct($connection, $query)
    {
        $this->connection = $connection;
        $this->query = $query;
    }

    /* Function Runners */
    public function execute($sqlData = [])
    {
        foreach ($sqlData as $dataKey => $dataValue) {
            if ($dataValue instanceof DataType\DataTypeInterface) {
                $sqlData[$dataKey] = $dataValue->toDB();
            }
        }

        if (!parent::execute($sqlData)) {
            throw new \Exception('Could not execute SQL statement');
        }

        return true;
    }

    public function setResultClass($resultClass)
    {
        $this->resultClass = $resultClass;

        return $this;
    }

    public function select($sqlData = [])
    {
        $this->execute($sqlData);

        $resultClass = $this->resultClass;
        return new $resultClass($this);
    }

    public function one($sqlData = [])
    {
        $this->execute($sqlData);
        return $this->query->fetch($this);
    }

    public function insert($sqlData = [])
    {
        if (!$this->execute($sqlData)) {
            return false;
        }
        
        return $this->connection->lastInsertId();
    }

    public function update($sqlData = [])
    {
        if (!$this->execute($sqlData)) {
            return false;
        }
        
        return $this->rowCount();
    }

    public function delete($sqlData = [])
    {
        if (!$this->execute($sqlData)) {
            return false;
        }
        
        return $this->rowCount();
    }

    public function value($sqlData = [], $column = 0)
    {
        $this->execute($sqlData);
        return $this->fetchColumn($column);
    }

    public function values($sqlData = [], $keyFields = 'id', $valueField = 'count')
    {
        $results = [];
        $this->execute($sqlData);

        if (is_scalar($keyFields)) {
            $keyFields = [$keyFields];
        }

        while ($row = $this->fetchObject()) {
            $resultRow =& $results;

            foreach ($keyFields as $keyField) {
                if (!is_array($resultRow)) {
                    $resultRow = [];
                }

                $resultRow =& $resultRow[$row->{$keyField}];
            }

            $resultRow = $row->{$valueField};
        }

        return $results;
    }
}
