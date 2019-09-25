<?php


namespace model;


class Validator
{
    protected $rules = null;
    public $success = false;
    public $errors = [];
    public $clear = []; // successful validated fields

    /**
     * @param array $rules
     */
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }

    public function execute() {
        if ($this->rules === null) {
            // throw exception
            return;
        }
        // validate
    }
}