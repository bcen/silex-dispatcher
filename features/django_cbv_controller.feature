Feature: Django CBV Controller

  Scenario: Handle GET request
    Given a class "RootController.php" with content:
    """
    <?php

    class RootController
    {
        public function get()
        {
            return '<p>This is a GET request</p>';
        }
    }
    """
    And map the route "/" to "RootController"
    And a "GET" request for path "/"
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    <p>This is a GET request</p>
    """

  Scenario: Handle POST request
    Given a class "PostController.php" with content:
    """
    <?php

    class PostController
    {
        public function post()
        {
            return 'This is a POST request';
        }
    }
    """
    And map the route "/" to "PostController"
    And a "POST" request for path "/"
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    This is a POST request
    """

  Scenario: Handle missing method
    Given a class "MissingMethod.php" with content:
    """
    <?php

    class MissingMethod
    {
        public function post()
        {
            return 'post';
        }

        public function handleMissingMethod()
        {
            return 'missing method';
        }
    }
    """
    And map the route "/" to "MissingMethod"
    And a "GET" request for path "/"
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    missing method
    """

  Scenario: Handle request
    Given a class "HanldeRequest.php" with content:
    """
    <?php

    class HanldeRequest
    {
        public function handleRequest()
        {
            return 'handle request';
        }

        public function get()
        {
            return 'get';
        }
    }
    """
    And map the route "/" to "HanldeRequest"
    And a "GET" request for path "/"
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    handle request
    """

  Scenario: No method handler
    Given a class "NoMethodHandler.php" with content:
    """
    <?php

    class NoMethodHandler
    {
    }
    """
    And map the route "/" to "NoMethodHandler"
    And a "GET" request for path "/"
    When I send the request
    Then I should see a 500 response

  Scenario: Resolve dependency
    Given a class "Dependency.php" with content:
    """
    <?php

    use SDispatcher\Common\RequiredServiceMetaProviderInterface;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;

    class Dependency implements RequiredServiceMetaProviderInterface
    {
        public function __construct(EventDispatcherInterface $dispatcher)
        {
        }

        public function get()
        {
            return 'get';
        }

        public static function getRequiredServices()
        {
            return array('dispatcher');
        }
    }
    """
    And map the route "/" to "Dependency"
    And a "GET" request for path "/"
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    get
    """

  Scenario: Resolve dependency with annotation
    Given a class "AnnotationDependency.php" with content:
    """
    <?php

    use SDispatcher\Common\Annotation as REST;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;

    /**
     * @REST\RequiredServices("dispatcher")
     */
    class AnnotationDependency
    {
        public function __construct(EventDispatcherInterface $dispatcher)
        {
        }

        public function get()
        {
            return 'get';
        }
    }
    """
    And map the route "/" to "AnnotationDependency"
    And a "GET" request for path "/"
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    get
    """

  Scenario: CBV resolver should be compatible with Closure controller
    Given a class "ClosureController.php" with content:
    """
    <?php
    function closure_controller () {
        return 'closure';
    };
    """
    And map the route "/" to "closure_controller"
    And a "GET" request for path "/"
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    closure
    """

  Scenario: CBV resolver should be comptaible with class method controller
    Given a class "ClassMethodDemo.php" with content:
    """
    <?php

    class ClassMethodDemo
    {
        public function method()
        {
            return 'method';
        }
    }
    """
    And map the route "/" to "ClassMethodDemo::method"
    And a "GET" request for path "/"
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    method
    """
