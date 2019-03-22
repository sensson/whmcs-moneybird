<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/init.php';

use \WHMCS\Module\Addon\Moneybird\Models\Invoice;

//
// For incoming webhooks the idea is:
// Get the payload--we need the entity part in our transaction
// $payload = file_get_contents("php://input");
// $payload = json_encode($payload);
// if ($payload->action == 'payment_registered') ...
//    processMoneybirdTransaction($payload->entity);
// ...
//
// The problem with webhooks is that there are cases where they WILL fail and
// you cannot debug them if they do.
//
// A cronjob is much more reliable
//
// To create a webhook we need to patch the moneybird class and we can then
// use the following code or similar.
//
// $moneybird = createMoneybirdConnection();
// $all = $moneybird->Webhook()->getAll();
// foreach ($all as $key => $webhook) {
//   if ($recreate) {
//     $webhook->delete();
//   }
// }
//
// if ($recreate) {
//   $webhook = $moneybird->Webhook();
//   $webhook->url = $endpoint;
//   $webhook->events = [
//     'payment',
//   ];
//   $webhook->save();
// }
//
