@mod @mod_studentquiz
Feature: Restore of studentquizzes in moodle exports contain question answers
  In order to reuse my studentquizzes
  As a admin
  I need to be able to restore the moodles backups from studentquizzes before, during and after aggregated codebase

  Legend:
  - earlybefore: The StudentQuiz codebase when it still was using mod_quiz (<= 2.0.3)
  - before: The StudentQuiz codebase before aggregated was introduced (<= v3.2.0)
  - during: The StudentQuiz codebase during aggregated was active (>= v3.2.1, <= v3.3.0), which had a setting aggregated:
  - 0: which was the old calculation using the question engine (equals before)
  - 1: which is the new calculation using studentquiz_progress table (equals after)
  - after: The StudentQuiz codebase where all activities are migrated to the new calculation and the codebase has no code from before except for the restore (>= 3.4.0)

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And I log in as "admin"

  @javascript @_file_upload
  Scenario Outline: Restore moodle backups containing old StudentQuiz activity
    When I am on "Course 1" course homepage
    And I navigate to "Restore" in current page administration
    And I press "Manage backup files"
    And I upload "mod/studentquiz/tests/fixtures/<file>" file to "Files" filemanager
    And I press "Save changes"
    And I restore "<file>" backup into a new course using this options:
    And I am on "<course>" course homepage
    And I follow "<studentquiz>"
    When I navigate to "Ranking" in current page administration
    Then I should see "<pos_1_correct_answered_points>" in the ".rankingtable tbody tr:nth-child(1) td.c6" "css_element"
    And I should see "<pos_1_total_points>" in the ".rankingtable tbody tr:nth-child(1) td.c2" "css_element"
    And I should see "<pos_2_correct_answered_points>" in the ".rankingtable tbody tr:nth-child(2) td.c6" "css_element"
    And I should see "<pos_2_total_points>" in the ".rankingtable tbody tr:nth-child(2) td.c2" "css_element"

    Examples:
      | file                                      | course              | studentquiz   | pos_1_correct_answered_points | pos_1_total_points | pos_2_correct_answered_points | pos_2_total_points |
# TODO: early before should be checked/supported? This is pretty old and seems not to work currently.
#      | backup-moodle2-aggregated-earlybefore.mbz | Course One          | StudentQuiz 1 | 2                             | 12                 | 2                             | 11                 |
      | backup-moodle2-aggregated_before.mbz      | aggregated before   | SQbefore      | 2                             | 32                 | 4                             | 23                 |
      | backup-moodle2-aggregated-during-0.mbz    | aggregated during 0 | SQduring0     | 4                             | 28                 | 2                             | 20                 |
      | backup-moodle2-aggregated-during-1.mbz    | aggregated during 1 | SQduring1     | 4                             | 28                 | 2                             | 20                 |
# after does not yet exist, must be added once aggregated field is removed from tbl_studentquiz - if that ever will happen
#       | backup-moodle2-aggregated-after.mbz          | Course One        | StudentQuiz 1 | 0                             | 0                  | 0                             | 0                  |
