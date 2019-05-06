Feature: Battle Resource
  In order to prove my programmers' worth against projects
  As an API client
  I need to be able to start and view battles

  Background:
    Given the user "weaverryan" exists
    And "weaverryan" has an authentication token "sywpcu2fq1c8044o4s8os8scoco8wso"
    And I set the "Authorization" header to be "token sywpcu2fq1c8044o4s8os8scoco8wso"

  Scenario: Creating a battle
    Given there is a programmer called "Fred"
    And there is a project called "my_project"
    And I have the payload:
      """
      {
        "programmerId": "%programmers.Fred.id%",
        "projectId": "%projects.my_project.id%"
      }
      """
    When I request "POST /api/battles"
    Then the response status code should be 201
    And the "Location" header should exist
    And the "didProgrammerWin" property should exist

  Scenario: Validation errors
    Given there is a programmer called "Fred"
    And there is a project called "my_project"
    And I have the payload:
      """
      {
        "programmerId": "foobar",
        "projectId": "%projects.my_project.id%"
      }
      """
    When I request "POST /api/battles"
    Then the response status code should be 422
    And the "errors.programmerId" property should be a string equalling "Invalid or missing programmerId"

    Scenario: Get ONE battle
      Given there is a programmer called "Fred"
      And there is a project called "project_facebook"
      And there has been a battle between "Fred" and "project_facebook"
      When I request "GET /api/battles/%battles.last.id%"
      Then the response status code should be 200
      And the following properties should exist:
      """
      didProgrammerWin
      notes
      """
      And the "Content-Type" header should be "application/hal+json"
      And the link "programmer" should exist and its value should be "/api/programmers/Fred"
      And the embedded "programmer" should have a "nickname" property equal to "Fred"
