<?php


namespace model;


class Validator
{
    protected $schema = null;
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

    public function execute(array $fields)
    {
        if ($this->schema === null) {
            // throw exception
            return;
        }

        foreach ($this->schema as $name => $rules) {
            if (!isset($fields[$name])) {
                if (isset($rules['required'])) {
                    $this->errors[$name] = sprintf('Field %s is required!', $name);
                } else {
                    $this->clear [] = $name;
                }
                continue;
            }
            switch ($rules['type']) {
                case self::TYPE_INT:
                    if (!ctype_digit($fields[$name])) {
                        $this->errors[$name] = sprintf('Field %s must be int!', $name);
                    }

                    break;
                case self::TYPE_FLOAT:
                    if (!is_numeric($fields[$name])) {
                        $this->errors[$name] = sprintf('Field %s must be float!', $name);
                    }

                    break;
                case self::TYPE_STRING:
                    $minLen = 0;

                    switch (count($rules['length'])) {
                        case 1:
                            $maxLen = $rules['length'][0];
                            break;
                        case 2:
                            $minLen = $rules['length'][0];
                            $maxLen = $rules['length'][1];
                            break;
                        default:
                            // throw exception
                            return;
                    }

                    $len = mb_strlen($fields[$name]);
                    if ($len < $minLen) {
                        $this->errors[$name] = sprintf('Field %s must be at least %d characters!', $name, $minLen);
                    } else if ($len > $maxLen) {
                        $this->errors[$name] = sprintf('Field %s can not be more than %d characters!', $name, $maxLen);
                    }

                    break;
                default:
                    // throw exception
                    return;
            }
            if (!isset($this->errors[$name])) {
                $this->clear [] = $name;
            }
        }

        if (empty($this->errors)) {
            $this->success = true;
        }
    }
}