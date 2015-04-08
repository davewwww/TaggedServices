<?php

namespace Dwo\TaggedServices\Tests\DependencyInjection\Compiler;

use Dwo\TaggedServices\DependencyInjection\Compiler\TaggedServicesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author David Wolter <david@lovoo.com>
 */
class TaggedServicesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testDefault()
    {
        $containerBuilder = new ContainerBuilder();

        $definitionContainer = new Definition('\Dwo\TaggedServices\Tests\Fixtures\MyContainer');
        $definitionContainer->addArgument(array());
        $definitionContainer->addTag('my.tag', array('find_tag' => $tag = 'my.services'));
        $containerBuilder->setDefinition('my.container', $definitionContainer);

        self::assertEmpty($definitionContainer->getArgument(0));

        $definitionService = new Definition('\Dwo\TaggedServices\Tests\Fixtures\MyClass');
        $definitionService->addArgument('foo');
        $definitionService->addTag($tag, array('type' => 'foo'));
        $containerBuilder->setDefinition('my.service.foo', $definitionService);

        $definitionService = new Definition('\Dwo\TaggedServices\Tests\Fixtures\MyClass');
        $definitionService->addArgument('bar');
        $definitionService->addTag($tag, array('type' => 'bar'));
        $containerBuilder->setDefinition('my.service.bar', $definitionService);

        $pass = new TaggedServicesPass(array('baseTag'=>'my.tag'));
        $pass->process($containerBuilder);

        self::assertNotEmpty($definitionContainer->getArgument(0));
        self::assertCount(2, $definitionContainer->getArgument(0));
    }
}