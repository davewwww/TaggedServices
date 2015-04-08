TaggedServices
==============

Find and use your tagged services via service configuration instead of building compiler passes every time.

### Installation

Add the CompilerPass to the AppKernel.

```php
AppKernel.php

protected function buildContainer()
{
    $container = parent::buildContainer();
    $container->addCompilerPass(new TaggedServicesPass());
}
```

### tag your services

Tag your services with your own tag name as usual and give them an name with the 'type' parameter.

```yaml
#services.yml

services:
  my.service.foo:
    class: My\Service\Foo
    tags:
      - {name: "my.services", type: "foo"}
      
  my.service.bar:
    class: My\Service\Bar
    tags:
      - {name: "my.services", type: "bar"}
```

### use your tagged services

Now add the 'tagged_services' tag with the 'find_tag' parameter to the service that gets injected all tagged services.

```yaml
#services.yml

services:
  my.service.container:
    class: My\Service\MyServiceContainer
    arguments: [[]]
    tags:
      - { name: 'tagged_services', find_tag: 'my.services' }
```

Your MyServiceContainer will get a array with your services.

```php
class MyServiceContainer {

    private $myServices = array();
    private $myFooService;
    
    function __construct(array $myServices) 
    {    
        $this->myServices = $myServices;
        $this->myFooService = $myServices['foo'];
    }
}
```