# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- Implemented chat interface to display messages with different styling for user and assistant messages
- Added visual indicator for tool calls ("Running tool: ...")
- Added ability to send messages by pressing Enter key
- Expanded MoneyBirdContacts helper with comprehensive contact API methods:
  - Contact search and filtering capabilities
  - Single contact retrieval by ID and customer ID
  - Contact synchronization for efficient updates
  - Contact people management (create, update, delete)
  - Contact notes functionality
  - Moneybird Payments mandate management
  - Additional charges for invoicing

### Fixed
- Fixed import paths in Chat.vue to use lowercase 'components' to match existing codebase patterns
- Removed unused Badge import to eliminate linter error 
- Fixed contact person methods in MoneyBirdContacts to work with the Moneybird PHP client correctly
- Fixed TypeError in contact person methods by correctly handling array return values from the Moneybird API 
- Fixed addContactNote method in MoneyBirdContacts to properly create a Note entity object instead of passing an array 