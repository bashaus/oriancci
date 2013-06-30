<?php

namespace Oriancci\Drivers\SQLite;

class Connection extends \Oriancci\Connection
{

    public function __construct($connection)
    {
        foreach (array('filename') as $parameter) {
            if (!array_key_exists($parameter, $connection)) {
                throw new \Exception('Connections must specify the parameter ' . $parameter);
            }
        }

        $connection['string'] = sprintf('sqlite:%s', $connection['filename']);

        $options = $this->getOptions();
        
        parent::__construct($connection, null, null, $options);
    }

    public function setEncoding($charset)
    {
        // Does not exist with sqlite
    }

    public function tableClass()
    {
        return 'Oriancci\Drivers\SQLite\Table';
    }
}
