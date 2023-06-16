<?php

namespace assignsubmission_pxaiwriter\app\migration;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use database_manager;
use moodle_database;
use xmldb_field;
use xmldb_index;
use xmldb_table;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class history_table_migration implements interfaces\migration
{
    private base_factory $factory;
    private database_manager $manager;
    private moodle_database $db;
    private string $table_name;

    public function __construct(base_factory $factory, string $table_name = 'pxaiwriter_history')
    {
        $this->factory = $factory;
        $this->db = $this->factory->moodle()->db();
        $this->manager = $this->db->get_manager();
        $this->table_name = $table_name;
    }

    public function up(): void
    {
        $table = new xmldb_table($this->table_name);

        if (!$this->manager->table_exists($table))
        {
            $this->add_fields($table);
            $this->add_keys($table);

            $this->manager->create_table($table);

            $this->add_indexes($table);

            return;
        }

        $this->drop_indexes($table);
        $this->change_fields_default($table);
        $this->add_indexes($table);
    }

    private function add_keys(xmldb_table $table): void
    {
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('assignment', XMLDB_KEY_FOREIGN, ['assignment'], 'assign', ['id']);
    }

    private function add_fields(xmldb_table $table): void
    {
        foreach ($this->get_fields() as $field)
        {
            $table->addField($field);
        }
    }

    private function add_indexes(xmldb_table $table)
    {
        foreach ($this->get_indexes() as $index)
        {
            $this->add_index($table, $index);
        }
    }

    private function drop_indexes(xmldb_table $table)
    {
        foreach ($this->get_indexes() as $index)
        {
            if ($this->manager->index_exists($table, $index))
            {
                $this->manager->drop_index($table, $index);
            }
        }
    }

    /**
     * @return xmldb_field[]
     */
    private function get_fields(): array
    {
        $fields = [];

        $fields['id'] = new xmldb_field(
            'id',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE
        );
        $fields['userid'] = new xmldb_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL
        );
        $fields['assignment'] = new xmldb_field(
            'assignment',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL
        );
        $fields['submission'] = new xmldb_field(
            'submission',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            false,
            '0'
        );
        $fields['step'] = new xmldb_field(
            'step',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            false,
            '1'
        );
        $fields['status'] = new xmldb_field(
            'status',
            XMLDB_TYPE_CHAR,
            16,
            null,
            XMLDB_NOTNULL,
            false,
            'drafted'
        );
        $fields['type'] = new xmldb_field(
            'type',
            XMLDB_TYPE_CHAR,
            32,
            null,
            XMLDB_NOTNULL,
            false,
            'user-edit'
        );
        $fields['input_text'] = new xmldb_field(
            'input_text',
            XMLDB_TYPE_TEXT,
            null,
            null,
            false
        );
        $fields['ai_text'] = new xmldb_field(
            'ai_text',
            XMLDB_TYPE_TEXT,
            null,
            null,
            false
        );
        $fields['response'] = new xmldb_field(
            'response',
            XMLDB_TYPE_TEXT,
            null,
            null,
            false
        );
        $fields['data'] = new xmldb_field(
            'data',
            XMLDB_TYPE_TEXT,
            null,
            null,
            false
        );
        $fields['hashcode'] = new xmldb_field(
            'hashcode',
            XMLDB_TYPE_CHAR,
            64,
            null,
            false,
            false,
            null
        );
        $fields['timecreated'] = new xmldb_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            false,
            '0'
        );
        $fields['timemodified'] = new xmldb_field(
            'timemodified',
            XMLDB_TYPE_INTEGER,
            10,
            true,
            XMLDB_NOTNULL,
            false,
            '0'
        );

        return $fields;
    }

    /**
     * @return xmldb_index[]
     */
    private function get_indexes(): array
    {
        $indexes = [];
        $indexes['userassignment'] = new xmldb_index(
            'userassignment',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid', 'assignment']
        );
        $indexes['userhistory'] = new xmldb_index(
            'userhistory',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid', 'assignment', 'status', 'hashcode']
        );
        $indexes['userattempt'] = new xmldb_index(
            'userattempt',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid', 'assignment', 'step', 'status', 'type', 'timecreated']
        );
        $indexes['submission'] = new xmldb_index(
            'submission',
            XMLDB_INDEX_NOTUNIQUE,
            ['submission']
        );
        $indexes['step'] = new xmldb_index(
            'step',
            XMLDB_INDEX_NOTUNIQUE,
            ['step']
        );
        $indexes['status'] = new xmldb_index(
            'status',
            XMLDB_INDEX_NOTUNIQUE,
            ['status']
        );
        $indexes['type'] = new xmldb_index(
            'type',
            XMLDB_INDEX_NOTUNIQUE,
            ['type']
        );
        $indexes['hashcode'] = new xmldb_index(
            'hashcode',
            XMLDB_INDEX_NOTUNIQUE,
            ['hashcode']
        );
        $indexes['timecreated'] = new xmldb_index(
            'timecreated',
            XMLDB_INDEX_NOTUNIQUE,
            ['timecreated']
        );
        $indexes['timemodified'] = new xmldb_index(
            'timemodified',
            XMLDB_INDEX_NOTUNIQUE,
            ['timemodified']
        );
        return $indexes;
    }

    private function add_index(xmldb_table $table, xmldb_index $index)
    {
        if (!$this->manager->index_exists($table, $index))
        {
            $this->manager->add_index($table, $index);
        }
    }

    private function change_fields_default(xmldb_table $table): void
    {
        $field = new xmldb_field(
            'status',
            XMLDB_TYPE_CHAR,
            16,
            true,
            XMLDB_NOTNULL,
            false,
            'drafted'
        );
        $this->manager->change_field_default(
            $table,
            $field
        );
    }
}
