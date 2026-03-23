<?php
namespace Haartyhanks\InvoicePdfApi\Api;

interface CustomerInvoicePdfInterface
{
    /**
     * Get PDF invoice for customer
     *
     * @param int $invoiceId
     * @return string Base64 encoded PDF
     */
    public function getInvoicePdf($invoiceId);
}
