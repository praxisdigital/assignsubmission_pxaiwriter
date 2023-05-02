<?php

namespace assignsubmission_pxaiwriter\app\migration;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use dml_exception;
use Exception;
use moodle_database;
use RuntimeException;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class history_data_migration implements interfaces\migration
{
    private base_factory $factory;
    private moodle_database $db;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
        $this->db = $this->factory->moodle()->db();
    }

    public function up(): void
    {
        $submissions = $this->get_submission_instances();
        $this->add_submissions_history($submissions);
    }

    /**
     * @param submission_instance[] $submissions
     * @throws dml_exception
     */
    private function add_submissions_history(array $submissions): void
    {

        foreach ($submissions as $submission)
        {
            $submission_history_list = [];
            foreach ($submission->get_steps_data()->get_legacy_steps() as $step)
            {
                if (empty($step->get_value()))
                {
                    continue;
                }

                $entity = $this->factory->ai()->history()->entity();
                $entity->set_userid($submission->get_userid());
                $entity->set_assignment($submission->get_assignment());
                $entity->set_submission($submission->get_submission());
                $entity->set_step($step->get_step());
                $entity->set_status_ok();
                $entity->set_type_user_edit();
                $entity->set_input_text($step->get_value());
                $entity->set_data($step->get_value());

                $this->factory->ai()->history()->repository()->insert($entity);

                $submission_history_list[$entity->get_id()] = $entity->get_step();
            }

            asort($submission_history_list);
            $history_ids = array_keys($submission_history_list);

            $steps_data = $submission->get_steps_data();
            $steps_data->set_history_ids($history_ids);
            $steps_data->set_latest_history_ids($history_ids);

            $this->db->update_record('assignsubmission_pxaiwriter', $submission->to_record());
        }
    }

    /**
     * @return submission_instance[]
     * @throws dml_exception
     */
    private function get_submission_instances(): array
    {
        $sql = "SELECT ss.*, s.userid FROM {assignsubmission_pxaiwriter} ss
            JOIN {assign_submission} s ON s.id = ss.submission";
        $records = $this->factory->moodle()->db()->get_recordset_sql($sql);
        $instances = [];
        foreach ($records as $id => $record)
        {
            if (!empty($record->steps_data))
            {
                try
                {
                    $record->steps_data = $this->get_new_steps_data($record->steps_data);
                }
                catch (Exception $exception)
                {
                    throw new RuntimeException(
                        "Invalid JSON format in the steps data of assignsubmission_pxaiwriter instance with id $id",
                        0,
                        $exception
                    );
                }
            }
            $instances[$id] = new submission_instance($this->factory, $record);
        }

        $records->close();

        return $instances;
    }

    private function get_new_steps_data(string $old_steps_data_json): steps_data
    {
        $old_data = $this->get_items_by_json($old_steps_data_json);
        return new steps_data($this->factory, [
            'old_steps_data' => $old_data
        ]);
    }

    private function get_items_by_json(string $json): array
    {
        return $this->factory->helper()->encoding()->json()->decode($json);
    }

}
