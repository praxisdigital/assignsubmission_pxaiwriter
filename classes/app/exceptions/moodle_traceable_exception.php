<?php

namespace assignsubmission_pxaiwriter\app\exceptions;


use assignsubmission_pxaiwriter\app\interfaces\factory as factory_interface;
use Exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class moodle_traceable_exception extends \moodle_exception
{
    public function __construct(
        string $identifier,
        ?Exception $previous_exception = null,
        $arguments = null,
        string $component = factory_interface::COMPONENT,
        string $link = '',
        ?string $debug_info = null
    )
    {
        $component = $this->get_error_component($component);
        $debug_info = $this->get_debug_info($debug_info, $previous_exception);
        parent::__construct($identifier, $component, $link, $arguments, $debug_info);
    }

    private function get_error_component(string $component): string
    {
        switch ($component)
        {
            case 'moodle':
            case 'core':
                return 'error';
            default:
                return $component;
        }
    }

    private function get_debug_info(?string $debug_info, ?Exception $exception): ?string
    {
        if ($exception !== null)
        {
            if (empty($debug_info))
            {
                $debug_info = $exception->getMessage();
            }
            else
            {
                $debug_info .= PHP_EOL;
                $debug_info .= $exception->getMessage();
            }
            $debug_info .= PHP_EOL . $exception->getTraceAsString();
        }
        return $debug_info;
    }
}
