# Changelog

## [Unreleased]

### Added
- Auto-scroll functionality to the chat window, ensuring the view always stays at the bottom when new messages arrive
- Added `ref`, `watch`, `nextTick`, and `onMounted` from Vue.js to implement the auto-scroll behavior
- Created a `scrollToBottom` function that automatically scrolls to the latest messages
- Added watchers to scroll to bottom whenever message content changes
- Set up initial scrolling when the component is first mounted
- Implemented `PurchaseInvoiceToolbox` class for managing purchase invoices through the Moneybird API
  - Added ability to get all purchase invoices
  - Added ability to get purchase invoice by ID
  - Added ability to create purchase invoices
  - Added ability to delete purchase invoices
  - Added ability to filter purchase invoices
  - Added ability to synchronize purchase invoices
  - Added ability to update purchase invoices 

### Changed
- Added ref="messagesContainer" to the chat container to provide a reference for scrolling control
- Improved layout of the Chat page to properly fit the viewport without overflow
  - Changed main container to use flex-col with h-screen and max-h-screen to prevent overflow
  - Adjusted chat container to use flex-1 with overflow-y-auto for proper scrolling
  - Added proper spacing with px-4 and pb-4 to contain all elements within the screen
  - Added mb-4 margin to the messages container to create space between it and the input area 

### Fixed
- Replaced empty favicon.ico file with a proper favicon to correctly display the site icon in browser tabs 