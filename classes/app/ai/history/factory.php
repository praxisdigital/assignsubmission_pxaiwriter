<?php

namespace assignsubmission_pxaiwriter\app\ai\history;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private base_factory $factory;
    private array $instances = [];

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function archive(
        int $assignment_id,
        string $type,
        int $submission = 0,
        ?int $userid = null,
        int $step = 1
    ): interfaces\archive
    {
        return new archive(
            $this->factory,
            $assignment_id,
            $type,
            $submission,
            $this->get_user_id($userid),
            $step,
        );
    }

    public function archive_expand_ai_text(
        int $assignment_id,
        int $submission = 0,
        ?int $userid = null,
        int $step = 1
    ): interfaces\archive
    {
        return $this->archive(
            $assignment_id,
            interfaces\entity::TYPE_AI_EXPAND,
            $submission,
            $userid,
            $step
        );
    }

    public function archive_generate_ai_text(
        int $assignment_id,
        int $submission = 0,
        ?int $userid = null,
        int $step = 1
    ): interfaces\archive
    {
        return $this->archive(
            $assignment_id,
            interfaces\entity::TYPE_AI_GENERATE,
            $submission,
            $userid,
            $step
        );
    }

    public function archive_user_edit(
        int $assignment_id,
        int $submission = 0,
        ?int $userid = null,
        int $step = 1
    ): interfaces\archive
    {
        return $this->archive(
            $assignment_id,
            interfaces\entity::TYPE_USER_EDIT,
            $submission,
            $userid,
            $step
        );
    }


    public function entity(array $record = []): interfaces\entity
    {
        return new entity($record, $this->factory);
    }

    public function mapper(): interfaces\mapper
    {
        return $this->instances[__FUNCTION__] ??= new mapper($this->factory);
    }

    public function repository(): interfaces\repository
    {
        return $this->instances[__FUNCTION__] ??= new repository($this->factory);
    }

    public function create_entity_by_current_user(int $submission_instance_id, int $step, string $text): interfaces\entity
    {
        $now = time();
        return $this->entity([
            'submission' => $submission_instance_id,
            'userid' => $this->factory->moodle()->user()->id,
            'step' => $step,
            'hashcode' => $this->factory->helper()->hash()->sha256()->digest($text),
            'content' => $text,
            'timecreated' => $now,
            'timemodified' => $now
        ]);
    }

    private function get_user_id(?int $id): int
    {
        if (empty($id))
        {
            return $this->factory->moodle()->user()->id;
        }
        return $id;
    }
}
