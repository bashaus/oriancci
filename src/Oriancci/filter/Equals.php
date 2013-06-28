<?php

namespace Oriancci\Filter;

class Equals extends \FilterIterator
{
    
    protected $field;
    protected $value;

    public function __construct(\IteratorAggregate $iterator, $field, $value)
    {
        parent::__construct($iterator->getIterator());

        $this->field = $field;
        $this->value = $value;
    }

    public function getField()
    {
        return $this->field;
    }

    public function getValue()
    {
        return $this->value;
    }
    
    public function accept()
    {
        return $this->getInnerIterator()->current()->{$this->field} == $this->value;
    }
}