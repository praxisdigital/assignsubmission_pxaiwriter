<?php

namespace assignsubmission_pxaiwriter\app\http\interfaces;


/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface header
{
    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name);

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name, $value): void;

    public function has(string $name): bool;

    public function delete(string $name): void;

    public function clear(): void;

    public function to_array(): array;

    public function to_http_header(): array;
}
