<?php


namespace model;


use core\DBDriverInterface;
use core\Validator;

class Texts extends Base
{
    const TEXT_VALUE_PREVIEW_MAX_LENGTH = 60;

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

    public function __construct(DBDriverInterface $db, Validator $validator)
    {
        parent::__construct($db, $validator, "dashboard_texts", "alias");
        $this->validator->setSchema(self::SCHEMA);
    }

    public function getOne(string $alias)
    {
        return $this->getById($alias)['value'];
    }

    public function update(string $name, string $value, string $alias)
    {
        return $this->db->update(
            $this->tableName,
            [
                'name' => $name,
                'value' => $value,
                'alias' => $alias
            ],
            "{$this->idAlias} = :alias",
            [
                'alias' => $alias
            ]
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
            return self::getPreview($text);
        }, $texts);
    }
}