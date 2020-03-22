@mod @mod_studentquiz
Feature: Create comment as an user
  In order to join the comment area
  As a user
  I need to be able to create comment

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher  | The       | Teacher  | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
      | student2 | Alex      | Dan      | student2@example.com |
      | student3 | Chris     | Bron     | student3@example.com |
      | student4 | Danny     | Civi     | student4@example.com |
      | student5 | Bob       | Alex     | student5@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher  | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
      | student5 | C1     | student        |
    And the following "activities" exist:
      | activity    | name          | intro              | course | idnumber     | forcecommenting | publishnewquestion | anonymrank |
      | studentquiz | StudentQuiz 1 | Quiz 1 description | C1     | studentquiz1 | 1               | 1                  | 0          |
      | studentquiz | StudentQuiz 2 | Quiz 2 description | C1     | studentquiz2 | 1               | 1                  | 1          |
    And the following "questions" exist:
      | questioncategory          | qtype     | name                          | questiontext          |
      | Default for StudentQuiz 1 | truefalse | Test question to be previewed | Answer the question 1 |
      | Default for StudentQuiz 2 | truefalse | Test question to be previewed | Answer the question 2 |

  @javascript
  Scenario: Test show initital view and Expand all comment/ Collapse all comment button. Check both start quiz and preview mode
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    # Prepare comments and replies.
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    # Wait for comment area init.
    And I wait until the page is ready
    # Enter "Comment 1".
    When I enter the text "Comment 1" into the "Add comment" editor
    Then I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(1)" "css_element" exists
    And I should see "Comment 1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    # Wait for different created time.
    And I wait "1" seconds
    # Enter "Comment 2"
    When I enter the text "Comment 2" into the "Add comment" editor
    Then I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(2)" "css_element" exists
    And I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    And I wait "1" seconds
    # Enter "Comment 3"
    When I enter the text "Comment 3" into the "Add comment" editor
    Then I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(3)" "css_element" exists
    And I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    And I wait "1" seconds
    # Enter "Comment 4"
    When I enter the text "Comment 4" into the "Add comment" editor
    Then I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(4)" "css_element" exists
    And I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    And I wait "1" seconds
    # Enter "Comment 5"
    When I enter the text "Comment 5" into the "Add comment" editor
    Then I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(5)" "css_element" exists
    And I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(5) .studentquiz-comment-text" "css_element"
    And I wait "1" seconds
    # Enter "Comment 6"
    When I enter the text "Comment 6" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(6)" "css_element" exists
    And I should see "Comment 6" in the ".studentquiz-comment-item:nth-child(6) .studentquiz-comment-text" "css_element"
    Then I should see "Collapse all comments"
    # Click "Collapse all comments" button, page should render like initial view.
    When I press "Collapse all comments"
    And I wait until the page is ready
    Then I should see "Expand all comments"
    And I should not see "Collapse all comments"
    And I should see "5 of 6" in the ".studentquiz-comment-postcount" "css_element"
    And I should not see "Comment 1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    And I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    And I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    And I should see "Comment 6" in the ".studentquiz-comment-item:nth-child(5) .studentquiz-comment-text" "css_element"
    # Click "Expand all comments" button, check that all comments and replies is show.
    When I press "Expand all comments"
    And I wait until the page is ready
    Then I should see "Collapse all comments"
    And I should not see "Expand all comments"
    And I should see "6 of 6" in the ".studentquiz-comment-postcount" "css_element"
    And I should see "Comment 1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    And I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    And I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    And I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(5) .studentquiz-comment-text" "css_element"
    And I should see "Comment 6" in the ".studentquiz-comment-item:nth-child(6) .studentquiz-comment-text" "css_element"
    And I should see "0" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-totalreply" "css_element"
    And I should see "Replies" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-totalreply" "css_element"
    And I should see "0" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-totalreply" "css_element"
    And I should see "Replies" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-totalreply" "css_element"
    And I should see "0" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-totalreply" "css_element"
    And I should see "Replies" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-totalreply" "css_element"
    And I should see "0" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-totalreply" "css_element"
    And I should see "Replies" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-totalreply" "css_element"
    And I should see "0" in the ".studentquiz-comment-item:nth-child(5) .studentquiz-comment-totalreply" "css_element"
    And I should see "Replies" in the ".studentquiz-comment-item:nth-child(5) .studentquiz-comment-totalreply" "css_element"
    And I should see "0" in the ".studentquiz-comment-item:nth-child(6) .studentquiz-comment-totalreply" "css_element"
    And I should see "Replies" in the ".studentquiz-comment-item:nth-child(6) .studentquiz-comment-totalreply" "css_element"
    # Check in preview.
    When I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    Then I click on "Preview" "link" in the "Test question to be previewed" "table_row"
    And I switch to "questionpreview" window
    And I wait until the page is ready
    # We only show max 5 latest comments.
    Then I should not see "Comment 1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    And I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    And I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    And I should see "Comment 6" in the ".studentquiz-comment-item:nth-child(5) .studentquiz-comment-text" "css_element"

  @javascript
  Scenario: Test reply comment.
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    # Prepare comments and replies.
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    # Wait for comment area init.
    And I wait until the page is ready
    When I enter the text "Comment 1" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(1)" "css_element" exists
    Then I should see "Comment 1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    # Check can reply
    When I click on "Reply" "button" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-commands-buttons" "css_element"
    # Wait for reply init.
    And I wait until the page is ready
    And I enter the text "Reply comment 1" into the "Add reply" editor
    And I press "Add reply"
    And I wait until the page is ready
    Then I should see "1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-totalreply" "css_element"
    And I should see "Reply" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-totalreply" "css_element"

  @javascript
  Scenario: Test delete comment feature.
    # Save document into course 1.
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    # Wait for comment area init.
    And I wait until the page is ready
    Then I enter the text "Comment 1" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(1)" "css_element" exists
    Then I should see "Comment 1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    # Check if delete button visible
    And I should see "Delete" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-commands-box" "css_element"
    And I should see "1 of 1"
    # Try to delete comment.
    When I click on "Delete" "button" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-commands-box" "css_element"
    And I wait until the page is ready
    And I click on "[title='Delete comment']" "css_element" in the ".modal.show" "css_element"
    And I wait until the page is ready
    # Check comment is render as deleted and global count updated.
    Then I should see "Comment deleted" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-item-outerbox" "css_element"
    And I should see "0 of 0"

  @javascript
  Scenario: Test force comment (as student)
    # Save document into course 1.
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    When I press "Finish"
    Then I should see "Please comment"
    When I enter the text "Comment 1" into the "Add comment" editor
    Then I press "Add comment"
    And I wait until the page is ready
    When I press "Finish"
    Then I should not see "Please comment"

  @javascript
  Scenario: Admin delete comment and check if student can view.
    # Save document into course 1.
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    # Wait for comment area init.
    And I wait until the page is ready
    When I enter the text "Comment 1" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(1)" "css_element" exists
    Then I should see "Comment 1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    # Check if delete button visible
    And I should see "Delete" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-commands-box" "css_element"
    And I should see "1 of 1"
    # Try to delete comment.
    When I click on "Delete" "button" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-commands-box" "css_element"
    And I click on "[title='Delete comment']" "css_element" in the ".modal.show" "css_element"
    And I wait until the page is ready
    # Check comment is render as deleted and global count updated.
    Then I should see "Comment deleted" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-item-outerbox" "css_element"
    And I should see "0 of 0"
    And I log out
    # Student log in and see it or not
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    And I should not see "Comment 1"

  @javascript
  Scenario: Test report comment feature.
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    # Try to report when disable report feature.
    When I follow "StudentQuiz 1"
    And I click on "Start Quiz" "button"
    And I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    When I enter the text "Comment 1" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    And I wait until ".studentquiz-comment-item:nth-child(1)" "css_element" exists
    Then I should see "Comment 1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    # Not visible!
    Then I should not see "Report"
    # Enable report feature.
    When I follow "StudentQuiz 1"
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    # Try to input wrong format.
    And I set the field "Email for reporting offensive comments" to "admin@domain.com;"
    When I press "Save and display"
    Then I should see "This email address is not valid. Please enter a single email address."
    # Then input right one.
    When I set the field "Email for reporting offensive comments" to "admin@domain.com;admin1@domain.com"
    And I press "Save and display"
    Then I should see "StudentQuiz 1"
    # Try to report.
    And I click on "Start Quiz" "button"
    And I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    Then I should see "Report"
    # Test with Report feature.
    When I click on "Report" "button" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-commands-box" "css_element"
    Then I should see "Report a comment as unacceptable"
    And I set the field "It is abusive" to "1"
    When I press "Send report"
    Then I should see "Your report has been sent successfully"
    When I press "Continue"
    And I wait until the page is ready
    # After report, check we navigate back.
    Then I should see "Add comment"

  @javascript
  Scenario: Admin and user can sortable.
     # Student 2
    Given I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    When I enter the text "Comment 2" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    And I log out
     # Student 3
    Given I log in as "student3"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    When I enter the text "Comment 3" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    And I log out
     # Student 4
    Given I log in as "student4"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    When I enter the text "Comment 4" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    And I log out
     # Student 5
    Given I log in as "student5"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    When I enter the text "Comment 5" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    And I log out
     # Log in as admin
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    # Sort Date DESC (Default is Date ASC).
    When I click on "Date" "link" in the ".studentquiz-comment-filters" "css_element"
    # Prevent behat fails (even single run is fine).
    And I wait until the page is ready
    Then I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    Then I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    Then I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    Then I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    # Sort Date ASC.
    When I click on "Date" "link" in the ".studentquiz-comment-filters" "css_element"
    And I wait until the page is ready
    Then I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    And I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    And I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    # Sort first name ASC.
    When I click on "Forename" "link" in the ".studentquiz-comment-filters" "css_element"
    And I wait until the page is ready
    Then I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    And I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    And I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    # Sort first name DESC.
    When I click on "Forename" "link" in the ".studentquiz-comment-filters" "css_element"
    # Prevent behat fails (even single run is fine).
    And I wait until the page is ready
    Then I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    And I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    And I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    # Sort last name ASC.
    When I click on "Surname" "link" in the ".studentquiz-comment-filters" "css_element"
    # Prevent behat fails (even single run is fine).
    And I wait until the page is ready
    Then I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    And I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    And I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    # Sort last name DESC.
    When I click on "Surname" "link" in the ".studentquiz-comment-filters" "css_element"
    # Prevent behat fails (even single run is fine).
    And I wait until the page is ready
    Then I should see "Comment 2" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    And I should see "Comment 4" in the ".studentquiz-comment-item:nth-child(2) .studentquiz-comment-text" "css_element"
    And I should see "Comment 3" in the ".studentquiz-comment-item:nth-child(3) .studentquiz-comment-text" "css_element"
    And I should see "Comment 5" in the ".studentquiz-comment-item:nth-child(4) .studentquiz-comment-text" "css_element"
    And I log out
    # Check as student 1.
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    Then I should see "Date" in the ".studentquiz-comment-filters" "css_element"
    And I should see "Forename" in the ".studentquiz-comment-filters" "css_element"
    And I should see "Surname" in the ".studentquiz-comment-filters" "css_element"
    # Should only see date filter.
    When I am on "Course 1" course homepage
    And I follow "StudentQuiz 2"
    When I click on "Start Quiz" "button"
    Then I set the field "True" to "1"
    And I press "Check"
    And I wait until the page is ready
    When I enter the text "Comment test user 1" into the "Add comment" editor
    And I press "Add comment"
    And I wait until the page is ready
    Then I should see "Date" in the ".studentquiz-comment-filters" "css_element"
    And I should not see "Forename" in the ".studentquiz-comment-filters" "css_element"
    And I should not see "Surname" in the ".studentquiz-comment-filters" "css_element"

  @javascript
  Scenario: Test placeholder display after click Add comment.
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "StudentQuiz 1"
    When I click on "Start Quiz" "button"
    And I set the field "True" to "1"
    And I press "Check"
    # Wait for comment area init.
    When I wait until the page is ready
    # Check if placeholder has correct text.
    Then the "data-placeholder" attribute of ".editor_atto_content_wrap" "css_element" should contain "Enter your comment here ..."
    # Enter "Comment 1".
    When I enter the text "Comment 1" into the "Add comment" editor
    # Check data-placeholder now is empty.
    Then ".editor_atto_content_wrap[data-placeholder='']" "css_element" should exist
    And I press "Add comment"
    And I wait until the page is ready
    When I wait until ".studentquiz-comment-item:nth-child(1)" "css_element" exists
    Then I should see "Comment 1" in the ".studentquiz-comment-item:nth-child(1) .studentquiz-comment-text" "css_element"
    # Check placeholder is back with correct text.
    And the "data-placeholder" attribute of ".editor_atto_content_wrap" "css_element" should contain "Enter your comment here ..."
