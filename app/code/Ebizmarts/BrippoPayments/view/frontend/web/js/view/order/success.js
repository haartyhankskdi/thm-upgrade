require(['jquery', 'Magento_Customer/js/customer-data', 'domReady!.modman/brippo-payments/view/frontend/web/js/view/order/success'], function($, customerData) {
    const sections = ['cart'];

    customerData.reload(sections, true);
});