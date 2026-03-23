<?php
namespace Haartyhanks\InvoicePdfApi\Model;

use Haartyhanks\InvoicePdfApi\Api\CustomerInvoicePdfInterface;
use Magento\Sales\Model\Order\Pdf\Invoice as InvoicePdf;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class CustomerInvoicePdf implements CustomerInvoicePdfInterface
{
    protected $invoiceRepository;
    protected $invoicePdf;
    protected $customerSession;
    protected $filesystem;
    protected $storeManager;

    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        InvoicePdf $invoicePdf,
        CustomerSession $customerSession,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->invoicePdf = $invoicePdf;
        $this->customerSession = $customerSession;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
    }

    /**
     * Generate invoice PDF and return download URL
     *
     * @param int $invoiceId
     * @return array
     * @throws LocalizedException
     */
    public function getInvoicePdf($invoiceId)
    {
        $invoice = $this->invoiceRepository->get($invoiceId);

        $customerId = $this->customerSession->getCustomerId();
      
        $pdf = $this->invoicePdf->getPdf([$invoice]);
        $pdfContent = $pdf->render();

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="invoice_'.$invoiceId.'.pdf"');
    header('Content-Length: ' . strlen($pdfContent));

    echo $pdfContent;
    exit;
    }
}
