<?php
namespace SDispatcher;

use Silex\Application;
use Silex\ServiceProviderInterface;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * @return \SDispatcher\ServiceDefinitionProviderInterface
     */
    abstract public function getServiceDefinitionProvider();

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $services = $this->getServiceDefinitionProvider()->getServices($app);
        foreach ($services as $id => $definition) {
            $app[$id] = $definition;
        }
    }
}
