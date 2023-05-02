<?php

namespace assignsubmission_pxaiwriter\integration\migration;


use assignsubmission_pxaiwriter\app\migration\history_table_migration;
use assignsubmission_pxaiwriter\app\test\integration_testcase;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class history_table_migration_test extends integration_testcase
{
    public function test_history_table_creation(): void
    {
        $manager = $this->db()->get_manager();
        $table = new \xmldb_table('pxaiwriter_history');

        if ($manager->table_exists($table))
        {
            $manager->drop_table($table);
        }

        self::assertFalse($manager->table_exists($table));

        $migration = new history_table_migration($this->factory());
        $migration->up();

        self::assertTrue($manager->table_exists($table));
    }
}
