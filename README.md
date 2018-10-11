# Moneybird WHMCS addon

This module sets up synchronisation between WHMCS and Moneybird. At this time
its a one way sync from WHMCS into Moneybird.

# Requirements

* WHMCS 7.6 or higher;
* EU VAT addon enabled;
* Composer

# Installation

* Run `composer install`;
* Upload all files to `modules/addons/moneybird/`.

## Cron

Configure a cronjob to run every 5 minutes.

`php -q /path/to/whmcs/modules/addons/moneybird/cron.php`

The cron is disabled by default. It can be managed through the addon
settings in Setup > Addon Modules. We suggest to leave the cron disabled
until you have configured your ledgers and taxes.

## Configuration

You can activate this module via Setup > Addon Modules in WHMCS.

This addon requires you to configure:

* A Moneybird access token;
* A Moneybird administration id;
* The first invoice you want to synchronise;

A token can be created via https://moneybird.com/user/applications/new.

## Ledgers and tax rates

### Ledgers

Moneybird calls ledgers categories. It allows you to group your revenue and
expenses. You can map product groups to ledgers in Moneybird. This is done
via Addons > Moneybird > Ledger mapping.

It is recommended to map product groups to categories but not required.

If you are unsure about ledgers, please consult with an accountant first.

### Tax rates

We strongly recommend to map your taxes in WHMCS to your tax rates in
Moneybird. This is done via Addons > Moneybird > Tax mapping.

If you are unsure about your taxes, please consult with an accountant first.

## Enable the cron

You can enable the cron via its setting in Setup > Addon Modules in WHMCS.

# How does it work?

Once you've got everything set up, configured your ledgers and taxes, you can
enable the cron. From that point onwards it will synchronise your contacts
and invoices to Moneybird. Invoices are synced once, contacts are checked
and updated every time a new invoice is created.

## Contacts and invoices

The addon will keep track of both contacts and invoices in WHMCS and Moneybird.

### Contacts

Every contact is given a new id in Moneybird. This is done to prevent overlap
since Moneybird uses the same numbering for both suppliers and customers. If
a supplier in Moneybird would have the same id as a customer in WHMCS, its
details would be changed.

### Invoices

WHMCS cannot be used as a single source of truth for accounting purposes. This
is why invoices are given a new number in Moneybird. The original invoice id
from WHMCS is set as a reference. This reference is also used by Moneybird
to automatically map payments.

# Development

We strongly believe in the power of open source. This module is our way of
saying thanks.

If you want to contribute please:

1. Fork the repository.
2. Push to your fork and submit a pull request to the develop branch.

# Tests

Sorry, no tests yet. Contributions are much appreciated.

# Support

This module is tested against WHMCS 7.6. It may work with previous versions
too. It is recommended to test this in a safe environment before enabling
it in production.

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

Sensson does not provide commercial support or paid development, neither are
we trained accountants. Always consult with a professional.
