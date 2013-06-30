<?php

namespace Oriancci\Query;

class Delete extends Built
{
    
    public function buildQuery()
    {
        // DELETE FROM
        $sql = 'DELETE FROM ';
        $sql .= $this->buildTableName(DELETE);

        // WHERE
        if (array_key_exists(WHERE, $this->sqlParameters)) {
            $sql .= ' WHERE ';
            $sql .= $this->buildConditions(WHERE);
        }

        // ORDER BY
        if (array_key_exists(ORDER_BY, $this->sqlParameters)) {
            $sql .= ' ORDER BY ';

            $fields = [];
            foreach ($this->sqlParameters[ORDER_BY] as $field => $direction) {
                $fields[] = $field . ' ' . (($direction) ? 'ASC' : 'DESC');
            }

            $sql .= implode(', ', $fields);
        }

        // LIMIT
        if (array_key_exists(LIMIT, $this->sqlParameters)) {
            $sql .= ' LIMIT ' . $this->sqlParameters[LIMIT];
        }

        return $sql;
    }
}
