<?php
namespace Mw\Extension\Service;

use Magento\Sales\Api\Data\InvoiceInterface;

class InvoicePdfGeneratorService
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    private $invoicePdf;

    /**
     * PdfGeneratorService constructor.
     * @param \Magento\Sales\Model\Order\Pdf\Invoice $invoicePdf
     */
    public function __construct(\Magento\Sales\Model\Order\Pdf\Invoice $invoicePdf)
    {
        $this->invoicePdf = $invoicePdf;
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     * @return \Zend_Pdf
     */
    public function execute(InvoiceInterface $invoice)
    {
        return $this->invoicePdf->getPdf([$invoice]);
    }
}