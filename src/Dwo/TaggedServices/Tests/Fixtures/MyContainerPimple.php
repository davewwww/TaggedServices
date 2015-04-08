<?php

namespace Dwo\TaggedServices\Tests\Fixtures;

use Dwo\TaggedServices\Container\PimpleContainer;

class MyContainerPimple
{
    protected $services;

    public function __construct(PimpleContainer $services)
    {
        $this->services = $services;
    }

    public function getServices()
    {
        return $this->services;
    }
}