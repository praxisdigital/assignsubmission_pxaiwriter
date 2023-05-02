<?php

namespace assignsubmission_pxaiwriter\app\migration;


use assignsubmission_pxaiwriter\app\migration\interfaces\migration;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class version_migration implements interfaces\migration
{
    private float $current_version;
    private float $min_version;
    /** @var migration[] */
    private array $migrations;

    public function __construct(
        float $current_version,
        float $min_version,
        array $migrations = []
    )
    {
        $this->current_version = $current_version;
        $this->min_version = $min_version;
        $this->migrations = $migrations;
    }

    public function add(migration $migration): self
    {
        $this->migrations[] = $migration;
        return $this;
    }

    public function up(): void
    {
        if ($this->current_version >= $this->min_version)
        {
            return;
        }

        foreach ($this->migrations as $migration)
        {
            $migration->up();
        }
    }
}
