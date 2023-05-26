<?php

namespace assignsubmission_pxaiwriter\app\submission;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\collection as history_collection;
use assignsubmission_pxaiwriter\app\interfaces\collection;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use context;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class submission_history implements interfaces\submission_history
{
    private base_factory $factory;
    private object $submission;
    private object $config;
    private ?history_collection $history_list;
    private context $context;
    private ?collection $steps_config = null;

    public function __construct(
        base_factory $factory,
        context $context,
        object $submission,
        ?object $config = null,
        ?history_collection $history_list = null
    )
    {
        $this->factory = $factory;
        $this->context = $context;
        $this->submission = $submission;
        $this->config = $config;
        $this->history_list = $history_list;
    }

    public function get_context(): context
    {
        return $this->context;
    }

    public function get_submission(): object
    {
        return $this->submission;
    }

    public function get_steps_config(): collection
    {
        if ($this->steps_config === null)
        {
            $this->steps_config = $this->get_steps_config_from_config();
        }
        return $this->steps_config;
    }

    public function get_step_config(int $step): ?interfaces\step_config
    {
        $configs = $this->get_steps_config();
        if (!isset($configs[$step]))
        {
            return null;
        }
        return $configs[$step];
    }

    public function get_history_list(bool $reset = false): history_collection
    {
        if (!isset($this->history_list) || $reset)
        {
            $this->history_list = $this->get_submission_history_list();
        }
        return $this->history_list;
    }

    private function get_userid(): int
    {
        return $this->get_submission()->userid;
    }

    private function get_assignment_id(): int
    {
        return $this->get_submission()->assignment;
    }

    private function get_submission_id(): int
    {
        return $this->get_submission()->id;
    }

    private function get_submission_history_list(): history_collection
    {
        return $this->factory->ai()->history()->repository()->get_all_submitted_by_submission(
            $this->get_submission_id(),
            $this->get_userid(),
            $this->get_assignment_id()
        );
    }

    private function get_steps_config_from_config(): collection
    {
        if (!isset($this->config->pxaiwritersteps))
        {
            return $this->factory->collection();
        }

        $steps = $this->factory->helper()->encoding()->json()->decode($this->config->pxaiwritersteps);

        $configs = [];
        foreach ($steps as $step)
        {
            $config = new step_config($step);

            $configs[$config->get_step()] = $config;
        }
        return $this->factory->collection($configs);
    }
}
