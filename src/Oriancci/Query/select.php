<?php

namespace Oriancci\Query;

class Select extends Built
{

    public function buildQuery()
    {
        // SELECT
        $sql = 'SELECT ';
        $sql .= $this->buildSelect(SELECT);

        // FROM
        $sql .= ' FROM ';
        $sql .= $this->buildTableName(FROM);

        // JOINS
        if (array_key_exists(JOIN, $this->sqlParameters)) {
            $sql .= ' ' . $this->sqlParameters[JOIN];
        }

        // WHERE
        if (array_key_exists(WHERE, $this->sqlParameters)) {
            $sql .= ' WHERE ';
            $sql .= $this->buildConditions(WHERE);
        }

        // GROUP BY
        if (array_key_exists(GROUP_BY, $this->sqlParameters)) {
            $sql .= ' GROUP BY ';

            if (is_array($this->sqlParameters[GROUP_BY])) {
                $sql .= implode(', ', $this->sqlParameters[GROUP_BY]);
            } else {
                $sql .= $this->sqlParameters[GROUP_BY];
            }
        }

        // HAVING
        if (array_key_exists(HAVING, $this->sqlParameters)) {
            $sql .= ' HAVING ';
            $sql .= $this->buildConditions(HAVING);
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
            
            if (array_key_exists(PAGE, $this->sqlParameters)) {
                $sql .= ' OFFSET ' . (($this->sqlParameters[PAGE] - 1) * $this->sqlParameters[LIMIT]);
            } elseif (array_key_exists(OFFSET, $this->sqlParameters)) {
                $sql .= ' OFFSET ' . $this->sqlParameters[OFFSET];
            }
        }

        return $sql;
    }
}
