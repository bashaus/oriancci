---
layout: default
slug  : connection
title : Connection
---

# Connection

Oriancci supports two database drivers:

* sqlite
* mysql

Create properties for each connection or deployment stage that you need.

    $connection_manager = \Oriancci\ConnectionManager::getInstance();

    $connection_manager->development = [
        'driver'    => 'mysql',
        'hostname'  => 'localhost',
        'username'  => 'username',
        'password'  => 'secretpassword',
        'database'  => 'oriancci_development'
    ];

    $connection_manager->production = [
        'driver'    => 'mysql',
        'hostname'  => 'localhost',
        'username'  => 'username',
        'password'  => 'verysecretpassword',
        'database'  => 'oriancci_development'
    ];

You will also need to set your default environment.

    $connection_manager->setDefault('development');

This can be useful if your stage is set by environmental value:

    $connection_manager->setDefault(SITE_ENV);
