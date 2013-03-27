<?php
namespace SDispatcher;

use Silex\Application;

interface ServiceDefinitionProviderInterface
{
    public function getServices(Application $app);
}
