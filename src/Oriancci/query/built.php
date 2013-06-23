<?php

namespace Oriancci\Query;

abstract class Built extends Query
{

    protected $sqlParameters;

    public function __construct($model, $sqlParameters = [])
    {
        parent::__construct($model);
        $this->sqlParameters = $sqlParameters;
        $this->sql = $this->buildQuery();
    }

    /* */

    abstract public function buildQuery();

    /* */

    public function buildTableName($key = null)
    {
        if (!is_null($key) && array_key_exists($key, $this->sqlParameters)) {
            return $this->sqlParameters[$key];
        }

        return call_user_func([$this->model, 'tableName']);
    }

    public function buildSelect($key = SELECT)
    {
        if (!array_key_exists($key, $this->sqlParameters)) {
            return '*';
        }

        $select = $this->sqlParameters[$key];

        if (is_array($select)) {
            $fields = [];

            foreach ($select as $selectAs => $selectField) {
                $field = $selectField;

                if (!is_numeric($selectAs)) {
                    $field .= ' AS ' . $selectAs;
                }

                $fields[] = $field;
            }

            return implode(', ', $fields);
        }

        return $select;
    }

    public function buildSet($key = SET)
    {
        $return = [];

        if (!array_key_exists($key, $this->sqlParameters)) {
            throw new \Exception('Set required');
        }

        foreach ($this->sqlParameters[$key] as $fieldName => $fieldIdentifier) {
            $return[] .= sprintf('%s = %s', $fieldName, $fieldIdentifier);
        }

        return implode(', ', $return);
    }

    public function buildConditions($key = WHERE)
    {
        if (!isset($this->sqlParameters[$key])) {
            return '1';
        }

        return $this->sqlParameters[$key];
    }

    public static function orderByInvert($orderBy)
    {
        $return = [];

        foreach ($orderBy as $field => $value) {
            $return[$field] = !$value;
        }

        return $return;
    }

    public static function methodSplit($method)
    {
        return preg_split('/(And|Or)/', $method, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    public static function methodToStatement($method, $args)
    {
        $conditions = '';
        $parts = static::methodSplit($method);
        $sqlParameters = [
            WHERE => '',
            PARAMS => []
        ];

        foreach ($parts as $part) {
            if ($part == 'And') {
                $conditions .= ' AND ';
                continue;
            }

            if ($part == 'Or') {
                $conditions .= ' OR ';
                continue;
            }

            $part = lcfirst($part);

            if (count($args) == 0) {
                throw new \Exception('Number of args does not match method signature in ' . $method);
            }

            $activeArg = array_shift($args);

            if (is_null($activeArg)) {
                $conditions .= $part . ' IS NULL';
                continue;
            }

            if ($activeArg === true) {
                $conditions .= $part . ' = 1';
                continue;
            }

            if ($activeArg === false) {
                $conditions .= $part . ' = 0';
                continue;
            }

            if (is_array($activeArg)) {
                $sqlParameters[PARAMS] = array_merge($sqlParameters[PARAMS], array_values($activeArg));
                $conditions .= $part . ' IN (' . implode(', ', array_fill(0, count($activeArg), '?')) . ')';
                continue;
            }

            $sqlParameters[PARAMS][] = $activeArg;
            $conditions .= $part . ' = ?';
        }

        $userDefinedParameters = array_shift($args);
        if (!is_null($userDefinedParameters)) {
            $sqlParameters += $userDefinedParameters;
        }

        if (count($args) != 0) {
            throw new \Exception('Number of args mismatches query');
        }

        $sqlParameters[WHERE] = $conditions;

        return $sqlParameters;
    }

    public static function primaryKeysAsWhere(array $primaryKeyFields)
    {
        $where = [];

        foreach ($primaryKeyFields as $primaryKeyField) {
            $where[] = $primaryKeyField . ' = :' . $primaryKeyField;
        }

        return implode(' AND ', $where);
    }
}
