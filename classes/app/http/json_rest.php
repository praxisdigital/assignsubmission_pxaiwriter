<?php

namespace assignsubmission_pxaiwriter\app\http;


use assignsubmission_pxaiwriter\app\exceptions\http_request_exception;
use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class json_rest implements interfaces\rest
{
    private base_factory $factory;
    private interfaces\header $header;
    private \curl $curl;

    public function __construct(base_factory $factory, ?interfaces\header $header = null)
    {
        $this->factory = $factory;
        $this->curl = $this->factory->moodle()->curl();
        $this->header = $header ?? new header();

        $this->setup();
    }

    private function setup(): void
    {
        $this->header()->set('Content-Type', 'application/json');
        $this->header()->set('Accept', 'application/json');
    }

    private function curl(): \curl
    {
        $this->curl->resetHeader();
        $this->curl->setHeader($this->header()->to_http_header());
        return $this->curl;
    }

    public function header(): interfaces\header
    {
        return $this->header;
    }

    public function post(string $url, array $params = []): interfaces\response
    {
        $response = $this->curl()->post(
            $url,
            $this->get_encoded_parameters($params)
        );

        if ($this->curl->errno > 0)
        {
            throw new http_request_exception("error_api_request: {$this->curl->error}");
        }
        return new response((string)$response);
    }

    private function get_encoded_parameters(array $params): string
    {
        return $this->factory->helper()->encoding()->json()->encode($params);
    }
}
