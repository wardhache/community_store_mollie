<?php

namespace Concrete\Package\CommunityStoreMollie\Src\CommunityStore\Payment\Methods\Mollie;

use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Routing\RedirectResponse;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStoreMollie\Src\Mollie\Method;
use Concrete\Package\CommunityStoreMollie\Src\Mollie\Order\Transaction;
use Mollie\Api\MollieApiClient;
use Session;
use URL;

class MolliePaymentMethod extends StorePaymentMethod
{
    public function dashboardForm()
    {
        $this->set('form', Application::getFacadeApplication()->make('helper/form'));
        $this->set('statusList', StoreOrderStatus::getList());
        $this->set('apiKey', Config::get('community_store.mollie.api_key'));
        $this->set('orderStatusOnCancel', Config::get('community_store.mollie.order_status_on_cancel'));
    }

    public function save(array $data = [])
    {
        $oldApiKey = Config::get('community_store.mollie.api_key');

        Config::save('community_store.mollie.order_status_on_cancel', $data['mollieOrderStatusOnCancel']);
        Config::save('community_store.mollie.api_key', $data['mollieApiKey']);

        if ($oldApiKey !== $data['mollieApiKey']) {
            Method::rescan();
        }
    }

    public function validate($args, $e)
    {
        $molliePaymentMethod = StorePaymentMethod::getByHandle('mollie');

        if ((bool)$args['paymentMethodEnabled'][$molliePaymentMethod->getID()] === false) {
            return $e;
        }

        if (empty($args['mollieApiKey']) || trim($args['mollieApiKey']) === '') {
            $e->add(t('Mollie API Key must be set'));
        }

        return $e;
    }

    public function checkoutForm()
    {
        $this->set('form', Application::getFacadeApplication()->make('helper/form'));
        $this->set('methods', Method::getAll());
    }

    public function getAction()
    {
        $mollie = new MollieApiClient();
        $mollie->setApiKey(Config::get('community_store.mollie.api_key'));

        $order = StoreOrder::getByID(Session::get('orderID'));
        $molliePaymentMethod = Session::get('molliePaymentMethod');

        $payment = $mollie->payments->create([
            'amount' => [
                'currency' => Config::get('community_store.currency'),
                'value' => number_format($order->getTotal(), 2, '.', ''),
            ],
            'description' => Config::get('concrete.site') . ' Order: ' . $order->getOrderID(),
            'redirectUrl' => (string)URL::to('/checkout/ordercompletion/' . $order->getOrderID()),
            'webhookUrl' => (string)URL::to('/checkout/mollieresponse'),
            'method' => $molliePaymentMethod,
        ]);

        Transaction::add($order, $payment->id);

        $response = new RedirectResponse($payment->getCheckoutUrl());

        return $response->send();
    }

    public static function validateCompletion()
    {
        $molliePaymentID = $_POST['id'];

        $transaction = Transaction::getByMolliePaymentID($molliePaymentID);
        $order = $transaction->getOrder();

        $mollie = new MollieApiClient();
        $mollie->setApiKey(Config::get('community_store.mollie.api_key'));
        $payment = $mollie->payments->get($molliePaymentID);

        if ($payment->isPaid()) {
            $order->completeOrder($transaction->getMolliePaymentID());
            $order->completePayment();
        }

        if ($payment->isCanceled()) {
            $order->updateStatus(Config::get('community_store.mollie.order_status_on_cancel'));
        }
    }

    public static function customerValidation($oID)
    {
        $section = Section::getDefaultSection();
        $languagePath = '';

        if ($sectionCollectionHandle = $section->getCollectionHandle()) {
            $languagePath = '/' . $sectionCollectionHandle;
        }

        $order = StoreOrder::getByID($oID);

        if (empty($order)) {
            $response = new RedirectResponse($languagePath . '/checkout');

            return $response->send();
        }

        $transaction = Transaction::getByOrder($order);

        $mollie = new MollieApiClient();
        $mollie->setApiKey(Config::get('community_store.mollie.api_key'));
        $payment = $mollie->payments->get($transaction->getMolliePaymentID());

        Session::set('molliePaymentMethod', '');

        if ($payment->isCanceled()) {
            $order->updateStatus(Config::get('community_store.mollie.order_status_on_cancel'));

            $response = new RedirectResponse($languagePath . '/cart');
            $response->send();
        }

        if ($payment->isOpen() || $payment->isPending()) {
            $order->completeOrder($transaction->getMolliePaymentID());
        }

        $response = new RedirectResponse($languagePath . '/checkout/complete');

        return $response->send();
    }

    public function isExternal()
    {
        return true;
    }

    public function markPaid()
    {
        return false;
    }
}
