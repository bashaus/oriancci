---
layout: default
slug  : manipulating
title : Manipulating
---

# Manipulating

## Instantiating

Create a new instance by instantiating the model.

    $user = new User;

You can pass through values as an array to pre-populate the object

    $user = new User([
        'firstName' => 'John',
        'lastName'  => 'Doe',
        'email'     => 'john.doe@example.com',
        'gender'    => 'M',
        'birthday'  => '1980-07-01'
    ]);

## Saving

The method `Model->save()` can be used save the data to the database. The method will return `true` or `false` depending on whether the save was successful.

In this instance, as this is a new object it will be created with an SQL INSERT statement.

    $user = new User;
    $user->firstName = 'John';
    $user->lastName  = 'Doe';
    $user->save();

This example shows how to update an existing model.

    $user = User::get(1);
    $user->email = 'john.doe@example.com';
    $user->save();

## Delete

Models can be deleted from the database using the `Model->delete()` method.

    $user = User::get(1);
    $user->delete();

## Bulk update/delete

You can update and delete a bulk amount of information using the `Model::deleteAll()` and `Model::updateAll()` static methods. Keep in mind, running these methods will not dispatch events. Both methods return the number of affected rows.

    User::deleteAll([WHERE => 'firstName = ?'], ['John']);
    User::updateAll([SET => 'firstName = ?', WHERE => 'firstName = ?'], ['Jonathan', 'John']);
