<?php

namespace assignsubmission_pxaiwriter\app\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface entity
{
    public function get_id(): int;

    public function set_id(int $id): void;

    public function to_array(): array;
    public function to_object(): object;
}
