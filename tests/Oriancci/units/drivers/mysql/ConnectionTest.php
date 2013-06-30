<?php

namespace Oriancci\Drivers\MySQL;

use Oriancci\ConnectionManager;
use PHPUnit_Framework_TestCase;

class ConnectionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException Exception
     */    
    public function testInvalidConnection()
    {
        $connectionManager = ConnectionManager::getInstance();
        $connectionManager->mysql = [
            'driver'    => 'mysql'
        ];

        $connectionManager->connect('mysql');
    }
}