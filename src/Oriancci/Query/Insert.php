<?php

namespace Oriancci\Query;

class Insert extends Built
{
    
    public function buildQuery()
    {
        // INSERT INTO
        $sql = 'INSERT INTO ';
        $sql .= $this->buildTableName(INSERT_INTO);

        // SET
        $sql .= ' SET ';
        $sql .= $this->buildSet(SET);

        return $sql;
    }
}
