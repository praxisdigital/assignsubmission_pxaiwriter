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

    public function is_ai_expand(): bool
    {
        return $this->get_type() === self::TYPE_AI_EXPAND;
    }

    public function is_ai_generate(): bool
    {
        return $this->get_type() === self::TYPE_AI_GENERATE;
    }

    public function is_user_edit(): bool
    {
        return $this->get_type() === self::TYPE_USER_EDIT;
    }

    public function get_userid(): int
    {
        return $this->record['userid'] ?? 0;
    }

    public function get_assignment(): int
    {
        return $this->record['assignment'] ?? 0;
    }

    public function get_submission(): int
    {
        return $this->record['submission'] ?? 0;
    }


    public function get_step(): int
    {
        return $this->record['step'] ?? 0;
    }

    public function get_status(): string
    {
        return $this->record['status'] ?? interfaces\entity::STATUS_FAILED;
    }

    public function get_type(): string
    {
        return $this->record['type'] ?? interfaces\entity::TYPE_USER_EDIT;
    }

    public function get_input_text(): string
    {
        return $this->record['input_text'] ?? '';
    }

    public function get_ai_text(): string
    {
        return $this->record['ai_text'] ?? '';
    }

    public function get_response(): ?string
    {
        return $this->record['response'] ?? null;
    }

    public function get_data(): string
    {
        return $this->record['data'] ?? '';
    }

    public function get_hashcode(): string
    {
        return $this->record['hashcode'] ?? $this->get_checksum_from_data($this->get_data());
    }

    public function get_timecreated(): int
    {
        return $this->record['timecreated'] ?? 0;
    }

    public function get_timemodified(): int
    {
        return $this->record['timemodified'] ?? 0;
    }

    public function set_userid(int $user_id): void
    {
        $this->record['userid'] = $user_id;
    }

    public function set_assignment(int $assignment_id): void
    {
        $this->record['assignment'] = $assignment_id;
    }

    public function set_submission(int $submission_id): void
    {
        $this->record['submission'] = $submission_id;
    }

    public function set_step(int $step): void
    {
        $this->record['step'] = $step;
    }

    public function set_status(string $status): void
    {
        $this->record['status'] = $status;
    }

    public function set_status_draft(): void
    {
        $this->set_status(interfaces\entity::STATUS_DRAFTED);
    }

    public function set_status_submitted(): void
    {
        $this->set_status(interfaces\entity::STATUS_SUBMITTED);
    }

    public function set_status_failed(): void
    {
        $this->set_status(interfaces\entity::STATUS_FAILED);
    }

    public function set_status_deleted(): void
    {
        $this->set_status(interfaces\entity::STATUS_DELETED);
    }

    public function set_type(string $type): void
    {
        $this->record['type'] = $type;
    }

    public function set_type_user_edit(): void
    {
        $this->set_type(interfaces\entity::TYPE_USER_EDIT);
    }

    public function set_type_ai_generate(): void
    {
        $this->set_type(interfaces\entity::TYPE_AI_GENERATE);
    }

    public function set_type_ai_expand(): void
    {
        $this->set_type(interfaces\entity::TYPE_AI_EXPAND);
    }

    public function set_input_text(string $text): void
    {
        $this->record['input_text'] = $text;
    }

    public function set_ai_text(?string $text): void
    {
        $this->record['ai_text'] = $text;
    }

    public function set_response(?string $response_data): void
    {
        $this->record['response'] = $response_data;
    }

    public function set_data(?string $data): void
    {
        $this->record['data'] = $data;
        $this->set_hashcode($this->get_checksum_from_data($data));
    }

    public function set_hashcode(?string $hashcode): void
    {
        $this->record['hashcode'] = $hashcode;
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

    private function get_checksum_from_data(?string $data): string
    {
        if (empty($data))
        {
            return self::EMPTY_CHECKSUM;
        }
        return $this->factory
            ->helper()
            ->hash()
            ->sha256()
            ->digest($data);
    }
}
