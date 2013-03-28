<?php
namespace SDispatcher;

use Silex\Application;

interface ServiceDefinitionProviderInterface
{
    public function getDefaultParameters(Application $app);
    public function getServices(Application $app);
}
