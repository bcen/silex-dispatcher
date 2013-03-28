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
        $params = $this
            ->getServiceDefinitionProvider()
            ->getDefaultParameters($app);
        foreach ((array)$params as $key => $value) {
            if (!isset($app[$key])) {
                $app[$key] = $value;
            }
        }
        $services = $this->getServiceDefinitionProvider()->getServices($app);
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
