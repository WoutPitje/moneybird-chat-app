# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- Implemented chat interface to display messages with different styling for user and assistant messages
- Added visual indicator for tool calls ("Running tool: ...")
- Added ability to send messages by pressing Enter key
- Added streaming support for real-time chat responses via Server-Sent Events
- Added frontend integration with Server-Sent Events including typing animation
- Created new API endpoint for streaming responses at '/chat/stream'
- Enhanced streaming to show real-time updates when tools are called and executed
- Added visual status indicators for tool execution progress

### Fixed
- Fixed import paths in Chat.vue to use lowercase 'components' to match existing codebase patterns
- Removed unused Badge import to eliminate linter error 
- Changed streaming endpoint from POST to GET for better compatibility with EventSource
- Added comprehensive error handling for streaming responses with visual feedback
- Added CORS headers to streaming response for better cross-origin compatibility
- Fixed output buffer handling in streaming response to prevent "Failed to flush buffer" errors 