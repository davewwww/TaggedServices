<?php

namespace Dwo\TaggedServices\Tests\DependencyInjection\Compiler;

use Dwo\TaggedServices\DependencyInjection\Compiler\ServiceConfig;

/**
 * @author Dave Www <davewwwo@gmail.com>
 */
class ServiceConfigTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $config = new ServiceConfig(array('foo' => 'bar', 'bar' => true));

        self::assertEquals('bar', $config->get('foo'));
        self::assertEquals(null, $config->get('foobar'));

        self::assertTrue($config->is('bar'));
        self::assertFalse($config->is('foobar'));


        self::assertTrue($config->has('bar'));
        self::assertFalse($config->has('foobar'));
    }
}