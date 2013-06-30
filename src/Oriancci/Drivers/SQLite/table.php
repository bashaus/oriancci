<?php

namespace Oriancci\Drivers\SQLite;

class Table extends \Oriancci\Table
{

    public function describe()
    {
        $modelName = $this->modelName;
        $connection = $modelName::connection();
        return $connection->query('PRAGMA table_info([' . $this->tableFullName() . '])');
    }
    
    public function columnClass()
    {
        return 'Oriancci\Drivers\SQLite\Column';
    }
}
