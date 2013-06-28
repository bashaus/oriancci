<?php

namespace Oriancci;

abstract class Model implements \JsonSerializable
{
    use Traits\EventTarget;

    const DEFAULT_AUTO_INCREMENT = 'id';

    public static $accessible = null;
    public static $foreignKeys = [];
    public static $serializable = null;

    public static $field = [];
    public static $validation = [];

    private $attributes = [];
    private $relationships = [];

    public $errors;

    public static $staticMethodHandlers = [
        'findBy'    => 'staticFind',
        'getBy'     => 'staticGet',
        'countBy'   => 'staticCount'
    ];

    public function __construct(array $attributes = null)
    {
        $this->errors = new Errors($this);

        $autoIncrementField = static::autoIncrementField();
        $autoIncrement = $this->autoIncrement();

        $isNew = !is_null($autoIncrementField) && is_null($autoIncrement);

        foreach (static::table()->columns() as $column) {
            $columnName = $column->getName();

            // If this is a row from scratch, then use the default value
            if ($isNew) {
                $columnValue = $column->getDefaultValue();
            } else {
                $columnValue = isset($this->{$columnName}) ? $this->{$columnName} : null;
            }

            switch ($column->getSimpleType()) {
                case Column::SIMPLE_TYPE_BOOLEAN:
                    $this->assignAttribute($columnName, $columnValue ? true : false);
                    break;
                case Column::SIMPLE_TYPE_DATE:
                case Column::SIMPLE_TYPE_TIME:
                case Column::SIMPLE_TYPE_DATETIME:
                    $this->assignAttribute($columnName, new DataType\DateTime($columnValue));
                    break;
                case Column::SIMPLE_TYPE_SET:
                    $this->assignAttribute($columnName, explode(',', $columnValue));
                    break;
                default:
                    $this->assignAttribute($columnName, $columnValue);
                    break;
            }
        }

        if (!is_null($attributes)) {
            $this->setAttributes($attributes);
        }

        // Get the auto increment value as soon as we're finished building the object
        // and before we call the afterConstruct method just in case it
        // taints the data
        $autoIncrement = $this->autoIncrement();

        $this->eventDispatch('afterConstruct');

        if (is_null($autoIncrement)) {
            $this->eventDispatch('afterInstantiation');
        } else {
            $this->eventDispatch('afterSelection');
        }
    }

    /* Connection */

    public static function connection()
    {
        return ConnectionManager::getInstance()->get(static::connectionName());
    }

    public static function connectionName()
    {
        return null;
    }

    /* Database */
    public static function databaseName()
    {
        return null;
    }

    /* Table */

    public static function table()
    {
        return Table::factory(get_called_class());
    }

    public static function tableName()
    {
        return str_replace('\\', '_', get_called_class());
    }

    public function tableFullName()
    {
        $tableFullName = '';

        $databaseName = static::databaseName();
        $tableName = static::tableName();

        if ($databaseName) {
            $tableFullName .= $databaseName . '.';
        }

        $tableFullName .= $tableName;

        return $tableFullName;
    }

    /* Auto increment */

    public static function autoIncrementField()
    {
        return static::DEFAULT_AUTO_INCREMENT;
    }

    public function autoIncrement()
    {
        $autoIncrementField = static::autoIncrementField();

        if (is_null($autoIncrementField)) {
            return null;
        }

        if (!isset($this->{$autoIncrementField})) {
            return null;
        }

        return $this->{$autoIncrementField};
    }

    /* Primary keys */

    public static function primaryKeyFields()
    {
    }

    public static function primaryKeys()
    {
    }

    public static function primaryKeyAsWhere()
    {
    }

    /* Attributes */

    public function assignAttributes(array $attributes = [])
    {
        foreach ($attributes as $attributeName => $attributeValue) {
            $this->assignAttribute($attributeName, $attributeValue);
        }
    }

    public function assignAttribute($attributeName, $attributeValue)
    {
        $this->attributes[$attributeName] = $attributeValue;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes = [])
    {
        foreach ($attributes as $attributeName => $attributeValue) {
            $this->setAttribute($attributeName, $attributeValue);
        }
    }

    public function setAttribute($attributeName, $attributeValue)
    {
        if (!is_null(static::$accessible) && !array_key_exists($attributeName, static::$accessible)) {
            return false;
        }

        $this->$attributeName = $attributeValue;
        return true;
    }

