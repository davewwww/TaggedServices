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
        $this->createFixtures($containerBuilder = new ContainerBuilder());

        $pass = new TaggedServicesPass();
        $pass->process($containerBuilder);

        $myContainer = $containerBuilder->get('my.container');
        $myContainerDefintion = $containerBuilder->getDefinition('my.container');

        self::assertInternalType('array', $myContainerDefintion->getArgument(0));
        self::assertCount(1, $myContainerDefintion->getArgument(0));
        self::assertCount(1, $myContainer->getServices());
        self::assertInstanceOf('Dwo\TaggedServices\LazyCaller', current($myContainer->getServices()));
        self::assertEquals('foo', current($myContainer->getServices())->getContent());
    }

    public function testOwnTag()
    {
        $this->createFixtures($containerBuilder = new ContainerBuilder(), $baseTag = 'my.tagged_services');

        $pass = new TaggedServicesPass(array('baseTag' => $baseTag));
        $pass->process($containerBuilder);

        $myContainer = $containerBuilder->get('my.container');
        $myContainerDefintion = $containerBuilder->getDefinition('my.container');

        self::assertInternalType('array', $myContainerDefintion->getArgument(0));
        self::assertCount(1, $myContainerDefintion->getArgument(0));
        self::assertCount(1, $myContainer->getServices());
    }

    public function testNoLazy()
    {
        $config = array('find_tag' => 'my.services', 'lazy' => false);
        $this->createFixtures($containerBuilder = new ContainerBuilder(), null, null, $config);

        $pass = new TaggedServicesPass();
        $pass->process($containerBuilder);

        $myContainer = $containerBuilder->get('my.container');
        $myContainerDefintion = $containerBuilder->getDefinition('my.container');

        self::assertInternalType('array', $myContainerDefintion->getArgument(0));
        self::assertCount(1, $myContainerDefintion->getArgument(0));
        self::assertCount(1, $myContainer->getServices());
        self::assertInstanceOf('Dwo\TaggedServices\Tests\Fixtures\MyClass', current($myContainer->getServices()));
        self::assertEquals('foo', current($myContainer->getServices())->getContent());
    }

    public function testArgumentNr()
    {
        $config = array('find_tag' => 'my.services', 'for_argument' => 1);
        $this->createFixtures($containerBuilder = new ContainerBuilder(), null, null, $config, "two_array");

        $pass = new TaggedServicesPass();
        $pass->process($containerBuilder);

        $myContainer = $containerBuilder->get('my.container');
        $myContainerDefintion = $containerBuilder->getDefinition('my.container');

        self::assertNull($myContainerDefintion->getArgument(0));
        self::assertInternalType('array', $myContainerDefintion->getArgument(1));
        self::assertCount(1, $myContainerDefintion->getArgument(1));
        self::assertNull($myContainer->getServices());
        self::assertCount(1, $myContainer->getServices2());
    }

    public function testContainer()
    {
        $config = array('find_tag' => 'my.services', 'container' => 1);
        $this->createFixtures($containerBuilder = new ContainerBuilder(), null, null, $config, "pimple");

        $pass = new TaggedServicesPass();
        $pass->process($containerBuilder);

        $myContainer = $containerBuilder->get('my.container');
        $myContainerDefintion = $containerBuilder->getDefinition('my.container');

        self::assertNotNull('Dwo\TaggedServices\Container\PimpleContainer', $myContainerDefintion->getArgument(0));
        self::assertInstanceOf('Dwo\TaggedServices\Container\PimpleContainer', $myContainer->getServices());

        self::assertInstanceOf('Dwo\TaggedServices\LazyCaller', $myContainer->getServices()['foo']);
        self::assertEquals('foo', $myContainer->getServices()['foo']->getContent());
    }

    private function buildMyContainer($type)
    {
        switch ($type) {
            case "two_array":
                $definitionContainer = new Definition('\Dwo\TaggedServices\Tests\Fixtures\MyContainerTwoArray');
                $definitionContainer->addArgument(null);
                $definitionContainer->addArgument(null);
                break;

            case "pimple":
                $definitionContainer = new Definition('\Dwo\TaggedServices\Tests\Fixtures\MyContainerPimple');
                $definitionContainer->addArgument(null);
                break;

            default:
                $definitionContainer = new Definition('\Dwo\TaggedServices\Tests\Fixtures\MyContainer');
                $definitionContainer->addArgument(null);
                break;
        }

        return $definitionContainer;
    }

    private function createFixtures(
        ContainerBuilder $containerBuilder,
        $baseTag = null,
        $findTag = null,
        array $config = null,
        $containerType=null
    ) {
        if (null === $baseTag) {
            $baseTag = 'tagged_services';
        }

        if (null === $findTag) {
            $findTag = 'my.services';
        }

        if (null === $config) {
            $config = array('find_tag' => $findTag);
        }

        $definitionContainer = $this->buildMyContainer($containerType);
        $definitionContainer->addTag($baseTag, $config);
        $containerBuilder->setDefinition('my.container', $definitionContainer);

        $definitionService = new Definition('\Dwo\TaggedServices\Tests\Fixtures\MyClass');
        $definitionService->addArgument('foo');
        $definitionService->addTag($findTag, array('type' => 'foo'));
        $containerBuilder->setDefinition('my.service.foo', $definitionService);
    }
}