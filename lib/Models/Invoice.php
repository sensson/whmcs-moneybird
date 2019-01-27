<?php

namespace WHMCS\Module\Addon\Moneybird\Models;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \WHMCS\Service\Service;
use \WHMCS\Domain\Domain;
use \WHMCS\Billing\Payment\Transaction;
use \WHMCS\Module\Addon\Setting;
use \WHMCS\Module\Addon\Moneybird\Models\Client;
use \WHMCS\Module\Addon\Moneybird\Models\Log;
use \WHMCS\Module\Addon\Moneybird\Models\Links\LedgerLink;
use \WHMCS\Module\Addon\Moneybird\Models\Links\InvoiceLink;
use \WHMCS\Module\Addon\Moneybird\Models\Links\TaxLink;
use \WHMCS\Module\Addon\Moneybird\Models\Links\WorkflowLink;

class Invoice extends \WHMCS\Billing\Invoice {
  private $eu_countries = array(
      'AT', 'BE', 'BG', 'CY', 'CZ', 'DE',
      'DK', 'EE', 'ES', 'FI', 'FR', 'GB',
      'GR', 'HR', 'HU', 'IE', 'IM', 'IT',
      'LT', 'LU', 'LV', 'MC', 'MT', 'NL',
      'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
  );

  private $domain_categories = array(
    'Domain',
    'DomainTransfer',
    'DomainRegister'
  );

  private $discount_categories = array(
    'PromoDomain',
    'PromoHosting',
    'GroupDiscount'
  );

  /**
   * Eloquent relationship with moneybird
   *
   * @param none
   * @return \WHMCS\Module\Addon\Moneybird\Models\Links\InvoiceLink
   */
  public function moneybird() {
    return $this->hasOne(
      '\WHMCS\Module\Addon\Moneybird\Models\Links\InvoiceLink',
      'whmcs_invoice_id'
    );
  }

  /**
   * Eloquent Accessor to get the Moneybird invoice id
   *
   * @param none
   * @return string
   */
  public function getMoneybirdIdAttribute() {
    return $this->moneybird->moneybird_invoice_id;
  }

  /**
   * Eloquent Accessor to get the Moneybird invoice tax rate
   *
   * @param none
   * @return string
   */
  public function getTaxRateIdAttribute() {
    // Find our home country
    $home_country = Setting::where('module', 'eu_vat');
    $home_country = $home_country->where('setting', 'homecountry')->get()[0];
    $home_country = strtoupper($home_country->value);
    $client_country = strtoupper($this->client->country);

    // Map tax rates to WHMCS tax ID's
    $tax_links = array();
    foreach (TaxLink::all() as $link) {
      $tax_links[$link->whmcs_tax_id] = $link->moneybird_tax_id;
    }

    // Map tax rates to countries
    $tax_countries = array();
    foreach (Capsule::table('tbltax')->select()->get() as $rate) {
      $tax_countries[$rate->country] = $tax_links[$rate->id];
    }

    // Charge local VAT, no tax exemption
    if ($client_country == $home_country) {
      return $tax_links[9991];
    }

    // If the customer was tax exempt AND from Europe
    if ($this->tax1 == 0 && in_array($client_country, $this->eu_countries)) {
      return $tax_links[9992];
    }

    // Specific tax rates (e.g. VAT MOSS)
    if ($this->tax1 > 0 && in_array($client_country, $this->eu_countries)) {
      return $tax_countries[$this->client->country];
    }

    // If we didn't charge tax
    if (intval($this->tax1) == 0) {
      return $tax_links[9993];
    }

    // Default to the local tax rate
    return $tax_links[9991];
  }

