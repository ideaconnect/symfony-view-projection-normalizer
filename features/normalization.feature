Feature: View Projection Normalization
  As a developer using the MVC View Projection Normalizer
  I want to normalize objects with different complexity levels
  So that I can control the serialization output through view projections

  Background:
    Given the serializer is properly configured with DefaultViewProjectionNormalizer

  Scenario: Normalize a simple object with combined attributes
    Given I have a person with first name "John" and last name "Doe" aged 30
    When I normalize the object
    Then the normalized result should contain "n" with value "John Doe"
    And the normalized result should contain "a" with value "30"

  Scenario: Serialize a simple object to JSON with SerializedName attribute
    Given I have a person with first name "Jane" and last name "Smith" aged 25
    When I serialize the object to JSON
    Then the serialized JSON should be valid
    And the JSON should contain "n" with value "Jane Smith"
    And the JSON should contain "a" with value "25"

  Scenario: Normalize an object with nested object
    Given I have a company "TechCorp" in "Technology" founded in 2010 with owner "Alice" "Johnson" aged 45
    When I normalize the object
    Then the normalized result should contain "companyName" with value "TechCorp"
    And the normalized result should contain "sector" with value "Technology"
    And the normalized result should have "ownerInfo" as an object
    And the nested object "ownerInfo" should contain "n" with value "Alice Johnson"
    And the nested object "ownerInfo" should contain "a" with value "45"

  Scenario: Normalize an object with calculated fields
    Given I have a company "StartupInc" in "Software" founded in 2020 with owner "Bob" "Wilson" aged 35
    When I normalize the object
    Then the normalized result should contain "companyName" with value "StartupInc"
    And the normalized result should contain "yearsInBusiness" with value "6"

  Scenario: Normalize an object with a collection of nested objects
    Given I have a team "Development Team" in "Engineering" with team lead "Carol" "Brown" aged 40
    And the team has member "David" "Lee" aged 28
    And the team has member "Emma" "Davis" aged 32
    And the team has member "Frank" "Miller" aged 26
    When I normalize the object
    Then the normalized result should contain "teamName" with value "Development Team"
    And the normalized result should contain "dept" with value "Engineering"
    And the normalized result should contain "size" with value "3"
    And the normalized result should have "teamMembers" as an array with 3 items
    And the array "teamMembers" should have item 0 containing "n" with value "David Lee"
    And the array "teamMembers" should have item 1 containing "n" with value "Emma Davis"
    And the array "teamMembers" should have item 2 containing "n" with value "Frank Miller"
    And the normalized result should have "lead" as an object
    And the nested object "lead" should contain "n" with value "Carol Brown"

  Scenario: Normalize collection with calculated aggregate values
    Given I have a team "QA Team" in "Quality Assurance" with team lead "Grace" "Taylor" aged 38
    And the team has member "Henry" "Anderson" aged 24
    And the team has member "Ivy" "Thomas" aged 29
    And the team has member "Jack" "Jackson" aged 31
    When I normalize the object
    Then the normalized result should contain "averageAge" with value "28"
    And the normalized result should contain "size" with value "3"

  Scenario: Serialize complex object with nested collections to JSON
    Given I have a team "Marketing Team" in "Marketing" with team lead "Kate" "White" aged 42
    And the team has member "Leo" "Harris" aged 27
    And the team has member "Mia" "Clark" aged 30
    When I serialize the object to JSON
    Then the serialized JSON should be valid
    And the JSON should contain "teamName" with value "Marketing Team"
    And the JSON should contain "size" with value "2"
