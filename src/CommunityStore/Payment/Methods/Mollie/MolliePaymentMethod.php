<?php
  namespace Concrete\Package\CommunityStoreMollie\Src\CommunityStore\Payment\Methods\Mollie;

  use Package;
  use Core;
  use Controller;
  use URL;
  use Config;
  use Session;
  use Log;
  use Page;
  use User;
  use Loader;
  use Database;
  use Redirect;
  use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
  use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
  use \Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
  use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
  use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
  use \Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
  use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
  use Events;
  use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderEvent as StoreOrderEvent;

  class MolliePaymentMethod extends StorePaymentMethod
  {
      public $external = true;

      public function dashboardForm()
      {
          $pkg = Package::getByHandle("community_store_mollie");
          $pkgconfig = $pkg->getConfig();
          $this->set('apiKey',$pkgconfig->get('storemollie.apikey'));
          $this->set('orderStatusOnCancel',$pkgconfig->get('storemollie.orderStatusOnCancel'));
          $this->set('form',Core::make("helper/form"));
      }

      public function save(array $data = [])
      {
          $pkg = Package::getByHandle("community_store_mollie");
          $pkg->getConfig()->save('storemollie.apikey',$data['apiKey']);
          $pkg->getConfig()->save('storemollie.orderStatusOnCancel',$data['orderStatusOnCancel']);
      }
      public function validate($data,$e)
      {
          //validation stuff
          //this checks if the method is enabled. If it isn't enabled,
          //then maybe we don't care to validate
          $pm = StorePaymentMethod::getByHandle('mollie');
          if ($args['paymentMethodEnabled'][$pm->getID()]==1) {
              if ($args['apiKey']=="") {
                  $e->add(t("API Key must be set"));
              }
          }
          return $e;
      }
      public function checkoutForm()
      {
          $years = array();
          $year = date("Y");
          for($i=0;$i<15;$i++){
              $years[$year+$i] = $year+$i;
          }
          $this->set("years",$years);
          $this->set('form',Core::make("helper/form"));

          $db = Database::connection();
          $molliemethods = $db->fetchAll('select * from molStoreMethods');
          $this->set('molliemethods', $molliemethods);
      }

      public function redirectForm(){
        //nothing here
      }

      public function getAction(){
        //create mollie payment and get paymentURL
        $pkg = Package::getByHandle('community_store_mollie');
        $pkgconfig = $pkg->getConfig();

        $mollie = new \Mollie_API_Client;
				$mollie->setApiKey($pkgconfig->get('storemollie.apikey'));

        $customer = new StoreCustomer();
        $totals = StoreCalculator::getTotals();
        $order = StoreOrder::getByID(Session::get('orderID'));

        $checkoutPage = Page::getByPath('checkout');
        $nh = Loader::helper('navigation');
        $cpl = BASE_URL.'/index.php/checkout';

        $payment = $mollie->payments->create(
		        array(
	            'amount'      => $order->getTotal(),
	            'description' => Config::get('concrete.site').' Order : '.$order->getOrderID(),
	            'redirectUrl' => $cpl.'/ordercompletion/'.$order->getOrderID(),
              'webhookUrl' => $cpl.'/mollieresponse',
	            'metadata'    => array(
	                'order_id' => $order->getOrderID()
	            )
		        )
			    );

        $opData = array();
        $opData[] = $order->getOrderID();
        $opData[] = $payment->id;

        $db = Database::connection();
        $db->Execute('insert into molStoreOrderTransactions (oID, pID) values (?,?)', $opData);

        return $payment->getPaymentUrl();
      }

      public static function validateCompletion(){
        //This function is called by mollie when the payment status is changed ... Not called on bank transfer
        $pkg = Package::getByHandle('community_store_mollie');
        $pkgconfig = $pkg->getConfig();

        $mollie = new \Mollie_API_Client;
				$mollie->setApiKey($pkgconfig->get('storemollie.apikey'));

        $db = Database::connection();
        $pData = array();
        $pData[] = $_POST['id'];
        $transactionDetails = $db->fetchAssoc('select * from molStoreOrderTransactions where pID=?',$pData);

        $order = StoreOrder::getByID($transactionDetails['oID']);
        $payment = $mollie->payments->get($_POST['id']);

        if ($payment->isPaid()){
          //Payment succesfull.
          // unset the shipping type, as next order might be unshippable
          $order->completeOrder($transactionDetails['pID']);

          Log::addEntry("Order with id: ".$transactionDetails['oID']." was paid. ");
        }else if ($payment->isOpen() || $payment->isPending()){
          //The order has not been cancelled, but hasn't been paid yet usually on bank transfer.
          //Other actions will be done on mollie redirect (see customerValidation method)
          //Adding a log entry .
          Log::addEntry("Order with id: ".$transactionDetails['oID']." as awaiting payment. ");
        }else if (!$payment->isOpen()){
          //No cancelled status and deleting an order is truly deleting instead of an active field.
          //Other actions will be done on mollie redirect (see customerValidation method)
          //Just placing a log entry on cancel...
          Log::addEntry("Order with id: ".$transactionDetails['oID']." was cancelled. ");
        }
      }

      public static function customerValidation($oID){
        //Mollie sends customers to this function when the transaction is completed
        //this will redirect the customer to the appropriate page
        //because people could just go to this URL
        $order = StoreOrder::getByID($oID);
        if(!empty($order)){
          $pkg = Package::getByHandle('community_store_mollie');
          $pkgconfig = $pkg->getConfig();

          $mollie = new \Mollie_API_Client;
  				$mollie->setApiKey($pkgconfig->get('storemollie.apikey'));

          $db = Database::connection();
          $pData = array();
          $pData[] = $oID;
          $transactionDetails = $db->fetchAssoc('select * from molStoreOrderTransactions where oID=?',$pData);


          $payment = $mollie->payments->get($transactionDetails['pID']);

          if ($payment->isPaid()){
            //Payment succesfull
            $order->completeOrder($transactionDetails['pID'], false);
            $order->completePayment(false);
            // unset the shipping type, as next order might be unshippable and clearing cart
            StoreDiscountCode::clearCartCode();
            \Session::set('community_store.smID', '');
            StoreCart::clear();

            //redirecting to complete single page
            $response = \Redirect::to('/checkout/complete');
            $response->send();
            die;
          }else if ($payment->isOpen() || $payment->isPending()){
            //The order has not been cancelled, but hasn't been paid yet usually on bank transfer.
            //Completing the order by setting transactionReference + updating status + emptying the cart + redirecting.
            //Mollie does not call the webhook when the status of a payment is not changed.
            StoreDiscountCode::clearCartCode();
            \Session::set('community_store.smID', '');
            StoreCart::clear();

            //Adding a log entry first because the validateCompletion is not called if the payment is still open.
            Log::addEntry("Order with id: ".$transactionDetails['oID']." as awaiting payment. ");

            //redirecting to complete single page
            $response = \Redirect::to('/checkout/complete');
            $response->send();
            die;
          }else if (!$payment->isOpen()){
            //No cancelled status and deleting an order is truly deleting instead of an active field.

            $cancelOrderStatus = $pkgconfig->get('storemollie.orderStatusOnCancel');
            if(!empty($cancelOrderStatus)){
              //set cancelled order status
              $order->updateStatus($cancelOrderStatus);
            }
            $order->setExternalPaymentRequested(null);
            $order->save();
            //redirecting to cart single page
            $response = \Redirect::to('/cart');
            $response->send();
            die;
          }
        }else{
          $response = \Redirect::to('/checkout');
          $response->send();
          die;
        }
      }

      public function isExternal() {
        return true;
      }

      public function submitPayment(){
        //nothing special
        return array('error'=>0, 'transactionReference'=>'');
      }

      public function getPaymentMinimum()
      {
          return 0.03;
      }

      public function getPaymentMaximum()
      {
          return 50000;
      }
  }
?>
