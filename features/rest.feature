Feature: RESTful API

  Background:
    Given a RESTful API endpoint
    And a json string:
    """
    {"message": "Hello World"}
    """

  Scenario:
    Given a path at "/"
    And route option "sdispatcher.route.supported_formats" -> "json"

    When I send a request to "/"
    And with header "Accept" -> "html/text"

    Then I should see 406 response

  Scenario:
    Given a path at "/somewhere"
    And route option "sdispatcher.route.supported_formats" -> "json"

    When I send a request to "/somewhere"
    And with header "Accept" -> "application/json"

    Then I should see 200 response
    And the response content is:
    """
    {"message":"Hello World"}
    """

  Scenario:
    Given a path at "/"
    And route option "sdispatcher.route.supported_formats" -> "json"

    When I send a request to "/"
    And with header "Accept" -> "html/text"
    And with query string "format" -> "json"

    Then I should see 200 response

  Scenario:
    Given a path at "/"
    And route option "sdispatcher.route.supported_formats" -> "json"

    When I send a request to "/"
    And with header "Accept" -> "html/text"
    And with query string "format" -> "html"

    Then I should see 406 response

  Scenario:
    Given a json string:
    """
    {"hey": "wow"}
    """
    And a path at "/"
    And route option "sdispatcher.route.supported_formats" -> "xml"

    When I send a request to "/"
    And with header "Accept" -> "text/xml"

    Then I should see 200 response
    And the response content is:
    """
    <?xml version="1.0" encoding="utf-8"?>
    <response><hey>wow</hey></response>

    """

  Scenario:
    Given a json string:
    """
    []
    """
    And a paginated response
    And a path at "/"
    And route option "sdispatcher.route.supported_formats" -> "json"
    And route option "sdispatcher.route.will_paginate" -> "true"
    And route option "sdispatcher.route.paginator_class" -> "SDispatcher\Common\InMemoryPaginator"
    And route option "sdispatcher.route.page_limit" -> "10"
    And route option "sdispatcher.route.paginated_data_container_name" -> "objects"
    And route option "sdispatcher.route.paginated_meta_container_name" -> "meta"

    When I send a request to "/"
    And with header "Accept" -> "application/json"

    Then I should see 200 response
    And the response content is:
    """
    {"meta":{"offset":0,"limit":10,"total":0,"prevLink":null,"nextLink":null},"objects":[]}
    """

  Scenario:
    Given a json string:
    """
    [1, 2]
    """
    And a paginated response
    And a path at "/"
    And route option "sdispatcher.route.supported_formats" -> "json"
    And route option "sdispatcher.route.will_paginate" -> "true"
    And route option "sdispatcher.route.paginator_class" -> "SDispatcher\Common\InMemoryPaginator"
    And route option "sdispatcher.route.page_limit" -> "10"
    And route option "sdispatcher.route.paginated_data_container_name" -> "objects"
    And route option "sdispatcher.route.paginated_meta_container_name" -> "meta"

    When I send a request to "/"
    And with header "Accept" -> "application/json"

    Then I should see 200 response
    And the response content is:
    """
    {"meta":{"offset":0,"limit":10,"total":2,"prevLink":null,"nextLink":null},"objects":[1,2]}
    """

  Scenario:
    Given a json string:
    """
    [1, 2]
    """
    And a paginated response
    And a path at "/"
    And route option "sdispatcher.route.supported_formats" -> "xml"
    And route option "sdispatcher.route.will_paginate" -> "true"
    And route option "sdispatcher.route.paginator_class" -> "SDispatcher\Common\InMemoryPaginator"
    And route option "sdispatcher.route.page_limit" -> "10"
    And route option "sdispatcher.route.paginated_data_container_name" -> "objects"
    And route option "sdispatcher.route.paginated_meta_container_name" -> "meta"

    When I send a request to "/"
    And with header "Accept" -> "application/xml"

    Then I should see 200 response
    And the response content is:
    """
    <?xml version="1.0" encoding="utf-8"?>
    <response><meta><offset>0</offset><limit>10</limit><total>2</total><prevLink></prevLink><nextLink></nextLink></meta><objects><item>1</item><item>2</item></objects></response>

    """
