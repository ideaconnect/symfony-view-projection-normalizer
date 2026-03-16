# Behat Tests for MVC View Projection Normalizer

This directory contains Behat acceptance tests that demonstrate the functionality of the MVC View Projection Normalizer library.

## Test Structure

### Features (`features/normalization.feature`)

The feature file contains comprehensive test scenarios that demonstrate:

1. **Simple Object Normalization**: Converting a `Person` entity with `firstName` and `lastName` into a `PersonViewProjection` that combines them into a single `name` field.

2. **Nested Object Normalization**: Testing a `Company` entity that contains a nested `Person` (owner) object, showing how nested ViewProjections are handled.

3. **Collection Normalization**: Testing a `Team` entity that contains multiple `Person` objects (members), demonstrating how collections of objects are normalized through their ViewProjections.

4. **Calculated Fields**: Showing how ViewProjections can add computed properties like `yearsInBusiness` and `averageAge`.

5. **JSON Serialization**: Demonstrating end-to-end serialization to JSON format.

### Test Fixtures (`features/fixtures/`)

#### Entities
- **Person**: Basic entity with `firstName`, `lastName`, and `age`
- **Company**: Entity with nested Person (owner) and business information
- **Team**: Entity with collection of Person objects (members) and a team lead

#### ViewProjections
- **PersonViewProjection**: Combines firstName and lastName into `name` field
- **CompanyViewProjection**: Transforms company data and includes calculated fields
- **TeamViewProjection**: Handles team data with member collection and aggregate calculations

### Context (`features/bootstrap/NormalizationContext.php`)

The Behat context class provides:
- Proper Serializer configuration with DefaultViewProjectionNormalizer
- Step definitions for creating test objects
- Assertions for verifying normalization results
- Support for both normalization and JSON serialization testing
- Coverage for the explicit `NormalizableInterface` opt-in model used by the library

## Example Test Scenarios

### Simple Object with Combined Attributes
```gherkin
Given I have a person with first name "John" and last name "Doe" aged 30
When I normalize the object
Then the normalized result should contain "name" with value "John Doe"
And the normalized result should contain "age" with value "30"
```

### Object with Nested Object
```gherkin
Given I have a company "TechCorp" in "Technology" founded in 2010 with owner "Alice" "Johnson" aged 45
When I normalize the object
Then the normalized result should contain "name" with value "TechCorp"
And the normalized result should have "owner" as an object
And the nested object "owner" should contain "name" with value "Alice Johnson"
```

### Object with Collection of Objects
```gherkin
Given I have a team "Development Team" in "Engineering" with team lead "Carol" "Brown" aged 40
And the team has member "David" "Lee" aged 28
And the team has member "Emma" "Davis" aged 32
When I normalize the object
Then the normalized result should have "members" as an array with 2 items
And the array "members" should have item 0 containing "name" with value "David Lee"
```

## Running the Tests

```bash
# Run all scenarios
composer run test:feature

# Run a specific scenario
./vendor/bin/behat features/normalization.feature:9

# Dry run to check syntax
./vendor/bin/behat --dry-run
```

## Key Features Demonstrated

1. **Attribute-based Configuration**: Uses `#[DefaultViewProjection]` attributes to specify ViewProjections
2. **SerializedName Support**: Full support for `#[SerializedName('alias')]` attributes for custom field names
3. **Automatic ViewProjection Instantiation**: The normalizer automatically creates ViewProjections from entities
4. **Nested Object Handling**: Proper handling of nested objects that also have ViewProjections
5. **Collection Support**: Normalization of arrays/collections of objects
6. **Calculated Properties**: ViewProjections can add computed fields not present in original entities
7. **Flexible Serialization**: Works with both normalize() and serialize() methods
8. **Explicit opt-in**: only classes implementing `NormalizableInterface` are handled by the projection normalizer

### SerializedName Examples

The tests demonstrate how ViewProjections can transform field names using `@SerializedName`:

- `PersonViewProjection`:
  - `getName()` → `"n"` (combines firstName + lastName)
  - `getAge()` → `"a"`
- `CompanyViewProjection`:
  - `getName()` → `"companyName"`
  - `getIndustry()` → `"sector"`
  - `getOwner()` → `"ownerInfo"`
- `TeamViewProjection`:
  - `getName()` → `"teamName"`
  - `getDepartment()` → `"dept"`
  - `getMembers()` → `"teamMembers"`
  - `getTeamLead()` → `"lead"`
  - `getTeamSize()` → `"size"`

This test suite provides comprehensive coverage of the library's capabilities and serves as both validation and documentation of expected behavior.
