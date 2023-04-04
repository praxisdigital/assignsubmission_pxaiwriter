<?php

namespace assignsubmission_pxaiwriter\app\ai\history;


use assignsubmission_pxaiwriter\app\ai\attempt\interfaces\entity as attempt_entity;
use assignsubmission_pxaiwriter\app\ai\history\interfaces\entity as history_entity;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use Exception;
use moodle_transaction;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class archive implements interfaces\archive
{
    private base_factory $factory;
    private int $assignment_id;
    private int $step;
    private int $user_id;
    private int $current_time;
    private moodle_transaction $transaction;
    private ?attempt_entity $attempt = null;

    public function __construct(
        base_factory $factory,
        int $assignment_id,
        int $step,
        ?int $user_id = null,
        ?moodle_transaction $transaction = null
    )
    {
        $this->factory = $factory;
        $this->assignment_id = $assignment_id;
        $this->step = $step;
        $this->user_id = $user_id ?? $factory->moodle()->user()->id;
        $this->transaction = $transaction ?? $factory->moodle()->db()->start_delegated_transaction();
        $this->current_time = time();
    }

    public function start_attempt(string $text): void
    {
        $attempt = $this->get_attempt();
        $attempt->set_data($text);
        $attempt->set_status_ok();
        $this->save_attempt($attempt);
    }

    public function commit(string $text, ?string $ai_text = null): history_entity
    {
        $history = $this->factory->ai()->history()->entity();
        $history->set_data($text);

        $old_history = $this->factory->ai()->history()->repository()->get_by_hashcode(
            $this->user_id,
            $this->assignment_id,
            $history->get_hashcode()
        );
        if ($old_history !== null)
        {
            return $old_history;
        }

        return $this->push_history($history, $ai_text);
    }

    public function force_commit(string $text, ?string $ai_text = null): history_entity
    {
        $history = $this->factory->ai()->history()->entity();
        $history->set_data($text);
        return $this->push_history($history, $ai_text);
    }

    public function rollback(string $input_text, Exception $exception): void
    {
        $this->transaction->rollback($exception);
        $this->get_attempt()->set_status_failed();
        $this->save_attempt($this->get_attempt());
    }

    private function get_attempt(): attempt_entity
    {
        if ($this->attempt === null)
        {
            $this->attempt = $this->factory->ai()->attempt()->entity();
            $this->attempt->set_userid($this->user_id);
            $this->attempt->set_assignment($this->assignment_id);
            $this->attempt->set_step($this->step);
            $this->attempt->set_timecreated($this->current_time);
        }
        return $this->attempt;
    }

    /**
     * @param history_entity $history
     * @param string|null $ai_text
     * @return history_entity
     * @throws \dml_transaction_exception
     */
    private function push_history(history_entity $history, ?string $ai_text): history_entity
    {
        $history->set_userid($this->user_id);
        $history->set_assignment($this->assignment_id);
        $history->set_step($this->step);
        $history->set_ai_text($ai_text);
        $history->set_timecreated($this->current_time);
        $history->set_timemodified($this->current_time);
        $history->set_status_ok();

        $this->factory->ai()->history()->repository()->insert($history);

        $this->transaction->allow_commit();

        return $history;
    }

    private function save_attempt(attempt_entity $attempt): void
    {
        $this->factory->ai()->attempt()->repository()->insert($attempt);
    }
}
