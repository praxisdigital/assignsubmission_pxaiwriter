<?php

namespace assignsubmission_pxaiwriter\app\ai\openai;

use assignsubmission_pxaiwriter\app\setting\interfaces\settings;
use JsonSerializable;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();
// @codeCoverageIgnoreEnd

class chat implements JsonSerializable
{
    private settings $settings;
    private array $messages = [];

    public function __construct(settings $settings)
    {
        $this->settings = $settings;
    }

    public function prompt(string $content, string $role): self
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
        ];
        return $this;
    }

    public function user_prompt(string $message): self
    {
        return $this->prompt($message, 'user');
    }

    public function assistant_prompt(string $message): self
    {
        return $this->prompt($message, 'assistant');
    }

    public function get_request_data(): array
    {
        return array_merge([], $this->get_request_body(), [
            'messages' => $this->messages,
        ]);
    }

    private function get_request_body(): array
    {
        return [
            'model' => $this->settings->get_model(),
            'temperature' => $this->settings->get_temperature(),
            'max_tokens' => $this->settings->get_max_tokens(),
            'top_p' => $this->settings->get_top_p(),
            'frequency_penalty' => $this->settings->get_frequency_penalty(),
            'presence_penalty' => $this->settings->get_presence_penalty(),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->get_request_data();
    }

    public function __toString(): string
    {
        return json_encode($this);
    }
}
