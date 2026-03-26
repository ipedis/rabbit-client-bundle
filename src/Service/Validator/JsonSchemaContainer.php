<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Rabbit\Service\Validator;

use stdClass;

class JsonSchemaContainer
{
    /** @var object[] */
    protected array $schemas = [];

    public function addSchema(string $id, object $schema): void
    {
        if (!isset($this->schemas[$id])) {
            $this->schemas[$id] = $schema;
        }
    }

    public function getSchema(string $id): object
    {
        return $this->schemas[$id] ?? new stdClass();
    }

    public function hasSchema(string $id): bool
    {
        return isset($this->schemas[$id]);
    }
}
