<?php

namespace assignsubmission_pxaiwriter\app\migration;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class factory implements interfaces\factory
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function get_migrations_by_version(string $version): array
    {
        switch ($version)
        {
            case '2023060100':
                return [
                    new history_table_migration($this->factory),
                    new history_data_migration($this->factory),
                    new openai_token_migration($this->factory),
                    new openai_model_migration($this->factory),
                ];
            default:
                return [];
        }
    }
}
