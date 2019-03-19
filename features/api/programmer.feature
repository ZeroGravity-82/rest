Feature: Programmer
  In order to battle projects
  As an API client
  I need to be able to create programmers and power them up

  Background:
     Given the user "weaverryan" exists

  Scenario: POST a programmer
  {
    Given I have the payload:
      """
      {
        "nickname": "ZeroGravity",
        "avatarNumber": 2,
        "tagLine": "I'm from a test!"
      }
      """
    When I request "POST /api/programmers"
    Then the response status code should be 201
    And the "Location" header should be "/api/programmers/ZeroGravity"
    And the "nickname" property should be a string equalling "ZeroGravity"

  Scenario: GET one programmer
    Given the following programmers exist:
      | nickname   | avatarNumber |
      | UnitTester | 3            |
    When I request "GET /api/programmers/UnitTester"
    Then the response status code should be 200
    And the following properties should exist:
      """
      nickname
      avatarNumber
      tagLine
      powerLevel
      """
    And the "nickname" property should be a string equalling "UnitTester"

  Scenario: GET all programmers
    Given the following programmers exist:
      | nickname   | avatarNumber |
      | UnitTester | 3            |
      | ZG         | 2            |
      | ZeroGravity| 1            |
    When I request "GET /api/programmers"
    Then the response status code should be 200
    And the "programmers" property should be an array
    And the "programmers" property should contain 3 items

  Scenario: DELETE a programmer
    Given the following programmers exist:
      | nickname   | avatarNumber | tagLine |
      | ZeroGravity| 1            | bar     |
    When I request "DELETE /api/programmers/ZeroGravity"
    Then the response status code should be 204

  Scenario: PATCH to update a programmer
    Given the following programmers exist:
      | nickname   | avatarNumber | tagLine |
      | UnitTester | 3            | foo     |
    And I have the payload:
      """
      {
        "avatarNumber": 5
      }
      """
    When I request "PATCH /api/programmers/UnitTester"
    Then the "tagLine" property should be a string equalling "foo"
    And the "avatarNumber" property should equal "5"


  Scenario: PUT to update a programmer
    Given the following programmers exist:
      | nickname   | avatarNumber | tagLine |
      | UnitTester | 3            | foo     |
      | ZeroGravity| 1            | bar     |
    And I have the payload:
      """
      {
        "nickname": "UnitTester",
        "avatarNumber": 5
      }
      """
    When I request "PUT /api/programmers/UnitTester"
    Then the response status code should be 200
    And the "avatarNumber" property should equal "5"
    And the "nickname" property should be a string equalling "UnitTester"