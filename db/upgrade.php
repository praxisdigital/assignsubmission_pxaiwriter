<?php

function xmldb_assignsubmission_pxaiwriter_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023040400) {

        // AI writer attempts

        $attempt_table = new xmldb_table('pxaiwriter_user_attempts');
        $attempt_table->setComment('AI writer step attempts');
        $attempt_table->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE
        );
        $attempt_table->add_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            false
        );
        $attempt_table->add_field(
            'assignment',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            false
        );
        $attempt_table->add_field(
            'step',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            false
        );
        $attempt_table->add_field(
            'status',
            XMLDB_TYPE_CHAR,
            16,
            null,
            XMLDB_NOTNULL,
            false,
            'ok'
        );
        $attempt_table->add_field(
            'hashcode',
            XMLDB_TYPE_CHAR,
            64,
            null,
            false,
            false
        );
        $attempt_table->add_field(
            'data',
            XMLDB_TYPE_TEXT,
            null,
            null,
            false
        );
        $attempt_table->add_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            false,
            0
        );

        $attempt_table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $attempt_table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $attempt_table->add_key('assignment', XMLDB_KEY_FOREIGN, ['assignment'], 'assign', ['id']);

        $attempt_table->add_index(
            'userid',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid']
        );
        $attempt_table->add_index(
            'assignment',
            XMLDB_INDEX_NOTUNIQUE,
            ['assignment']
        );
        $attempt_table->add_index(
            'step',
            XMLDB_INDEX_NOTUNIQUE,
            ['step']
        );
        $attempt_table->add_index(
            'status',
            XMLDB_INDEX_NOTUNIQUE,
            ['status']
        );
        $attempt_table->add_index(
            'hashcode',
            XMLDB_INDEX_NOTUNIQUE,
            ['hashcode']
        );
        $attempt_table->add_index(
            'userattempt',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid, assignment, status, timecreated']
        );

        if (!$dbman->table_exists($attempt_table)) {
            $dbman->create_table($attempt_table);
        }

        // AI writer History table

        $history_table = new xmldb_table('pxaiwriter_user_history');
        $history_table->setComment('AI writer user history');

        $history_table->add_field(
            'id',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            XMLDB_SEQUENCE
        );
        $history_table->add_field(
            'userid',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            false
        );
        $history_table->add_field(
            'assignment',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            false
        );
        $history_table->add_field(
            'step',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            false
        );
        $history_table->add_field(
            'status',
            XMLDB_TYPE_CHAR,
            16,
            null,
            XMLDB_NOTNULL,
            false,
            'ok'
        );
        $history_table->add_field(
            'hashcode',
            XMLDB_TYPE_CHAR,
            64,
            null,
            XMLDB_NOTNULL,
            false
        );
        $history_table->add_field(
            'data',
            XMLDB_TYPE_TEXT,
            null,
            null,
            XMLDB_NOTNULL
        );
        $history_table->add_field(
            'ai_text',
            XMLDB_TYPE_TEXT,
            null,
            null,
            XMLDB_NOTNULL
        );
        $history_table->add_field(
            'timecreated',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            false,
            0
        );
        $history_table->add_field(
            'timemodified',
            XMLDB_TYPE_INTEGER,
            10,
            null,
            XMLDB_NOTNULL,
            false,
            0
        );

        $history_table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $history_table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $history_table->add_key('assignment', XMLDB_KEY_FOREIGN, ['assignment'], 'assign', ['id']);

        $history_table->add_index(
            'userid',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid']
        );
        $history_table->add_index(
            'assignment',
            XMLDB_INDEX_NOTUNIQUE,
            ['assignment']
        );
        $history_table->add_index(
            'userassignment',
            XMLDB_INDEX_NOTUNIQUE,
            ['userid, assignment']
        );
        $history_table->add_index(
            'step',
            XMLDB_INDEX_NOTUNIQUE,
            ['step']
        );
        $history_table->add_index(
            'hashcode',
            XMLDB_INDEX_NOTUNIQUE,
            ['hashcode']
        );
        $history_table->add_index(
            'status',
            XMLDB_INDEX_NOTUNIQUE,
            ['status']
        );
        $history_table->add_index(
            'timecreated',
            XMLDB_INDEX_NOTUNIQUE,
            ['timecreated']
        );
        $history_table->add_index(
            'timemodified',
            XMLDB_INDEX_NOTUNIQUE,
            ['timemodified']
        );

        if (!$dbman->table_exists($history_table)) {
            $dbman->create_table($history_table);
        }

        upgrade_plugin_savepoint(true, 2023032400, 'assignsubmission', 'pxaiwriter');
    }

    return true;
}