    public function validate()
    {
        $this->errors->clearAutomated();

        // Loop through each column and validate their type
        foreach (static::table()->columns() as $column) {
            $value = $this->{$column->getName()};
            $errors = $column->validate($value);

            if (is_object($errors)) {
                $this->errors[] = $errors;
            } elseif (is_array($errors)) {
                foreach ($errors as $error) {
                    $this->errors[] = $error;
                }
            }
        }

        $validation = new Validation\Validation($this);
        $errors = $validation->validate();

        if (is_object($errors)) {
            $this->errors[] = $errors;
        }

        if (is_array($errors)) {
            foreach ($errors as $error) {
                $this->errors[] = $error;
            }
        }
    }

    public function isValid()
    {
        $this->validate();
        return $this->errors->count() == 0;
    }

    /**
     * SQL functions
     */

    public function save()
    {
        return is_null($this->autoIncrement()) ? $this->insert() : $this->update();
    }

    public function insert()
    {
        if (!$this->eventDispatch('beforeSave', true)) {
            return false;
        }

        if (!$this->eventDispatch('beforeInsert', true)) {
            return false;
        }

        // Set createdAt and updatedAt values
        if (isset($this->createdAt) && $this->createdAt->isNull()) {
            $this->createdAt = 'now';
        }

        if (isset($this->updatedAt)) {
            $this->updatedAt = 'now';
        }

        // Check if the model is valid
        if (!$this->isValid()) {
            return false;
        }

        // Prepare the statement
        $sqlParameters = [
            SET => static::table()->columnsAsSet()
        ];

        $sqlData = $this->toArray();

        // Insert
        $statement = static::table()->insert($sqlParameters);
        if (!$statement->execute($sqlData)) {
            return false;
        }

        // Save the auto increment
        $autoIncrementField = static::autoIncrementField();
        if (!is_null($autoIncrementField)) {
            $this->{$autoIncrementField} = $statement->connection->lastInsertId();
        }

        $this->eventDispatch('afterInsert');
        $this->eventDispatch('afterSave');

        return true;
    }

    public function update()
    {
        if (!$this->eventDispatch('beforeSave', true)) {
            return false;
        }

        if (!$this->eventDispatch('beforeUpdate', true)) {
            return false;
        }

        if (isset($this->updatedAt)) {
            $this->updatedAt = new DataType\DateTime('now');
        }

        if (!$this->isValid()) {
            return false;
        }

        $sqlParameters = [
            SET    => static::table()->columnsAsSet(),
            WHERE  => $this->primaryKeyAsWhere(),
            LIMIT  => 1
        ];

        // Update
        $statement = static::table()->update($sqlParameters);
        $sqlData = $this->toArray();
        $statement->execute($sqlData);

        if ($statement->rowCount() != 1) {
            return false;
        }
        
        $this->eventDispatch('afterUpdate');
        $this->eventDispatch('afterSave');

        return true;
    }

    public function delete()
    {
        if (!$this->eventDispatch('beforeDelete', true)) {
            return false;
        }

        if (count($this->primaryKeyAsWhere()) == 0) {
            return false;
        }

        $sqlParameters = [
            WHERE => $this->primaryKeyAsWhere(),
            LIMIT => 1
        ];

        $statement = static::table()->delete($sqlParameters);
        $statement->execute([$this->primaryKey()]);

        if ($statement->rowCount() != 1) {
            return false;
        }

        $this->eventDispatch('afterDelete');

        return true;
    }

    /**
     * @return int
     *  Number of rows deleted
     */

    public static function deleteAll($sqlParameters, $sqlData = [])
    {
        if (!array_key_exists(WHERE, $sqlParameters)) {
            throw new \Exception('Static method deleteAll requires WHERE clause');
        }

        $statement = static::table()->delete($sqlParameters);
        $statement->execute($sqlData);

        return $statement->rowCount();
    }

    /**
     * @return int
     *  Number of rows updated
     */

    public static function updateAll($sqlParameters, $sqlData = [])
    {
        if (!array_key_exists(WHERE, $sqlParameters)) {
            throw new \Exception('Static method updateAll requires WHERE clause');
        }

        $statement = static::table()->update($sqlParameters);
        $statement->execute($sqlData);

        return $statement->rowCount();
    }

