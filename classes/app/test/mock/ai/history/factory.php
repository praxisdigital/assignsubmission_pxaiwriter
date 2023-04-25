<?php

namespace assignsubmission_pxaiwriter\app\test\mock\ai\history;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\archive;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\factory as history_factory_interface;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\mapper;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\repository;
use assignsubmission_pxaiwriter\app\factory as base_factory;
use assignsubmission_pxaiwriter\app\test\mock\mocker;
use moodle_transaction;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory extends mocker implements history_factory_interface
{
    private history_factory_interface $factory;

    public function __construct(?history_factory_interface $factory = null)
    {
        $this->factory = $factory ?? base_factory::make()->ai()->history();
    }

    public function archive(
        int $assignment_id,
        int $step,
        ?int $user_id = null,
        ?moodle_transaction $transaction = null
    ): archive
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(
                __FUNCTION__,
                $assignment_id,
                $step,
                $user_id,
                $transaction
            );
        }
        return $this->factory->archive(
            $assignment_id,
            $step,
            $user_id,
            $transaction
        );
    }

    public function entity(array $record = []): entity
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->entity();
    }

    public function mapper(): mapper
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->mapper();
    }

    public function repository(): repository
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__);
        }
        return $this->factory->repository();
    }

    public function create_entity_by_current_user(int $submission_instance_id, int $step, string $text): entity
    {
        if ($this->has_mock(__FUNCTION__))
        {
            return $this->call_mock_method(__FUNCTION__, $submission_instance_id, $step, $text);
        }
        return $this->factory->create_entity_by_current_user($submission_instance_id, $step, $text);
    }
}
