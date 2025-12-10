<?php

defined('MOODLE_INTERNAL') || die();

/** @var object $plugin */
$plugin->component = 'assignsubmission_pxaiwriter';
$plugin->version = 2025121000;
$plugin->requires = 2021051700; // Moodle 3.11
$plugin->release = '1.7.1 (Build: 2025-12-10)';
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = [
    'local_mxaimanager' => 2025111900,
];
