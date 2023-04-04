<?php

namespace assignsubmission_pxaiwriter\app\ai\history;


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

    public function get_id(): int
    {
        return $this->record['id'] ?? 0;
    }

    public function get_assignment(): int
    {
        return $this->record['assignment'] ?? 0;
    }

    public function get_userid(): int
    {
        return $this->record['userid'] ?? 0;
    }

    public function get_step(): int
    {
        return $this->record['step'] ?? 0;
    }

    public function get_status(): string
    {
        return $this->record['status'] ?? interfaces\entity::STATUS_FAILED;
    }

    public function get_hashcode(): string
    {
        return $this->record['hashcode'] ?? $this->factory
            ->helper()
            ->hash()
            ->sha256()
            ->digest($this->get_data());
    }

    public function get_ai_text(): string
    {
        return $this->record['ai_text'] ?? '';
    }

    public function get_data(): string
    {
        return $this->record['data'] ?? '';
    }

    public function get_timecreated(): int
    {
        return $this->record['timecreated'] ?? 0;
    }

    public function get_timemodified(): int
    {
        return $this->record['timemodified'] ?? 0;
    }

    public function set_assignment(int $assignment_id): void
    {
        $this->record['assignment'] = $assignment_id;
    }

    public function set_userid(int $user_id): void
    {
        $this->record['userid'] = $user_id;
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

    public function set_hashcode(string $hashcode): void
    {
        $this->record['hashcode'] = $hashcode;
    }

    public function set_ai_text(?string $text): void
    {
        $this->record['ai_text'] = $text;
    }

    public function set_data(string $data): void
    {
        $this->record['data'] = $data;
        $this->set_hashcode(
            $this->factory->helper()->hash()->sha256()->digest($data)
        );
    }

    public function set_timecreated(int $timestamp): void
    {
        $this->record['timecreated'] = $timestamp;
    }

    public function set_timemodified(int $timestamp): void
    {
        $this->record['timemodified'] = $timestamp;
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
