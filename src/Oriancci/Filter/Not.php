<?php

namespace Oriancci\Filter;

class Not extends \FilterIterator
{
    
    public function __construct(\FilterIterator $iterator)
    {
        parent::__construct($iterator->getIterator());
    }

    public function accept()
    {
        return !parent::accept();
    }
}
