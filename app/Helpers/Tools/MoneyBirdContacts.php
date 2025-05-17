<?php

namespace App\Helpers\Tools;

use App\Helpers\Moneybird;
use Illuminate\Support\Collection;

class MoneyBirdContacts
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
        $tools[] = self::$addContactNote;
        $tools[] = self::$deleteContactNote;
        $tools[] = self::$getContactPerson;
        $tools[] = self::$createContactPerson;
        $tools[] = self::$updateContactPerson;
        $tools[] = self::$deleteContactPerson;
        $tools[] = self::$getMandateInfo;
        $tools[] = self::$requestMandate;
        $tools[] = self::$requestMandateURL;
        $tools[] = self::$deleteMandate;
        $tools[] = self::$addCharge;
        $tools[] = self::$getCharges;
        return $tools;
    }

    public static function runTool($toolName, $toolParameters)
    {
        if (method_exists(self::class, $toolName)) {
            return self::$toolName($toolParameters);
        }
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
                    'send_invoices_to_email' => [
                        'type' => 'string',
                        'description' => 'The email to send invoices to.',
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
            if (property_exists($contact, $key)) {
                $contact->$key = $value;
            }
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

    public static $addContactNote = [
        'type' => 'function',
        'function' => [
            'name' => 'addContactNote',
            'description' => 'Adds a note to a contact in Moneybird.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to add a note to.',
                    ],
                    'note' => [
                        'type' => 'string',
                        'description' => 'The text of the note.',
                    ],
                    'todo' => [
                        'type' => 'boolean',
                        'description' => 'Whether the note is a to-do item.',
                    ],
                    'assignee_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the user to assign the to-do to (if it is a to-do).',
                    ],
                ],
                'required' => ['contact_id', 'note'],
            ],
        ],
    ];
    public static function addContactNote($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        
        // Create Note entity
        $note = $moneybird->note();
        $note->note = $parameters['note'];
        
        if (isset($parameters['todo']) && $parameters['todo']) {
            $note->todo = true;
        }
        
        if (isset($parameters['assignee_id'])) {
            $note->assignee_id = $parameters['assignee_id'];
        }
        
        $contact = $moneybird->contact()->find($contactId);
        $response = $contact->addNote($note);
        
        return $response;
    }

    public static $deleteContactNote = [
        'type' => 'function',
        'function' => [
            'name' => 'deleteContactNote',
            'description' => 'Deletes a note from a contact in Moneybird.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact the note belongs to.',
                    ],
                    'note_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the note to delete.',
                    ],
                ],
                'required' => ['contact_id', 'note_id'],
            ],
        ],
    ];
    public static function deleteContactNote($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        $noteId = $parameters['note_id'];
        
        $contact = $moneybird->contact()->find($contactId);
        $contact->deleteNote($noteId);
        
        return [
            'success' => true,
            'message' => 'Note deleted successfully'
        ];
    }

    public static $getContactPerson = [
        'type' => 'function',
        'function' => [
            'name' => 'getContactPerson',
            'description' => 'Gets a contact person from a contact in Moneybird.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact the person belongs to.',
                    ],
                    'person_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact person to get.',
                    ],
                ],
                'required' => ['contact_id', 'person_id'],
            ],
        ],
    ];
    public static function getContactPerson($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        $personId = $parameters['person_id'];
        
        $contact = $moneybird->contact()->find($contactId);
        
        // The contact class doesn't have a direct getContactPerson method
        // Instead, we need to check the contact_people array in the contact object
        if (isset($contact->contact_people) && is_array($contact->contact_people)) {
            foreach ($contact->contact_people as $person) {
                if ($person->id == $personId) {
                    return $person;
                }
            }
        }
        
        return [
            'success' => false,
            'message' => 'Contact person not found'
        ];
    }

    public static $createContactPerson = [
        'type' => 'function',
        'function' => [
            'name' => 'createContactPerson',
            'description' => 'Creates a new contact person for a contact in Moneybird.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to add a person to.',
                    ],
                    'firstname' => [
                        'type' => 'string',
                        'description' => 'The first name of the contact person.',
                    ],
                    'lastname' => [
                        'type' => 'string',
                        'description' => 'The last name of the contact person.',
                    ],
                    'phone' => [
                        'type' => 'string',
                        'description' => 'The phone number of the contact person.',
                    ],
                    'email' => [
                        'type' => 'string',
                        'description' => 'The email of the contact person.',
                    ],
                    'department' => [
                        'type' => 'string',
                        'description' => 'The department of the contact person.',
                    ],
                ],
                'required' => ['contact_id', 'firstname', 'lastname'],
            ],
        ],
    ];
    public static function createContactPerson($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        
        $personData = [
            'firstname' => $parameters['firstname'],
            'lastname' => $parameters['lastname'],
        ];
        
        if (isset($parameters['phone'])) {
            $personData['phone'] = $parameters['phone'];
        }
        
        if (isset($parameters['email'])) {
            $personData['email'] = $parameters['email'];
        }
        
        if (isset($parameters['department'])) {
            $personData['department'] = $parameters['department'];
        }
        
        try {
            $contact = $moneybird->contact()->find($contactId);
            // Use the API endpoint directly since the method is not available
            $result = $moneybird->getConnection()->post('contacts/' . $contactId . '/contact_people', json_encode(['contact_person' => $personData]));
            
            // The result is already an array, no need to decode
            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static $updateContactPerson = [
        'type' => 'function',
        'function' => [
            'name' => 'updateContactPerson',
            'description' => 'Updates a contact person in Moneybird.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact the person belongs to.',
                    ],
                    'person_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact person to update.',
                    ],
                    'firstname' => [
                        'type' => 'string',
                        'description' => 'The first name of the contact person.',
                    ],
                    'lastname' => [
                        'type' => 'string',
                        'description' => 'The last name of the contact person.',
                    ],
                    'phone' => [
                        'type' => 'string',
                        'description' => 'The phone number of the contact person.',
                    ],
                    'email' => [
                        'type' => 'string',
                        'description' => 'The email of the contact person.',
                    ],
                    'department' => [
                        'type' => 'string',
                        'description' => 'The department of the contact person.',
                    ],
                ],
                'required' => ['contact_id', 'person_id'],
            ],
        ],
    ];
    public static function updateContactPerson($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        $personId = $parameters['person_id'];
        
        $personData = [];
        
        if (isset($parameters['firstname'])) {
            $personData['firstname'] = $parameters['firstname'];
        }
        
        if (isset($parameters['lastname'])) {
            $personData['lastname'] = $parameters['lastname'];
        }
        
        if (isset($parameters['phone'])) {
            $personData['phone'] = $parameters['phone'];
        }
        
        if (isset($parameters['email'])) {
            $personData['email'] = $parameters['email'];
        }
        
        if (isset($parameters['department'])) {
            $personData['department'] = $parameters['department'];
        }
        
        try {
            $contact = $moneybird->contact()->find($contactId);
            // Use the API endpoint directly since the method is not available
            $result = $moneybird->getConnection()->patch('contacts/' . $contactId . '/contact_people/' . $personId, json_encode(['contact_person' => $personData]));
            
            // The result is already an array, no need to decode
            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static $deleteContactPerson = [
        'type' => 'function',
        'function' => [
            'name' => 'deleteContactPerson',
            'description' => 'Deletes a contact person from a contact in Moneybird.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact the person belongs to.',
                    ],
                    'person_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact person to delete.',
                    ],
                ],
                'required' => ['contact_id', 'person_id'],
            ],
        ],
    ];
    public static function deleteContactPerson($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        $personId = $parameters['person_id'];
        
        try {
            // Use the API endpoint directly since the method is not available
            $moneybird->getConnection()->delete('contacts/' . $contactId . '/contact_people/' . $personId);
            
            return [
                'success' => true,
                'message' => 'Contact person deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static $getMandateInfo = [
        'type' => 'function',
        'function' => [
            'name' => 'getMandateInfo',
            'description' => 'Gets Moneybird Payments mandate information for a contact.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to get mandate information for.',
                    ],
                ],
                'required' => ['contact_id'],
            ],
        ],
    ];
    public static function getMandateInfo($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        
        try {
            $contact = $moneybird->contact()->find($contactId);
            $mandate = $contact->getMandateInfo();
            
            return $mandate;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static $requestMandate = [
        'type' => 'function',
        'function' => [
            'name' => 'requestMandate',
            'description' => 'Requests a new Moneybird Payments mandate for a contact.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to request a mandate for.',
                    ],
                    'email_message' => [
                        'type' => 'string',
                        'description' => 'Optional custom message to include in the mandate request email.',
                    ],
                    'identity_id' => [
                        'type' => 'string',
                        'description' => 'Optional identity ID to use for the mandate request.',
                    ],
                ],
                'required' => ['contact_id'],
            ],
        ],
    ];
    public static function requestMandate($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        
        $mandateOptions = [];
        
        if (isset($parameters['email_message'])) {
            $mandateOptions['email_message'] = $parameters['email_message'];
        }
        
        if (isset($parameters['identity_id'])) {
            $mandateOptions['identity_id'] = $parameters['identity_id'];
        }
        
        try {
            $contact = $moneybird->contact()->find($contactId);
            $result = $contact->requestMandateByEmail($mandateOptions);
            
            return [
                'success' => true,
                'message' => 'Mandate request sent successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static $requestMandateURL = [
        'type' => 'function',
        'function' => [
            'name' => 'requestMandateURL',
            'description' => 'Requests a URL for setting up a Moneybird Payments mandate.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to request a mandate URL for.',
                    ],
                    'identity_id' => [
                        'type' => 'string',
                        'description' => 'Optional identity ID to use for the mandate request.',
                    ],
                ],
                'required' => ['contact_id'],
            ],
        ],
    ];
    public static function requestMandateURL($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        
        $mandateOptions = [];
        
        if (isset($parameters['identity_id'])) {
            $mandateOptions['identity_id'] = $parameters['identity_id'];
        }
        
        try {
            $contact = $moneybird->contact()->find($contactId);
            $url = $contact->requestMandateURL($mandateOptions);
            
            return [
                'success' => true,
                'url' => $url
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static $deleteMandate = [
        'type' => 'function',
        'function' => [
            'name' => 'deleteMandate',
            'description' => 'Deletes a stored Moneybird Payments mandate for a contact.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to delete the mandate for.',
                    ],
                ],
                'required' => ['contact_id'],
            ],
        ],
    ];
    public static function deleteMandate($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        
        try {
            $contact = $moneybird->contact()->find($contactId);
            $contact->deleteMandate();
            
            return [
                'success' => true,
                'message' => 'Mandate deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static $addCharge = [
        'type' => 'function',
        'function' => [
            'name' => 'addCharge',
            'description' => 'Creates an additional charge to be invoiced at the start of next period.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to add a charge to.',
                    ],
                    'product_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the product for the charge.',
                    ],
                    'amount' => [
                        'type' => 'string',
                        'description' => 'The amount for the charge (e.g., "5 x").',
                    ],
                    'price' => [
                        'type' => 'number',
                        'description' => 'The price for the charge.',
                    ],
                    'period' => [
                        'type' => 'string',
                        'description' => 'The period for the charge (e.g., "20230601..20230630").',
                    ],
                    'description' => [
                        'type' => 'string',
                        'description' => 'Description for the charge.',
                    ],
                ],
                'required' => ['contact_id', 'product_id', 'price', 'period'],
            ],
        ],
    ];
    public static function addCharge($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        
        $chargeData = [
            'product_id' => $parameters['product_id'],
            'price' => $parameters['price'],
            'period' => $parameters['period'],
        ];
        
        if (isset($parameters['amount'])) {
            $chargeData['amount'] = $parameters['amount'];
        }
        
        if (isset($parameters['description'])) {
            $chargeData['description'] = $parameters['description'];
        }
        
        try {
            $contact = $moneybird->contact()->find($contactId);
            $charge = $contact->createAdditionalCharge($chargeData);
            
            return $charge;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static $getCharges = [
        'type' => 'function',
        'function' => [
            'name' => 'getCharges',
            'description' => 'Gets additional charges for a contact.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contact_id' => [
                        'type' => 'string',
                        'description' => 'The ID of the contact to get charges for.',
                    ],
                    'include_billed' => [
                        'type' => 'boolean',
                        'description' => 'Whether to include charges that have already been billed.',
                    ],
                ],
                'required' => ['contact_id'],
            ],
        ],
    ];
    public static function getCharges($parameters)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactId = $parameters['contact_id'];
        $includeBilled = isset($parameters['include_billed']) ? $parameters['include_billed'] : false;
        
        try {
            $contact = $moneybird->contact()->find($contactId);
            $charges = $contact->getAdditionalCharges(['include_billed' => $includeBilled]);
            
            return $charges;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}