<?php

namespace App\Helpers\Tools;

use App\Helpers\Moneybird;
use Illuminate\Support\Collection;

class MoneyBirdContacts
{
    /**
     * Get all contacts from Moneybird.
     *
     * @return Collection A collection of contacts containing id, firstname, lastname, and company_name.
     */
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

    /**
     * Create a new contact in Moneybird.
     *
     * @param string $company_name The company name of the contact.
     * @param string $firstname The first name of the contact.
     * @param string $lastname The last name of the contact.
     * @return mixed The created Moneybird contact object.
     */
    public static function createContact(string $company_name, string $firstname, string $lastname)
    {
        $moneybird = Moneybird::getMoneybird();
        $contact = $moneybird->contact();       

        $contact->company_name = $company_name;
        $contact->firstname = $firstname;  
        $contact->lastname = $lastname;
       
        $contact->save();

        return $contact;
    }

    /**
     * Update a contact in Moneybird.
     *
     * @param string $id The ID of the contact to update.
     * @param string $company_name The company name of the contact.
     * @param string $firstname The first name of the contact.
     * @param string $lastname The last name of the contact.
     * @return mixed The updated Moneybird contact object.
     */
    public static function updateContact(string $id, string $company_name, string $firstname, string $lastname)
    {
        $moneybird = Moneybird::getMoneybird();
        $contact = $moneybird->contact($id);
        
        $contact->company_name = $company_name;
        $contact->firstname = $firstname;
        $contact->lastname = $lastname;
        
        $contact->save();
        
        return $contact;
    }
}