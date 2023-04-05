<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface repository
{
    public const TABLE = 'pxaiwriter_history';
    public function get_by_hashcode(
        int $user_id,
        int $assignment_id,
        string $hashcode,
        int $step = 1
    ): ?entity;

    /**
     * @param int $user_id
     * @param int $assignment_id
     * @param int $offset
     * @param int $limit
     * @return collection<entity>
     */
    public function get_all_by_user_assignment(
        int $user_id,
        int $assignment_id,
        int $offset = 0,
        int $limit = 0
    ): collection;

    public function insert(entity $entity): void;
    public function delete_by_id(int $id): void;
    public function delete_by_user_id(int $user_id): void;
}
