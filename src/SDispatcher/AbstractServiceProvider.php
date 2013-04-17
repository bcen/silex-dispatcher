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
        $servicesDefinitionProvider = $this->getServiceDefinitionProvider($app);
        $params = $servicesDefinitionProvider->getDefaultParameters($app);
        foreach ((array)$params as $key => $value) {
            if (!isset($app[$key])) {
                $app[$key] = $value;
            }
        }
        $services = $servicesDefinitionProvider->getServices($app);
        foreach ($services as $id => $definition) {
            $app[$id] = $definition;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
