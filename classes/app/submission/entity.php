<?php

namespace assignsubmission_pxaiwriter\app\submission;


use assignsubmission_pxaiwriter\app\entity as base_entity;
use assignsubmission_pxaiwriter\app\helper\encoding\interfaces\json;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory_interface;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class entity extends base_entity implements interfaces\entity
{
    private base_factory_interface $factory;
    private ?array $history_ids = null;
    private ?array $latest_history_ids = null;

    public function __construct(array $record = [], ?base_factory_interface $factory = null)
    {
        parent::__construct($record);
        $this->factory = $factory ?? \assignsubmission_pxaiwriter\app\factory::make();
    }

    public function get_assignment(): int
    {
        return $this->record['assignment'] ?? 0;
    }

    public function get_submission(): int
    {
        return $this->record['submission'] ?? 0;
    }

    public function get_step_data(): ?string
    {
        return $this->record['steps_data'] ?? null;
    }

    public function get_history_ids(): array
    {
        return $this->history_ids ??= $this->get_ids_from_step_data('history_ids');
    }

    public function get_latest_step_history_ids(): array
    {
        return $this->latest_history_ids ??= $this->get_ids_from_step_data('latest_history_ids');
    }

    public function set_assignment(int $id): void
    {
        $this->record['assignment'] = $id;
    }

    public function set_submission(int $id): void
    {
        $this->record['submission'] = $id;
    }

    public function set_step_data(?string $data): void
    {
        $this->record['steps_data'] = $data;
    }

    public function set_history_ids(array $ids): void
    {
        $ids = array_unique($ids);
        $this->history_ids = $ids;
        $this->set_step_data_options('history_ids', $ids);
    }

    public function set_latest_step_history_ids(array $ids): void
    {
        $ids = array_unique($ids);
        $this->latest_history_ids = $ids;
        $this->set_step_data_options('latest_history_ids', $ids);
    }

    public function to_array(): array
    {
        return $this->record;
    }

    public function to_object(): object
    {
        return (object)$this->to_array();
    }

    private function get_ids_from_step_data(string $name): array
    {
        $step_data_json = $this->get_step_data();
        if (empty($step_data_json))
        {
            return [];
        }

        $step_data = $this->json_converter()->decode_as_array($step_data_json);
        if (empty($step_data[$name]))
        {
            return [];
        }
        return $step_data[$name];
    }

    private function set_step_data_options(string $name, array $options): void
    {
        $converter = $this->json_converter();
        $step_data_json = $this->get_step_data();
        $step_data = empty($step_data_json) ? [] : $converter->decode_as_array($step_data_json);
        $step_data[$name] = $options;
        $this->set_step_data($converter->encode($step_data));
    }

    private function json_converter(): json
    {
        return $this->factory->helper()->encoding()->json();
    }
}
