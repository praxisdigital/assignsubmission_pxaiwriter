<?php

namespace assignsubmission_pxaiwriter\app\helper\diff;


use assignsubmission_pxaiwriter\app\interfaces\factory as base_factory;
use FineDiff\Diff;
use FineDiff\Granularity\Character;
use FineDiff\Granularity\GranularityInterface;
use FineDiff\Granularity\Paragraph;
use FineDiff\Granularity\Sentence;
use FineDiff\Granularity\Word;
use FineDiff\Render\Html;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

global $CFG;
require_once "$CFG->dirroot/mod/assign/submission/pxaiwriter/vendor/autoload.php";

class text implements interfaces\text
{
    private string $granularity;
    private base_factory $factory;

    public function __construct(base_factory $factory)
    {
        $this->factory = $factory;
        $this->granularity = $this->factory->setting()->admin()->get_granularity();
    }

    public function diff(string $old_data, string $new_data): string
    {
        $diff = new Diff();
        $diff->setGranularity($this->get_granularity());

        $optionCode =  $diff->getOperationCodes($old_data, $new_data);
        $renderer = new Html();

        $html = $renderer->process($old_data, $optionCode);
        $html = nl2br(trim($html), false);

        $html = $this->replace_deletion($html);
        return $this->replace_insertion($html);
    }

    private function replace_deletion(string $data): string
    {
        $text = str_replace('<del>', $this->get_deletion_open_tag(), $data);
        return str_replace('</del>', $this->get_deletion_close_tag(), $text);
    }

    private function replace_insertion(string $data): string
    {
        $text = str_replace('<ins>', $this->get_insertion_open_tag(), $data);
        return str_replace('</ins>', $this->get_insertion_close_tag(), $text);
    }

    private function get_deletion_open_tag(): string
    {
        return '<span style="color:red;background-color:#ffdddd;text-decoration:line-through;">';
    }

    private function get_deletion_close_tag(): string
    {
        return '</span>';
    }

    private function get_insertion_open_tag(): string
    {
        return '<span style="color:green;background-color:#ddffdd;text-decoration:none;">';
    }

    private function get_insertion_close_tag(): string
    {
        return '</span>';
    }

    private function get_granularity(): GranularityInterface
    {
        switch ($this->granularity) {
            case 'word':
                $word = new Word();
                $delimiters = $word->getDelimiters();
                $delimiters[] = '.';
                $word->setDelimiters($delimiters);
                return $word;
            case 'sentence':
                return new Sentence();
            case 'paragraph':
                return new Paragraph();
            default:
                return new Character();
        }
    }
}
