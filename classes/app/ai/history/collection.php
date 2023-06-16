<?php

namespace assignsubmission_pxaiwriter\app\ai\history;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity;
use assignsubmission_pxaiwriter\app\collection as base_collection;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

/**
 * @extends base_collection<interfaces\entity>
 */
class collection extends base_collection implements interfaces\collection
{
    /** @var array<int, entity>|null */
    private ?array $step_data = null;
    /** @var int[]|null */
    private ?array $step_numbers = null;

    /**
     * @param interfaces\entity[] $items
     */
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function get_step_numbers(): array
    {
        if ($this->step_numbers === null)
        {
            $steps = $this->to_step_array();
            $this->step_numbers = array_keys($steps);
        }
        return $this->step_numbers;
    }

    public function get_step_entities(int $step): interfaces\collection
    {
        $step_data = $this->to_step_array();
        return new static($step_data[$step] ?? []);
    }

    public function get_first_entity_by_step(int $step): ?entity
    {
        $step_data = $this->to_step_array();

        if (isset($step_data[$step]))
        {
            return $this->get_first_entity($step_data[$step]);
        }

        return null;
    }

    public function get_latest_entity_by_step(int $step): ?interfaces\entity
    {
        $step_data = $this->to_step_array();

        if (isset($step_data[$step]))
        {
            return $this->get_last_entity($step_data[$step]);
        }

        return null;
    }

    public function get_latest_history_ids(): array
    {
        $step_numbers = $this->get_step_numbers();
        $history_ids = [];
        foreach ($step_numbers as $step_number)
        {
            $history = $this->get_latest_entity_by_step($step_number);
            if ($history !== null)
            {
                $id = $history->get_id();
                $history_ids[$id] = $id;
            }
        }
        return array_values($history_ids);
    }

    public function get_latest_history_ids_include_substeps(): array
    {
        $step_numbers = $this->get_step_numbers();
        if (empty($step_numbers))
        {
            return [];
        }

        $history_ids = [];
        $first_step_number = $step_numbers[array_key_first($step_numbers)];
        $first_step_history_list = $this->get_step_entities($first_step_number);

        foreach ($first_step_history_list as $history)
        {
            $id = $history->get_id();
            $history_ids[$id] = $id;
        }

        next($step_numbers);
        foreach ($step_numbers as $step_number)
        {
            $history = $this->get_latest_entity_by_step($step_number);
            if ($history !== null)
            {
                $id = $history->get_id();
                $history_ids[$id] = $id;
            }
        }

        return array_values($history_ids);
    }

    public function to_step_array(bool $reset = false): array
    {
        if ($this->step_data === null || $reset)
        {
            $this->step_data = [];
            foreach ($this->items as $entity)
            {
                $this->step_data[$entity->get_step()][] = $entity;
            }
        }

        return $this->step_data;
    }

    private function get_first_entity(array $entities): ?interfaces\entity
    {
        $first_index = array_key_first($entities);
        return $entities[$first_index] ?? null;
    }

    private function get_last_entity(array $entities): ?interfaces\entity
    {
        $last_index = array_key_last($entities);
        return $entities[$last_index] ?? null;
    }
}
