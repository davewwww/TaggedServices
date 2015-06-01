<?php

namespace Dwo\TaggedServices\Container;

use Pimple\Container;

/**
 * Class PimpleContainer
 *
 * @author Dave Www <davewwwo@gmail.com>
 */
class PimpleContainer extends Container implements \IteratorAggregate
{
    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $values = array();

        foreach ($this->keys() as $id) {
            $values[$id] = $this[$id];
        }

        return new \ArrayIterator($values);
    }
}
