<?php

namespace Ipedis\Bundle\Rabbit\Service\Validator;

class JsonSchemaContainer
{

    protected array $schemas;

    public function __construct()
    {
        $this->schemas = [];
    }

    public function addSchema(string $id, string $schema): void
    {
        if (!isset($this->schemas[$id])) {
            $this->schemas[$id] = $schema;
        }
    }

    public function getSchema(string $id): string
    {
        return $this->schemas[$id] ?? '';
    }

    public function hasSchema(string $id): bool
    {
        return isset($this->schemas[$id]) && !empty($this->schemas[$id]);
    }

}
