<?php

namespace Concrete\Package\CommunityStoreMollie;

/**
 * Community Store Mollie
 *
 * @author Jos De Berdt <www.josdeberdt.be>
 * @version 1.0.0
 * @package community_store_mollie
 * @github jozzeh
 */

use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single as SinglePage;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Support\Facade\Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderEvent;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Route;
use Whoops\Exception\ErrorException;

class controller extends Package
{
    protected $pkgHandle = 'community_store_mollie';
    protected $appVersionRequired = '8.2.1';
    protected $pkgVersion = '1.0.0';
    protected $paymentMethodName = 'Mollie';

    public function getPackageDescription()
    {
        return t("Mollie Payment Method for Community Store");
    }

    public function getPackageName()
    {
        return t("Mollie payment method");
    }

    protected $pkgAutoloaderRegistries = [
        'src/CommunityStore' => '\Concrete\Package\CommunityStoreMollie\Src\CommunityStore',
        'src/Mollie' => '\Concrete\Package\CommunityStoreMollie\Src\Mollie',
    ];

    public function on_start()
    {
        $this->registerRoutes();
        $this->setupAutoloader();

        Events::addListener(OrderEvent::ORDER_CREATED, function ($event) {
            /** @var Order $order */
            $order = $event->getOrder();

            if ($order->getPaymentMethodName() !== $this->paymentMethodName) {
                return;
            }

            Session::set('molliePaymentMethod', $_POST['molliePaymentMethod'] ?? null);
        });
    }

    public function install()
    {
        $installedPackages = $this->app->make(PackageService::class)->getInstalledHandles();

        if (!(is_array($installedPackages) && in_array('community_store', $installedPackages, true))) {
            throw new ErrorException(t('This package requires that Community Store be installed'));
        }

        parent::install();

        $this->installSinglePage();
        PaymentMethod::add('mollie', $this->paymentMethodName, $this);

        $orderStatus = StoreOrderStatus::getByHandle('nodelivery');
        if (is_object($orderStatus)) {
            Config::set('community_store.mollie.order_status_on_cancel', 'nodelivery');
        }
    }

    public function upgrade()
    {
        parent::upgrade();
        $this->installSinglePage();
    }

    public function uninstall()
    {
        if ($paymentMethod = PaymentMethod::getByHandle('mollie')) {
            $paymentMethod->delete();
        }

        parent::uninstall();
    }

    private function installSinglePage()
    {
        $page = Page::getByPath('/dashboard/store/settings/paymollie');

        if ($page->isError() || (!is_object($page))) {
            $page = SinglePage::add('/dashboard/store/settings/paymollie', $this);

            $page->update([
                'cName' => 'Mollie Payment'
            ]);
        }
    }

    private function registerRoutes()
    {
        Route::register(
            '/checkout/mollieresponse',
            '\Concrete\Package\CommunityStoreMollie\Src\CommunityStore\Payment\Methods\Mollie\MolliePaymentMethod::validateCompletion'
        );

        Route::register(
            '/checkout/ordercompletion/{oID}',
            '\Concrete\Package\CommunityStoreMollie\Src\CommunityStore\Payment\Methods\Mollie\MolliePaymentMethod::customerValidation',
        );
    }

    private function setupAutoloader()
    {
        if (file_exists($this->getPackagePath() . '/vendor')) {
            require_once $this->getPackagePath() . '/vendor/autoload.php';
        }
    }
}
