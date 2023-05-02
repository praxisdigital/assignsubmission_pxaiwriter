<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface archive
{

    public function force_commit(
        string $input_text,
        string $data,
        ?int $step = null
    ): entity;

    public function commit(
        string $input_text,
        ?string $data = null,
        ?int $step = null
    ): entity;

    public function commit_by_generate_ai_text(
        string $input_text,
        string $ai_text,
        string $data,
        string $response_data,
        ?int $step = null
    ): entity;

    public function commit_by_expand_ai_text(
        string $input_text,
        string $ai_text,
        string $data,
        string $response_data,
        ?int $step = null
    ): entity;

    public function failed(
        string $input_text,
        ?int $step = null
    ): entity;
}
