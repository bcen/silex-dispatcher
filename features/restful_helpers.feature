Feature: RESTful Middlewares/Helpers

  Background:
    Given a set of restful middlewares

  Scenario: Content Negotiation
    Given a class "NumberListResourceController.php" with content:
    """
    <?php

    use SDispatcher\Common\Annotation as REST;

    /**
     * @REST\SupportedFormats("some_crazy_format")
     */
    class NumberListResourceController
    {
        public function get()
        {
            return range(1, 10);
        }
    }
    """
    And map the route "/" to "NumberListResourceController"
    And a "GET" request for path "/"
    And with headers:
    """
    {
        "Accept": "text/html"
    }
    """
    When I send the request
    Then I should see a 406 response

  Scenario: Automated Serialization
    Given a class "SerializationDemoController.php" with content:
    """
    <?php

    use SDispatcher\Common\Annotation as REST;
    use SDispatcher\DataResponse;

    /**
     * @REST\SupportedFormats("json")
     */
    class SerializationDemoController
    {
        public function get()
        {
            return new DataResponse(array(
                'message' => 'Hi'
            ));
        }
    }
    """
    And map the route "/" to "SerializationDemoController"
    And a "GET" request for path "/"
    And with headers:
    """
    {
        "Accept": "application/json"
    }
    """
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    {"message":"Hi"}
    """

  Scenario: Multiple Formats
    Given a class "MultipleFormatController.php" with content:
    """
    <?php

    use SDispatcher\Common\Annotation as REST;
    use SDispatcher\DataResponse;

    /**
     * @REST\SupportedFormats({"xml", "json"})
     */
    class MultipleFormatController
    {
        public function get()
        {
            return new DataResponse(array(
                'message' => 'Hi'
            ));
        }
    }
    """
    And map the route "/" to "MultipleFormatController"
    And a "GET" request for path "/"
    And with headers:
    """
    {
        "Accept": "application/json,application/xml"
    }
    """
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    <?xml version="1.0" encoding="utf-8"?>
    <response><message>Hi</message></response>

    """

  Scenario: Automated Pagination
    Given a class "PaginationDemoController.php" with content:
    """
    <?php

    use SDispatcher\Common\Annotation as REST;
    use SDispatcher\DataResponse;

    /**
     * @REST\SupportedFormats({"xml", "json"})
     * @REST\PageLimit(2)
     */
    class PaginationDemoController
    {
        public function get()
        {
          return array(1, 2, 3, 4, 5, 6, 7, 8);
        }
    }
    """
    And map the route "/" to "PaginationDemoController"
    And a "GET" request for path "/?offset=3"
    And with headers:
    """
    {
        "Accept": "application/json,application/xml"
    }
    """
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    <?xml version="1.0" encoding="utf-8"?>
    <response><meta><offset>3</offset><limit>2</limit><total>8</total><prevLink>http://localhost/?limit=2&amp;offset=1</prevLink><nextLink>http://localhost/?limit=2&amp;offset=5</nextLink></meta><objects><item>4</item><item>5</item></objects></response>

    """

  Scenario: Automated Pagination with declarative resource option
    Given a class "Declarative.php" with content:
    """
    <?php

    use SDispatcher\Common\Annotation as REST;
    use SDispatcher\DataResponse;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;

    abstract class AbstractDeclarative
    {
        protected static $supportedFormats = array('xml', 'json');
        protected static $pageLimit = 2;

    }

    class Declarative extends AbstractDeclarative
    {
        protected static $requiredServices = array('dispatcher', 'request.http_port');

        public function __construct(EventDispatcherInterface $dispatcher, $port)
        {
        }

        public function get()
        {
            return range(1, 8);
        }
    }
    """
    And a declarative resource option class
    And map the route "/" to "Declarative"
    And a "GET" request for path "/?offset=3"
    And with headers:
    """
    {
        "Accept": "application/json,application/xml"
    }
    """
    When I send the request
    Then I should see a 200 response
    And with content:
    """
    <?xml version="1.0" encoding="utf-8"?>
    <response><meta><offset>3</offset><limit>2</limit><total>8</total><prevLink>http://localhost/?limit=2&amp;offset=1</prevLink><nextLink>http://localhost/?limit=2&amp;offset=5</nextLink></meta><objects><item>4</item><item>5</item></objects></response>

    """
