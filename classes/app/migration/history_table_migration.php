<?php

namespace assignsubmission_pxaiwriter\app\migration;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use database_manager;
use moodle_database;
use xmldb_table;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class history_table_migration implements interfaces\migration
{
    private base_factory $factory;
    private database_manager $manager;
    private moodle_database $db;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
        $this->db = $this->factory->moodle()->db();
        $this->manager = $this->db->get_manager();
    }
    public function up(): void
    {
        $table = new xmldb_table('pxaiwriter_history');
        if ($this->manager->table_exists($table))
        {
            return;
        }

        $this->add_fields($table);
        $this->add_keys($table);
        $this->add_indexes($table);

        $this->manager->create_table($table);
    }

    private function add_keys(xmldb_table $table): void
    {
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('assignment', XMLDB_KEY_FOREIGN, ['assignment'], 'assign', ['id']);
    }

    private function add_fields(xmldb_table $table): void
    {
        $table->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE
        );
        $table->add_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL
        );
        $table->add_field(
            'assignment',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL
        );
        $table->add_field(
            'submission',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            false,
            '0'
        );
        $table->add_field(
            'step',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            false,
            '1'
        );
        $table->add_field(
            'status',
            XMLDB_TYPE_CHAR,
            16,
            true,
            XMLDB_NOTNULL,
            false,
            'ok'
        );
        $table->add_field(
            'type',
            XMLDB_TYPE_CHAR,
            32,
            true,
            XMLDB_NOTNULL,
            false,
            'user-edit'
        );
        $table->add_field(
            'input_text',
            XMLDB_TYPE_TEXT,
            null,
            null,
            XMLDB_NOTNULL
        );
        $table->add_field(
            'ai_text',
            XMLDB_TYPE_TEXT,
            null,
            null,
            false
        );
        $table->add_field(
            'response',
            XMLDB_TYPE_TEXT,
            null,
            null,
            false
        );
        $table->add_field(
            'data',
            XMLDB_TYPE_TEXT,
            null,
            null,
            false
        );
        $table->add_field(
            'hashcode',
            XMLDB_TYPE_CHAR,
            64,
            null,
            false
        );
        $table->add_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            false,
            '0'
        );
        $table->add_field(
            'timemodified',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            false,
            '0'
        );
    }

    private function add_indexes(xmldb_table $table)
    {
        $table->add_index(
            'userassignment',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid, assignment']
        );
        $table->add_index(
            'userhistory',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid, assignment', 'status', 'hashcode']
        );
        $table->add_index(
            'userattempt',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid', 'assignment', 'step', 'status', 'type', 'timecreated']
        );
        $table->add_index(
            'submission',
            XMLDB_INDEX_NOTUNIQUE,
            ['submission']
        );
        $table->add_index(
            'step',
            XMLDB_INDEX_NOTUNIQUE,
            ['step']
        );
        $table->add_index(
            'status',
            XMLDB_INDEX_NOTUNIQUE,
            ['status']
        );
        $table->add_index(
            'type',
            XMLDB_INDEX_NOTUNIQUE,
            ['type']
        );
        $table->add_index(
            'hashcode',
            XMLDB_INDEX_NOTUNIQUE,
            ['hashcode']
        );
        $table->add_index(
            'timecreated',
            XMLDB_INDEX_NOTUNIQUE,
            ['timecreated']
        );
        $table->add_index(
            'timemodified',
            XMLDB_INDEX_NOTUNIQUE,
            ['timemodified']
        );
    }
}
