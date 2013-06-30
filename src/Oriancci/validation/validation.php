<?php

namespace Oriancci\Validation;

class Validation
{

    public static $validation = [
        // General
        'required'      => [__CLASS__, 'testRequired'],
        'unique'        => [__CLASS__, 'testUnique'],
        // Numeric
        'int'           => [__CLASS__, 'testInt'],
        'float'         => [__CLASS__, 'testFloat'],

        // String
        'alphabetic'    => [__CLASS__, 'testAlphabetic'],
        'alphanumeric'  => [__CLASS__, 'testAlphanumeric'],

        'email'         => [__CLASS__, 'testEmail'],
        'length'        => [__CLASS__, 'testLength'],

        // Pattern
        'regex'         => [__CLASS__, 'testRegex'],
        'pattern'       => [__CLASS__, 'testPattern'],

        // Date
        'age'           => [__CLASS__, 'testAge']
    ];

    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function validate()
    {
        $errors = [];

        $static = get_class($this->model);
        $validations = $static::$validation;

        foreach ($validations as $field => $tests) {
            foreach ($tests as $testName => $testParameters) {
                if (!array_key_exists($testName, static::$validation)) {
                    throw new \Exception('Cannot run test as it does not exist');
                }

                $return = call_user_func_array(
                    static::$validation[$testName],
                    [$field, $testParameters]
                );

                if (is_array($return)) {
                    foreach ($return as $error) {
                        $error->field = $field;
                        $errors[] = $error;
                    }
                }
            }
        }

        return $errors;
    }

    // General validation
    public function testRequired($field, $parameters)
    {
        $value = $this->model->$field;

        if ($value instanceof \Oriancci\DataType\DataTypeInterface) {
            if ($value->hasErrors()) {
                return;
            }

            $value = $value->toDB();
        }

        if (is_null($value) || $value == "") {
            return [static::errorGenerate('REQUIRED')];
        }
    }

    public function testUnique($field, $parameters)
    {
        throw new \Exception('TODO');
    }

    // Number validation
    public function testInt($field, $parameters)
    {
        $value = $this->model->$field;

        $min = array_key_exists('min', $parameters) ? $parameters['min'] : null;
        $max = array_key_exists('max', $parameters) ? $parameters['max'] : null;

        if (is_null($min) && is_null($max)) {
            throw new \Exception('validatesLength: requires: min, max or both');
        }

        if (is_null($value) || $value === '') {
            return;
        }

        $filterValue = filter_var($value, FILTER_VALIDATE_INT);
        if ($filterValue !== 0 && $filterValue === false) {
            return [static::errorGenerate('NUMBER_NOT_NUMERIC')];
        }

        if (!is_null($min) && $value < $min) {
            return [static::errorGenerate('NUMBER_TOO_SMALL')];
        }

        if (!is_null($max) && $value > $max) {
            return [static::errorGenerate('NUMBER_TOO_BIG')];
        }
    }

    public function testFloat($field, $parameters)
    {
        $value = $this->model->$field;

        $min = array_key_exists('min', $parameters) ? $parameters['min'] : null;
        $max = array_key_exists('max', $parameters) ? $parameters['max'] : null;

        if (is_null($min) && is_null($max)) {
            throw new \Exception('validatesLength: requires: min, max or both');
        }

        if (is_null($value) || $value === '') {
            return;
        }

        $filterValue = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($filterValue !== 0 && $filterValue === false) {
            return [static::errorGenerate('NUMBER_NOT_NUMERIC')];
        }

        if (!is_null($min) && $value < $min) {
            return [static::errorgenerate('NUMBER_TOO_SMALL')];
        }

        if (!is_null($max) && $value > $max) {
            return [static::errorGenerate('NUMBER_TOO_BIG')];
        }
    }

    // String validation
    public function testAlphabetic($field, $parameters)
    {
        throw new \Exception('TODO');
    }

    public function testAlphanumeric($field, $parameters)
    {
        throw new \Exception('TODO');
    }

    public function testLength($field, $parameters)
    {
        $value = $this->model->$field;
        
        if ($value === '') {
            return;
        }

        $min = array_key_exists('min', $parameters) ? $parameters['min'] : null;
        $max = array_key_exists('max', $parameters) ? $parameters['max'] : null;

        if (is_null($min) && is_null($max)) {
            throw new \Exception('validatesLength: requires: min, max or both');
        }

        if (!is_null($min) && strlen($value) < $min) {
            return [static::errorGenerate('STRING_TOO_SHORT')];
        }

        if (!is_null($max) && strlen($value) > $max) {
            return [static::errorGenerate('STRING_TOO_LONG')];
        }
    }

    public function testEmail($field, $parameters)
    {
        $value = $this->model->$field;

        if ($value == '') {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return [static::errorGenerate('STRING_NOT_EMAIL')];
        }
    }

    // Pattern validation
    public function testRegex($field, $parameters)
    {
    }

    public function testPattern($field, $parameters)
    {
        $value = (string) $this->model->$field;

        if ($value == '') {
            return;
        }

        $pattern = array_key_exists('pattern', $parameters) ? $parameters['pattern'] : null;

        if (is_null($pattern)) {
            throw new \Exception('You must pass a pattern to validate');
        }

        if (!preg_match('/^' . $pattern . '$/', $value)) {
            return [static::errorGenerate('PATTERN_NOT_VALID')];
        }
    }

    // Age validation
    public function testAge($field, $parameters)
    {
        $value = $this->model->$field;

        // Validate parameters
        $min = array_key_exists('min', $parameters) ? $parameters['min'] : null;
        $max = array_key_exists('max', $parameters) ? $parameters['max'] : null;

        if (is_null($min) && is_null($max)) {
            throw new \Exception('validatesLength: requires: min, max or both');
        }

        // If there is no date, ignore
        if (is_null($value)) {
            return;
        }

        if (!($value instanceof \Oriancci\DataType\iDateTime)) {
            throw new \Exception('Age checks can only happen on date fields.');
        }

        // If the date already has errors, ignore
        if ($value->hasErrors()) {
            return;
        }

        // Get the date value
        $datetime = $value->toDateTime();

        // Like all validation, if is null, return true (will be cause by required)
        if (is_null($datetime)) {
            return null;
        }

        // Compare date against now
        $now = new \DateTime('now');

        // If the date is in the future, it is invalid
        if ($now < $datetime) {
            return [static::errorGenerate('AGE_INVALID')];
        }

        // Get the age (in years)
        $age = $datetime->diff($now)->y;

        // Compare ages
        if (!is_null($min) && $age < $min) {
            return [static::errorGenerate('AGE_TOO_YOUNG')];
        }

        if (!is_null($max) && $age > $max) {
            return [static::errorGenerate('AGE_TOO_OLD')];
        }
    }

    // Error
    public static function errorGenerate($code)
    {
        return new \Oriancci\Error(['code' => $code]);
    }
}
