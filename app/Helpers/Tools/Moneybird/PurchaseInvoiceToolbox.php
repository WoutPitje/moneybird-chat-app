<?php

namespace App\Helpers\Tools\Moneybird;

use App\Helpers\Moneybird;
use Illuminate\Support\Collection;
use App\Helpers\Tools\ToolboxInterface;

class PurchaseInvoiceToolbox implements ToolboxInterface
{
    public static function getTools()
    {
        $tools = [];
        $tools[] = self::$getPurchaseInvoices;
        $tools[] = self::$createPurchaseInvoice;
        $tools[] = self::$deletePurchaseInvoice;
        $tools[] = self::$getPurchaseInvoiceById;
        $tools[] = self::$filterPurchaseInvoices;
        $tools[] = self::$synchronizePurchaseInvoices;
        $tools[] = self::$updatePurchaseInvoice;
        
        return $tools;
    }

    public static function runTool($toolName, $toolParameters)
    {
    
        if (method_exists(self::class, $toolName)) {
            return self::$toolName($toolParameters);
        }
    }

    public static function hasTool($toolName)
    {
        $names = array_map(function($tool) {
            return $tool['function']['name'];
        }, self::getTools());
        return in_array($toolName, $names);
    }

    public static $getPurchaseInvoices = [
        'type' => 'function',
        'function' => [
            'name' => 'getPurchaseInvoices',
            'description' => 'Gets all purchase invoices from Moneybird.'
        ],
    ];
    public static function getPurchaseInvoices()
    {
        $moneybird = Moneybird::getMoneybird();
        $purchaseInvoices = collect($moneybird->purchaseInvoice()->getAll());

        $purchaseInvoices = $purchaseInvoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'contact_id' => $invoice->contact_id,
                'reference' => $invoice->reference,
                'date' => $invoice->date,
                'due_date' => $invoice->due_date,
                'state' => $invoice->state,
                'total_price_incl_tax' => $invoice->total_price_incl_tax,
            ];
        });

        return $purchaseInvoices;
    }

    public static $getPurchaseInvoiceById = [
        'type' => 'function',
        'function' => [
            'name' => 'getPurchaseInvoiceById',
            'description' => 'Gets a purchase invoice from Moneybird by ID.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string',
                        'description' => 'The ID of the purchase invoice to be retrieved.',
                    ],
                ],
                'required' => ['id'],
            ],
        ],
    ];
    public static function getPurchaseInvoiceById($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $purchaseInvoice = $moneybird->purchaseInvoice()->find($parameters['id']);
        
        return $purchaseInvoice;
    }

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
                    'reference' => [
                        'type' => 'string',
                        'description' => 'The reference number for this invoice.',
                    ],
                    'date' => [
                        'type' => 'string',
                        'description' => 'The date of the invoice (YYYY-MM-DD).',
                    ],
                    'due_date' => [
                        'type' => 'string',
                        'description' => 'The due date of the invoice (YYYY-MM-DD).',
                    ],
                    'prices_are_incl_tax' => [
                        'type' => 'boolean',
                        'description' => 'Whether prices include tax.',
                    ],
                    'details' => [
                        'type' => 'array',
                        'description' => 'Line items for the invoice.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'description' => [
                                    'type' => 'string',
                                    'description' => 'Description of the line item.',
                                ],
                                'price' => [
                                    'type' => 'number',
                                    'description' => 'Price of the line item.',
                                ],
                                'amount' => [
                                    'type' => 'string',
                                    'description' => 'Amount/quantity of the line item.',
                                ],
                                'tax_rate_id' => [
                                    'type' => 'string',
                                    'description' => 'Tax rate ID for the line item.',
                                ],
                                'ledger_account_id' => [
                                    'type' => 'string',
                                    'description' => 'Ledger account ID for the line item.',
                                ],
                            ],
                        ],
                    ],
                ],
                'required' => ['contact_id', 'date'],
            ],
        ],
    ];
    public static function createPurchaseInvoice($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $purchaseInvoice = $moneybird->purchaseInvoice();       

        foreach ($parameters as $key => $value) {
            if ($key !== 'details') {
                $purchaseInvoice->$key = $value;
            }
        }

        if (isset($parameters['details'])) {
            $purchaseInvoice->details = $parameters['details'];
        }
       
        $purchaseInvoice->save();

        return $purchaseInvoice;
    }

    public static $deletePurchaseInvoice = [
        'type' => 'function',
        'function' => [
            'name' => 'deletePurchaseInvoice',
            'description' => 'Deletes a purchase invoice from Moneybird by ID.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string',
                        'description' => 'The ID of the purchase invoice to be deleted.',
                    ],
                ],
                'required' => ['id'],
            ],
        ],
    ];
    public static function deletePurchaseInvoice($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $id = $parameters['id'];
        $purchaseInvoice = $moneybird->purchaseInvoice()->find($id);
        $purchaseInvoice->delete();
        
        return [
            'success' => true,
            'message' => 'Purchase invoice deleted successfully'
        ];
    }

    public static $filterPurchaseInvoices = [
        'type' => 'function',
        'function' => [
            'name' => 'filterPurchaseInvoices',
            'description' => 'Filter purchase invoices in Moneybird based on specific criteria.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'filter' => [
                        'type' => 'string',
                        'description' => 'Filter string (e.g., "reference:INV001,state:open")',
                    ],
                ],
                'required' => ['filter'],
            ],
        ],
    ];
    public static function filterPurchaseInvoices($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $filterString = $parameters['filter'];
        
        $options = [
            'filter' => $filterString
        ];
        
        $purchaseInvoices = $moneybird->purchaseInvoice()->filter($options);
        
        return $purchaseInvoices;
    }

    public static $synchronizePurchaseInvoices = [
        'type' => 'function',
        'function' => [
            'name' => 'synchronizePurchaseInvoices',
            'description' => 'Fetch purchase invoices based on a list of IDs for synchronization.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'ids' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ],
                        'description' => 'Array of purchase invoice IDs to synchronize.',
                    ],
                ],
                'required' => ['ids'],
            ],
        ],
    ];
    public static function synchronizePurchaseInvoices($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $ids = $parameters['ids'];
        
        $purchaseInvoices = $moneybird->purchaseInvoice()->getVersions($ids);
        
        return $purchaseInvoices;
    }

    public static $updatePurchaseInvoice = [
        'type' => 'function',
        'function' => [
            'name' => 'updatePurchaseInvoice',
            'description' => 'Updates an existing purchase invoice in Moneybird.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string',
                        'description' => 'The ID of the purchase invoice to update.',
                    ],
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact associated with this invoice.',
                    ],
                    'reference' => [
                        'type' => 'string',
                        'description' => 'The reference number for this invoice.',
                    ],
                    'date' => [
                        'type' => 'string',
                        'description' => 'The date of the invoice (YYYY-MM-DD).',
                    ],
                    'due_date' => [
                        'type' => 'string',
                        'description' => 'The due date of the invoice (YYYY-MM-DD).',
                    ],
                    'prices_are_incl_tax' => [
                        'type' => 'boolean',
                        'description' => 'Whether prices include tax.',
                    ],
                    'details' => [
                        'type' => 'array',
                        'description' => 'Line items for the invoice.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => [
                                    'type' => 'string',
                                    'description' => 'ID of the existing line item (for updates).',
                                ],
                                'description' => [
                                    'type' => 'string',
                                    'description' => 'Description of the line item.',
                                ],
                                'price' => [
                                    'type' => 'number',
                                    'description' => 'Price of the line item.',
                                ],
                                'amount' => [
                                    'type' => 'string',
                                    'description' => 'Amount/quantity of the line item.',
                                ],
                                'tax_rate_id' => [
                                    'type' => 'string',
                                    'description' => 'Tax rate ID for the line item.',
                                ],
                                'ledger_account_id' => [
                                    'type' => 'string',
                                    'description' => 'Ledger account ID for the line item.',
                                ],
                                '_destroy' => [
                                    'type' => 'boolean',
                                    'description' => 'Whether to remove this line item.',
                                ],
                            ],
                        ],
                    ],
                ],
                'required' => ['id'],
            ],
        ],
    ];
    public static function updatePurchaseInvoice($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $id = $parameters['id'];
        unset($parameters['id']);
        
        $purchaseInvoice = $moneybird->purchaseInvoice()->find($id);
        
        foreach ($parameters as $key => $value) {
           $purchaseInvoice->$key = $value;
        }
        
        $purchaseInvoice->save();
        return $purchaseInvoice;
    }
}