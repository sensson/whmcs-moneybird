<?php

// Require any libraries needed for the cron to function.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/init.php';

use \WHMCS\Module\Addon\Moneybird\Models\Invoice;
use \WHMCS\Module\Addon\Moneybird\Models\Log;

// Do not allow direct calls
if (php_sapi_name() != "cli") {
  die('Unsupported');
}

// Set time limit to 5 minutes, we shouldn't really need longer
set_time_limit(300);

/**
 * Hook: synchronise invoices with Moneybird
 *
 * @param none
 * @return none
 */
function syncInvoiceInBatch($params = array(), $debug = false) {
  $module_settings = getMoneybirdSettings();

  // Do not run syncInvoiceInBatch if EnableCron is disabled
  if ($module_settings['EnableCron'] != 'on') {
    if ($debug) {
      print("Cron disabled\r\n");
    }

    return;
  }

  $moneybird = createMoneybirdConnection();

  // Loop through all invoices
  // TODO: Improve so InvoiceLink can be used, which would make this
  // loop much more efficient.
  $invoices = Invoice::where('tblinvoices.id', '>=', (int) $module_settings['InvoiceSyncStart']);
  $invoices = $invoices->where(function ($query) {
      $query = $query->where('tblinvoices.status', '=', 'Paid');
      $query = $query->orWhere('tblinvoices.status', '=', 'Unpaid');
      return $query;
  });

  // Do not include 0.00 invoices, perhaps make this optional in the future
  $invoices = $invoices->where('tblinvoices.total', '!=', '0.00');

  $invoice_sync_count = 0;
  foreach ($invoices->get() as $invoice) {
    try {
      // Save the invoice in Moneybird
      $moneybird_invoice = $invoice->createMoneybirdInvoice($moneybird);

      // Throttle invoice synchronisation so we're not overloading the API
      if ($moneybird_invoice != null) {
        $invoice_sync_count++;
      }

      if ($invoice_sync_count >= (int) $module_settings['InvoiceSyncThrottle']) {
        if ($debug) {
          print("Throttling limit hit.\r\n");
        }

        break;
      }
    } catch (\Exception $e) {
      // Log invoice exception and break the loop
      $log = new Log;
      $log->whmcs_id = $invoice->id;
      $log->status = 1;
      $log->type = 'invoice';
      $log->message = $e->getMessage();
      $log->save();
      break;
    }
  }

  if ($debug) {
    print("${invoice_sync_count} invoices processed.\r\n");
  }
}

// Example of how to synchronise a single invoice
function syncInvoice($params) {
  $moneybird = createMoneybirdConnection();
  $invoice = Invoice::find(10985);
  $invoice->createMoneybirdInvoice($moneybird);
}

// Run jobs, enable debug mode
syncInvoiceInBatch(array(), true);