    /**
     * Run dynamic finders
     */
    public static function __callStatic($method, $args)
    {
        // Get the action
        foreach (static::$staticMethodHandlers as $staticPrefix => $staticMethod) {
            if (substr($method, 0, strlen($staticPrefix)) == $staticPrefix) {
                return call_user_func(
                    ['static', $staticMethod],
                    substr($method, strlen($staticPrefix)),
                    $args
                );
            }
        }

        throw new \Exception('Could not understand static method: ' . $method);
    }

    public static function staticFind($name, $args)
    {
        $sqlParameters = Query\Built::methodToStatement($name, $args);
        $inputParameters = $sqlParameters[PARAMS];
        unset($sqlParameters[PARAMS]);
        
        $statement = static::table()->select($sqlParameters);
        return $statement->select($inputParameters);
    }

    public static function staticGet($name, $arguments)
    {
        $sqlParameters = Query\Built::methodToStatement($name, $arguments);
        $inputParameters = $sqlParameters[PARAMS];
        unset($sqlParameters[PARAMS]);

        $statement = static::table()->select($sqlParameters);
        return $statement->one($inputParameters);
    }

    public static function staticCount($name, $arguments)
    {
        $sqlParameters = [];
        $columns = [];
        $parts = Query\Built::methodSplit($name);

        foreach ($parts as $part) {
            if ($part == 'And') {
                continue;
            }

            if ($part == 'Or') {
                throw new Exception('Cannot use OR in countBy');
            }

            $columns[] = lcfirst($part);
        }

        $sqlParameters[SELECT] = $columns;
        $sqlParameters[SELECT]['count'] = 'COUNT(*)';

        $sqlParameters[GROUP_BY] = $columns;

        $sqlData = array_key_exists(0, $arguments) ? $arguments[0] : [];
        $statement = static::table()->select($sqlParameters);
        return $statement->values($sqlData, $columns, 'count');
    }

    /**
     * Finds multiple rows based on conditions
     */
    public static function find($sqlParameters = null)
    {
        $statement = static::table()->select(
            is_null($sqlParameters) ? [] : $sqlParameters
        );

        if (is_null($sqlParameters)) {
            return $statement->select();
        }
        
        return $statement;
    }

    /**
     * Gets a row by its primary key
     */
    public static function get($primaryKey)
    {
        $statement = static::table()->select([WHERE => static::autoIncrementField() . ' = ?']);
        $statement->execute([$primaryKey]);
        return $statement->fetchObject(get_called_class());
    }

    /**
     * Gets the first row in a query
     */
    public static function first(array $sqlParameters = [], array $sqlData = [])
    {
        if (!array_key_exists(ORDER_BY, $sqlParameters)) {
            $sqlParameters[ORDER_BY] = [static::autoIncrementField() => ASC];
        }

        $sqlParameters[LIMIT] = 1;
        $sqlParameters[OFFSET] = 0;
        unset($sqlParameters[PAGE]);

        return static::table()->select($sqlParameters)->one($sqlData);
    }

    /**
     * Gets the last row in a query
     */
    public static function last(array $sqlParameters = [], array $sqlData = [])
    {
        if (!array_key_exists(ORDER_BY, $sqlParameters)) {
            $sqlParameters[ORDER_BY] = [static::autoIncrementField() => DESC];
        } else {
            $sqlParameters[ORDER_BY] = Query::orderByInvert($sqlParameters[ORDER_BY]);
        }

        $sqlParameters[LIMIT] = 1;
        $sqlParameters[OFFSET] = 0;
        unset($sqlParameters[PAGE]);

        return static::table()->select($sqlParameters)->one($sqlData);
    }

    /**
     * Counts the number of rows in a table
     */
    public static function exists($sqlParameters = [], $sqlData = [])
    {
        if (array_key_exists(GROUP_BY, $sqlParameters)) {
            throw new \Exception('Cannot use GROUP_BY clause in exists query.');
        }

        $sqlParameters[SELECT]['count'] = 'COUNT(*)';
        $sqlParameters[LIMIT] = 1;
        $sqlParameters[OFFSET] = 0;

        $statement = static::table()->aggregate($sqlParameters);
        return $statement->value($sqlData) != 0;
    }

