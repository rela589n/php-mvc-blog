<?php


namespace core;

use core\exceptions\ValidatorException;

class Validator
{
    protected $schema = null;
    protected $length = []; // [min, max, current] for string validation

    public $success;
    public $errors;
    public $clear; // successful validated fields


    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';
    public const TYPE_STRING = 'string';

    /**
     * @param array $schema
     */
    public function setSchema(array $schema): void
    {
        $this->schema = $schema;
    }

    public function validateByFields(array $fields)
    {
        $this->errors = [];
        $this->clear = [];
        $this->success = false;

        if ($this->schema == null) {
            throw new ValidatorException('Schema is not set in validator!');
        }

        foreach ($fields as $name => $val) {
            $rules = $this->schema[$name];

            if (
                $this->validateRequired($name, $fields, $rules) &&

                $this->validateType($name, $fields[$name], $rules) &&

                $this->validatePregMatch($name, $fields[$name], $rules) &&

                $this->validateEqualsTo($name, $fields, $rules)
            ) {
                $this->clear [] = $name;
            }
        }

        if (empty($this->errors)) {
            $this->success = true;
        }
    }

    public function validateBySchema(array $fields)
    {
        $this->errors = [];
        $this->clear = [];
        $this->success = false;

        if ($this->schema === null) {
            throw new ValidatorException('Schema is not set in validator!');
        }

        foreach ($this->schema as $name => $rules) {

            if (
                $this->validateRequired($name, $fields, $rules) &&

                $this->validateType($name, $fields[$name], $rules) &&

                $this->validatePregMatch($name, $fields[$name], $rules) &&

                $this->validateEqualsTo($name, $fields, $rules)
            ) {
                $this->clear [] = $name;
            }
        }

        if (empty($this->errors)) {
            $this->success = true;
        }
    }

    protected function checkLength(string $field, $length)
    {
        $minLen = 0;

        if (is_array($length)) {
            switch (count($length)) {
                case 1:
                    $maxLen = $length[0];
                    break;
                case 2:
                    $minLen = $length[0];
                    $maxLen = $length[1];
                    break;
                default:
                    throw new ValidatorException('Too many params passed as length. No more than 2 is expected.');
            }
        } else if (ctype_digit($length)) {
            $maxLen = intval($length);
        } else if (is_int($length)) {
            $maxLen = $length;
        } else {
            throw new ValidatorException('Invalid param length. It must be array with 2 values [min, max].');
        }

        $currentLen = mb_strlen($field);
        $this->length = [$minLen, $maxLen, $currentLen];

        return ($minLen <= $currentLen) && ($currentLen <= $maxLen);
    }

    protected function validateType(string $fieldName, $field, array &$rules)
    {
        if (!isset($rules['type'])) {
            return true;
        }

        switch ($rules['type']) {
            case self::TYPE_INT:
                if (!(is_int($field) || ctype_digit($field))) {
                    $this->errors[$fieldName] = ($rules['type_message']) ??
                        sprintf('Field %s must be int!', $fieldName);

                    return false;
                }
                return true;

            case self::TYPE_FLOAT:
                if (!is_numeric($field)) {
                    $this->errors[$fieldName] = ($rules['type_message']) ??
                        sprintf('Field %s must be float!', $fieldName);

                    return false;
                }

                return true;
            case self::TYPE_STRING:

                if (!$this->checkLength($field, $rules['length'])) {
                    $this->errors[$fieldName] = ($rules['length_message']) ??
                        sprintf(
                            'Field %s must be between %d and %d characters! Now is: %d.',
                            $fieldName,
                            $this->length[0],
                            $this->length[1],
                            $this->length[2],
                        );

                    return false;
                }

                return true;
            default:
                throw new ValidatorException('Unknown property type.');
        }
    }

    protected function validatePregMatch(string $fieldName, string $field, array &$rules)
    {
        if (!isset($rules['preg_match'])) {
            return true;
        }

        $r = preg_match($rules['preg_match'], $field);
        if (!$r) {
            $this->errors[$fieldName] = ($rules['preg_match_message']) ?? "Invalid symbols passed to $fieldName!";
        }

        return $r;
    }

    protected function validateRequired(string $fieldName, array &$fields, array &$rules)
    {

        if (isset($rules['required']) && (!isset($fields[$fieldName]) || empty($fields[$fieldName]))) {
            $this->errors[$fieldName] = ($rules['required_message']) ??
                sprintf('Field %s is required!', $fieldName);
            return false;
        }
        return true;
    }

    protected function validateEqualsTo(string $fieldName, array &$fields, array &$rules)
    {
        if (!isset($rules['equals_to'])) {
            return true;
        }

        $field1 = ($fields[$fieldName]) ?? null;
        $field2 = ($fields[$rules['equals_to']]) ?? null;

        $r = $field1 && $field2 && $field1 === $field2;
        if (!$r) {
            $this->errors[$fieldName] = ($rules['equals_to_message']) ??
                sprintf('Field %s must be equals to %s', $fieldName, $rules['equals_to']);
        }

        return $r;
    }

    public function appendErrors(array $errors)
    {
        $this->errors = array_merge($this->errors, $errors);
    }
}