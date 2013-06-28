<?php

namespace Oriancci;

use PDO;
use Psr\Log\LogLevel;
use Psr\Log\LoggerAwareInterface;

abstract class Connection extends PDO implements LoggerAwareInterface
{

    protected $logger = null;

    public static $options = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false
    ];

    const DEFAULT_CHARSET = 'UTF8';

    public function __construct()
    {
        // Build connection parameters
        $parameters = func_get_args();
        $connection = array_shift($parameters);
        array_unshift($parameters, $connection['string']);

        // Connect via PDO
        call_user_func_array(['parent', '__construct'], $parameters);

        // Set charset
        $charset = array_key_exists('charset', $connection) ? $connection['charset'] : static::DEFAULT_CHARSET;
        $this->setEncoding($charset);
    }

    /* \Psr\Log\LoggerAwareInterface */
    public function setLogger(\Psr\Log\LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function log($level, $message, array $context = array())
    {
        if (is_null($this->logger)) {
            return;
        }

        $this->logger->log($level, $message, $context);
    }

    public function getOptions()
    {
        return static::$options + self::$options;
    }

    /* Transactions */
    public function transact($callback)
    {
        $result = null;

        if (!is_callable($callback)) {
            throw new \Exception('Callback is not callable');
        }

        $this->log(LogLevel::DEBUG, 'START TRANSACTION');

        if (!$this->beginTransaction()) {
            throw new \Exception('Cannot run transaction for this DBMS');
        }

        try {
            if (is_array($callback)) {
                $result = call_user_func($callback);
            } else {
                $result = $callback();
            }
            
            if ($result === false) {
                throw new \Exception('Callback returned false');
            }

            $this->commit();
            $this->log(LogLevel::DEBUG, 'COMMIT');
            return true;
        } catch (\Exception $e) {
            $this->rollBack();
            $this->log(LogLevel::DEBUG, 'ROLLBACK');
            $this->log(LogLevel::DEBUG, $e->getMessage());
            return false;
        }
    }

    /* Override for debugging */

    public function query($sql)
    {
        $this->log(LogLevel::DEBUG, $sql);
        return call_user_func_array(['parent', 'query'], func_get_args());
    }

    public function prepare($sql, $options = null)
    {
        $this->log(LogLevel::DEBUG, $sql);
        return call_user_func_array(['parent', 'prepare'], func_get_args());
    }

    public function exec($sql)
    {
        $this->log(LogLevel::DEBUG, $sql);
        return call_user_func_array(['parent', 'exec'], func_get_args());
    }

    /* Abstract */

    abstract public function setEncoding($charset);
    abstract public function sqlDescribeTable($tableName);
    abstract public function getColumnClass();
}
