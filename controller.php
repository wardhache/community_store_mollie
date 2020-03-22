<?php
namespace Concrete\Package\CommunityStoreMollie;

/**
 * Community Store Mollie
 *
 * @author Jos De Berdt <www.josdeberdt.be>
 * @version 0.0.8
 * @package community_store_mollie
 * @github jozzeh
 */

use Concrete\Core\Package\Package;
use Page;
use SinglePage;
use Route;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use Whoops\Exception\ErrorException;

class controller extends Package{

  protected $pkgHandle = 'community_store_mollie';
  protected $appVersionRequired = '8.2.1';
  protected $pkgVersion = '0.0.8';

  public function getPackageDescription(){
    return t("Mollie Payment Method for Community Store");
  }

  public function getPackageName(){
    return t("Mollie payment method");
  }

  protected $pkgAutoloaderRegistries = [
    'src/CommunityStore' => '\Concrete\Package\CommunityStoreMollie\Src\CommunityStore',
    'src/Mollie' => '\Concrete\Package\CommunityStoreMollie\Src\Mollie',
  ];

  public function on_start(){
    $this->registerRoutes();
    $this->setupAutoloader();
  }

  public function install(){
    $installed = Package::getInstalledHandles();
    if(!(is_array($installed) && in_array('community_store',$installed)) ) {
      throw new ErrorException(t('This package requires that Community Store be installed'));
    } else {
      $pkg = parent::install();
      $pm = new PaymentMethod();
      $pm->add('mollie','Mollie',$pkg);

      $sp = Page::getByPath('/dashboard/store/settings/paymollie');
      if ($sp->isError() || (!is_object($sp))) {
          $sp = SinglePage::add('/dashboard/store/settings/paymollie', $pkg);
      }
      if(is_object($sp)){
        $uData = array();
        $uData['cName'] = "Mollie Payment";
        $sp->update($uData);
      }

      $orderStatus = StoreOrderStatus::getByHandle('nodelivery');
      if(is_object($orderStatus)){
        $pkg->getConfig()->save('storemollie.orderStatusOnCancel','nodelivery');
      }
    }
  }

  public function upgrade(){
    parent::upgrade();
    $pkg = Package::getByHandle('community_store_mollie');
    $sp = Page::getByPath('/dashboard/store/settings/paymollie');
    if ($sp->isError() || (!is_object($sp))) {
      $sp = SinglePage::add('/dashboard/store/settings/paymollie', $pkg);
    }
    if(is_object($sp)){
      $uData = array();
      $uData['cName'] = "Mollie Payment";
      $sp->update($uData);
    }
  }

  public function uninstall(){
    if(PaymentMethod::getByHandle('mollie')){
      PaymentMethod::getByHandle('mollie')->delete();
    }

    parent::uninstall();
  }

  public function registerRoutes(){
    Route::register('/checkout/mollieresponse', '\Concrete\Package\CommunityStoreMollie\Src\CommunityStore\Payment\Methods\Mollie\MolliePaymentMethod::validateCompletion');
    Route::register('/checkout/ordercompletion/{oID}', '\Concrete\Package\CommunityStoreMollie\Src\CommunityStore\Payment\Methods\Mollie\MolliePaymentMethod::customerValidation', 'customervalidate' ,array('oID' => '\d+'));
  }

  private function setupAutoloader()
  {
    if (file_exists($this->getPackagePath() . '/vendor')) {
      require_once $this->getPackagePath() . '/vendor/autoload.php';
    }
  }
}
