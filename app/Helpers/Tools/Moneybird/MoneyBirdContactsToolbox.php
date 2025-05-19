<?php

namespace App\Helpers\Tools\Moneybird;

use App\Helpers\Moneybird;
use Illuminate\Support\Collection;
use App\Helpers\Tools\ToolboxInterface;
class MoneyBirdContactsToolbox implements ToolboxInterface
{
    public static function getTools()
    {
        $tools = [];
        $tools[] = self::$getContacts;
        $tools[] = self::$createContact;
        $tools[] = self::$deleteContact;
        $tools[] = self::$getContactById;
        $tools[] = self::$getContactByCustomerId;
        $tools[] = self::$filterContacts;
        $tools[] = self::$synchronizeContacts;
        $tools[] = self::$updateContact;
        $tools[] = self::$archiveContact;
       
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

    public static $getContacts = [
        'type' => 'function',
        'function' => [
            'name' => 'getContacts',
            'description' => 'Gets all contacts from Moneybird.'
        ],
    ];
    public static function getContacts()
    {
        $moneybird = Moneybird::getMoneybird();
        $contacts = collect($moneybird->contact()->getAll());

        $contacts = $contacts->map(function ($contact) {
            return [
                'id' => $contact->id,
                'firstname' => $contact->firstname,
                'lastname' => $contact->lastname,
                'company_name' => $contact->company_name,
            ];
        });

        return $contacts;
    }

    public static $getContactById = [
        'type' => 'function',
        'function' => [
            'name' => 'getContactById',
            'description' => 'Gets a contact from Moneybird by ID.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to be retrieved.',
                    ],
                ],
                'required' => ['id'],
            ],
        ],
    ];
    public static function getContactById($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contact = $moneybird->contact()->find($parameters['id']);
        
        return $contact;
    }

    public static $getContactByCustomerId = [
        'type' => 'function',
        'function' => [
            'name' => 'getContactByCustomerId',
            'description' => 'Gets a contact from Moneybird by customer ID.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'customer_id' => [
                        'type' => 'string',
                        'description' => 'The customer ID of the contact to be retrieved.',
                    ],
                ],
                'required' => ['customer_id'],
            ],
        ],
    ];
    public static function getContactByCustomerId($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contact = $moneybird->contact()->findByCustomerId($parameters['customer_id']);
        
        return $contact;
    }

    public static $createContact = [
        'type' => 'function',
        'function' => [
            'name' => 'createContact',
            'description' => 'Creates a new contact in Moneybird.',
            'parameters' => [
            'type' => 'object',
            'properties' => [
                'firstname' => [
                    'type' => 'string',
                    'description' => 'The first name of the contact.',
                ],
                'lastname' => [
                    'type' => 'string',
                    'description' => 'The last name of the contact.',
                ],
                'company_name' => [
                    'type' => 'string',
                    'description' => 'The company name of the contact.',
                ],
            ],
                'required' => ['firstname', 'lastname', 'company_name'],
            ],
        ],
    ];
    public static function createContact($newContact)
    {
        $moneybird = Moneybird::getMoneybird();
        $contact = $moneybird->contact();

        $contact->company_name = $newContact['company_name'];
        $contact->firstname = $newContact['firstname'];
        $contact->lastname = $newContact['lastname'];
       
        $contact->save();

        return $contact;
    }

    public static $deleteContact = [
        'type' => 'function',
        'function' => [
            'name' => 'deleteContact',
            'description' => 'Deletes a contact from Moneybird by ID.',
            'parameters' => [
                'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'The ID of the contact to be deleted.',
                ],
            ],
                'required' => ['id'],
            ],
        ],
    ];
    public static function deleteContact($id)
    {
        $moneybird = Moneybird::getMoneybird();
        $contact = $moneybird->contact($id);
        $contact->delete();
    }

    public static $filterContacts = [
        'type' => 'function',
        'function' => [
            'name' => 'filterContacts',
            'description' => 'Filter contacts in Moneybird based on specific criteria.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'filter' => [
                        'type' => 'string',
                        'description' => 'Filter string (e.g., "first_name:john,created_after:2023-01-01")',
                    ],
                    'include_archived' => [
                        'type' => 'boolean',
                        'description' => 'Whether to include archived contacts.',
                    ],
                ],
                'required' => ['filter'],
            ],
        ],
    ];
    public static function filterContacts($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $filterString = $parameters['filter'];
        $includeArchived = isset($parameters['include_archived']) ? $parameters['include_archived'] : false;
        
        $options = [
            'filter' => $filterString,
            'include_archived' => $includeArchived
        ];
        
        $contacts = $moneybird->contact()->filter($options);
        
        return $contacts;
    }

    public static $synchronizeContacts = [
        'type' => 'function',
        'function' => [
            'name' => 'synchronizeContacts',
            'description' => 'Fetch contacts based on a list of IDs for synchronization.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'ids' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ],
                        'description' => 'Array of contact IDs to synchronize.',
                    ],
                ],
                'required' => ['ids'],
            ],
        ],
    ];
    public static function synchronizeContacts($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $ids = $parameters['ids'];
        
        $contacts = $moneybird->contact()->getVersions($ids);
        
        return $contacts;
    }

    public static $updateContact = [
        'type' => 'function',
        'function' => [
            'name' => 'updateContact',
            'description' => 'Updates an existing contact in Moneybird.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to update.',
                    ],
                    'company_name' => [
                        'type' => 'string',
                        'description' => 'The company name of the contact.',
                    ],
                    'firstname' => [
                        'type' => 'string',
                        'description' => 'The first name of the contact.',
                    ],
                    'lastname' => [
                        'type' => 'string',
                        'description' => 'The last name of the contact.',
                    ],
                    'attention' => [
                        'type' => 'string',
                        'description' => 'The attention line for the contact.',
                    ],
                    'address1' => [
                        'type' => 'string',
                        'description' => 'The first address line of the contact.',
                    ],
                    'address2' => [
                        'type' => 'string',
                        'description' => 'The second address line of the contact.',
                    ],
                    'zipcode' => [
                        'type' => 'string',
                        'description' => 'The zipcode of the contact.',
                    ],
                    'city' => [
                        'type' => 'string',
                        'description' => 'The city of the contact.',
                    ],
                    'country' => [
                        'type' => 'string',
                        'description' => 'The country of the contact (ISO two-character code).',
                    ],
                    'email' => [
                        'type' => 'string',
                        'description' => 'The email address of the contact.',
                    ],
                    'phone' => [
                        'type' => 'string',
                        'description' => 'The phone number of the contact.',
                    ],
                    'delivery_method' => [
                        'type' => 'string',
                        'description' => 'The delivery method for the contact.',
                    ],
                    'customer_id' => [
                        'type' => 'string',
                        'description' => 'The customer ID of the contact.',
                    ],
                    'tax_number' => [
                        'type' => 'string',
                        'description' => 'The tax number of the contact.',
                    ],
                    'chamber_of_commerce' => [
                        'type' => 'string',
                        'description' => 'The chamber of commerce number of the contact.',
                    ],
                    'bank_account' => [
                        'type' => 'string',
                        'description' => 'The bank account of the contact.',
                    ],
                    'moneybird_payments_mandate' => [
                        'type' => 'boolean',
                        'description' => 'The Moneybird payments mandate status.',
                    ],
                    'send_invoices_to_attention' => [
                        'type' => 'string',
                        'description' => 'The attention line for sending invoices.',
                    ],
                    'send_invoices_to_email' => [
                        'type' => 'string',
                        'description' => 'The email to send invoices to.',
                    ],
                    'send_estimates_to_attention' => [
                        'type' => 'string',
                        'description' => 'The attention line for sending estimates.',
                    ],
                    'send_estimates_to_email' => [
                        'type' => 'string',
                        'description' => 'The email to send estimates to.',
                    ],
                    'sepa_active' => [
                        'type' => 'boolean',
                        'description' => 'Whether SEPA is active for this contact.',
                    ],
                    'sepa_iban' => [
                        'type' => 'string',
                        'description' => 'The SEPA IBAN of the contact.',
                    ],
                    'sepa_iban_account_name' => [
                        'type' => 'string',
                        'description' => 'The SEPA IBAN account name of the contact.',
                    ],
                    'sepa_bic' => [
                        'type' => 'string',
                        'description' => 'The SEPA BIC of the contact.',
                    ],
                    'sepa_mandate_id' => [
                        'type' => 'string',
                        'description' => 'The SEPA mandate ID of the contact.',
                    ],
                    'sepa_mandate_date' => [
                        'type' => 'string',
                        'description' => 'The SEPA mandate date of the contact.',
                    ],
                    'sepa_sequence_type' => [
                        'type' => 'string',
                        'description' => 'The SEPA sequence type of the contact.',
                    ],
                    'credit_card_number' => [
                        'type' => 'string',
                        'description' => 'The credit card number of the contact.',
                    ],
                    'credit_card_reference' => [
                        'type' => 'string',
                        'description' => 'The credit card reference of the contact.',
                    ],
                    'credit_card_type' => [
                        'type' => 'string',
                        'description' => 'The credit card type of the contact.',
                    ],
                    'invoice_workflow_id' => [
                        'type' => 'string',
                        'description' => 'The invoice workflow ID for the contact.',
                    ],
                    'estimate_workflow_id' => [
                        'type' => 'string',
                        'description' => 'The estimate workflow ID for the contact.',
                    ],
                    'email_ubl' => [
                        'type' => 'boolean',
                        'description' => 'Whether to email UBL to the contact.',
                    ],
                ],
                'required' => ['id'],
            ],
        ],
    ];
    public static function updateContact($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $id = $parameters['id'];
        unset($parameters['id']);
        
        $contact = $moneybird->contact()->find($id);
        
        foreach ($parameters as $key => $value) {
           $contact->$key = $value;
        }
        
        $contact->save();
        return $contact;
    }

    public static $archiveContact = [
        'type' => 'function',
        'function' => [
            'name' => 'archiveContact',
            'description' => 'Archives a contact in Moneybird.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to archive.',
                    ],
                ],
                'required' => ['id'],
            ],
        ],
    ];
    public static function archiveContact($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $id = $parameters['id'];
        
        $contact = $moneybird->contact()->find($id);
        $contact->archive();
        
        return [
            'success' => true,
            'message' => 'Contact archived successfully'
        ];
    }
}