<?php

namespace assignsubmission_pxaiwriter\integration\external;


use assignsubmission_pxaiwriter\app\ai\openai\interfaces\api;
use assignsubmission_pxaiwriter\app\ai\openai\interfaces\response;
use assignsubmission_pxaiwriter\app\test\integration_testcase;
use assignsubmission_pxaiwriter\app\test\mock\ai\factory as ai_factory;
use assignsubmission_pxaiwriter\app\test\mock\factory;
use assignsubmission_pxaiwriter\external\ai\generate_ai_text;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class generate_ai_text_test extends integration_testcase
{
    public function test_execute(): void
    {
        $max_attempts = 2;
        $this->set_config(
            'attempt_count',
            $max_attempts
        );

        $ai_text = 'Hello you';

        $mock_response = $this->createMock(response::class);
        $mock_response->method('get_text')
            ->willReturn($ai_text);

        $api = $this->createMock(api::class);
        $api->method('generate_ai_text')
            ->willReturn($mock_response);

        $openai_factory = new \assignsubmission_pxaiwriter\app\test\mock\ai\openai\factory();
        $openai_factory->set_mock_method('api', $api);

        $ai_factory = new ai_factory();
        $ai_factory->set_mock_method('openai', $openai_factory);

        $factory = new factory();
        $factory->set_mock_method('ai', $ai_factory);

        $duedate = new \DateTime('now', new \DateTimeZone('UTC'));
        $duedate->modify('+20day');

        $user = $this->create_user();
        $course = $this->create_course();
        $assignment = $this->create_assignment_with_ai_writer($course, 2, ['duedate' => $duedate->getTimestamp()]);
        $assign_id = $assignment->get_instance()->id;

        $this->enrol_user($user, $course);

        self::setUser($user);

        $text = 'Hello world';
        generate_ai_text::set_factory($factory);

        $response = generate_ai_text::execute($assign_id, $text);

        $expected_text = $factory->ai()->formatter()->text($text, $ai_text);

        self::assertSame(
            $expected_text,
            $response['data']
        );

        self::assertSame(
            1,
            $response['attempted_count']
        );

        self::assertSame(
            $max_attempts,
            $response['max_attempts']
        );
    }
}
