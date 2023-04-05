<?php

namespace assignsubmission_pxaiwriter\app\ai\history\interfaces;


use Exception;
use moodle_transaction;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface archive
{
    public function start_attempt(string $text): void;

    /**
     * Record history (only if text is has been changed)
     * @param string $text
     * @param string|null $ai_text
     * @return entity
     */
    public function commit(string $text, ?string $ai_text = null): entity;

    public function force_commit(string $text, ?string $ai_text = null): entity;

    public function rollback(string $input_text, Exception $exception): void;
}
