<?php

namespace Dwo\TaggedServices\Tests\Fixtures;

class MyContainerTwoArray
{
    protected $services;
    protected $services2;

    public function __construct(array $services = null,array $services2 = null)
    {
        $this->services = $services;
        $this->services2 = $services2;
    }

    public function getServices()
    {
        return $this->services;
    }

    public function getServices2()
    {
        return $this->services2;
    }
}