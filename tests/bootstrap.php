<?php

ini_set('date.timezone', 'UTC');

$environmentalValues = [
	'ORIANCCI_PHPUNIT_HOSTTYPE',
	'ORIANCCI_PHPUNIT_HOSTNAME',
	'ORIANCCI_PHPUNIT_USERNAME',
	'ORIANCCI_PHPUNIT_PASSWORD',
	'ORIANCCI_PHPUNIT_DATABASE'
];

foreach ($environmentalValues as $environmentalValue) {
	if (!array_key_exists($environmentalValue, $_ENV)) {
		throw new \Exception('Environmental value does not exists: ' . $environmentalValue);
	}

	define($environmentalValue, $_ENV[$environmentalValue]);
}

require __DIR__ . '/Oriancci/units/OriancciTest.php';

define('ORIANCCI_PHPUNIT_DIR_FIXTURES', __DIR__ . '/Oriancci/fixtures');

if (!@include __DIR__ . '/../vendor/autoload.php') {
    die(<<<'EOT'
You must set up the project dependencies, run the following commands:
wget http://getcomposer.org/composer.phar
php composer.phar install

EOT
    );
}

require __DIR__ . '/Oriancci/models/department.php';
require __DIR__ . '/Oriancci/models/user.php';

$log = null;
if (class_exists('Monolog\Logger')) {
    $log = new Monolog\Logger('Oriancci');
    $log->pushHandler(new Monolog\Handler\StreamHandler(realpath(__DIR__ . '/oriancci.log')));
}

$connection_manager = \Oriancci\ConnectionManager::getInstance();
$connection_manager->build = [
	'driver'	=> $_ENV['ORIANCCI_PHPUNIT_HOSTTYPE'],
	'hostname'	=> $_ENV['ORIANCCI_PHPUNIT_HOSTNAME'],
	'username'	=> $_ENV['ORIANCCI_PHPUNIT_USERNAME'],
	'password'	=> $_ENV['ORIANCCI_PHPUNIT_PASSWORD'],
	'database'	=> $_ENV['ORIANCCI_PHPUNIT_DATABASE'],
	'logger'	=> $log
];

$connection_manager->setDefault('build');
