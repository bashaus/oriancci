<?php

namespace Oriancci\Drivers\MySQL;

class Connection extends \Oriancci\Connection
{

    const DEFAULT_HOST_NAME   = 'locahost';
    const DEFAULT_HOST_PORT   = 3306;
    const DEFAULT_DATABASE    = '/';

    public function __construct($connection)
    {
        foreach (array('username', 'password', 'database') as $parameter) {
            if (!array_key_exists($parameter, $connection)) {
                throw new \Exception('Connections must specify the parameter: ' . $parameter);
            }
        }

        $connection['string'] = sprintf(
            'mysql:host=%s;port=%d;dbname=%s',
            array_key_exists('hostname', $connection) ? $connection['hostname'] : static::DEFAULT_HOST_NAME,
            array_key_exists('hostport', $connection) ? $connection['hostport'] : static::DEFAULT_HOST_PORT,
            $connection['database']
        );

        $options = $this->getOptions();
        
        parent::__construct($connection, $connection['username'], $connection['password'], $options);

        if (array_key_exists('logger', $connection)) {
            $this->setLogger($connection['logger']);
        }
    }

    public function setEncoding($charset)
    {
        $this->prepare('SET NAMES ?')->execute([$charset]);
        $this->charset = $charset;
    }

    public function sqlDescribeTable($tableName)
    {
        return 'DESCRIBE ' . $tableName;
    }

    public function getColumnClass()
    {
        return 'Oriancci\Drivers\MySQL\Column';
    }
}
