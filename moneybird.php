<?php
/**
 * Moneybird integration
 *
 * This module integrates WHMCS into Moneybird and the other way round. Sensson
 * does not provide commercial support or paid development.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright Copyright (c) Sensson
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Require any libraries needed for the module to function.
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Module\Addon\Moneybird\Core\AdminPages;

/**
 * Define the Moneybird configuration
 *
 * @param none
 * @return array
 */
function moneybird_config() {
    $config = array(
      'name'        => 'Moneybird',
      'description' => 'This module integrates Moneybird into WHMCS',
      'author'      => 'Sensson',
      'language'    => 'english',
      'version'     => '0.0.2',
      'fields'      => array(
        'AccessToken' => array(
          'FriendlyName' => 'MoneyBird access token',
          'Type' => 'password',
          'Size' => '64',
          'Default' => '',
          'Description' => 'Enter the MoneyBird access token',
        ),
        'AdministrationId' => array(
          'FriendlyName' => 'MoneyBird administration',
          'Type' => 'text',
          'Size' => '24',
          'Default' => '',
          'Description' => 'Enter your Moneybird administration ID',
        ),
        'InvoiceSyncStart' => array(
          'FriendlyName' => 'First invoice number',
          'Type' => 'text',
          'Size' => '12',
          'Default' => '1',
          'Description' => 'Specify the first invoice number to synchronise',
        ),
        'InvoiceSyncThrottle' => array(
          'FriendlyName' => 'Throttle invoice sync',
          'Type' => 'text',
          'Size' => '12',
          'Default' => '10',
          'Description' => 'Specify the number of invoices to sync in one go',
        ),
        'EnableCron' => array(
          'FriendlyName' => 'Enable cronjob',
          'Type' => 'yesno',
          'Description' => 'Tick to enable the cronjob',
        ),
      ),
    );

    return $config;
}

/**
 * Activation of the Moneybird module
 *
 * @param none
 * @return array
 */
function moneybird_activate() {
  if (Capsule::schema()->hasTable('mod_moneybird_log') and
      Capsule::schema()->hasTable('mod_moneybird_ledger_links') and
      Capsule::schema()->hasTable('mod_moneybird_tax_links') and
      Capsule::schema()->hasTable('mod_moneybird_invoice_links') and
      Capsule::schema()->hasTable('mod_moneybird_contact_links')
  ) {
    return array(
      'status' => 'success',
      'description' => 'The Moneybird addon has been installed already and is activated'
    );
  }

  try {
    Capsule::schema()->create(
      'mod_moneybird_log',
      function ($table) {
        $table->increments('id');
        $table->dateTime('created_at');
        $table->dateTime('updated_at');
        $table->integer('whmcs_id');
        $table->bigInteger('moneybird_id');
        $table->text('type');
        $table->integer('status');
        $table->text('message');
      }
    );

    Capsule::schema()->create(
      'mod_moneybird_ledger_links',
      function ($table) {
        $table->integer('whmcs_product_group_id');
        $table->bigInteger('moneybird_ledger_id');
        $table->unique('whmcs_product_group_id');
      }
    );

    Capsule::schema()->create(
      'mod_moneybird_tax_links',
      function ($table) {
        $table->integer('whmcs_tax_id');
        $table->bigInteger('moneybird_tax_id');
        $table->unique('whmcs_tax_id');
      }
    );

    Capsule::schema()->create(
      'mod_moneybird_invoice_links',
      function ($table) {
        $table->integer('whmcs_invoice_id');
        $table->text('moneybird_invoice_id');
        $table->bigInteger('moneybird_id');
        $table->unique('whmcs_invoice_id');
      }
    );

    Capsule::schema()->create(
      'mod_moneybird_customer_links',
      function ($table) {
        $table->integer('whmcs_customer_id');
        $table->text('moneybird_customer_id');
        $table->unique('whmcs_customer_id');
      }
    );

    Capsule::schema()->create(
      'mod_moneybird_workflow_links',
      function ($table) {
        $table->string('whmcs_payment_method', 255);
        $table->text('moneybird_workflow_id');
        $table->unique('whmcs_payment_method');
      }
    );

    return array(
      'status' => 'success',
      'description' => 'The Moneybird addon has been activated'
    );
  } catch (Exception $e) {
    return array(
      'status' => 'error',
      'description' => 'The installation failed with ' . $e->getMessage()
    );
  }

}

/**
 * Upgrade the installation
 *
 * @param array $vars
 * @return none
 */
function moneybird_upgrade($vars) {
  $version = $vars['version'];

  // In 0.0.2 we have added mod_moneybird_workflow_links
  if (version_compare($version, '0.0.2', '<')) {
    Capsule::schema()->create(
      'mod_moneybird_workflow_links',
      function ($table) {
        $table->string('whmcs_payment_method', 255);
        $table->text('moneybird_workflow_id');
        $table->unique('whmcs_payment_method');
      }
    );

    Capsule::schema()->table(
      'mod_moneybird_invoice_links',
      function($table) {
        $table->bigInteger('moneybird_id');
      }
    );

    // Due to the lack of dbal/doctrine we have to run a raw statement here
    Capsule::statement("ALTER TABLE `mod_moneybird_log` CHANGE `moneybird_id` `moneybird_id` BIGINT(20) NOT NULL;");
  }
}

/**
 * Deactivate the installation
 *
 * @return array Optional success/failure message
 */
function moneybird_deactivate() {
  try {
    // TODO: Decide if this should be done as it could turn out to be a
    // small disaster if someone hits it by accident.
    // Capsule::schema()->dropIfExists('mod_moneybird_log');
    // Capsule::schema()->dropIfExists('mod_moneybird_ledger_links');
    // Capsule::schema()->dropIfExists('mod_moneybird_contact_links');
    // Capsule::schema()->dropIfExists('mod_moneybird_invoice_links');
    // Capsule::schema()->dropIfExists('mod_moneybird_tax_links');
    // Capsule::schema()->dropIfExists('mod_moneybird_workflow_links');

    return array(
      'status' => 'success',
      'description' => 'The module has been deactivated'
    );
  } catch (Exception $e) {
    return array(
      'status' => 'error',
      'description' => 'Deactivating the Moneybird addon failed with ' . $e->getMessage()
    );
  }
}

/**
 * Admin area output.
 *
 * @return string
 */

function moneybird_output($vars) {
  $smarty = new Smarty;
  $smarty->caching = false;
  $smarty->compile_dir = $GLOBALS['templates_compiledir'];
  $smarty->template_dir = dirname(__FILE__) . '/templates';

  $pages = new AdminPages();

  if ($_POST) {
    echo $pages->post($_GET['page'], $smarty, $vars);
  } else {
    echo $pages->get($_GET['page'], $smarty, $vars);
  }
}

/**
 * Admin area sidebar output.
 *
 * @return string
 */

 function moneybird_sidebar($vars) {
  $smarty = new Smarty;
  $smarty->caching = false;
  $smarty->compile_dir = $GLOBALS['templates_compiledir'];
  $smarty->template_dir = dirname(__FILE__) . '/templates';

  // Get a list of all pages
  $pages = new AdminPages();
  $smarty->assign('language', $vars['_lang']);
  $smarty->assign('pages', $pages->getPages());
  $smarty->assign('modulelink', $vars['modulelink']);

  return $smarty->fetch('admin/sidebar.tpl');
}
