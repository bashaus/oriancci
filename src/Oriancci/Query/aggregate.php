<?php

namespace Oriancci\Query;

class Aggregate extends Select
{

    public function fetch($statement)
    {
        return $statement->fetchObject();
    }
}