  /**
   * Create an invoice in Moneybird
   *
   * @param \Picqer\Financials\Moneybird\Moneybird $moneybird
   * @return \Picqer\Financials\Moneybird\Entities\SalesInvoice
   */
  public function createMoneybirdInvoice(
    \Picqer\Financials\Moneybird\Moneybird $moneybird
  ) {
    // If a link exists, do nothing
    if (InvoiceLink::find($this->id) != null) {
      return null;
    }

    // If the invoice contains invoices (yes, invoices, thanks WHMCS), do nothing
    if ($this->items()->where('type', 'Invoice')->count() >= 1) {
      return null;
    }

    // Update the customer -- find is in fact necessary to ensure
    // we're using our Moneybird Client model instead of $this->client
    $client = Client::find($this->client->id);
    $contact = $client->saveMoneybirdContact($moneybird);

    // Set the tax_rate_id before looping through all items
    $tax_rate_id = $this->tax_rate_id;

    // Get ledger links
    $ledger_links = array();
    foreach (LedgerLink::all() as $link) {
      $ledger_links[$link->whmcs_product_group_id] = $link->moneybird_ledger_id;
    }

    // Get all items of the invoice in WHMCS
    $items = $this->items()->get();

    // Convert them to salesInvoiceDetail()
    $salesInvoiceDetailsArray = array();
    foreach ($items as $item) {
      $category = 0;

      // Find the category of the product
      if (in_array($item->type, $this->domain_categories)) {
        $category = 99999;
      } elseif (in_array($item->type, $this->discount_categories)) {
        $category = 99998;
      } else {
        $service = Service::find($item->relatedEntityId);

        if ($service != null) {
          $category = $service->product->gid;
        }
      }

      // Create the invoice lines
      $salesInvoiceDetail = $moneybird->salesInvoiceDetail();
      $salesInvoiceDetail->description = $item->description;
      $salesInvoiceDetail->ledger_account_id = $ledger_links[$category];
      $salesInvoiceDetail->amount = 1;
      $salesInvoiceDetail->price = $item->amount;
      $salesInvoiceDetail->tax_rate_id = $tax_rate_id;
      $salesInvoiceDetailsArray[] = $salesInvoiceDetail;
    }

    // Create a new invoice
    $salesInvoice = $moneybird->salesInvoice();
    $salesInvoice->contact_id = $contact->id;
    $salesInvoice->reference = $this->id;
    $salesInvoice->details = $salesInvoiceDetailsArray;
    $salesInvoice->invoice_date = $this->dateCreated->toDateString();

    // Optional, set a workflow if it has been configured
    $workflow_link = WorkflowLink::find($this->paymentmethod);
    if ($workflow_link != null) {
      $salesInvoice->workflow_id = $workflow_link->moneybird_workflow_id;
    }

    // Store the invoice and make it final by 'sending' it
    $invoice = $salesInvoice->save();
    $invoice->sendInvoice('Manual');

    // sendInvoice doesn't return the invoice id, fetch it manually
    $invoice = $moneybird->salesInvoice()->find($invoice->id);

    // Create a link
    $invoice_link = new InvoiceLink();
    $invoice_link->whmcs_invoice_id = $this->id;
    $invoice_link->moneybird_invoice_id = $invoice->invoice_id;
    $invoice_link->moneybird_id = $invoice->id;
    $invoice_link->save();

    logActivity('Invoice created at Moneybird. Invoice ID: ' . $this->id, 0);

    return $invoice;
  }

  /**
   * Create a Moneybird payment in WHMCS
   *
   * @param object transaction entity
   * @param \Picqer\Financials\Moneybird\Moneybird $moneybird
   * @return \WHMCS\Billing\Invoice
   */
  public function addPaymentFromMoneybird(
    object $payment,
    \Picqer\Financials\Moneybird\Moneybird $moneybird
  ) {
    // Check if it is a valid payment
    if ($payment->invoice_type !== 'SalesInvoice') {
      return null;
    }

    // If a link does not exist, do nothing--it means we never synchronised
    // this invoice and that means there can't be a payment registered in
    // Moneybird either. Something is wrong.
    if (InvoiceLink::find($this->id) == null) {
      return null;
    }

    // If the invoice contains invoices (yes, invoices, thanks WHMCS), do nothing
    // This shouldn't happen--it's just a precaution
    if ($this->items()->where('type', 'Invoice')->count() >= 1) {
      return null;
    }

    // Only process unpaid invoices
    if (strtolower($this->status) == 'unpaid') {
      // We check for transactions with the Transaction model instead of the
      // collection in $invoices->transactions as it is a little easier
      $amount = $payment->price_base;
      $payment_date = $payment->payment_date;

      // 1. Check for the same transaction
      $same_transactions = Transaction::where('invoiceid', '=', $this->id);
      $same_transactions = $same_transactions->where('transid', '=', $payment->id);
      if ($same_transactions->get()->count() >= 1) {
        Log::updateOrCreate(
            ['whmcs_id' => $this->id, 'moneybird_id' => $payment->id, 'status' => '1'],
            ['message' => "{$this->id} - Transaction has been processed already"]
        );

        return null;
      }

      // 2. Check for similar transactions
      $similar_transactions = Transaction::where('invoiceid', '=', $this->id);
      $similar_transactions = $similar_transactions->where('amountin', '=', $amount);
      $similar_transactions = $similar_transactions->whereDate('date', '=', $payment_date);
      if ($similar_transactions->get()->count() >= 1) {
        Log::updateOrCreate(
            ['whmcs_id' => $this->id, 'moneybird_id' => $payment->id, 'status' => '2'],
            ['message' => "{$this->id} - It is very likely that this transaction has been processed already."]
        );

        return null;
      }

      // Create an API call
      $add_payment = array(
        'invoiceid' => $this->id,
        'transid' => $payment->id,
        'gateway' => 'banktransfer',
        'date' => $payment_date,
      );

      // This is required if $amount is less than the current amount due
      // See: https://developers.whmcs.com/api-reference/addinvoicepayment/
      // the amount paid, can be left undefined to take full amount of invoice
      if (($this->total - $this->balance) - $amount >= 0) {
        $payment['amount'] = $amount;
      }

      // We don't really care at this point, we want to be sure we're not
      // adding duplicate payments -- we need to filter for status 1 and 2
      Log::updateOrCreate(
          ['whmcs_id' => $this->id, 'moneybird_id' => $payment->id, 'status' => '3'],
          ['message' => "{$this->id} - payment added for {$amount}."]
      );

      // Add the actual payment
      $results = localAPI('AddInvoicePayment', $add_payment);

      return $this;
    }
  }
}
