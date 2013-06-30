---
layout: default
slug  : conventions
title : Conventions
---

# Conventions

Oriancci takes a convention over configuration approach.

## Naming convention

Tables are identified by the full namespace of the class.

    class Car extends Oriancci\Model
    {
        // Table name: car
    }

When a class uses a namespace, slashes are replaced with underscores.

    namespace Company\Accounting;
    class Car extends Oriancci\Model
    {
        // Table name: company_accounting_car
    }

## Overriding conventions

Your can provide alternative tables for models by overriding the `Model::tableName()` method:

    namespace Company\Accounting;
    class Car extends Oriancci\Model
    {
        public static function tableName()
        {
            return 'car';
        }
    }

By default, database names are not referenced in each individual query. If you are using multiple database but would only like to use one connection, you can override a model's database name using the `Model::databaseName()` static override.

    namespace Company\Accounting;
    class Car extends Oriancci\Model
    {
        public static function databaseName()
        {
            return 'accounting';
        }

        public static function tableName()
        {
            return 'car';
        }
    }