<?php

namespace Dwo\TaggedServices\Tests;

use Dwo\TaggedServices\Container\PimpleContainer;

/**
 * @author Dave Www <davewwwo@gmail.com>
 */
class PimpleContainerTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $container = new PimpleContainer();
        $container->offsetSet('foo', new \stdClass());

        self::assertInstanceOf('ArrayIterator', $container->getIterator());

        foreach ($container as $name => $class) {
            self::assertEquals('foo', $name);
            self::assertInstanceOf('\stdClass', $class);
        }
    }
}