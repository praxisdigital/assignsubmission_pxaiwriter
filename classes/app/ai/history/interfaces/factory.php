<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


use moodle_transaction;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public function archive(
        int $assignment_id,
        int $step,
        ?int $user_id = null,
        ?moodle_transaction $transaction = null
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
