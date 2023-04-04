<?php

namespace assignsubmission_pxaiwriter\app;


use JsonSerializable;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

abstract class entity implements interfaces\entity, JsonSerializable
{
    protected array $record;

    public function __construct(array $record = [])
    {
        $this->record = $record;
    }

    public function get_id(): int
    {
        return $this->record['id'] ?? 0;
    }

    public function set_id(int $id): void
    {
        $this->record['id'] = $id;
    }

    public function jsonSerialize()
    {
        return $this->to_array();
    }
}
