# Moneybird Integration Tools

## Contact People Toolbox

The `MoneybirdContactPeopleToolbox` class provides a set of tools for working with contact people in Moneybird. This class implements the `ToolboxInterface` and provides methods for retrieving, creating, updating, and deleting contact people.

### Available Tools

1. `getContactPersonById` - Retrieves a specific contact person by their ID.
2. `createContactPerson` - Creates a new contact person with the specified properties.
3. `updateContactPerson` - Updates an existing contact person with new properties.
4. `deleteContactPerson` - Deletes a contact person by their ID.

### Testing

The `MoneybirdContactPeopleToolboxTest` class provides unit tests for each of the tools in the `MoneybirdContactPeopleToolbox` class. These tests use Mockery to create mocks of the Moneybird client and repositories, allowing us to test the behavior of each tool without making actual API calls to Moneybird.

The tests verify that:
- The correct tools are available
- Contact people can be retrieved by ID
- New contact people can be created
- Existing contact people can be updated
- Contact people can be deleted
- Tools can be run by name

## Implementation Notes

The implementation uses the Moneybird PHP SDK to interact with the Moneybird API. The `Moneybird::getMoneybird()` helper method is used to get the Moneybird client, which is then used to interact with the contact people endpoints.

### Example Usage

```php
// Get a contact person by ID
$contactPerson = MoneybirdContactPeopleToolbox::getContactPersonById('123');
// or
$contactPerson = MoneybirdContactPeopleToolbox::getContactPersonById(['id' => '123']);

// Create a new contact person
$newContactPerson = [
    'contact_id' => '456',
    'firstname' => 'John',
    'lastname' => 'Doe',
    'phone' => '1234567890',
    'email' => 'john.doe@example.com',
    'department' => 'Sales'
];
$createdContactPerson = MoneybirdContactPeopleToolbox::createContactPerson($newContactPerson);

// Update a contact person
$updateData = [
    'id' => '123',
    'firstname' => 'Updated',
    'lastname' => 'Name',
    'email' => 'updated@example.com'
];
$updatedContactPerson = MoneybirdContactPeopleToolbox::updateContactPerson($updateData);

// Delete a contact person
MoneybirdContactPeopleToolbox::deleteContactPerson('123');
// or
MoneybirdContactPeopleToolbox::deleteContactPerson(['id' => '123']);
```

## Changelog

- 2023-06-20: Updated getContactPersonById and deleteContactPerson to handle both array and string parameters
- 2023-06-20: Fixed test setup for createContactPerson to resolve mock conflicts
- 2023-06-20: Fixed parameter handling in MoneybirdContactPeopleToolbox to match test cases
- 2023-06-20: Fixed variable naming issue in updateContactPerson method to avoid variable overwriting
- 2023-06-20: Fixed tests to align with the implementation of the toolbox methods
- 2023-06-20: Fixed updateContactPerson test to better match implementation
- 2023-06-20: Fixed createContactPerson test to use correct mocking approach
- 2023-06-20: Created tests for the MoneybirdContactPeopleToolbox class
- 2023-06-20: Created documentation for the Moneybird Tools 