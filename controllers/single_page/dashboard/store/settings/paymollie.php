<?php
namespace Concrete\Package\CommunityStoreMollie\Controller\SinglePage\Dashboard\Store\Settings;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Loader;
use Database;
use Package;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Config;
use Core;

class Paymollie extends DashboardPageController{

  public function view(){
    $apikey = $this->includePaymentAPI();
    $this->loadPaymentData();
  }

  public function rescan(){
    //scan for mollie methods through api
    $apikey = $this->includePaymentAPI();
    //test_qBsqUj8twKrtsU9BGkuqUcUu9jHCUx

    $mollie = new \Mollie_API_Client;
    $mollie->setApiKey($apikey);
    $methods = $mollie->methods->all();
    if(!empty($methods)){
      $db = Database::connection();
      foreach($methods as $payMethod){
        $cData = array();
        $cData[] = $payMethod->id;
        $check = $db->fetchAssoc('select * from molStoreMethods where pMollieID=?', $cData);
        if(empty($check['pID'])){
          //insert
          $iData = array();
          $iData[] = $payMethod->id;
          $iData[] = $payMethod->description;
          $iData[] = $payMethod->image->normal;
          $iData[] = $payMethod->amount->minimum;
          $iData[] = $payMethod->amount->maximum;
          $db->Execute('insert into molStoreMethods (pMollieID, pTitle, pImage, pMinimum, pMaximum) values (?,?,?,?,?)', $iData);
        }
      }
    }
    $this->loadPaymentData();
  }

  public function includePaymentAPI(){
    $pkg = Package::getByHandle('community_store_mollie');
    $ppath = $pkg->getPackagePath();
    include_once($ppath.'/vendor/Mollie/API/Autoloader.php');
    $pkgconfig = $pkg->getConfig();
    $this->set('apiKey',$pkgconfig->get('storemollie.apikey'));
    return $pkgconfig->get('storemollie.apikey');
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
