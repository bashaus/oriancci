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

Your can provide simple names for classes using override methods:

    namespace CarCo\Accounting;
    class Car extends Oriancci\Model
    {
        public static function tableName()
        {
            return 'car';
        }
    }