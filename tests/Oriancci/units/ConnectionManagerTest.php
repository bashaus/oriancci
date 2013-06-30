<?php

namespace Oriancci;

use PHPUnit_Framework_TestCase;

class ConnectionManagerTest extends PHPUnit_Framework_TestCase
{

    public function testGetInstance()
    {
        $connectionManager = ConnectionManager::getInstance();
        $this->assertInstanceOf('Oriancci\ConnectionManager', $connectionManager);
    }

    public function testMagicMethods()
    {
        $connectionManager = ConnectionManager::getInstance();
        $connectionManager->sqlite = [
            'driver'    => 'sqlite',
            'filename'  => ':memory:'
        ];

        $this->assertTrue(isset($connectionManager->sqlite));
        $this->assertTrue(is_array($connectionManager->sqlite));

        unset($connectionManager->sqlite);
        $this->assertFalse(isset($connectionManager->sqlite));

        $this->assertInstanceOf('Oriancci\ConnectionManager', $connectionManager);
    }

    /**
     * @expectedException Exception
     */
    public function testNoDriver()
    {
        $connectionManager = ConnectionManager::getInstance();
        $connectionManager->noDriver = [
            'filename'  => ':memory:'
        ];

        $connectionManager->connect('noDriver');
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidDriver()
    {
        $connectionManager = ConnectionManager::getInstance();
        $connectionManager->invalidDriver = [
            'driver'    => 'lqsym'
        ];

        $connectionManager->connect('invalidDriver');
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidProfile()
    {
        $connectionManager = ConnectionManager::getInstance();
        $connectionManager->profileA = [
            'driver'    => 'sqlite',
            'filename'  => ':memory:'
        ];

        $connectionManager->connect('profileB');
    }

    public function testDefault()
    {
        $connectionManager = ConnectionManager::getInstance();
        $connection = [
            'driver'    => 'sqlite',
            'filename'  => ':memory:'
        ];

        $revert = $connectionManager->getDefault();

        $connectionManager->sqlite = $connection;
        $connectionManager->setDefault('sqlite');
        $this->assertEquals('sqlite', $connectionManager->getDefault());

        try {
            $connectionManager->setDefault(null);
            $connectionManager->connect();
            $this->assertFalse(true);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        $connectionManager->setDefault($revert);
    }
}