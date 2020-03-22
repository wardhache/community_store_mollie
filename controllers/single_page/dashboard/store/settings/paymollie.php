<?php
namespace Concrete\Package\CommunityStoreMollie\Controller\SinglePage\Dashboard\Store\Settings;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\RedirectResponse;
use Concrete\Package\CommunityStoreMollie\Src\Mollie\Method;
use View;
use Loader;
use Database;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Config;
use Core;

class Paymollie extends DashboardPageController{

  public function view($status = null)
  {
    $this->set('status', $status);

    $apikey = $this->includePaymentAPI();
    $this->loadPaymentData();
  }

  public function rescan()
  {
    Method::rescan();

    $response = new RedirectResponse('/dashboard/store/settings/paymollie/rescanned');
    $response->send();
  }

  public function includePaymentAPI(){
    $this->set('apiKey', Config::get('community_store.mollie.api_key'));

    return Config::get('community_store.mollie.api_key');
  }
  public function loadPaymentData(){
    $this->set('form',Core::make("helper/form"));
    //db load current mollie methods
    $db = Database::connection();

    $molliemethods = $db->fetchAll('select * from molStoreMethods');
    $this->set('molliemethods', $molliemethods);
  }

}

?>
