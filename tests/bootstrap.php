<?php

ini_set('date.timezone', 'UTC');

//require __DIR__ . '/../src/Oriancci/constants.php';
require __DIR__ . '/Oriancci/units/OriancciTest.php';

define('ORIANCCI_PHPUNIT_DIR_FIXTURES', __DIR__ . '/Oriancci/fixtures');

$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Oriancci\\', __DIR__);

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('Oriancci');
$log->pushHandler(new StreamHandler('tests/oriancci.log'));

$connection_manager = \Oriancci\ConnectionManager::getInstance();
$connection_manager->build = [
	'driver'	=> ORIANCCI_PHPUNIT_HOSTTYPE,
	'hostname'	=> ORIANCCI_PHPUNIT_HOSTNAME,
	'username'	=> ORIANCCI_PHPUNIT_USERNAME,
	'password'	=> ORIANCCI_PHPUNIT_PASSWORD,
	'database'	=> ORIANCCI_PHPUNIT_DATABASE,
	'logger'	=> $log
];

$connection_manager->setDefault('build');
