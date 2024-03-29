<?php

namespace assignsubmission_pxaiwriter\app\file\interfaces;


use context;
use stored_file;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface repository
{
    public function get_file_area(): string;

    /**
     * @return stored_file[]
     */
    public function get_submission_files(context $context, int $submission_id): array;

    /**
     * @return stored_file[]
     */
    public function get_submission_files_with_path(context $context, object $submission): array;

    public function create_from_submission(
        string $filename,
        string $data,
        context $context,
        object $submission
    ): stored_file;

    public function delete_files_by_submission(context $context, int $submission_id): void;

    public function delete_files_by_context(context $context): void;
}
