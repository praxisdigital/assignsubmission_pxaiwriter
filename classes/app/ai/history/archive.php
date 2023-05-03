<?php

namespace assignsubmission_pxaiwriter\app\ai\history;


use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class archive implements interfaces\archive
{
    private base_factory $factory;
    private int $userid;
    private int $assignment_id;
    private string $type;
    private ?int $submission_id;
    private int $default_step;

    public function __construct(
        base_factory $factory,
        int $assignment_id,
        string $type,
        ?int $submission = null,
        ?int $userid = null,
        int $default_step = 1
    )
    {
        $this->factory = $factory;
        $this->userid = $userid ?? $this->factory->moodle()->user()->id;
        $this->assignment_id = $assignment_id;
        $this->type = $type;
        $this->submission_id = $submission;
        $this->default_step = $default_step;
    }

    public function force_commit(
        string $input_text,
        string $data,
        ?int $step = null
    ): interfaces\entity
    {
        $entity = $this->create_entity($step);
        $entity->set_status_ok();
        $entity->set_input_text($input_text);
        $entity->set_data($data);
        $this->get_repository()->insert($entity);
        return $entity;
    }

    public function commit(
        string $input_text,
        ?string $data = null,
        ?int $step = null
    ): interfaces\entity
    {
        $data ??= $input_text;
        return $this->get_entity_by_hashcode($input_text, $step) ?? $this->force_commit($input_text, $data, $step);
    }

    public function commit_by_generate_ai_text(
        string $input_text,
        string $ai_text,
        string $data,
        string $response_data,
        ?int $step = null
    ): entity
    {
        $entity = $this->get_history_with_ai_data(
            $input_text,
            $ai_text,
            $data,
            $response_data,
            $step
        );
        $entity->set_type_ai_generate();
        $this->get_repository()->insert($entity);
        return $entity;
    }

    public function commit_by_expand_ai_text(
        string $input_text,
        string $ai_text,
        string $data,
        string $response_data,
        ?int $step = null
    ): entity
    {
        $entity = $this->get_history_with_ai_data(
            $input_text,
            $ai_text,
            $data,
            $response_data,
            $step
        );
        $entity->set_type_ai_expand();
        $this->get_repository()->insert($entity);
        return $entity;
    }


    private function get_history_with_ai_data(
        string $input_text,
        string $ai_text,
        string $data,
        string $response_data,
        ?int $step = null
    ): interfaces\entity
    {
        $entity = $this->create_entity($step);
        $entity->set_status_ok();
        $entity->set_input_text($input_text);
        $entity->set_ai_text($ai_text);
        $entity->set_data($data);
        $entity->set_response($response_data);
        return $entity;
    }

    public function failed(
        string $input_text,
        ?int $step = null
    ): interfaces\entity
    {
        $entity = $this->create_entity($step);
        $entity->set_status_failed();
        $entity->set_input_text($input_text);
        $this->get_repository()->insert($entity);
        return $entity;
    }

    private function get_repository(): interfaces\repository
    {
        return $this->factory->ai()->history()->repository();
    }

    private function create_entity(?int $step = null): interfaces\entity
    {
        $current_time = $this->factory->helper()->times()->current_time();
        $entity = $this->factory->ai()->history()->entity();
        $entity->set_userid($this->userid);
        $entity->set_assignment($this->assignment_id);
        $entity->set_submission($this->get_submission_id());
        $entity->set_step($step ?? $this->default_step);
        $entity->set_type($this->type);
        $entity->set_timecreated($current_time);
        $entity->set_timemodified($current_time);
        $entity->set_status_failed();
        return $entity;
    }

    private function get_submission_id(): int
    {
        return $this->submission_id ??= $this->factory->assign()
            ->repository()
            ->get_latest_submission_id_by_user_assignment(
                $this->userid,
                $this->assignment_id
            );
    }
    private function get_entity_by_hashcode(
        string $data,
        ?int $step = null
    ): ?interfaces\entity
    {
        $hash = $this->factory->helper()->hash()->sha256()->digest($data);
        return $this->get_repository()->get_by_hashcode(
            $this->userid,
            $this->assignment_id,
            $hash,
            $step ?? $this->default_step
        );
    }
}
