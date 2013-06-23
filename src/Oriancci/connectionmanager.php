<?php

namespace Oriancci;

class ConnectionManager
{
    use Traits\Singleton;

    /* Data structure */
    public static $drivers = [
        'mysql'     => '\Oriancci\Drivers\MySQL\Connection',
        'sqlite'    => '\Oriancci\Drivers\SQLite\Connection'
    ];

    /* Class */
    private $default = null;
    private $profiles = [];
    private $connections = [];

    private function __construct()
    {
    }

    public function setDefault($profile)
    {
        $this->default = $profile;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function get($profileName = null)
    {
        if (is_null($this->default)) {
            throw new \Exception('You must set a default connection in your ConnectionManager configuration');
        }

        if (is_null($profileName)) {
            $profileName = $this->default;
        }

        if (!array_key_exists($profileName, $this->profiles)) {
            throw new \Exception('Profile does not exist: ' . $profileName);
        }

        $profile = $this->profiles[$profileName];

        if (!array_key_exists($profileName, $this->connections)) {
            if (!array_key_exists('driver', $profile)) {
                throw new \Exception('A driver must be specified for a connection profile');
            }

            if (!array_key_exists($profile['driver'], static::$drivers)) {
                throw new \Exception('Driver ' . $profile['driver'] . ' is not supported');
            }

            $hostClass = static::$drivers[$profile['driver']];

            $this->connections[$profileName] = new $hostClass($profile);
        }

        return $this->connections[$profileName];
    }

    /* profiles */
    public function __isset($offset)
    {
        return array_key_exists($offset, $this->profiles);
    }

    public function __unset($offset)
    {
        unset($this->profiles[$offset]);
    }

    public function __get($offset)
    {
        return $this->profiles[$offset];
    }

    public function __set($offset, $value)
    {
        $this->profiles[$offset] = $value;
    }
}
