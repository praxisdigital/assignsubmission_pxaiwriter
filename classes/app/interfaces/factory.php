<?php

namespace assignsubmission_pxaiwriter\app\interfaces;


use assignsubmission_pxaiwriter\app\ai\interfaces\factory as ai_factory;
use assignsubmission_pxaiwriter\app\assign\interfaces\factory as assign_factory;
use assignsubmission_pxaiwriter\app\file\interfaces\factory as file_factory;
use assignsubmission_pxaiwriter\app\helper\interfaces\factory as helper_factory;
use assignsubmission_pxaiwriter\app\http\interfaces\factory as http_factory;
use assignsubmission_pxaiwriter\app\moodle\interfaces\factory as moodle_factory;
use assignsubmission_pxaiwriter\app\migration\interfaces\factory as migration_factory;
use assignsubmission_pxaiwriter\app\setting\interfaces\factory as setting_factory;
use assignsubmission_pxaiwriter\app\submission\interfaces\factory as submission_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

interface factory
{
    public const COMPONENT = 'assignsubmission_pxaiwriter';

    public function assign(): assign_factory;

    public function ai(): ai_factory;

    /**
     * @template T
     * @psalm-template T
     * @param array $items
     * @return collection<T>
     */
    public function collection(array $items = []): collection;

    public function file(): file_factory;

    public function helper(): helper_factory;

    public function http(): http_factory;

    public function migration(): migration_factory;

    public function moodle(): moodle_factory;

    public function setting(): setting_factory;

    public function submission(): submission_factory;
}
