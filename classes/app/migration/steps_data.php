<?php

namespace assignsubmission_pxaiwriter\app\migration;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use assignsubmission_pxaiwriter\app\submission\interfaces\step_config;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class steps_data implements \JsonSerializable
{
    private base_factory $factory;
    private array $data;

    public function __construct(base_factory $factory, array $data)
    {
        $this->factory = $factory;
        $this->data = $data;
    }

    /**
     * @return step_config[]
     */
    public function get_legacy_steps(): array
    {
        $configs = [];
        foreach ($this->get_legacy_steps_data() as $data)
        {
            $config = $this->factory->submission()->step_config($data);
            $configs[$config->get_step()] = $config;
        }
        return $configs;
    }

    public function set_history_ids(array $ids): void
    {
        $this->data['history_ids'] = $ids;
    }

    public function set_latest_history_ids(array $ids): void
    {
        $this->data['latest_history_ids'] = $ids;
    }

    public function set_legacy_steps_data(array $steps_data): void
    {
        $this->data['old_steps_data'] = $steps_data;
    }

    private function get_legacy_steps_data(): array
    {
        return $this->data['old_steps_data'] ?? [];
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
