<?php

namespace assignsubmission_pxaiwriter\app\ai\history;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use moodle_transaction;

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
        int $step,
        ?int $user_id = null,
        ?moodle_transaction $transaction = null
    ): interfaces\archive
    {
        return new archive(
            $this->factory,
            $assignment_id,
            $step,
            $user_id,
            $transaction
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
}
