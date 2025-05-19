<?php

namespace App\Helpers\Tools\Moneybird;

use App\Helpers\Tools\ToolboxInterface;
use App\Helpers\Moneybird;

class MoneybirdContactPeopleToolbox implements ToolboxInterface
{
    public static function getTools()
    {
        $tools = [];
        $tools[] = self::$getContactPersonById;
        $tools[] = self::$createContactPerson;
        $tools[] = self::$updateContactPerson;
        $tools[] = self::$deleteContactPerson;
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

    public static $getContactPersonById = [
        'type' => 'function',
        'function' => [
            'name' => 'getContactPersonById',
            'description' => 'Get a contact person by id',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string', 'description' => 'The id of the contact person'],
                ],
            ],
        ],
    ];
    public static function getContactPersonById($id)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactPerson = $moneybird->contactPerson()->find($id);
        return $contactPerson;
    }

    public static $createContactPerson = [
        'type' => 'function',
        'function' => [
            'name' => 'createContactPerson',
            'description' => 'Create a new contact person',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contactPerson' => ['type' => 'object',
                     'description' => 'The contact person to create',
                     'properties' => [
                        'contact_id' => ['type' => 'string', 'description' => 'The id of the contact'],
                        'firstname' => ['type' => 'string', 'description' => 'The first name of the contact person'],
                        'lastname' => ['type' => 'string', 'description' => 'The last name of the contact person'],
                        'phone' => ['type' => 'string', 'description' => 'The phone number of the contact person'],
                        'email' => ['type' => 'string', 'description' => 'The email of the contact person'],
                        'department' => ['type' => 'string', 'description' => 'The department of the contact person'],

                     ],
                    ],
                ],
            ],
        ],
    ];
    public static function createContactPerson($contactPerson)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactPerson = $moneybird->contactPerson($contactPerson);
        return $contactPerson;
    }

    public static $updateContactPerson = [
        'type' => 'function',
        'function' => [
            'name' => 'updateContactPerson',
            'description' => 'Update a contact person',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'contactPerson' => ['type' => 'object',
                     'description' => 'The contact person to update',
                     'properties' => [
                        'id' => ['type' => 'string', 'description' => 'The id of the contact person'],
                        'firstname' => ['type' => 'string', 'description' => 'The first name of the contact person'],
                        'lastname' => ['type' => 'string', 'description' => 'The last name of the contact person'],
                        'phone' => ['type' => 'string', 'description' => 'The phone number of the contact person'],
                        'email' => ['type' => 'string', 'description' => 'The email of the contact person'],
                        'department' => ['type' => 'string', 'description' => 'The department of the contact person'],
                     ],
                    ],
                ],
            ],
        ],
    ];
    public static function updateContactPerson($contactPerson)
    {
        $moneybird = Moneybird::getMoneybird();
        $id = $contactPerson['id'];
        unset($contactPerson['id']);
        $contactPerson = $moneybird->contactPerson()->find($id);
        foreach ($contactPerson as $key => $value) {
            $contactPerson->$key = $value;
        }
        $contactPerson->save();
        return $contactPerson;
    }

    public static $deleteContactPerson = [
        'type' => 'function',
        'function' => [
            'name' => 'deleteContactPerson',
            'description' => 'Delete a contact person',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'string', 'description' => 'The id of the contact person'],
                ],
            ],
        ],
    ];
    public static function deleteContactPerson($id)
    {
        $moneybird = Moneybird::getMoneybird();
        $contactPerson = $moneybird->contactPerson()->find($id);
        $contactPerson->delete();
        return $contactPerson;
    }
}
