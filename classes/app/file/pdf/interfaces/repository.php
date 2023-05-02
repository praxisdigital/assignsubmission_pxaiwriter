<?php

namespace assignsubmission_pxaiwriter\app\file\pdf\interfaces;


use assignsubmission_pxaiwriter\app\submission\interfaces\submission_history;
use stored_file;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface repository
{
    public function save_submission_as_pdf(submission_history $submission_history): ?stored_file;
    public function get_pdf_diff_by_history_list(submission_history $submission_history): ?string;
}
