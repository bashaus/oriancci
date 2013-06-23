<?php

namespace Oriancci\Relationship;

abstract class Relationship
{
    
    const KEY_PRIMARY = 'primary';
    const KEY_FOREIGN = 'foreign';

    const HAS_ONE  = 'one';
    const HAS_MANY = 'many';

    public static $accessible = [
        'name' => true,
        'caller' => true,
        'has' => true,
        'through' => true,
        'model' => true,
        'key' => true
    ];

    public static $required = [
        'name' => true,
        'caller' => true,
        'has' => true,
        'model' => true,
        'key' => true
    ];

    public $name = null;
    public $caller = null;
    public $callee = null;

    public $has = null;
    public $through = null;
    public $model = null;
    public $key = null;

    public $primaryModel;
    public $primaryField;
    public $foreignModel;
    public $foreignField;

    public $fromModel;
    public $fromField;
    public $findModel;
    public $findField;

    private function __construct($relationshipData)
    {
        foreach ($relationshipData as $attributeName => $attributeValue) {
            if (!array_key_exists($attributeName, static::$accessible) || !static::$accessible[$attributeName]) {
                throw new \Exception(sprintf('Relationship attribute not accessible: %s', $attributeName));
            }

            $this->{$attributeName} = $attributeValue;
        }

        foreach (static::$required as $attributeName => $attributeIsRequired) {
            if (!$attributeIsRequired) {
                continue;
            }

            if (is_null($this->{$attributeName})) {
                throw new \Exception(
                    sprintf('For a relationship, you must specify the attribute: %s', $attributeName)
                );
            }
        }

        $this->caller = strtolower($this->caller);
        $this->callee = strtolower(!is_null($this->model) ? $this->model : $this->name);

        // Name of the external model
        $this->primaryModel = ($this->key == self::KEY_PRIMARY) ? $this->caller : $this->callee;
        $this->foreignModel = ($this->key == self::KEY_PRIMARY) ? $this->callee : $this->caller;

        // Instance variables for calling static class
        $primaryModel = $this->primaryModel;
        $foreignModel = $this->foreignModel;

        // Name of the active field
        $this->primaryField = $primaryModel::$primaryKey;
        $this->foreignField = $primaryModel . '_' . $primaryModel::$primaryKey;

        // Get from/find information
        $this->fromModel = ($this->key == self::KEY_PRIMARY) ? $this->primaryModel : $this->foreignModel;
        $this->fromField = ($this->key == self::KEY_PRIMARY) ? $this->primaryField : $this->foreignField;

        $this->findModel = ($this->key == self::KEY_PRIMARY) ? $this->foreignModel : $this->primaryModel;
        $this->findField = ($this->key == self::KEY_PRIMARY) ? $this->foreignField : $this->primaryField;
    }

    public function query($resultsIn)
    {
        $findModel = $this->findModel;

        // Run the query
        $values = $resultsIn->each($this->fromField);

        // If there are no results in the query, return nothing
        if (empty($values)) {
            return null;
        }

        $sqlQuery = [
            WHERE => $this->findField . ' IN (' . implode(', ', array_fill(0, count($values), '?')) . ')'
        ];

        $sqlStmt = $findModel::find($sqlQuery);

        $resultsOut = $sqlStmt->select($values);

        $this->result($resultsIn, $resultsOut);

        return $resultsOut;
    }

    abstract public function result($resultsIn, $resultsOut);

    /**
     * Factory loads a relationship
     */
    public static function factory($relationship)
    {
        if (!array_key_exists('has', $relationship)) {
            throw new \Exception('You must specify a "has" value for a relationship.');
        }

        switch ($relationship['has']) {
            case self::HAS_ONE:
                return new HasOne($relationship);
                break;
            case self::HAS_MANY:
                return new HasMany($relationship);
                break;
        }
    }
}
