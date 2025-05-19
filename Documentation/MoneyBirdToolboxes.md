# Moneybird Toolboxes

This document provides an overview of the toolboxes used for integrating with the Moneybird API.

## Overview

Toolboxes implement the `ToolboxInterface` which requires two methods:
- `getTools()`: Returns an array of available tools in the toolbox
- `runTool($toolName, $toolParameters)`: Executes a specific tool with given parameters

Each toolbox corresponds to a specific entity in the Moneybird API.

## Available Toolboxes

### 1. MoneyBirdContactsToolbox

Handles operations related to contacts in Moneybird, such as:
- Getting all contacts
- Creating contacts
- Deleting contacts
- Updating contacts
- Archiving contacts
- Filtering contacts
- Synchronizing contacts

### 2. MoneybirdContactPeopleToolbox

Handles operations related to contact people in Moneybird.

### 3. PurchaseInvoiceToolbox

Handles operations related to purchase invoices in Moneybird, such as:
- Getting all purchase invoices
- Getting a specific purchase invoice by ID
- Creating new purchase invoices
- Deleting purchase invoices
- Filtering purchase invoices
- Synchronizing purchase invoices
- Updating existing purchase invoices

## Usage

Toolboxes are accessed through the application's tools system. Each tool is defined with a type, name, description, and parameters schema.

Example for creating a purchase invoice:

```php
// Tool definition (from PurchaseInvoiceToolbox.php)
public static $createPurchaseInvoice = [
    'type' => 'function',
    'function' => [
        'name' => 'createPurchaseInvoice',
        'description' => 'Creates a new purchase invoice in Moneybird.',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'contact_id' => [
                    'type' => 'string',
                    'description' => 'The ID of the contact associated with this invoice.',
                ],
                // ... other parameters
            ],
            'required' => ['contact_id', 'date'],
        ],
    ],
];

// Tool implementation
public static function createPurchaseInvoice($parameters)
{
    $moneybird = Moneybird::getMoneybird();
    $purchaseInvoice = $moneybird->purchaseInvoice();       

    // ... implementation details
   
    $purchaseInvoice->save();

    return $purchaseInvoice;
}
```

## Data Structure

Purchase invoices contain the following main fields:
- id: Unique identifier
- contact_id: Related contact's ID
- reference: Invoice reference number
- date: Invoice date
- due_date: Payment due date
- state: Current state of the invoice
- total_price_incl_tax: Total amount including tax
- details: Array of line items 