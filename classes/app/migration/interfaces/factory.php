<?php

namespace assignsubmission_pxaiwriter\app\migration\interfaces;



/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    /**
     * @param string $version
     * @return migration[]
     */
    public function get_migrations_by_version(string $version): array;
}
