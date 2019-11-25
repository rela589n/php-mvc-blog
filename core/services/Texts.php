<?php


namespace core\services;

use core\exceptions\IncorrectDataException;
use core\Validator;
use model\Texts as TextsModel;

class Texts extends Base
{
    const TEXT_VALUE_PREVIEW_MAX_LENGTH = 60;
    const INVALID_PARAMS_GIVEN = 'Передано невалидные данные';

    private const SCHEMA = [
        'alias' => [
            'type' => 'string',
            'max_length' => 64
        ],
        'name' => [
            'type' => 'string',
            'min_length' => 4,
            'max_length' => 128,
        ],
        'value' => [
            'type' => 'string',
            'max_length' => 256,
        ]
    ];

    private $textsModel;

    public function __construct(TextsModel $textsModel, Validator $validator)
    {
        $this->textsModel = $textsModel;
        parent::__construct($validator);
        $this->validator->setSchema(self::SCHEMA);
    }

    public function getText(string $alias)
    {
        return $this->textsModel->getById($alias)['value'];
    }

    public function alterText(array $params)
    {
        $this->validator->validateByFields([
            'name' => $params['name'],
            'value' => $params['value'],
            $this->textsModel->getIdAlias() => $params['alias']
        ]);

        if (!$this->validator->success()) {
            throw new IncorrectDataException($this->validator->errors, self::INVALID_PARAMS_GIVEN);
        }

        $clear = $this->validator->clear;

        return $this->textsModel->update(
            $clear['name'],
            $clear['value'],
            $clear[$this->textsModel->getIdAlias()]
        );
    }

    public function getPreview(array $text)
    {
        $text['value'] = substr($text['value'], 0, self::TEXT_VALUE_PREVIEW_MAX_LENGTH) . '...';
        return $text;
    }

    public function getPreviews(array $texts)
    {
        return array_map(function ($text) {
            return $this->getPreview($text);
        }, $texts);
    }


}