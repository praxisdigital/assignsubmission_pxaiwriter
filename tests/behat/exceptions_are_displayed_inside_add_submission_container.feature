@assignsubmission_pxaiwriter
Feature: Exceptions are displayed inside the Add Submission dialog
  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | admin    | C1     | editingteacher |
      | admin    | C1     | student        |
    And the following "activities" exist:
      | activity | name | intro     | introformat | course | content | contentformat | idnumber | allowsubmissionsfromdate | duedate        |
      | assign   | A1   | PageDesc1 | 1           | C1     | S       | 1             | 1        | ##yesterday##            | ##+1 week##    |
    And I log in as "admin"
    ## A lack of AI provider instance configured will trigger an exception in CoCreate.
    And I check that mxaimanager "has not" an AI provider instance configured

  @javascript
  Scenario: mxaimanager lacks a configured AI provider instance and the user tries to use CoCreate
    When I am on "C1" course homepage
    And I click on "A1" "link"
    And I click on "Settings" "link"
    # Make the assignment submissable with AI Writer
    And I set the field "assignsubmission_pxaiwriter_enabled" to "1"
    # Save and return to course
    And I click on "//input[@name='submitbutton2']" "xpath_element"
    And I click on "A1" "link"
    And I click on "//button[text()='Add submission']" "xpath_element"
    And I set the field "pxaiwriter-data-step-1" to "Whatever"
    And I click on "//button[@id='pxaiwriter-do-ai-magic']" "xpath_element"
    # Verify exception is visible
    Then "//div[@class='alert alert-danger']" "xpath_element" should be visible
    # Verify there is text content inside the exception
    And "//div[@class='alert alert-danger']//p[normalize-space(text()) != '']" "xpath_element" should exist