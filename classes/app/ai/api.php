<?php

namespace assignsubmission_pxaiwriter\app\ai;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class api
{
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
    }

    public function generate_ai_text(string $assistant_text, string $user_text): string
    {
        $feature = $this->factory->local_mxaimanager()->ai()->feature()->repository()->get_by_component_and_name_identifier(
            base_factory::COMPONENT,
            'ai:feature:assist_with_assignment_writing'
        );
        $messages = [
            new \local_mxaimanager\app\ai\provider\message('system', 'You\'re a text completion AI used to help students complete their texts.'),
        ];

        if (!empty($assistant_text)) {
            $messages[] = new \local_mxaimanager\app\ai\provider\message('system', $assistant_text);
        }

        $messages[] = new \local_mxaimanager\app\ai\provider\message('user', $user_text);

        return $this->factory->local_mxaimanager()->ai()->feature()->handler($feature)->chat_completion($messages);
    }

    public function expand_ai_text(string $assistant_text, string $user_text): string
    {
        $feature = $this->factory->local_mxaimanager()->ai()->feature()->repository()->get_by_component_and_name_identifier(
            base_factory::COMPONENT,
            'ai:feature:assist_with_assignment_writing'
        );
        $messages = [
            new \local_mxaimanager\app\ai\provider\message('system', 'You\'re a text completion AI used to help students complete their texts.'),
        ];

        if (!empty($assistant_text)) {
            $messages[] = new \local_mxaimanager\app\ai\provider\message('system', $assistant_text);
        }

        $messages[] = new \local_mxaimanager\app\ai\provider\message('user', $this->get_expand_text_sentence($user_text));

        return $this->factory->local_mxaimanager()->ai()->feature()->handler($feature)->chat_completion($messages);
    }

    private function get_expand_text_sentence(string $user_text): string
    {
        $command = $this->factory->moodle()->get_string('expand_command');
        return "$command : $user_text";
    }
}
