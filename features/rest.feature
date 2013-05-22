Feature: RESTful API

  Scenario: Django-alike CBV controller
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
    When I send a "GET" request to "/"
    Then I should see a 200 response
    And with content:
    """
    <p>This is a GET request</p>
    """

  Scenario:
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
    When I send a "POST" request to "/"
    Then I should see a 200 response
    And with content:
    """
    This is a POST request
    """

  Scenario:
    Given a class "NoMethodHandlerController.php" with content:
    """
    <?php

    class NoMethodHandlerController
    {
    }
    """
    And map the route "/" to "NoMethodHandlerController"
    When I send a "GET" request to "/"
    Then I should see a 405 response

  Scenario:
    Given a class "MissingMethod.php" with content:
    """
    <?php

    class MissingMethod
    {
        public function handleMissingMethod()
        {
            return 'missing method';
        }
    }
    """
    And map the route "/" to "MissingMethod"
    When I send a "GET" request to "/"
    Then I should see a 200 response
    And with content:
    """
    missing method
    """

#  Background:
#    Given a RESTful API endpoint
#    And a json string:
#    """
#    {"message": "Hello World"}
#    """
#
#  Scenario:
#    Given a path at "/"
#    And route option "sdispatcher.route.supported_formats" -> "json"
#
#    When I send a request to "/"
#    And with header "Accept" -> "html/text"
#
#    Then I should see 406 response
#
#  Scenario:
#    Given a path at "/somewhere"
#    And route option "sdispatcher.route.supported_formats" -> "json"
#
#    When I send a request to "/somewhere"
#    And with header "Accept" -> "application/json"
#
#    Then I should see 200 response
#    And the response content is:
#    """
#    {"message":"Hello World"}
#    """
#
#  Scenario:
#    Given a path at "/"
#    And route option "sdispatcher.route.supported_formats" -> "json"
#
#    When I send a request to "/"
#    And with header "Accept" -> "html/text"
#    And with query string "format" -> "json"
#
#    Then I should see 200 response
#
#  Scenario:
#    Given a path at "/"
#    And route option "sdispatcher.route.supported_formats" -> "json"
#
#    When I send a request to "/"
#    And with header "Accept" -> "html/text"
#    And with query string "format" -> "html"
#
#    Then I should see 406 response
#
#  Scenario:
#    Given a json string:
#    """
#    {"hey": "wow"}
#    """
#    And a path at "/"
#    And route option "sdispatcher.route.supported_formats" -> "xml"
#
#    When I send a request to "/"
#    And with header "Accept" -> "text/xml"
#
#    Then I should see 200 response
#    And the response content is:
#    """
#    <?xml version="1.0" encoding="utf-8"?>
#    <response><hey>wow</hey></response>
#
#    """
#
#  Scenario:
#    Given a json string:
#    """
#    []
#    """
#    And a paginated response
#    And a path at "/"
#    And route option "sdispatcher.route.supported_formats" -> "json"
#    And route option "sdispatcher.route.paginator_class" -> "SDispatcher\Common\InMemoryPaginator"
#    And route option "sdispatcher.route.page_limit" -> "10"
#    And route option "sdispatcher.route.paginated_data_container_name" -> "objects"
#    And route option "sdispatcher.route.paginated_meta_container_name" -> "meta"
#
#    When I send a request to "/"
#    And with header "Accept" -> "application/json"
#
#    Then I should see 200 response
#    And the response content is:
#    """
#    {"meta":{"offset":0,"limit":10,"total":0,"prevLink":null,"nextLink":null},"objects":[]}
#    """
#
#  Scenario:
#    Given a json string:
#    """
#    [1, 2]
#    """
#    And a paginated response
#    And a path at "/"
#    And route option "sdispatcher.route.supported_formats" -> "json"
#    And route option "sdispatcher.route.paginator_class" -> "SDispatcher\Common\InMemoryPaginator"
#    And route option "sdispatcher.route.page_limit" -> "10"
#    And route option "sdispatcher.route.paginated_data_container_name" -> "objects"
#    And route option "sdispatcher.route.paginated_meta_container_name" -> "meta"
#
#    When I send a request to "/"
#    And with header "Accept" -> "application/json"
#
#    Then I should see 200 response
#    And the response content is:
#    """
#    {"meta":{"offset":0,"limit":10,"total":2,"prevLink":null,"nextLink":null},"objects":[1,2]}
#    """
#
#  Scenario:
#    Given a json string:
#    """
#    [1, 2, 3, 4, 5, 6, 7, 8]
#    """
#    And a paginated response
#    And a path at "/"
#    And route option "sdispatcher.route.supported_formats" -> "xml"
#    And route option "sdispatcher.route.paginator_class" -> "SDispatcher\Common\InMemoryPaginator"
#    And route option "sdispatcher.route.page_limit" -> "2"
#    And route option "sdispatcher.route.paginated_data_container_name" -> "objects"
#    And route option "sdispatcher.route.paginated_meta_container_name" -> "meta"
#
#    When I send a request to "/?offset=3"
#    And with header "Accept" -> "application/xml"
#
#    Then I should see 200 response
#    And the response content is:
#    """
#    <?xml version="1.0" encoding="utf-8"?>
#    <response><meta><offset>3</offset><limit>2</limit><total>8</total><prevLink>http://localhost/?limit=2&amp;offset=1</prevLink><nextLink>http://localhost/?limit=2&amp;offset=5</nextLink></meta><objects><item>4</item><item>5</item></objects></response>
#
#    """
