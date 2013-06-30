<?php

namespace Oriancci\Result;

use Oriancci\Statement;

class Cursor implements \Iterator, \Countable
{

    public $statement;

    public $i = 0;
    public $result;

    public function __construct(Statement $statement = null)
    {
        $this->statement = $statement;
        $this->next();
    }

    public function current()
    {
        return $this->result;
    }

    public function key()
    {
        return $this->i;
    }

    public function next()
    {
        $this->result = $this->statement->query->fetch($this->statement);
        $this->i++;
    }

    public function rewind()
    {
    }

    public function valid()
    {
        if ($this->result == false) {
            $this->statement->closeCursor();
            return false;
        }

        return true;
    }

    /* Countable */
    public function count()
    {
        /*
        http://uk1.php.net/manual/en/pdostatement.rowcount.php

        If the last SQL statement executed by the associated PDOStatement was a SELECT statement, some 
        databases may return the number of rows returned by that statement. However, this behaviour is not guaranteed 
        for all databases and should not be relied on for portable applications.
        */
        return $this->statement->rowCount();
    }
}
