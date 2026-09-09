<?php

defined('MOODLE_INTERNAL') || die();

/** @var object $plugin */
$plugin->component = 'assignsubmission_pxaiwriter';
$plugin->version = 2026090401;
$plugin->requires = 2025041400; // Moodle 5.0
$plugin->release = '1.7.5 (Build: 2026-09-07)';
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = [
    'local_mxaimanager' => 2025111900,
];
