<?php

namespace Dwo\TaggedServices\Container;

use Pimple\Container;

/**
 * Class PimpleContainer
 *
 * @author David Wolter <david@lovoo.com>
 */
class PimpleContainer extends Container implements \IteratorAggregate
{
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $values = [];
        foreach ($this->keys() as $id) {
            $values[$id] = $this[$id];
        }

        return new \ArrayIterator($values);
    }
}
