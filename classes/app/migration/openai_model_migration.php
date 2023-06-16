<?php

namespace assignsubmission_pxaiwriter\app\migration;


use assignsubmission_pxaiwriter\app\ai\openai\interfaces\models;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use Exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class openai_model_migration implements interfaces\migration
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function up(): void
    {
        try
        {
            $this->factory->moodle()->set_config(
                'model',
                models::GPT_3_5_TURBO
            );
        }
        catch (Exception $exception) {}
    }
}
