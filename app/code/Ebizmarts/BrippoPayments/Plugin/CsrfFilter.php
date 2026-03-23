<?php 

namespace Ebizmarts\BrippoPayments\Plugin;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;

class CsrfFilter {

    public function filterCrsfInterfaceImplementation($controller) {
        if (interface_exists(CsrfAwareActionInterface::class)) {
            $request = $controller->getRequest();
            if ($request instanceof Http && $request->isPost()) {
                $request->setParam('isAjax', true);
                $request->getHeaders()->addHeaderLine('X_REQUESTED_WITH', 'XMLHttpRequest');
            }
        }
    }
}
