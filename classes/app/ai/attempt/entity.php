<?php

namespace assignsubmission_pxaiwriter\app\ai\attempt;


use assignsubmission_pxaiwriter\app\entity as base_entity;
use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory_interface;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class entity extends base_entity implements interfaces\entity
{
    private base_factory_interface $factory;

    public function __construct(array $record = [], ?base_factory_interface $factory = null)
    {
        $this->factory = $factory ?? base_factory::make();
        parent::__construct($record);
    }

    public function get_userid(): int
    {
        return $this->record['userid'] ?? 0;
    }

    public function get_assignment(): int
    {
        return $this->record['assignment'] ?? 0;
    }

    public function get_step(): int
    {
        return $this->record['step'] ?? 0;
    }

    public function get_status(): string
    {
        return $this->record['status'] ?? 0;
    }

    public function get_hashcode(): string
    {
        return $this->record['hashcode'] ?? 0;
    }

    public function get_data(): ?string
    {
        return $this->record['data'] ?? 0;
    }

    public function get_timecreated(): int
    {
        return $this->record['timecreated'] ?? 0;
    }

    public function set_userid(int $id): void
    {
        $this->record['userid'] = $id;
    }

    public function set_assignment(int $id): void
    {
        $this->record['assignment'] = $id;
    }

    public function set_step(int $step): void
    {
        $this->record['step'] = $step;
    }

    public function set_status(string $status): void
    {
        $this->record['status'] = $status;
    }

    public function set_status_ok(): void
    {
        $this->set_status(interfaces\entity::STATUS_OK);
    }

    public function set_status_failed(): void
    {
        $this->set_status(interfaces\entity::STATUS_FAILED);
    }

    public function set_status_deleted(): void
    {
        $this->set_status(interfaces\entity::STATUS_DELETED);
    }

    public function set_hashcode(string $hash): void
    {
        $this->record['hashcode'] = $hash;
    }

    public function set_data(?string $data): void
    {
        $this->record['data'] = $data;
        if ($data !== null)
        {
            $this->set_hashcode(
                $this->factory->helper()->hash()->sha256()->digest($data)
            );
        }
    }

    public function set_timecreated(int $timestamp): void
    {
        $this->record['timecreated'] = $timestamp;
    }


    public function to_array(): array
    {
        return $this->record;
    }

    public function to_object(): object
    {
        return (object)$this->to_array();
    }
}
