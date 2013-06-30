---
layout: default
slug  : validation
title : Validation
---

# Validation

## Built-in validation

Oriancci comes with some pre-built variables for determining the validity of data.

    class User extends \Oriancci\Model
    {

        static $validation = [
            'firstName' => ['required' => true],
            'lastName'  => ['required' => true],
            'email'     => ['email'    => true],
            'birthday'  => ['age'      => ['min' => 18]]
        ];
    }

### required

Ensures that the value is not `null` and not `empty`.

### unique

Ensures that the value does not currently exist in the database under the same field.

### int

Ensures that the value is a valid integer.

### float

Ensures that the value is a valid floating-point number.

### alphabetic

Ensures that the value only contains alphabetic characters.

### alphanumeric

Ensures that the value only contains alpha-numeric characters.

### email

Ensures that the value is a valid email address.

### length

Ensures that the length of the value is between two values.

### regex

Ensures that the value matches a regular expression.

### pattern

Ensures that the value matches a regular expression (surrounded by ^ and $).

### age

Ensures that the value in the date field is of a particular age.

## Custom validation

You can declare your own validation methods by overring the `Model->validate()` method. This can be used in conjunction with the declarations made in `static::$validation`.

    use Oriancci\Error;
    
    class User extends \Oriancci\Model
    {

        static $validation = [
            'firstName' => ['required' => true],
            'lastName'  => ['required' => true],
            'email'     => ['email'    => true],
            'birthday'  => ['age'      => ['min' => 18]]
        ];

        public function validate()
        {
            if (empty($this->firstName)) {
                $this->errors->append(new Error(['code' => 'REQUIRED']));
            }
        }
    }
