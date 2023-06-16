<?php

namespace assignsubmission_pxaiwriter\app\submission;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity as history_entity;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class step_config implements interfaces\step_config, \JsonSerializable
{
    private object $config;

    public function __construct(object $config)
    {
        $this->config = $config;
    }

    public function get_step(): int
    {
        return $this->config->step ?? 0;
    }

    public function get_description(): string
    {
        return $this->config->description ?? '';
    }

    public function get_value(): string
    {
        return $this->config->value ?? '';
    }

    public function set_history_data(?history_entity $entity): void
    {
        if ($entity === null)
        {
            $this->config->value = '';
            return;
        }
        $this->config->value = $entity->get_data();
    }

    public function jsonSerialize()
    {
        return $this->to_object();
    }

    public function to_array(): array
    {
        return (array)$this->to_object();
    }

    public function to_object(): object
    {
        return $this->config;
    }
}