    /**
     * Counts the number of rows in a table
     */
    public static function count($sqlParameters = [])
    {
        $hasParameters = count($sqlParameters);

        if (!array_key_exists(SELECT, $sqlParameters)) {
            $sqlParameters[SELECT] = [];
        }

        $sqlParameters[SELECT]['count'] = 'COUNT(*)';

        $statement = static::table()->aggregate($sqlParameters);

        if ($hasParameters) {
            return $statement;
        }

        return (int) $statement->value();
    }

    /**
     * Magic method for checking is item exists
     */

    public function __isset($attributeName)
    {
        if (method_exists($this, 'get' . $attributeName)) {
            return true;
        }

        if (array_key_exists($attributeName, $this->attributes)) {
            return true;
        }

        if (array_key_exists($attributeName, $this->relationships)) {
            return true;
        }

        return false;
    }

    /**
     * Magic method for getting attributes
     */

    public function __get($attributeName)
    {
        if (method_exists($this, 'get' . $attributeName)) {
            return call_user_func([$this, 'get' . $attributeName]);
        }

        if (array_key_exists($attributeName, $this->attributes)) {
            return $this->attributes[$attributeName];
        }

        if (array_key_exists($attributeName, $this->relationships)) {
            return $this->relationships[$attributeName];
        }

        return null;
    }

    /**
     * Magic method for setting attributes
     */

    public function __set($attributeName, $attributeValue)
    {
        // This first conditional is for when the object hasn't yet
        // been instantiated and we just need to store the value
        if (is_null($this->errors)) {
            return $this->attributes[$attributeName] = $attributeValue;
        }

        if (method_exists($this, 'set' . $attributeName)) {
            return call_user_func([$this, 'set' . $attributeName], $attributeValue);
        }

        if (is_null(static::table()->column($attributeName))) {
            throw new \Exception('Column does not exist: ' . $attributeName);
        }

        $column = static::table()->column($attributeName);

        if ($column->isAllowedNull()) {
            if (empty($attributeValue)) {
                $attributeValue = null;
            }
        }

        if ($this->attributes[$attributeName] instanceof DataType\DataTypeInterface) {
            $this->attributes[$attributeName]->setInput($attributeValue);
        } else {
            $this->attributes[$attributeName] = $attributeValue;
        }

        return $attributeValue;
    }








    /**
     * Relationships
     */

    public function relationshipSet($relationshipName, $relationshipObject)
    {
        $this->relationships[$relationshipName] = $relationshipObject;
    }

    public static function relationship($relationshipName, $dataIn)
    {
        if (!array_key_exists($relationshipName, static::$foreignKeys)) {
            throw new \Exception(sprintf('Relationship "%s" does not exist', $relationshipName));
        }

        $relationship = static::$foreignKeys[$relationshipName];

        if (!($relationship instanceof Relationship\Relationship)) {
            $relationship['name'] = $relationshipName;
            $relationship['caller'] = get_called_class();
            $relationship = Relationship\Relationship::factory($relationship);
            static::$foreignKeys[$relationshipName] = $relationship;
        }

        // At this point, $relationship is an object of type \Oriancci\Relationship\*;
        return $relationship->query($dataIn);
    }











    /* Transaction */
    public static function transact($callable)
    {
        return static::connection()->transact($callable);
    }















    /* Serialisation */

    /**
     * JSON Serialization
     */
    public function jsonSerialize()
    {
        $return = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (is_null(static::$serializable)) {
                $return[$key] = $value;
            } elseif (array_key_exists($key, static::$serializable) && static::$serializable[$key] == true) {
                $return[$key] = $value;
            }
        }

        return (object) $return;
    }



    /**
     * Convert the object to a 2D array
     */

    public function toArray()
    {
        $return = [];

        foreach (static::table()->columns() as $column) {
            $columnValue = $this->{$column->getName()};

            if ($column->getSimpleType() == Column::SIMPLE_TYPE_BOOLEAN) {
                $columnValue = $columnValue ? '1' : '0';
            } elseif ($columnValue instanceof DataType\DataTypeInterface) {
                $columnValue = $columnValue->toDB();
            } elseif (is_array($columnValue)) {
                $columnValue = implode(',', $columnValue);
            }

            $return[':' . $column->getName()] = $columnValue;
        }

        return $return;
    }
}
