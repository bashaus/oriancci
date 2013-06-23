<?php

namespace Oriancci;

use PHPUnit_Extensions_Database_DataSet_YamlDataSet;
use PHPUnit_Extensions_Database_TestCase;
use PDO;

abstract class OriancciTest extends PHPUnit_Extensions_Database_TestCase
{

    static private $pdo = null;
    static private $conn = null;

    final public function getConnection()
    {
        if (is_null(self::$conn))
        {
            if (is_null(self::$pdo))
            {
                self::$pdo = new PDO(
                    sprintf(
                        '%s:host=%s;dbname=%s', 
                        ORIANCCI_PHPUNIT_HOSTTYPE, 
                        ORIANCCI_PHPUNIT_HOSTNAME, 
                        ORIANCCI_PHPUNIT_DATABASE
                    ),
                    ORIANCCI_PHPUNIT_USERNAME,
                    ORIANCCI_PHPUNIT_PASSWORD
                );
            }

            self::$conn = $this->createDefaultDBConnection(self::$pdo, ORIANCCI_PHPUNIT_DATABASE);
        }

        return self::$conn;
    }

    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            ORIANCCI_PHPUNIT_DIR_FIXTURES . '/fixtures.yml'
        );
    }
}
