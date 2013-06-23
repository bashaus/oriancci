<?php

namespace Oriancci\Result;

use Oriancci\Statement;

class Collection extends \FilterIterator implements \ArrayAccess, \Countable, \JsonSerializable
{

    private $filterField;
    private $filterValue;
    private $filterCount;

    public $results;
    public $statement;

    public function __construct(Statement $statement = null)
    {
        $this->statement = $statement;
        $this->results = new \ArrayObject;

        while ($object = $this->statement->query->fetch($this->statement)) {
            $this->results->append($object);
        }

        $this->statement->closeCursor();

        $this->clearFilter();

        parent::__construct($this->results->getIterator());
    }

    /* FilterIterator */

    public function filter($filterField = null, $filterValue = null)
    {
        $this->filterField = $filterField;
        $this->filterValue = $filterValue;

        $this->filterCount = 0;
        foreach ($this as $object) {
            ++$this->filterCount;
        }
    }

    public function clearFilter()
    {
        $this->filterField = null;
        $this->filterValue = null;
        $this->filterCount = count($this->results);
    }

    public function accept()
    {
        if (is_null($this->filterField)) {
            return true;
        }

        return $this->current()->{$this->filterField} == $this->filterValue;
    }

    /* Relationship */

    public function __call($methodName, $methodArguments)
    {
        return $this->relationship($methodName);
    }

    public function relationship($relationshipName)
    {
        $model = $this->statement->query->model;
        return $model::relationship($relationshipName, $this);
    }

    /* Helpers */

    public function collate($keyFields, $valueField)
    {
        $results = [];

        if (is_scalar($keyFields)) {
            $keyFields = [$keyFields];
        }

        foreach ($this->results as $row) {
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

    public function each($valueField)
    {
        $return = [];

        foreach ($this as $object) {
            $return[] = $object->$valueField;
        }

        return $return;
    }

    /* ArrayAccess */

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->results);
    }

    public function offsetGet($offset)
    {
        return $this->results[$offset];
    }

    /**
     * You cannot alter the value of a result
     * Consequentially, this method does nothing
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception('You cannot alter the value of a result');
    }

    /**
     * You cannot alter the value of a result
     * Consequentially, this method does nothing
     */
    public function offsetUnset($offset)
    {
        throw new \Exception('You cannot alter the value of a result');
    }

    /* Countable */
    public function count()
    {
        return $this->filterCount;
    }

    /* Serialization */

    /**
     * @see http://css-tricks.com/snippets/php/generate-csv-from-array/
     */

    public function toCSV($headers = true, $delimiter = ',', $enclosure = '"')
    {
        $contents = '';

        $fp = fopen('php://temp', 'r+');

        if ($headers) {
            $fieldNames = [];

            $model = $this->statement->query->model;

            foreach ($model::table()->columns() as $column) {
                $fieldNames[] = $column->getName();
            }

            fputcsv($fp, $fieldNames, $delimiter, $enclosure);
        }

        foreach ($this as $row) {
            fputcsv($fp, $row->toArray(), $delimiter, $enclosure);
        }
        
        rewind($fp);
        
        while (!feof($fp)) {
            $contents .= fread($fp, 1024);
        }

        fclose($fp);
        return $contents;
    }

    public function jsonSerialize()
    {
        $return = [];

        foreach ($this->results as $key => $value) {
            if ($value instanceof \JsonSerializable) {
                $return[$key] = $value->jsonSerialize();
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}
