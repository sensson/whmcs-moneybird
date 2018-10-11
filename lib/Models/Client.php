<?php

namespace WHMCS\Module\Addon\Moneybird\Models;

use \WHMCS\CustomField;
use \WHMCS\CustomField\CustomFieldValue;
use \WHMCS\Module\Addon\Setting;
use \WHMCS\Module\Addon\Moneybird\Models\Links\CustomerLink;

class Client extends \WHMCS\User\Client {
  /**
   * Eloquent Accessor to get the customer tax number
   *
   * @param none
   * @return string
   */
  public function getTaxNumberAttribute() {
    $customfield = Setting::where('module', 'eu_vat');
    $customfield = $customfield->where('setting', 'vatcustomfield');
    return $this->getCustomFieldValueByName($customfield->get()[0]->value);
  }

  /**
   * Get the value of a custom field by id
   *
   * @param int $id
   * @return string
   */
  private function getCustomFieldValueById($id) {
    $value = CustomFieldValue::where('fieldId', $id);
    $value = $value->where('relid', $this->id);

    if ($value->count() != 1) {
      return;
    }

    return $value->get()[0]->value;
  }

  /**
   * Get the value of a custom field by name
   *
   * @param string $name
   * @return string
   */
  private function getCustomFieldValueByName($name) {
    $field_id = CustomField::where('fieldName', $name);

    if ($field_id->count() == 0) {
      return;
    }

    $field_id = $field_id->get()[0]->id;
    return $this->getCustomFieldValueById($field_id);
  }

  /**
   * Save the contact in Moneybird
   *
   * @param \Picqer\Financials\Moneybird\Moneybird $moneybird
   * @return \Picqer\Financials\Moneybird\Entities\Contact
   */
  public function saveMoneybirdContact(
    \Picqer\Financials\Moneybird\Moneybird $moneybird
  ) {
    // Get the current contact
    $link = CustomerLink::find($this->id);

    // If we couldn't find a link, create a new contact
    if ($link == null) {
      $contact = $moneybird->contact();
    } else {
      try {
        // Try to find the customer in Moneybird
        $contact = $moneybird->contact()->findByCustomerId($link->moneybird_customer_id);
      } catch(\Exception $e) {
        // If that fails, the customer doesn't exist or the API is not available
        $message = $e->getMessage();

        // Only keep the error number so we know the status code
        preg_match_all('/\d+(?=:)/', $message, $out);
        $error = (int) $out[0][0];

        if ($error == 404) {
          // A 404 means that the contact doesn't exist
          // Which is strange as at this point we do have a link, so we'll
          // simply relink it to the customer id that we have stored
          $contact = $moneybird->contact();
          $contact->customer_id = $link->moneybird_customer_id;
        } else {
          // Anything else means that the API call failed
          throw new \Exception($message);
        }
      }
    }

    // Set all attributes
    $contact->company_name = $this->companyName;
    $contact->firstname = $this->firstName;
    $contact->lastname = $this->lastName;
    $contact->address1 = $this->address1;
    $contact->address2 = $this->address2;
    $contact->zipcode = $this->postcode;
    $contact->city = $this->city;
    $contact->country = $this->country;
    $contact->phone = $this->phoneNumber;
    $contact->delivery_method = 'Manual';
    $contact->tax_number = $this->tax_number;
    $contact->save();

    // As there was no CustomerLink we should create one
    if ($link == null) {
      $contact_link = new CustomerLink();
      $contact_link->whmcs_customer_id = $this->id;
      $contact_link->moneybird_customer_id = $contact->customer_id;
      $contact_link->save();

      logActivity('User created at Moneybird. User ID: ' . $this->id, 0);
    }

    return $contact;
  }
}
