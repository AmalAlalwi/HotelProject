<?php

namespace App\Interfaces\User;

interface InvoiceInterface
{
    public function getUnpaidInvoices($request);
    public function getPaidInvoices($request);
    public function getPartialInvoices($request);
    public function showInvoice($id);
    public function downloadPDF($id);
    public function simulatePayment($request,$invoiceId);
}
