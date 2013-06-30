<?php

namespace Oriancci\Result;

use Oriancci\Statement;

class Collection extends \ArrayObject implements \ArrayAccess, \Countable, \JsonSerializable
{

    public $statement;

    public function __construct(Statement $statement = null)
    {
        $this->statement = $statement;

        while ($object = $this->statement->query->fetch($this->statement)) {
            $this->append($object);
        }

        $this->statement->closeCursor();

        parent::__construct();
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

        foreach ($this as $row) {
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
