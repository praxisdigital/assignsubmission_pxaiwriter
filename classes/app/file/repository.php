<?php

namespace assignsubmission_pxaiwriter\app\file;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use context;
use Exception;
use file_storage;
use stored_file;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class repository implements interfaces\repository
{
    private base_factory $factory;
    private file_storage $storage;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
        $this->storage = $this->factory->moodle()->file_storage();
    }

    public function get_file_area(): string
    {
        if (defined('ASSIGNSUBMISSION_PXAIWRITER_FILEAREA'))
        {
            return ASSIGNSUBMISSION_PXAIWRITER_FILEAREA;
        }
        return 'submissions_pxaiwriter';
    }

    public function get_submission_files(context $context, int $submission_id): array
    {
        return $this->storage->get_area_files(
            $context->id,
            $this->get_component(),
            $this->get_file_area(),
            $submission_id,
            'id',
            false
        );
    }

    public function delete_files_by_submission(context $context, int $submission_id): void
    {
        try
        {
            $this->storage->delete_area_files(
                $context->id,
                $this->get_component(),
                $this->get_file_area(),
                $submission_id
            );
        }
        catch (Exception $exception) {}
    }

    public function create_from_submission(
        string $filename,
        string $data,
        context $context,
        object $submission
    ): stored_file
    {
        $record = $this->get_stored_file_record(
            $context->id,
            $this->get_file_area(),
            $submission->id,
            $submission->userid,
            $filename
        );

        return $this->storage->create_file_from_string(
            $record,
            $data
        );
    }

    private function get_component(): string
    {
        return base_factory::COMPONENT;
    }

    private function get_stored_file_record(
        int $context_id,
        string $area,
        int $item_id,
        int $user_id,
        string $filename
    ): object
    {
        return (object)[
            'contextid' => $context_id,
            'component' => $this->get_component(),
            'filearea' => $area,
            'itemid' => $item_id,
            'filepath' => '/',
            'userid' => $user_id,
            'filename' => $filename
        ];
    }
}
