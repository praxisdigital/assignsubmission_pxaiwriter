<?php

namespace assignsubmission_pxaiwriter\app\interfaces;


use ArrayAccess;
use Countable;
use Iterator;

/* @codeCoverageIgnoreStart */
defined('MOODLE_INTERNAL') || die();
/* @codeCoverageIgnoreEnd */

/**
 * @template T
 * @psalm-template T
 */
interface collection extends ArrayAccess, Iterator, Countable
{
    /**
     * @return T
     */
    public function current();

    /**
     * @return T|null
     */
    public function last();

    /**
     * @param int $count
     * @return collection<T>|array<int,T>|T[]
     */
    public function skip(int $count): collection;

    /**
     * @param mixed $offset
     * @return T
     */
    public function offsetGet($offset);

    public function to_array(): array;
}
