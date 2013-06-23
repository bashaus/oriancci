<?php

namespace Oriancci\Query;

class Update extends Built
{
    
    public function buildQuery()
    {
        // UPDATE
        $sql = 'UPDATE ';
        $sql .= $this->buildTableName(UPDATE);

        // SET
        $sql .= ' SET ';
        $sql .= $this->buildSet(SET);

        // WHERE
        $sql .= ' WHERE ';
        $sql .= $this->buildConditions(WHERE);

        // LIMIT
        if (array_key_exists(LIMIT, $this->sqlParameters)) {
            $sql .= ' LIMIT ' . $this->sqlParameters[LIMIT];
            
            if (array_key_exists(PAGE, $this->sqlParameters)) {
                $sql .= ' OFFSET ' . (($this->sqlParameters[PAGE] - 1) * $this->sqlParameters[LIMIT]);
            } elseif (array_key_exists(OFFSET, $this->sqlParameters)) {
                $sql .= ' OFFSET ' . $this->sqlParameters[OFFSET];
            }
        }

        return $sql;
    }
}
