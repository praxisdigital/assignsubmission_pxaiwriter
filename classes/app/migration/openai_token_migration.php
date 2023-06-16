<?php

namespace assignsubmission_pxaiwriter\app\migration;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use Exception;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class openai_token_migration implements interfaces\migration
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function up(): void
    {
        try
        {
            $config = $this->factory->moodle()->get_config_instance();

            if (!empty($config->openai_token))
            {
                return;
            }

            if (empty($config->authorization))
            {
                return;
            }


            $openai_token = $this->extract_token_from_authorization_header($config->authorization);
            if (empty($openai_token))
            {
                return;
            }

            $this->factory->moodle()->set_config('openai_token', $openai_token);
        }
        catch (Exception $exception) {}
    }

    private function extract_token_from_authorization_header(string $authorization): ?string
    {
        $auth_type = 'Bearer ';
        $index = strpos($authorization, $auth_type);
        if ($index !== 0)
        {
            return null;
        }

        $position = $index + strlen($auth_type);
        $token = substr($authorization, $position);
        if (empty($token))
        {
            return null;
        }

        return trim($token);
    }
}
