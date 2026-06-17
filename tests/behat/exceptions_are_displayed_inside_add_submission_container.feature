@assignsubmission_pxaiwriter
Feature: Exceptions are displayed inside the Add Submission dialog
  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | pxgrid |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | admin    | C1     | editingteacher |
    And the following "activities" exist:
      | activity   | name    | intro     | introformat | course | content | contentformat | idnumber |
      | assignment | SM1     | PageDesc1 | 1           | C1     | S       | 1             | 1        |
    And I log in as "admin"
    And I turn editing mode on
    ## A lack of AI provider instance configured will trigger an exception in CoCreate.
    And I check that mxaimanager "has not" an AI provider instance configured

  @javascript
  Scenario: mxaimanager lacks a configured AI provider instance and the user tries to use CoCreate
    When I am on "C1" course homepage
    And I click on "General" "link"
    ## Click on smartlink "Get AI version" dropdown
    And I click on "//li[contains(concat(' ',normalize-space(@class),' '),' smartlink ')]//div[@class='row']//button" "xpath_element"
    ## Click on create new
    And I click on "//li[contains(concat(' ',normalize-space(@class),' '),' smartlink ')]//div[@class='row']//a[@class='dropdown-item']" "xpath_element"
    And I set the field "prompt" to "xyz"
    And I click on "//div[@class='modal-footer']//button[@type='submit']" "xpath_element"
    ## Verify that an exception is displayed inside the modal of smartlink
    Then "//div[@class='modal-content']//div[@class='alert alert-danger']" "xpath_element" should be visible