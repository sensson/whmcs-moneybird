# Moneybird WHMCS addon

This module sets up synchronisation between WHMCS and Moneybird. Its freely
available for anyone to use. If you have suggestions, please open an issue or
make the change by raising a pull request.

# Requirements

* WHMCS 7.6 or higher;
* EU VAT addon enabled;
* Composer

# Installation

* Run `composer install`;
* Upload all files to `modules/addons/moneybird/`.

# Configuration

This addon requires you to configure:

* A Moneybird access token;
* A Moneybird administration id;

You can configure both of them after activating the module via Setup > Addon
Modules. Once the module has been configured you can set up your ledgers
and tax mappings.

A token can be created via https://moneybird.com/user/applications/new.

## Ledgers and tax

If you want to record the revenue of your product groups on specific ledgers
you can link them via Addons > Moneybird > Ledger mapping.

Tax rates can be mapped via Addons > Moneybird > Tax mapping.

If you are unsure about your taxes, please consult with an accountant first.

## Contacts and invoices

Every invoice and customer will get a new id in Moneybird. The addon will keep
track of these links. The invoice id in WHMCS will be set as reference.

# Development

We strongly believe in the power of open source. This module is our way of
saying thanks.

If you want to contribute please:

1. Fork the repository.
2. Push to your fork and submit a pull request to the develop branch.

# Tests

Sorry, no tests yet.

# Support

This module is tested against WHMCS 7.6. It may work with previous versions
too. It is recommended to test this in a safe environment before enabling
it in production.

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

Sensson does not provide commercial support or paid development.
