# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- Implemented chat interface to display messages with different styling for user and assistant messages
- Added visual indicator for tool calls ("Running tool: ...")
- Added ability to send messages by pressing Enter key

### Fixed
- Fixed import paths in Chat.vue to use lowercase 'components' to match existing codebase patterns
- Removed unused Badge import to eliminate linter error 