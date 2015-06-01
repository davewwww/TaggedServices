<?php

namespace Dwo\TaggedServices\Tests;

use Symfony\Component\DependencyInjection\Container;
use Dwo\TaggedServices\LazyCaller;
use Dwo\TaggedServices\Tests\Fixtures\MyClass;

/**
 * @author Dave Www <davewwwo@gmail.com>
 */
class LazyCallerTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $container = new Container();
        $container->set('my.service', new MyClass('bar'));

        /** @var MyClass $caller */
        $caller = new LazyCaller($container, 'my.service');

        self::assertEquals('bar', $caller->getContent());
    }
}