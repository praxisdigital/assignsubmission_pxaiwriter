<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function archive(
        int $assignment_id,
        string $type,
        int $submission = 0,
        ?int $userid = null,
        int $step = 1
    ): archive;

    public function archive_expand_ai_text(
        int $assignment_id,
        int $submission = 0,
        ?int $userid = null,
        int $step = 1
    ): archive;

    public function archive_generate_ai_text(
        int $assignment_id,
        int $submission = 0,
        ?int $userid = null,
        int $step = 1
    ): archive;

    public function archive_user_edit(
        int $assignment_id,
        int $submission = 0,
        ?int $userid = null,
        int $step = 1
    ): archive;

    public function entity(array $record = []): entity;

    public function mapper(): mapper;

    public function repository(): repository;

    public function create_entity_by_current_user(
        int $submission_instance_id,
        int $step,
        string $text
    ): entity;
}
