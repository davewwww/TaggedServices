<?php

namespace Dwo\TaggedServices\DependencyInjection\Compiler;

/**
 * Class ServiceConfig
 *
 * @author Dave Www <davewwwo@gmail.com>
 */
class ServiceConfig
{
    const FIND_TAG = 'find_tag';
    const NAME = 'key_name';
    const ARGUMENT = 'for_argument';
    const CONTAINER = 'container';
    const LAZY = 'lazy';

    /**
     * :TODO:
     * - force Interface option
     */

    /**
     * @var array
     */
    private $service = array();

    /**
     * @param array $service
     */
    public function __construct(array $service)
    {
        $this->service = $service;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->has($key) ? $this->service[$key] : null;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->service);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function is($key)
    {
        return $this->has($key) && $this->get($key);
    }
}