<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Silex\Application;
use SDispatcher\SDispatcherServiceProvider;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * @var \Silex\Application
     */
    private $app;

    /**
     * @var \Silex\ControllerCollection
     */
    private $controllerRoute;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var array
     */
    private $responseData;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    private $response;

    /**
     * @var bool
     */
    private $willPaginate;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->willPaginate = false;
    }

    /**
     * @Given /^a RESTful API endpoint$/
     */
    public function aRestfulApiEndpoint()
    {
        $this->app = new Application();
        $this->app->register(new SDispatcherServiceProvider());
        $this->app->before($this->app[\SDispatcher\ServiceDefinitionProvider::CONTENT_NEGOTIATOR]);
        $this->app->before($this->app[\SDispatcher\ServiceDefinitionProvider::DESERIALIZER]);
        $this->app->after($this->app[\SDispatcher\ServiceDefinitionProvider::SERIALIZER]);
    }

    /**
     * @Given /^a json string:$/
     */
    public function aJsonString(PyStringNode $string)
    {
        $this->responseData = json_decode($string->getRaw(), true);
    }

    /**
     * @Given /^a path at "([^"]*)"$/
     */
    public function aPathAt($path)
    {
        $paginate = $this->willPaginate;
        $data = $this->responseData;
        $this->controllerRoute = $this->app->match($path, function () use ($path, $data, $paginate) {
            if ($paginate) {
                return $data;
            }
            return new \SDispatcher\DataResponse($data);
        });
    }

    /**
     * @Given /^a paginated response$/
     */
    public function aPaginatedResponse()
    {
        $this->willPaginate = true;
    }

    /**
     * @Given /^route option "([^"]*)" -> "([^"]*)"$/
     */
    public function routeOption($key, $value)
    {
        if (is_int($value)) {
            $value = (int)$value;
        } elseif (is_bool($value)) {
            $value = (bool)$value;
        }
        $this->controllerRoute->setOption($key, $value);
    }

    /**
     * @Given /^with query string "([^"]*)" -> "([^"]*)"$/
     */
    public function withQueryString($key, $value)
    {
        $this->request->query->set($key, $value);
    }

    /**
     * @When /^I send a request to "([^"]*)"$/
     */
    public function iSendARequestTo($path)
    {
        $this->request = \Symfony\Component\HttpFoundation\Request::create($path);
    }

    /**
     * @Given /^with header "([^"]*)" -> "([^"]*)"$/
     */
    public function withHeader($key, $value)
    {
        $this->request->headers->set($key, $value);
    }

    /**
     * @Then /^I should see (\d+) response$/
     */
    public function iShouldSeeResponse($statusCode)
    {
        $this->app->boot();
        $this->response = $response = $this->app->handle($this->request);
        if (!$response) {
            throw new \Exception();
        }

        if ($response->getStatusCode() !== (int)$statusCode) {
            throw new \Exception();
        }
    }

    /**
     * @Given /^the response content is:$/
     */
    public function theResponseContentIs2(PyStringNode $string)
    {
        $actual = $this->response->getContent();
        $expected = $string->getRaw();
        $actual = strtr($actual, array("\r\n" => "\n", "\r" => "\n"));
        $expected = strtr($expected, array("\r\n" => "\n", "\r" => "\n"));
        if ($actual !== $expected) {
            var_dump($this->response->getContent());
            var_dump($string->getRaw());
            throw new \Exception();
        }
    }
}
