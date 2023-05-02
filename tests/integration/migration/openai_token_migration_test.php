<?php

namespace assignsubmission_pxaiwriter\integration\migration;


use assignsubmission_pxaiwriter\app\migration\openai_token_migration;
use assignsubmission_pxaiwriter\app\test\integration_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class openai_token_migration_test extends integration_testcase
{
    public function test_migrate_openai_token_from_authorization_setting(): void
    {
        $token = 'sk-1234567890';
        $this->factory()->moodle()->set_config(
            'authorization',
            "Bearer $token"
        );

        $migration = new openai_token_migration($this->factory());
        $migration->up();

        $actual = $this->db()->get_field('config_plugins', 'value', [
            'plugin' => 'assignsubmission_pxaiwriter',
            'name' => 'openai_token'
        ]);

        self::assertSame(
            $token,
            $actual
        );
    }
}
