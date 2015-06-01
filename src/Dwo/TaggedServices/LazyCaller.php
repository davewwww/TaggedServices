<?php

namespace Dwo\TaggedServices;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LazyCaller
 *
 * @author Dave Www <davewwwo@gmail.com>
 */
class LazyCaller
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var string
     */
    public $id;

    /**
     * @param ContainerInterface $container
     * @param string             $id
     */
    public function __construct(ContainerInterface $container, $id)
    {
        $this->container = $container;
        $this->id = $id;
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->container->get($this->id), $method), $args);
    }

}
