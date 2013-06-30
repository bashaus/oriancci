<?php

namespace Oriancci\Drivers\MySQL;

class Table extends \Oriancci\Table
{
    
    public function describe()
    {
        $modelName = $this->modelName;
        $connection = $modelName::connection();
        return $connection->query('DESCRIBE ' . $this->tableFullName());
    }

    public function getColumnClass()
    {
        return 'Oriancci\Drivers\MySQL\Column';
    }
}
