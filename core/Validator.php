<?php


namespace core;

use core\exceptions\ValidatorException;
use core\exceptions\ValidatorLessThanRequiredException;
use core\exceptions\ValidatorMoreThanRequiredException;


class Validator
{
    protected $schema = null;
    protected $length = []; // [min, max, current] for string validation

    public $success = false;
    public $errors = [];
    public $clear = []; // successful validated fields

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

    /**
     * @param array $fields
     * @return $this
     * @throws ValidatorException
     */
    public function validateByFields(array $fields)
    {
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

        $this->success = empty($this->errors);
        return $this;
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
        return $this;
    }

    /**
     * If message for error handling isset,
     * %1$d = required number of characters
     * %2$d = current number of characters,
     * @param string $value
     * @param array $rules
     * @throws ValidatorLessThanRequiredException
     * @throws ValidatorMoreThanRequiredException
     */
    protected function validateLength(string $value, array &$rules): void
    {
        $minLen = (int)($rules['min_length'] ?? 0);
        $strLen = mb_strlen($value);

        if ($strLen < $minLen) {
            throw new ValidatorLessThanRequiredException($strLen, $minLen);
        }

        if (!isset($rules['max_length'])) {
            return;
        }

        $maxLen = $rules['max_length'];

        if ($strLen > $maxLen) {
            throw new ValidatorMoreThanRequiredException($strLen, $maxLen);
        }
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
//c3d6e8
                try {
                    $this->validateLength($field, $rules);
                    return true;
                } catch (ValidatorLessThanRequiredException $e) {

                    $userErrMsg = $rules['min_length_message'] ?? null;

                    $this->errors[$fieldName] =
                        ($userErrMsg === null) ?
                            sprintf(
                                'Field %s must be at least %d characters. Now is: %d.',
                                $fieldName,
                                $e->getMinLen(),
                                $e->getCurrentLen()
                            ) :
                            sprintf($userErrMsg, $e->getMinLen(), $e->getCurrentLen());

                } catch (ValidatorMoreThanRequiredException $e) {
                    $userErrMsg = $rules['max_length_message'] ?? null;

                    $this->errors[$fieldName] =
                        ($userErrMsg === null) ?
                            sprintf(
                                'Field %s must be below %d characters. Now is: %d.',
                                $fieldName,
                                $e->getMaxLength(),
                                $e->getCurrentLen()
                            ) :
                            sprintf($userErrMsg, $e->getMaxLength(), $e->getCurrentLen());
                }

                return false;
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