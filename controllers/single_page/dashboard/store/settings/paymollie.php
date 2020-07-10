<?php

namespace Concrete\Package\CommunityStoreMollie\Controller\SinglePage\Dashboard\Store\Settings;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\RedirectResponse;
use Concrete\Package\CommunityStoreMollie\Src\Mollie\Method;

class Paymollie extends DashboardPageController
{
    public function view($status = null)
    {
        $this->set('status', $status);
        $this->set('methods', Method::getAll());
    }

    public function rescan()
    {
        Method::rescan();

        $response = new RedirectResponse('/dashboard/store/settings/paymollie/rescanned');
        $response->send();
    }
}
