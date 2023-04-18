<?php

namespace assignsubmission_pxaiwriter\unit\http;


use assignsubmission_pxaiwriter\app\factory;
use assignsubmission_pxaiwriter\app\helper\encoding\json;
use assignsubmission_pxaiwriter\app\http\json_rest;
use assignsubmission_pxaiwriter\app\test\unit_testcase;
use curl;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

class json_rest_test extends unit_testcase
{
    public function test_post_convert_array_parameters_to_json_string(): void
    {
        $params = [
            'message' => 'Hello world'
        ];

        $json_converter = new json();

        $expected = $json_converter->encode($params);

        $curl = $this->createMock(curl::class);
        $curl->method('post')
            ->willReturnCallback(static function($url, $params) use ($expected) {
                self::assertSame(
                    $expected,
                    $params
                );
            });

        $encoding_factory = $this->createMock(\assignsubmission_pxaiwriter\app\helper\encoding\factory::class);
        $encoding_factory->method('json')->willReturn($json_converter);

        $helper_factory = $this->createMock(\assignsubmission_pxaiwriter\app\helper\factory::class);
        $helper_factory->method('encoding')->willReturn($encoding_factory);

        $moodle_factory = $this->createMock(\assignsubmission_pxaiwriter\app\moodle\factory::class);
        $moodle_factory->method('curl')->willReturn($curl);

        $factory = $this->createMock(factory::class);
        $factory->method('moodle')->willReturn($moodle_factory);
        $factory->method('helper')->willReturn($helper_factory);

        $rest = new json_rest($factory);
        $rest->post('https://localhost', $params);
    }
}
