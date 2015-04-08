<?php

namespace Dwo\TaggedServices\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TaggedServicesPass
 *
 * @author David Wolter <david@lovoo.com>
 */
class TaggedServicesPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    protected $baseTag = 'tagged_services';

    /**
     * @var string
     */
    protected $containerClass = 'Dwo\TaggedServices\Container\PimpleContainer';

    /**
     * @var string
     */
    protected $invokeClass = 'Dwo\TaggedServices\LazyCaller';

    /**
     * @var array
     */
    protected $possibleNames = array('type', 'key');

    /**
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        $this->initConfig($config);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds($this->baseTag) as $id => $services) {
            foreach ((array) $services as $service) {
                $this->findAndReplaceTaggedServices($id, $service, $container);
            }
        }
    }

    /**
     * find als tagged services and add/replace serviceContainer
     *
     * @param string           $id
     * @param array            $service
     * @param ContainerBuilder $container
     */
    private function findAndReplaceTaggedServices($id, array $service, ContainerBuilder $container)
    {
        $serviceConfig = $this->createServiceConfig($id, $service);

        if ($serviceConfig->has(ServiceConfig::CONTAINER)) {
            $replace = $this->getDefinitionsAsContainer($container, $serviceConfig);
        } else {
            $replace = $this->getDefinitionsAsArray($container, $serviceConfig);
        }

        $definition = $container->findDefinition($id);
        $definition->replaceArgument($this->findArgumentToReplace($definition, $serviceConfig), $replace);
    }

    /**
     * @param ContainerBuilder $container
     * @param ServiceConfig    $serviceConfig
     *
     * @return Definition
     */
    private function getDefinitionsAsContainer(ContainerBuilder $container, ServiceConfig $serviceConfig)
    {
        $tag = $serviceConfig->get(ServiceConfig::FIND_TAG);

        if (!$container->hasDefinition($serviceId = $tag.'.service_container')) {
            $definition = $this->createContainerDefinition($container, $serviceConfig);
            $definition->setPublic(true);

            $container->setDefinition($serviceId, $definition);

        } else {
            $definition = $container->getDefinition($serviceId);
        }

        return $definition;
    }

    /**
     * @param ContainerBuilder $container
     * @param ServiceConfig    $serviceConfig
     *
     * @return Definition[]
     */
    private function getDefinitionsAsArray(ContainerBuilder $container, ServiceConfig $serviceConfig)
    {
        $services = array();

        foreach ($this->findServiceIdsByTag($container, $serviceConfig) as $name => $serviceId) {
            $services[$name] = $this->getDefinitionForServiceId($serviceId, $serviceConfig);
        }

        return $services;
    }

    /**
     * return a InvokeClass for lazyLoading or a reference of the service
     *
     * @param string        $serviceId
     * @param ServiceConfig $serviceConfig
     *
     * @return object|Definition
     */
    private function getDefinitionForServiceId($serviceId, ServiceConfig $serviceConfig)
    {

        if ($serviceConfig->has(ServiceConfig::LAZY) && !$serviceConfig->get(ServiceConfig::LAZY)) {
            return new Reference($serviceId);
        } else {
            return new Definition($this->invokeClass, array(new Reference('service_container'), $serviceId));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param ServiceConfig    $serviceConfig
     *
     * @return Definition
     */
    private function createContainerDefinition(ContainerBuilder $container, ServiceConfig $serviceConfig)
    {
        $containerDefinition = new Definition($this->containerClass);

        #$definitionBase = new Definition($this->containerClass);
        #$container->addDefinitions(array($baseTag => $definitionBase));

        # $definitionBase = $container->getDefinition($baseTag);
        #$definitionBase->addMethodCall('addServiceId', array($tag, $serviceId));

        foreach ($this->findServiceIdsByTag($container, $serviceConfig) as $name => $serviceId) {
            $containerDefinition->addMethodCall(
                'offsetSet',
                array(
                    $name,
                    $this->getDefinitionForServiceId($serviceId, $serviceConfig)
                )
            );
        }

        return $containerDefinition;
    }

    /**
     * @param Definition    $definition
     * @param ServiceConfig $serviceConfig
     *
     * @return int
     *
     * @throws \Exception
     */
    private function findArgumentToReplace(Definition $definition, ServiceConfig $serviceConfig)
    {
        $container = $serviceConfig->is(ServiceConfig::CONTAINER);

        if ($serviceConfig->has(ServiceConfig::ARGUMENT)) {
            return (int) $serviceConfig->get(ServiceConfig::ARGUMENT);
        } else {
            $possible = array();
            $ref = new \ReflectionClass($definition->getClass());

            foreach ($ref->getConstructor()->getParameters() as $argumentNr => $argument) {
                if ($argument->isArray() && !$container) {
                    $possible[] = $argumentNr;
                } else {
                    if ((null !== $class = $argument->getClass()) && $class->isInstance(new $this->containerClass())) {
                        $possible[] = $argumentNr;
                    }
                }
            }
        }

        if (1 === count($possible)) {
            return current($possible);
        }

        throw new \Exception('unable to find the argument to replace');
    }

    /**
     * @param ContainerBuilder $container
     * @param ServiceConfig    $serviceConfig
     *
     * @return array
     */
    private function findServiceIdsByTag(ContainerBuilder $container, ServiceConfig $serviceConfig)
    {
        $tag = $serviceConfig->get(ServiceConfig::FIND_TAG);

        $serviceIds = array();

        foreach ($container->findTaggedServiceIds($tag) as $id => $services) {
            foreach ((array) $services as $service) {
                if (null === $name = $this->findName($service, $serviceConfig)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Service "%s" must define a name attribute like "key" or "type" for "%s" tags.',
                            $id,
                            $tag
                        )
                    );
                }

                $serviceIds[$service[$name]] = $id;
            }
        }

        return $serviceIds;
    }

    /**
     * @param array         $service
     * @param ServiceConfig $serviceConfig
     *
     * @return string|null
     */
    private function findName($service, ServiceConfig $serviceConfig)
    {
        if ($serviceConfig->has(ServiceConfig::NAME)) {
            return $serviceConfig->get(ServiceConfig::NAME);
        } else {
            foreach ((array) $this->possibleNames as $possibleName) {
                if (isset($service[$possibleName])) {
                    return $possibleName;
                }
            }
        }

        return null;
    }

    /**
     * @param string $id
     * @param array  $service
     *
     * @return ServiceConfig
     */
    private function createServiceConfig($id, array $service)
    {
        $serviceConfig = new ServiceConfig($service);

        if (!$serviceConfig->has($tag = ServiceConfig::FIND_TAG)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Service "%s" must define at least the "%s" attribute for tag "%s".',
                    $id,
                    $tag,
                    $this->baseTag
                )
            );
        }

        return $serviceConfig;
    }

    /**
     * @param array $config
     */
    private function initConfig(array $config = null)
    {
        if (is_array($config)) {
            if (array_key_exists('baseTag', $config)) {
                $this->baseTag = $config['baseTag'];
            }
            if (array_key_exists('containerClass', $config)) {
                $this->containerClass = $config['containerClass'];
            }
            if (array_key_exists('invokeClass', $config)) {
                $this->invokeClass = $config['invokeClass'];
            }
            if (array_key_exists('possibleNames', $config)) {
                $this->possibleNames = $config['possibleNames'];
            }
        }

        /**
         * :TODO: config via container
         */
        #$baseClass = $container->getParameter('lab.tagged_service_container.class');
        #$baseTag = $container->getParameter('lab.tagged_service_container.tag_name');
        #$baseClass = 'Lab\Bundle\TaggedServiceContainerBundle\PimpleContainer';
    }
}
