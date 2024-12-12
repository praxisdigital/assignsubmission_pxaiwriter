<?php

namespace assignsubmission_pxaiwriter\integration\file\pdf;


use assignsubmission_pxaiwriter\app\test\integration_testcase;
use Exception;
use pdf;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

global $CFG;
require_once $CFG->libdir . '/pdflib.php';

class repository_test extends integration_testcase
{
    public function test_save_submission_as_pdf(): void
    {
        $user = $this->create_user();
        $course = $this->create_course();
        $this->enrol_user($user, $course);

        $assignment = $this->create_assignment_with_ai_writer($course, 3);

        $submission = $this->create_submission($assignment, $user);

        $ai_text_archive = $this->factory()->ai()->history()->archive_generate_ai_text(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $openai_response = '{"message": "OpenAI response data"}';
        $formatter = $this->factory()->ai()->formatter();
        $ai_step = 1;

        $prompt1 = 'Step 1: This is a user text 1.';
        $ai_text1 = 'Step 1.1: This is an AI text 1.';
        $output1 = $formatter->text($prompt1, $ai_text1);

        $ai_text_archive->commit_by_generate_ai_text(
            $prompt1,
            $ai_text1,
            $output1,
            $openai_response,
            $ai_step
        );


        $user_text2 = 'Step 1.2: This is a user text 2.';
        $prompt2 = $output1 . "\n\n$user_text2";
        $ai_text2 = 'Step 1.2: This is an AI text 2.';
        $output2 = $formatter->text($prompt2, $ai_text2);

        $ai_text_archive->commit_by_generate_ai_text(
            $prompt2,
            $ai_text2,
            $output2,
            $openai_response,
            $ai_step
        );

        $user_text3 = 'Step 1.3: This is a user text 3.';
        $prompt3 = $output2 . "\n\n$user_text3";
        $ai_text3 = 'Step 1.3: This is an AI text 3.';
        $output3 = $formatter->text($prompt3, $ai_text3);

        $ai_text_archive->commit_by_generate_ai_text(
            $prompt3,
            $ai_text3,
            $output3,
            $openai_response,
            $ai_step
        );

        $user_archive = $this->factory()->ai()->history()->archive_user_edit(
            $submission->assignment,
            $submission->id,
            $submission->userid
        );

        $user_text4 = 'Step 2: This is a user text 4.';
        $prompt4 = $output3 . "\n\n$user_text4";

        $user_archive->commit(
            $prompt4,
            2
        );

        $user_text5 = 'Step 3: This is a user text 5.';
        $prompt5 = $output3 . "\n\n$user_text5";

        $user_archive->commit(
            $prompt5,
            3
        );

        $history_repo = $this->factory()->ai()->history()->repository();
        $history_list = $history_repo->get_all_by_submission($submission->id);

        $submission_configs = $this->get_submission_configs($submission);

        $submission_repo = $this->factory()->submission()->repository();
        $submission_history = $submission_repo->get_submission_history(
            $assignment->get_context(),
            $submission,
            $submission_configs,
            $history_list
        );

        $pdf_repo = $this->factory()->file()->pdf()->repository();
        $data = $pdf_repo->get_html_diff_by_history_list($submission_history);

        preg_match_all('#<b>Step (?<steps>1.[0-9]+|[0-9]+)</b>#', $data, $matches);

        $expected_steps = [
            '1.0',
            '1.1',
            '1.2',
            '1.3',
            '1.4',
            '1.5',
            '2',
            '3',
        ];

        self::assertSame(
            $expected_steps,
            $matches['steps']
        );
    }
}
