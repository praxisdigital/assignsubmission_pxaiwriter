<?php

namespace assignsubmission_pxaiwriter\app\ai\openai;

use Exception;
use JsonSerializable;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

class chat_response implements interfaces\chat_response, JsonSerializable
{
    private string $response;
    private ?array $data = null;
    private ?array $messages = null;
    private ?bool $finished = null;
    protected ?interfaces\chat_response $previous;

    public function __construct(string $response, ?interfaces\chat_response $previous = null)
    {
        $this->response = $response;
        $this->previous = $previous;
    }

    public function get_message(): string
    {
        return current($this->get_messages()) ?: '';
    }

    public function get_as_json(): string
    {
        return $this->response;
    }

    public function get_data(): array
    {
        return $this->data ??= $this->get_response_data();
    }

    public function get_previous_response(): ?interfaces\chat_response
    {
        return $this->previous;
    }

    public function get_responses(): array
    {
        return $this->get_responses_from_previous($this);
    }

    public function is_finished(): bool
    {
        if ($this->finished === null) {
            $choices = $this->get_choices();
            $this->finished = isset($choices[0]['finish_reason']) && $choices[0]['finish_reason'] !== 'length';
        }
        return $this->finished;
    }

    public function set_previous_response(interfaces\chat_response $response): void
    {
        $this->previous = $response;
    }

    public function jsonSerialize(): mixed
    {
        return $this->get_data();
    }

    private function get_responses_from_previous(?interfaces\chat_response $response, array $responses = []): array
    {
        if ($response === null) {
            return array_reverse($responses);
        }

        $responses[] = $response;
        return $this->get_responses_from_previous(
            $response->get_previous_response(),
            $responses
        );
    }

    private function get_messages(): array
    {
        if ($this->messages === null) {
            $this->messages = [];
            $choices = $this->get_choices();
            foreach ($choices as $choice) {
                if (!isset($choice['message']['role'], $choice['message']['content'])) {
                    continue;
                }
                if ($choice['message']['role'] !== 'assistant') {
                    continue;
                }
                $this->messages[] = $choice['message']['content'];
            }
        }
        return $this->messages;
    }

    private function get_choices(): array
    {
        return $this->get_data()['choices'] ?? [];
    }

    private function get_response_data(): array
    {
        try {
            return json_decode($this->response, true);
        }
        catch (Exception) {
            return [];
        }
    }
}
