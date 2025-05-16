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
     * @param string $firstname The first name of the contact.
     * @param string $lastname The last name of the contact.
     * @param string $company_name The company name associated with the contact.
     * @return mixed The created Moneybird contact object.
     */
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

    /**
     * Delete a contact from Moneybird.
     *
     * @param string $id The ID of the contact to delete.
     * @return void
     */
    public static function deleteContact($id)
    {
        $moneybird = Moneybird::getMoneybird();
        $contact = $moneybird->contact($id);
        $contact->delete();
    }
}